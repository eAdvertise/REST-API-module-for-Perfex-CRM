#!/usr/bin/env python3
"""Generate a dependency-light, self-hosted static API documentation site."""

from __future__ import annotations

import html
import json
import re
import shutil
from collections import defaultdict
from dataclasses import dataclass
from pathlib import Path, PurePosixPath
from typing import Any, Iterable
from urllib.parse import quote

try:
    import mistune
    from jinja2 import Environment, FileSystemLoader, select_autoescape
    from markupsafe import Markup
except ImportError as exc:
    raise SystemExit("Install build dependencies first: pip install -r requirements.txt") from exc

ROOT = Path(__file__).resolve().parents[1]
CONFIG_PATH = ROOT / "config" / "site.json"
SPEC_PATH = ROOT / "openapi" / "combined.openapi.json"
DOCS_DIR = ROOT / "docs"
TEMPLATES_DIR = ROOT / "templates"
ASSETS_DIR = ROOT / "assets"
EXTRAS_DIR = ROOT / "extras"
OUTPUT_DIR = ROOT / "site"
HTTP_METHODS = ("get", "post", "put", "patch", "delete", "options", "head", "trace")
METHOD_ORDER = {method: index for index, method in enumerate(HTTP_METHODS)}


@dataclass(frozen=True)
class Guide:
    source: Path
    title: str
    url: str
    section: str
    description: str


@dataclass(frozen=True)
class Operation:
    method: str
    path: str
    tag: str
    summary: str
    description: str
    operation_id: str
    slug: str
    url: str
    raw: dict[str, Any]
    path_item: dict[str, Any]


def load_json(path: Path) -> dict[str, Any]:
    try:
        value = json.loads(path.read_text(encoding="utf-8"))
    except FileNotFoundError as exc:
        raise SystemExit(f"Missing required file: {path}") from exc
    except json.JSONDecodeError as exc:
        raise SystemExit(f"Invalid JSON in {path}: {exc}") from exc
    if not isinstance(value, dict):
        raise SystemExit(f"Expected a JSON object in {path}")
    return value


def slugify(value: str) -> str:
    value = value.strip().lower()
    value = re.sub(r"[^a-z0-9]+", "-", value)
    value = value.strip("-")
    return value or "item"


def compact_text(value: str, limit: int = 180) -> str:
    clean = re.sub(r"\s+", " ", re.sub(r"[`*_>#]", "", value or "")).strip()
    return clean if len(clean) <= limit else clean[: limit - 1].rstrip() + "…"


def first_heading(markdown_text: str, fallback: str) -> str:
    for line in markdown_text.splitlines():
        match = re.match(r"^#\s+(.+?)\s*$", line)
        if match:
            return re.sub(r"[`*_]", "", match.group(1)).strip()
    return fallback


def first_paragraph(markdown_text: str) -> str:
    in_code = False
    paragraphs: list[str] = []
    for line in markdown_text.splitlines():
        stripped = line.strip()
        if stripped.startswith("```"):
            in_code = not in_code
            continue
        if in_code or not stripped or stripped.startswith(("#", "|", ">", "- ", "* ", "1. ")):
            if paragraphs:
                break
            continue
        paragraphs.append(stripped)
    return compact_text(" ".join(paragraphs))


def prefix_url(config: dict[str, Any], path: str) -> str:
    base = str(config.get("base_path", "")).strip()
    if base and not base.startswith("/"):
        base = "/" + base
    base = base.rstrip("/")
    normalized = "/" + path.lstrip("/")
    return base + normalized if base else normalized


def output_path_for_url(url: str, config: dict[str, Any]) -> Path:
    base = str(config.get("base_path", "")).strip("/")
    clean = url.split("?", 1)[0].split("#", 1)[0].strip("/")
    if base and clean.startswith(base + "/"):
        clean = clean[len(base) + 1 :]
    elif base and clean == base:
        clean = ""
    if not clean:
        return OUTPUT_DIR / "index.html"
    return OUTPUT_DIR / clean / "index.html"


def resolve_pointer(document: dict[str, Any], ref: str) -> Any:
    if not ref.startswith("#/"):
        return {"$ref": ref}
    current: Any = document
    for raw_part in ref[2:].split("/"):
        part = raw_part.replace("~1", "/").replace("~0", "~")
        if not isinstance(current, dict) or part not in current:
            return {"$ref": ref}
        current = current[part]
    return current


def resolve_object(document: dict[str, Any], value: Any) -> Any:
    if isinstance(value, dict) and isinstance(value.get("$ref"), str):
        resolved = resolve_pointer(document, value["$ref"])
        if isinstance(resolved, dict):
            merged = dict(resolved)
            merged.update({key: child for key, child in value.items() if key != "$ref"})
            return merged
    return value


def schema_label(document: dict[str, Any], schema: Any, depth: int = 0) -> str:
    if depth > 6:
        return "object"
    schema = resolve_object(document, schema)
    if not isinstance(schema, dict):
        return "any"
    if "oneOf" in schema:
        return " | ".join(schema_label(document, item, depth + 1) for item in schema["oneOf"][:4])
    if "anyOf" in schema:
        return " | ".join(schema_label(document, item, depth + 1) for item in schema["anyOf"][:4])
    if "allOf" in schema:
        return " + ".join(schema_label(document, item, depth + 1) for item in schema["allOf"][:4])
    type_name = schema.get("type")
    if not type_name and "properties" in schema:
        type_name = "object"
    if type_name == "array":
        return f"array<{schema_label(document, schema.get('items', {}), depth + 1)}>"
    if isinstance(schema.get("enum"), list):
        enum = ", ".join(map(str, schema["enum"][:5]))
        return f"{type_name or 'string'} ({enum})"
    if schema.get("format"):
        return f"{type_name or 'string'}:{schema['format']}"
    return str(type_name or "any")


def sample_from_schema(document: dict[str, Any], schema: Any, seen: set[str] | None = None, depth: int = 0) -> Any:
    if depth > 7:
        return None
    seen = set() if seen is None else set(seen)
    if isinstance(schema, dict) and isinstance(schema.get("$ref"), str):
        ref = schema["$ref"]
        if ref in seen:
            return None
        seen.add(ref)
    schema = resolve_object(document, schema)
    if not isinstance(schema, dict):
        return None
    if "example" in schema:
        return schema["example"]
    if "default" in schema:
        return schema["default"]
    if isinstance(schema.get("enum"), list) and schema["enum"]:
        return schema["enum"][0]
    if isinstance(schema.get("oneOf"), list) and schema["oneOf"]:
        return sample_from_schema(document, schema["oneOf"][0], seen, depth + 1)
    if isinstance(schema.get("anyOf"), list) and schema["anyOf"]:
        return sample_from_schema(document, schema["anyOf"][0], seen, depth + 1)
    if isinstance(schema.get("allOf"), list):
        merged: dict[str, Any] = {}
        for child in schema["allOf"]:
            value = sample_from_schema(document, child, seen, depth + 1)
            if isinstance(value, dict):
                merged.update(value)
        return merged

    type_name = schema.get("type")
    if type_name == "object" or "properties" in schema:
        result: dict[str, Any] = {}
        for name, child in schema.get("properties", {}).items():
            value = sample_from_schema(document, child, seen, depth + 1)
            if value is not None:
                result[name] = value
        return result
    if type_name == "array":
        value = sample_from_schema(document, schema.get("items", {}), seen, depth + 1)
        return [] if value is None else [value]
    if type_name == "integer":
        return schema.get("minimum", 1)
    if type_name == "number":
        return schema.get("minimum", 0.0)
    if type_name == "boolean":
        return True
    if type_name == "string" or not type_name:
        formats = {
            "date": "2026-08-06",
            "date-time": "2026-08-06T12:00:00Z",
            "email": "user@example.com",
            "uri": "https://example.com/resource",
            "uuid": "550e8400-e29b-41d4-a716-446655440000",
            "binary": "<binary>",
        }
        return formats.get(schema.get("format"), "string")
    return None


def pretty_json(value: Any) -> str:
    return json.dumps(value, ensure_ascii=False, indent=2)


def flatten_schema_fields(document: dict[str, Any], schema: Any) -> list[dict[str, Any]]:
    schema = resolve_object(document, schema)
    if not isinstance(schema, dict):
        return []
    properties = schema.get("properties", {})
    required = set(schema.get("required", []))
    if not isinstance(properties, dict):
        return []
    fields: list[dict[str, Any]] = []
    for name, child in properties.items():
        child = resolve_object(document, child)
        fields.append(
            {
                "name": name,
                "type": schema_label(document, child),
                "required": name in required,
                "description": child.get("description", "") if isinstance(child, dict) else "",
                "example": sample_from_schema(document, child),
            }
        )
    return fields


def collect_security_names(document: dict[str, Any], operation: dict[str, Any]) -> list[str]:
    security = operation.get("security", document.get("security", []))
    names: list[str] = []
    if isinstance(security, list):
        for requirement in security:
            if isinstance(requirement, dict):
                names.extend(str(name) for name in requirement)
    return list(dict.fromkeys(names))


def collect_parameters(document: dict[str, Any], operation: Operation) -> list[dict[str, Any]]:
    values: list[Any] = []
    path_parameters = operation.path_item.get("parameters", [])
    operation_parameters = operation.raw.get("parameters", [])
    if isinstance(path_parameters, list):
        values.extend(path_parameters)
    if isinstance(operation_parameters, list):
        values.extend(operation_parameters)

    rows: list[dict[str, Any]] = []
    seen: set[tuple[str, str]] = set()
    for value in values:
        parameter = resolve_object(document, value)
        if not isinstance(parameter, dict):
            continue
        identity = (str(parameter.get("name", "")), str(parameter.get("in", "")))
        if identity in seen:
            continue
        seen.add(identity)
        schema = parameter.get("schema", {})
        rows.append(
            {
                "name": parameter.get("name", ""),
                "location": parameter.get("in", ""),
                "required": bool(parameter.get("required")),
                "description": parameter.get("description", ""),
                "type": schema_label(document, schema),
                "example": parameter.get("example", sample_from_schema(document, schema)),
            }
        )
    return rows


def collect_request_bodies(document: dict[str, Any], operation: Operation) -> list[dict[str, Any]]:
    body = resolve_object(document, operation.raw.get("requestBody", {}))
    if not isinstance(body, dict):
        return []
    content = body.get("content", {})
    if not isinstance(content, dict):
        return []
    rows: list[dict[str, Any]] = []
    for media_type, media in content.items():
        media = resolve_object(document, media)
        if not isinstance(media, dict):
            continue
        schema = media.get("schema", {})
        example = media.get("example")
        if example is None and isinstance(media.get("examples"), dict):
            first = next(iter(media["examples"].values()), None)
            first = resolve_object(document, first)
            if isinstance(first, dict):
                example = first.get("value")
        if example is None:
            example = sample_from_schema(document, schema)
        rows.append(
            {
                "media_type": media_type,
                "required": bool(body.get("required")),
                "description": body.get("description", ""),
                "type": schema_label(document, schema),
                "fields": flatten_schema_fields(document, schema),
                "example": pretty_json(example) if example is not None else "",
                "raw_example": example,
            }
        )
    return rows


def collect_responses(document: dict[str, Any], operation: Operation) -> list[dict[str, Any]]:
    responses = operation.raw.get("responses", {})
    if not isinstance(responses, dict):
        return []
    rows: list[dict[str, Any]] = []
    for status, raw_response in responses.items():
        response = resolve_object(document, raw_response)
        if not isinstance(response, dict):
            continue
        examples: list[dict[str, str]] = []
        content = response.get("content", {})
        if isinstance(content, dict):
            for media_type, media in content.items():
                media = resolve_object(document, media)
                if not isinstance(media, dict):
                    continue
                example = media.get("example")
                if example is None and isinstance(media.get("examples"), dict):
                    first = next(iter(media["examples"].values()), None)
                    first = resolve_object(document, first)
                    if isinstance(first, dict):
                        example = first.get("value")
                if example is None:
                    example = sample_from_schema(document, media.get("schema", {}))
                if example is not None:
                    examples.append({"media_type": media_type, "value": pretty_json(example)})
        rows.append(
            {
                "status": str(status),
                "description": response.get("description", ""),
                "examples": examples,
                "headers": response.get("headers", {}),
            }
        )
    return rows


def curl_example(document: dict[str, Any], operation: Operation, parameters: list[dict[str, Any]], bodies: list[dict[str, Any]], config: dict[str, Any]) -> str:
    servers = document.get("servers", [])
    server = config.get("default_api_server", "https://crm.example.com/api")
    if isinstance(servers, list) and servers and isinstance(servers[0], dict):
        candidate = servers[0].get("url")
        if candidate and "yourdomain.com" not in candidate:
            server = candidate
    path = operation.path
    for parameter in parameters:
        if parameter["location"] == "path":
            value = parameter["example"] if parameter["example"] is not None else 1
            path = path.replace("{" + str(parameter["name"]) + "}", quote(str(value)))
    query: list[str] = []
    for parameter in parameters:
        if parameter["location"] == "query" and parameter["required"]:
            value = parameter["example"] if parameter["example"] is not None else "value"
            query.append(f"{quote(str(parameter['name']))}={quote(str(value))}")
    url = str(server).rstrip("/") + "/" + path.lstrip("/")
    if query:
        url += "?" + "&".join(query)

    parts = [f'curl -X {operation.method.upper()} "{url}"']
    security_names = collect_security_names(document, operation.raw)
    if security_names:
        schemes = document.get("components", {}).get("securitySchemes", {})
        added_auth = False
        for name in security_names:
            scheme = resolve_object(document, schemes.get(name, {})) if isinstance(schemes, dict) else {}
            if isinstance(scheme, dict) and scheme.get("in") == "header":
                header_name = scheme.get("name", name)
                parts.append(f'  -H "{header_name}: YOUR_API_TOKEN"')
                added_auth = True
                break
        if not added_auth:
            parts.append('  -H "authtoken: YOUR_API_TOKEN"')

    if bodies:
        body = bodies[0]
        if body["media_type"] == "multipart/form-data":
            for field in body["fields"][:8]:
                value = field["example"]
                if isinstance(value, (dict, list)):
                    value = json.dumps(value, ensure_ascii=False)
                if value is None:
                    value = "value"
                parts.append(f'  -F "{field["name"]}={value}"')
        else:
            parts.append(f'  -H "Content-Type: {body["media_type"]}"')
            if body["raw_example"] is not None:
                compact = json.dumps(body["raw_example"], ensure_ascii=False)
                parts.append(f"  -d '{compact}'")

    return " \\\n".join(parts)


def operation_slug(method: str, route: str, operation_id: str) -> str:
    if operation_id:
        return slugify(operation_id)
    route_slug = route.replace("{", "").replace("}", "")
    return slugify(f"{method}-{route_slug}")


def collect_operations(document: dict[str, Any], config: dict[str, Any]) -> list[Operation]:
    operations: list[Operation] = []
    used_slugs: set[str] = set()
    paths = document.get("paths", {})
    if not isinstance(paths, dict):
        return operations
    for route, path_item in paths.items():
        if not isinstance(path_item, dict):
            continue
        for method in HTTP_METHODS:
            raw = path_item.get(method)
            if not isinstance(raw, dict):
                continue
            tag = str(next(iter(raw.get("tags", [])), "Other"))
            summary = str(raw.get("summary") or raw.get("operationId") or f"{method.upper()} {route}")
            operation_id = str(raw.get("operationId", ""))
            slug = operation_slug(method, route, operation_id)
            original_slug = slug
            suffix = 2
            while slug in used_slugs:
                slug = f"{original_slug}-{suffix}"
                suffix += 1
            used_slugs.add(slug)
            url = prefix_url(config, f"reference/{slug}/")
            operations.append(
                Operation(
                    method=method,
                    path=route,
                    tag=tag,
                    summary=summary,
                    description=str(raw.get("description", "")),
                    operation_id=operation_id,
                    slug=slug,
                    url=url,
                    raw=raw,
                    path_item=path_item,
                )
            )
    return sorted(operations, key=lambda item: (item.tag.lower(), item.path, METHOD_ORDER[item.method]))


def guide_url_from_source(source: Path, config: dict[str, Any]) -> str:
    relative = source.relative_to(DOCS_DIR).with_suffix("")
    parts = [slugify(part) for part in relative.parts]
    if parts == ["index"]:
        return prefix_url(config, "guides/overview/")
    return prefix_url(config, "guides/" + "/".join(parts) + "/")


def collect_guides(config: dict[str, Any]) -> list[Guide]:
    guides: list[Guide] = []

    def source_order(source: Path) -> tuple[int, str]:
        relative = source.relative_to(DOCS_DIR).as_posix()
        if relative == "index.md":
            return (0, relative)
        if relative == "custom/getting-started.md":
            return (1, relative)
        if relative.startswith("custom/"):
            return (2, relative)
        if relative.startswith("vendor/"):
            return (3, relative)
        return (4, relative)

    for source in sorted(DOCS_DIR.rglob("*.md"), key=source_order):
        text = source.read_text(encoding="utf-8")
        relative = source.relative_to(DOCS_DIR)
        fallback = relative.stem.replace("-", " ").title()
        section = relative.parts[0].replace("-", " ").title() if len(relative.parts) > 1 else "Overview"
        guides.append(
            Guide(
                source=source,
                title=first_heading(text, fallback),
                url=guide_url_from_source(source, config),
                section=section,
                description=first_paragraph(text),
            )
        )
    return guides


def rewrite_markdown_links(text: str, source: Path, guide_by_source: dict[Path, Guide], config: dict[str, Any]) -> str:
    pattern = re.compile(r"\]\(([^)\s]+\.md)(#[^)]+)?\)")

    def replace(match: re.Match[str]) -> str:
        raw_target = match.group(1)
        anchor = match.group(2) or ""
        target = (source.parent / raw_target).resolve()
        guide = guide_by_source.get(target)
        if guide:
            return f"]({guide.url}{anchor})"
        return match.group(0)

    text = pattern.sub(replace, text)
    upstream_blob = "https://github.com/themesic/perfex-rest-api-examples/blob/main/"

    def replace_snippet(match: re.Match[str]) -> str:
        raw_target = match.group(1)
        normalized = str(PurePosixPath("docs") / PurePosixPath(raw_target)).replace("docs/../", "")
        local = ROOT / "extras" / normalized.replace("snippets/", "snippets/", 1)
        if local.exists():
            return f"]({prefix_url(config, 'extras/' + normalized)}{match.group(2) or ''})"
        return f"]({upstream_blob}{normalized}{match.group(2) or ''})"

    text = re.sub(r"\]\((\.\./(?:snippets|postman)/[^)\s]+)(#[^)]+)?\)", replace_snippet, text)
    return text


def create_markdown_renderer() -> Any:
    plugins = ["strikethrough", "table", "url", "task_lists"]
    try:
        return mistune.create_markdown(escape=False, plugins=plugins)
    except Exception:
        return mistune.create_markdown(escape=False)


def group_operations(operations: Iterable[Operation]) -> list[dict[str, Any]]:
    grouped: dict[str, list[Operation]] = defaultdict(list)
    for operation in operations:
        grouped[operation.tag].append(operation)
    return [
        {"name": tag, "slug": slugify(tag), "operations": values}
        for tag, values in sorted(grouped.items(), key=lambda item: item[0].lower())
    ]


def write_rendered(url: str, content: str, config: dict[str, Any]) -> None:
    destination = output_path_for_url(url, config)
    destination.parent.mkdir(parents=True, exist_ok=True)
    destination.write_text(content, encoding="utf-8")


def main() -> None:
    config = load_json(CONFIG_PATH)
    document = load_json(SPEC_PATH)
    operations = collect_operations(document, config)
    operation_groups = group_operations(operations)
    guides = collect_guides(config)
    guide_by_source = {guide.source.resolve(): guide for guide in guides}

    if OUTPUT_DIR.exists():
        shutil.rmtree(OUTPUT_DIR)
    OUTPUT_DIR.mkdir(parents=True)
    shutil.copytree(ASSETS_DIR, OUTPUT_DIR / "assets", dirs_exist_ok=True)
    if EXTRAS_DIR.exists():
        shutil.copytree(EXTRAS_DIR, OUTPUT_DIR / "extras", dirs_exist_ok=True)

    markdown_renderer = create_markdown_renderer()
    environment = Environment(
        loader=FileSystemLoader(str(TEMPLATES_DIR)),
        autoescape=select_autoescape(["html", "xml"]),
        trim_blocks=True,
        lstrip_blocks=True,
    )
    environment.filters["json_pretty"] = pretty_json
    environment.filters["markdown"] = lambda value: Markup(markdown_renderer(str(value or "")))
    environment.globals.update(
        site=config,
        url=lambda path: prefix_url(config, path),
        nav_guides=guides,
        nav_groups=operation_groups,
    )

    stats = {
        "paths": len(document.get("paths", {})),
        "operations": len(operations),
        "groups": len(operation_groups),
        "guides": len(guides),
        "api_version": document.get("info", {}).get("version", ""),
        "openapi_version": document.get("openapi", ""),
    }
    vendor_state = str(document.get("x-docs-vendor-state", ""))
    vendor_imported = "not imported" not in vendor_state

    home_template = environment.get_template("home.html")
    home_html = home_template.render(
        page_title=config.get("site_name"),
        page_description=config.get("site_description", ""),
        current_url=prefix_url(config, "/"),
        stats=stats,
        featured_guides=guides[:8],
        operation_groups=operation_groups,
        vendor_imported=vendor_imported,
        vendor_state=vendor_state,
    )
    write_rendered(prefix_url(config, "/"), home_html, config)

    reference_template = environment.get_template("reference_index.html")
    reference_html = reference_template.render(
        page_title="API Reference",
        page_description=f"{len(operations)} API operations grouped by resource.",
        current_url=prefix_url(config, "reference/"),
        stats=stats,
        operation_groups=operation_groups,
        vendor_imported=vendor_imported,
    )
    write_rendered(prefix_url(config, "reference/"), reference_html, config)

    operation_template = environment.get_template("operation.html")
    for operation in operations:
        parameters = collect_parameters(document, operation)
        request_bodies = collect_request_bodies(document, operation)
        responses = collect_responses(document, operation)
        rendered = operation_template.render(
            page_title=operation.summary,
            page_description=compact_text(operation.description or f"{operation.method.upper()} {operation.path}"),
            current_url=operation.url,
            operation=operation,
            parameters=parameters,
            request_bodies=request_bodies,
            responses=responses,
            security_names=collect_security_names(document, operation.raw),
            curl=curl_example(document, operation, parameters, request_bodies, config),
            vendor_imported=vendor_imported,
        )
        write_rendered(operation.url, rendered, config)

    guide_template = environment.get_template("guide.html")
    for guide in guides:
        source_text = guide.source.read_text(encoding="utf-8")
        source_text = rewrite_markdown_links(source_text, guide.source, guide_by_source, config)
        guide_html = markdown_renderer(source_text)
        rendered = guide_template.render(
            page_title=guide.title,
            page_description=guide.description,
            current_url=guide.url,
            guide=guide,
            guide_html=guide_html,
        )
        write_rendered(guide.url, rendered, config)

    search_entries: list[dict[str, Any]] = []
    for guide in guides:
        text = guide.source.read_text(encoding="utf-8")
        search_entries.append(
            {
                "type": "guide",
                "title": guide.title,
                "subtitle": guide.section,
                "url": guide.url,
                "text": compact_text(text, 900),
            }
        )
    for operation in operations:
        search_entries.append(
            {
                "type": "operation",
                "title": operation.summary,
                "subtitle": f"{operation.method.upper()} {operation.path} · {operation.tag}",
                "url": operation.url,
                "method": operation.method.upper(),
                "text": compact_text(operation.description, 900),
            }
        )
    (OUTPUT_DIR / "assets" / "search-index.json").write_text(
        json.dumps(search_entries, ensure_ascii=False, separators=(",", ":")),
        encoding="utf-8",
    )

    spec_output = OUTPUT_DIR / "downloads" / "combined.openapi.json"
    spec_output.parent.mkdir(parents=True, exist_ok=True)
    shutil.copy2(SPEC_PATH, spec_output)

    print(
        f"Built {OUTPUT_DIR}: {len(guides)} guide(s), {len(operations)} operation page(s), "
        f"{len(search_entries)} search entries."
    )


if __name__ == "__main__":
    main()
