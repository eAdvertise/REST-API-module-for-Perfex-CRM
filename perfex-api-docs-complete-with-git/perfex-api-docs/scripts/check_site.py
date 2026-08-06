#!/usr/bin/env python3
"""Check generated HTML for broken local links, duplicate IDs, and missing assets."""

from __future__ import annotations

import argparse
import json
from html.parser import HTMLParser
from pathlib import Path
from urllib.parse import unquote, urlsplit

ROOT = Path(__file__).resolve().parents[1]
DEFAULT_SITE = ROOT / "site"
CONFIG = ROOT / "config" / "site.json"


class PageParser(HTMLParser):
    def __init__(self) -> None:
        super().__init__(convert_charrefs=True)
        self.links: list[str] = []
        self.ids: list[str] = []
        self.title_text: list[str] = []
        self.in_title = False

    def handle_starttag(self, tag: str, attrs: list[tuple[str, str | None]]) -> None:
        values = dict(attrs)
        if values.get("id"):
            self.ids.append(str(values["id"]))
        for attribute in ("href", "src"):
            value = values.get(attribute)
            if value:
                self.links.append(str(value))
        if tag == "title":
            self.in_title = True

    def handle_endtag(self, tag: str) -> None:
        if tag == "title":
            self.in_title = False

    def handle_data(self, data: str) -> None:
        if self.in_title:
            self.title_text.append(data)


def parse_page(path: Path) -> PageParser:
    parser = PageParser()
    parser.feed(path.read_text(encoding="utf-8"))
    return parser


def target_for_link(site: Path, page: Path, raw: str, base_path: str) -> tuple[Path | None, str]:
    if raw.startswith(("mailto:", "tel:", "javascript:", "data:")):
        return None, ""
    parsed = urlsplit(raw)
    if parsed.scheme or parsed.netloc:
        return None, ""
    path = unquote(parsed.path)
    if not path:
        return page, parsed.fragment
    if path.startswith("/"):
        normalized = path
        if base_path and (normalized == base_path or normalized.startswith(base_path + "/")):
            normalized = normalized[len(base_path) :] or "/"
        target = site / normalized.lstrip("/")
    else:
        target = page.parent / path
    target = target.resolve()
    if path.endswith("/") or not target.suffix:
        target = target / "index.html"
    return target, parsed.fragment


def main() -> None:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("site", nargs="?", type=Path, default=DEFAULT_SITE)
    args = parser.parse_args()
    site = args.site.resolve()
    if not site.is_dir():
        raise SystemExit(f"Site directory does not exist: {site}")

    config = json.loads(CONFIG.read_text(encoding="utf-8"))
    base_path = "/" + str(config.get("base_path", "")).strip("/") if config.get("base_path") else ""
    pages = sorted(site.rglob("*.html"))
    parsed_pages = {page: parse_page(page) for page in pages}
    errors: list[str] = []

    for page, parsed in parsed_pages.items():
        relative = page.relative_to(site)
        if not "".join(parsed.title_text).strip():
            errors.append(f"{relative}: missing <title>")
        duplicates = sorted({value for value in parsed.ids if parsed.ids.count(value) > 1})
        for duplicate in duplicates:
            errors.append(f"{relative}: duplicate id #{duplicate}")

        for raw in parsed.links:
            target, fragment = target_for_link(site, page, raw, base_path)
            if target is None:
                continue
            try:
                target.relative_to(site)
            except ValueError:
                errors.append(f"{relative}: link escapes site root: {raw}")
                continue
            if not target.exists():
                errors.append(f"{relative}: missing local target: {raw}")
                continue
            if fragment and target.suffix == ".html":
                target_parser = parsed_pages.get(target)
                if target_parser is None:
                    target_parser = parse_page(target)
                    parsed_pages[target] = target_parser
                if fragment not in target_parser.ids:
                    errors.append(f"{relative}: missing anchor in {target.relative_to(site)}: #{fragment}")

    search_index = site / "assets" / "search-index.json"
    try:
        search_entries = json.loads(search_index.read_text(encoding="utf-8"))
        if not isinstance(search_entries, list):
            errors.append("assets/search-index.json: root is not an array")
    except (FileNotFoundError, json.JSONDecodeError) as exc:
        errors.append(f"assets/search-index.json: {exc}")
        search_entries = []

    spec = site / "downloads" / "combined.openapi.json"
    try:
        json.loads(spec.read_text(encoding="utf-8"))
    except (FileNotFoundError, json.JSONDecodeError) as exc:
        errors.append(f"downloads/combined.openapi.json: {exc}")

    for error in errors:
        print(f"ERROR: {error}")
    print(
        f"Checked {len(pages)} HTML page(s), {len(search_entries)} search entry/entries: "
        f"{len(errors)} error(s)."
    )
    if errors:
        raise SystemExit(1)


if __name__ == "__main__":
    main()
