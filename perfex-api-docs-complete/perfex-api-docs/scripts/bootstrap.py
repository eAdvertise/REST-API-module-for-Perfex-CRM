#!/usr/bin/env python3
"""Import the upstream eAD-CRM API reference without modifying custom files."""

from __future__ import annotations

import argparse
import json
import os
import shutil
import subprocess
import sys
import tempfile
import urllib.error
import urllib.request
from datetime import datetime, timezone
from pathlib import Path
from typing import Any

ROOT = Path(__file__).resolve().parents[1]
UPSTREAM_CONFIG = ROOT / "config" / "upstream.json"
VENDOR_REPO = ROOT / "vendor" / "upstream"
VENDOR_SPEC = ROOT / "openapi" / "vendor" / "eAD-CRM.openapi.json"
VENDOR_DOCS = ROOT / "docs" / "vendor"
EXTRAS = ROOT / "extras"
METADATA = ROOT / "vendor" / "UPSTREAM_METADATA.json"


def load_json(path: Path) -> dict[str, Any]:
    try:
        data = json.loads(path.read_text(encoding="utf-8"))
    except FileNotFoundError as exc:
        raise SystemExit(f"Missing configuration: {path}") from exc
    except json.JSONDecodeError as exc:
        raise SystemExit(f"Invalid JSON in {path}: {exc}") from exc
    if not isinstance(data, dict):
        raise SystemExit(f"Expected a JSON object in {path}")
    return data


def run(command: list[str], cwd: Path | None = None) -> None:
    display = " ".join(command)
    print(f"+ {display}")
    try:
        subprocess.run(command, cwd=cwd, check=True)
    except FileNotFoundError as exc:
        raise RuntimeError(f"Command not found: {command[0]}") from exc
    except subprocess.CalledProcessError as exc:
        raise RuntimeError(f"Command failed with exit code {exc.returncode}: {display}") from exc


def clone_at_ref(repository: str, ref: str, force: bool) -> Path:
    if force and VENDOR_REPO.exists():
        shutil.rmtree(VENDOR_REPO)

    if not (VENDOR_REPO / ".git").exists():
        VENDOR_REPO.mkdir(parents=True, exist_ok=True)
        run(["git", "init", "-q"], cwd=VENDOR_REPO)
        run(["git", "remote", "add", "origin", repository], cwd=VENDOR_REPO)

    run(["git", "fetch", "--depth", "1", "origin", ref], cwd=VENDOR_REPO)
    run(["git", "checkout", "--detach", "--force", "FETCH_HEAD"], cwd=VENDOR_REPO)
    run(["git", "clean", "-fdx"], cwd=VENDOR_REPO)
    return VENDOR_REPO


def fetch_bytes(url: str, headers: dict[str, str] | None = None) -> bytes:
    request = urllib.request.Request(
        url,
        headers={
            "User-Agent": "eAD-CRM-api-docs-starter/0.1",
            "Accept": "application/json,text/plain,*/*",
            **(headers or {}),
        },
    )
    with urllib.request.urlopen(request, timeout=60) as response:
        return response.read()


def download_core_fallback(config: dict[str, Any], ref: str) -> Path:
    """Download the core text files if git is unavailable."""
    full_name = str(config["repository_full_name"])
    raw_base = f"https://raw.githubusercontent.com/{full_name}/{ref}/"
    temp_dir = Path(tempfile.mkdtemp(prefix="eAD-CRM-upstream-"))

    files = [
        str(config["openapi_path"]),
        str(config["license_path"]),
        "docs/authentication.md",
        "docs/automation.md",
        "docs/custom-tables.md",
        "docs/errors.md",
        "docs/mcp.md",
        "docs/pagination-filtering.md",
        "docs/webhooks.md",
    ]

    print("Git import failed; downloading the core raw files instead.")
    for relative in files:
        target = temp_dir / relative
        target.parent.mkdir(parents=True, exist_ok=True)
        target.write_bytes(fetch_bytes(raw_base + relative))
        print(f"Downloaded {relative}")
    return temp_dir


def copy_tree_if_present(source: Path, destination: Path) -> None:
    if not source.exists():
        return
    if destination.exists():
        shutil.rmtree(destination)
    shutil.copytree(source, destination)


def install_upstream_files(source_root: Path, config: dict[str, Any]) -> dict[str, Any]:
    source_spec = source_root / str(config["openapi_path"])
    if not source_spec.exists():
        raise RuntimeError(f"Upstream OpenAPI file was not found: {source_spec}")

    spec = load_json(source_spec)
    VENDOR_SPEC.parent.mkdir(parents=True, exist_ok=True)
    VENDOR_SPEC.write_text(
        json.dumps(spec, ensure_ascii=False, indent=2) + "\n",
        encoding="utf-8",
    )

    source_docs = source_root / str(config["docs_path"])
    if source_docs.exists():
        if VENDOR_DOCS.exists():
            shutil.rmtree(VENDOR_DOCS)
        VENDOR_DOCS.mkdir(parents=True, exist_ok=True)
        for markdown_file in sorted(source_docs.glob("*.md")):
            shutil.copy2(markdown_file, VENDOR_DOCS / markdown_file.name)

    copy_tree_if_present(source_root / str(config["postman_path"]), EXTRAS / "postman")
    copy_tree_if_present(source_root / str(config["snippets_path"]), EXTRAS / "snippets")

    source_license = source_root / str(config["license_path"])
    if source_license.exists():
        license_target = ROOT / "vendor" / "licenses" / "eAdvertise-MIT.txt"
        license_target.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(source_license, license_target)

    return spec


def normalize_crm_candidates(base_url: str) -> list[str]:
    value = base_url.strip().rstrip("/")
    if not value:
        raise SystemExit("A CRM URL is required. Use --crm-url or EAD_CRM_BASE_URL.")
    if value.endswith("/openapi") or value.endswith("/openapi.json"):
        return [value]
    if value.endswith("/api"):
        return [value + "/openapi.json", value + "/openapi"]
    return [value + "/api/openapi.json", value + "/api/openapi"]


def fetch_crm_spec(base_url: str, token: str) -> tuple[dict[str, Any], str]:
    headers = {"authtoken": token} if token else {}
    errors: list[str] = []
    for url in normalize_crm_candidates(base_url):
        try:
            payload = fetch_bytes(url, headers=headers)
            spec = json.loads(payload.decode("utf-8-sig"))
            if not isinstance(spec, dict) or "paths" not in spec:
                raise ValueError("response is not an OpenAPI document")
            VENDOR_SPEC.parent.mkdir(parents=True, exist_ok=True)
            VENDOR_SPEC.write_text(
                json.dumps(spec, ensure_ascii=False, indent=2) + "\n",
                encoding="utf-8",
            )
            return spec, url
        except (urllib.error.URLError, urllib.error.HTTPError, UnicodeDecodeError, json.JSONDecodeError, ValueError) as exc:
            errors.append(f"{url}: {exc}")
    raise RuntimeError("Could not fetch OpenAPI from the CRM:\n- " + "\n- ".join(errors))


def operation_count(spec: dict[str, Any]) -> int:
    methods = {"get", "post", "put", "patch", "delete", "options", "head", "trace"}
    total = 0
    for item in spec.get("paths", {}).values():
        if isinstance(item, dict):
            total += sum(1 for key in item if key.lower() in methods)
    return total


def main() -> None:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--source", choices=["github", "crm", "local"], default="github")
    parser.add_argument("--crm-url", default=os.environ.get("EAD_CRM_BASE_URL", ""))
    parser.add_argument("--local-path", type=Path, help="Path to an already downloaded upstream repository")
    parser.add_argument("--token-env", default="EAD_CRM_API_TOKEN")
    parser.add_argument("--ref", default="")
    parser.add_argument("--latest", action="store_true", help="Use the configured branch instead of the locked commit")
    parser.add_argument("--force", action="store_true", help="Replace the cached upstream checkout")
    args = parser.parse_args()

    config = load_json(UPSTREAM_CONFIG)
    ref = args.ref or (str(config["branch"]) if args.latest else str(config["locked_ref"]))
    metadata: dict[str, Any] = {
        "imported_at": datetime.now(timezone.utc).isoformat(),
        "repository": config["repository"],
        "requested_ref": ref,
        "source": args.source,
    }

    source_root: Path | None = None
    if args.source == "local":
        if args.local_path is None:
            raise SystemExit("Use --local-path with --source local.")
        source_root = args.local_path.expanduser().resolve()
        if not source_root.is_dir():
            raise SystemExit(f"Local upstream directory does not exist: {source_root}")
        metadata["local_path"] = str(source_root)
    else:
        try:
            source_root = clone_at_ref(str(config["repository"]), ref, args.force)
        except RuntimeError as exc:
            print(f"Warning: {exc}", file=sys.stderr)
            if args.source == "github":
                source_root = download_core_fallback(config, ref)

    if args.source == "crm":
        token = os.environ.get(args.token_env, "")
        if not token:
            raise SystemExit(f"Set {args.token_env} before importing from your CRM.")
        spec, source_url = fetch_crm_spec(args.crm_url, token)
        metadata["openapi_source_url"] = source_url
        if source_root is not None:
            # Install guides/extras, then restore the CRM-specific OpenAPI document.
            install_upstream_files(source_root, config)
            VENDOR_SPEC.write_text(json.dumps(spec, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    else:
        if source_root is None:
            raise SystemExit("Unable to import the upstream repository.")
        spec = install_upstream_files(source_root, config)

    metadata.update(
        {
            "openapi_version": spec.get("openapi"),
            "api_title": spec.get("info", {}).get("title"),
            "api_version": spec.get("info", {}).get("version"),
            "path_count": len(spec.get("paths", {})),
            "operation_count": operation_count(spec),
        }
    )

    if (VENDOR_REPO / ".git").exists():
        try:
            result = subprocess.run(
                ["git", "rev-parse", "HEAD"],
                cwd=VENDOR_REPO,
                check=True,
                capture_output=True,
                text=True,
            )
            metadata["resolved_commit"] = result.stdout.strip()
        except subprocess.CalledProcessError:
            pass

    METADATA.parent.mkdir(parents=True, exist_ok=True)
    METADATA.write_text(json.dumps(metadata, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")

    print(
        f"Imported {metadata['path_count']} paths / {metadata['operation_count']} operations "
        f"(API version {metadata.get('api_version') or 'unknown'})."
    )
    print("Next: python scripts/merge_openapi.py && python scripts/build_site.py")


if __name__ == "__main__":
    main()
