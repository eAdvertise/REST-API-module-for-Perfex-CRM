#!/usr/bin/env python3
"""Perform dependency-free structural checks on an OpenAPI document."""

from __future__ import annotations

import argparse
import json
import re
from pathlib import Path
from typing import Any

HTTP_METHODS = {"get", "post", "put", "patch", "delete", "options", "head", "trace"}
PATH_PARAMETER = re.compile(r"\{([^{}]+)\}")


def load(path: Path) -> dict[str, Any]:
    try:
        data = json.loads(path.read_text(encoding="utf-8"))
    except FileNotFoundError as exc:
        raise SystemExit(f"Missing OpenAPI file: {path}") from exc
    except json.JSONDecodeError as exc:
        raise SystemExit(f"Invalid JSON in {path}: {exc}") from exc
    if not isinstance(data, dict):
        raise SystemExit("OpenAPI root must be an object")
    return data


def internal_ref_exists(document: dict[str, Any], ref: str) -> bool:
    if not ref.startswith("#/"):
        return True
    current: Any = document
    for raw_part in ref[2:].split("/"):
        part = raw_part.replace("~1", "/").replace("~0", "~")
        if not isinstance(current, dict) or part not in current:
            return False
        current = current[part]
    return True


def walk_refs(value: Any, location: str = "$") -> list[tuple[str, str]]:
    found: list[tuple[str, str]] = []
    if isinstance(value, dict):
        for key, child in value.items():
            child_location = f"{location}.{key}"
            if key == "$ref" and isinstance(child, str):
                found.append((child_location, child))
            else:
                found.extend(walk_refs(child, child_location))
    elif isinstance(value, list):
        for index, child in enumerate(value):
            found.extend(walk_refs(child, f"{location}[{index}]"))
    return found


def main() -> None:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("path", type=Path)
    args = parser.parse_args()
    document = load(args.path)

    errors: list[str] = []
    warnings: list[str] = []

    if not isinstance(document.get("openapi"), str):
        errors.append("Missing string field: openapi")
    if not isinstance(document.get("info"), dict):
        errors.append("Missing object field: info")
    else:
        for field in ("title", "version"):
            if not document["info"].get(field):
                errors.append(f"Missing info.{field}")
    if not isinstance(document.get("paths"), dict):
        errors.append("Missing object field: paths")

    operation_ids: dict[str, str] = {}
    operation_total = 0

    for route, path_item in document.get("paths", {}).items():
        if not isinstance(route, str) or not route.startswith("/"):
            errors.append(f"Invalid path key: {route!r}")
            continue
        if not isinstance(path_item, dict):
            errors.append(f"Path item must be an object: {route}")
            continue

        common_parameters = path_item.get("parameters", [])
        for method, operation in path_item.items():
            if method.lower() not in HTTP_METHODS:
                continue
            operation_total += 1
            label = f"{method.upper()} {route}"
            if not isinstance(operation, dict):
                errors.append(f"Operation must be an object: {label}")
                continue
            if not operation.get("summary") and not operation.get("description"):
                warnings.append(f"No summary or description: {label}")
            if not isinstance(operation.get("responses"), dict) or not operation.get("responses"):
                errors.append(f"Missing responses: {label}")

            operation_id = operation.get("operationId")
            if operation_id:
                if operation_id in operation_ids:
                    errors.append(f"Duplicate operationId {operation_id!r}: {operation_ids[operation_id]} and {label}")
                else:
                    operation_ids[operation_id] = label

            parameters = [*common_parameters, *operation.get("parameters", [])]
            defined_path_parameters = {
                parameter.get("name")
                for parameter in parameters
                if isinstance(parameter, dict) and parameter.get("in") == "path"
            }
            for name in PATH_PARAMETER.findall(route):
                if name not in defined_path_parameters:
                    warnings.append(f"Path parameter {{{name}}} is not declared: {label}")

    for location, ref in walk_refs(document):
        if not internal_ref_exists(document, ref):
            errors.append(f"Broken internal reference at {location}: {ref}")

    for warning in warnings:
        print(f"WARNING: {warning}")
    for error in errors:
        print(f"ERROR: {error}")

    print(
        f"Checked {len(document.get('paths', {}))} paths and {operation_total} operations: "
        f"{len(errors)} error(s), {len(warnings)} warning(s)."
    )
    if errors:
        raise SystemExit(1)


if __name__ == "__main__":
    main()
