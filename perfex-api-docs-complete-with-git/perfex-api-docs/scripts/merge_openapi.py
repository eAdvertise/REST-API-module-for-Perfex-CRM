#!/usr/bin/env python3
"""Merge the immutable vendor OpenAPI document with custom additions."""

from __future__ import annotations

import argparse
import json
from copy import deepcopy
from pathlib import Path
from typing import Any

ROOT = Path(__file__).resolve().parents[1]
DEFAULT_VENDOR = ROOT / "openapi" / "vendor" / "eAD-CRM.openapi.json"
DEFAULT_CUSTOM_DIR = ROOT / "openapi" / "custom"
DEFAULT_OUTPUT = ROOT / "openapi" / "combined.openapi.json"
SITE_CONFIG = ROOT / "config" / "site.json"
HTTP_METHODS = {"get", "post", "put", "patch", "delete", "options", "head", "trace"}


def load_json(path: Path) -> dict[str, Any]:
    try:
        data = json.loads(path.read_text(encoding="utf-8"))
    except FileNotFoundError as exc:
        raise SystemExit(f"Missing JSON file: {path}") from exc
    except json.JSONDecodeError as exc:
        raise SystemExit(f"Invalid JSON in {path}: {exc}") from exc
    if not isinstance(data, dict):
        raise SystemExit(f"Expected an object in {path}")
    return data


def empty_spec(site: dict[str, Any]) -> dict[str, Any]:
    return {
        "openapi": "3.0.3",
        "info": {
            "title": site.get("site_name", "API Documentation"),
            "version": "0.1.0",
            "description": site.get("site_description", ""),
        },
        "servers": [{"url": site.get("default_api_server", "https://crm.example.com/api")}],
        "security": [{"authtoken": []}],
        "paths": {},
        "components": {
            "securitySchemes": {
                "authtoken": {
                    "type": "apiKey",
                    "in": "header",
                    "name": "authtoken",
                    "description": "API token sent with every request.",
                }
            }
        },
    }


def merge_named_list(target: list[Any], incoming: list[Any], key: str = "name") -> list[Any]:
    result: list[Any] = []
    positions: dict[Any, int] = {}
    for value in [*target, *incoming]:
        identity = value.get(key) if isinstance(value, dict) else value
        if identity in positions:
            result[positions[identity]] = deepcopy(value)
        else:
            positions[identity] = len(result)
            result.append(deepcopy(value))
    return result


def merge_components(target: dict[str, Any], incoming: dict[str, Any], source: Path) -> None:
    for section, entries in incoming.items():
        if not isinstance(entries, dict):
            raise SystemExit(f"components.{section} in {source} must be an object")
        section_target = target.setdefault(section, {})
        if not isinstance(section_target, dict):
            raise SystemExit(f"Existing components.{section} is not an object")
        for name, value in entries.items():
            if name in section_target:
                raise SystemExit(f"Component conflict in {source}: components.{section}.{name}")
            section_target[name] = deepcopy(value)


def merge_paths(target: dict[str, Any], incoming: dict[str, Any], source: Path, allow_overrides: bool) -> None:
    for route, path_item in incoming.items():
        if not isinstance(path_item, dict):
            raise SystemExit(f"Path item {route} in {source} must be an object")
        if route not in target:
            target[route] = deepcopy(path_item)
            continue

        existing = target[route]
        if not isinstance(existing, dict):
            raise SystemExit(f"Existing path {route} is not an object")

        for key, value in path_item.items():
            normalized = key.lower()
            if key not in existing:
                existing[key] = deepcopy(value)
                continue
            if normalized in HTTP_METHODS and not allow_overrides:
                raise SystemExit(
                    f"Operation conflict in {source}: {normalized.upper()} {route}. "
                    "Rename it or rerun with --allow-overrides."
                )
            if key == "parameters" and isinstance(existing[key], list) and isinstance(value, list):
                existing[key] = [*existing[key], *deepcopy(value)]
            elif allow_overrides:
                existing[key] = deepcopy(value)
            elif existing[key] != value:
                raise SystemExit(f"Path-level conflict in {source}: {route} -> {key}")


def merge_document(base: dict[str, Any], incoming: dict[str, Any], source: Path, allow_overrides: bool) -> None:
    if incoming.get("openapi") and not base.get("openapi"):
        base["openapi"] = incoming["openapi"]

    if isinstance(incoming.get("tags"), list):
        base["tags"] = merge_named_list(base.get("tags", []), incoming["tags"])

    if isinstance(incoming.get("servers"), list) and incoming["servers"]:
        base["servers"] = merge_named_list(base.get("servers", []), incoming["servers"], key="url")

    if isinstance(incoming.get("security"), list):
        base["security"] = deepcopy(incoming["security"])

    if isinstance(incoming.get("paths"), dict):
        merge_paths(base.setdefault("paths", {}), incoming["paths"], source, allow_overrides)

    if isinstance(incoming.get("components"), dict):
        merge_components(base.setdefault("components", {}), incoming["components"], source)

    if isinstance(incoming.get("webhooks"), dict):
        webhooks = base.setdefault("webhooks", {})
        for name, value in incoming["webhooks"].items():
            if name in webhooks and not allow_overrides:
                raise SystemExit(f"Webhook conflict in {source}: {name}")
            webhooks[name] = deepcopy(value)

    for extension, value in incoming.items():
        if extension.startswith("x-"):
            base[extension] = deepcopy(value)


def operation_count(spec: dict[str, Any]) -> int:
    return sum(
        1
        for item in spec.get("paths", {}).values()
        if isinstance(item, dict)
        for method in item
        if method.lower() in HTTP_METHODS
    )


def main() -> None:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--vendor", type=Path, default=DEFAULT_VENDOR)
    parser.add_argument("--custom-dir", type=Path, default=DEFAULT_CUSTOM_DIR)
    parser.add_argument("--output", type=Path, default=DEFAULT_OUTPUT)
    parser.add_argument("--allow-overrides", action="store_true")
    args = parser.parse_args()

    site = load_json(SITE_CONFIG)
    if args.vendor.exists():
        merged = load_json(args.vendor)
        vendor_state = str(args.vendor.relative_to(ROOT)) if args.vendor.is_relative_to(ROOT) else str(args.vendor)
    else:
        merged = empty_spec(site)
        vendor_state = "not imported; custom-only build"

    custom_files = sorted(args.custom_dir.glob("*.json"))
    for custom_file in custom_files:
        merge_document(merged, load_json(custom_file), custom_file, args.allow_overrides)

    merged.setdefault("info", {})["title"] = site.get("site_name", merged.get("info", {}).get("title", "API Documentation"))
    if site.get("site_description"):
        merged["info"]["description"] = site["site_description"]
    merged["x-docs-vendor-state"] = vendor_state

    args.output.parent.mkdir(parents=True, exist_ok=True)
    args.output.write_text(json.dumps(merged, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")

    print(
        f"Generated {args.output}: {len(merged.get('paths', {}))} paths, "
        f"{operation_count(merged)} operations, {len(custom_files)} custom document(s)."
    )


if __name__ == "__main__":
    main()
