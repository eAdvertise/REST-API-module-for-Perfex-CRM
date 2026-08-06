# Upstream guides

This directory is the import target for Markdown guides from the public eAD-CRM REST API companion repository.

Run:

```bash
make bootstrap
make build
```

The importer replaces or refreshes upstream guide files while preserving everything under `docs/custom/`.

Do not put company-owned documentation in this directory. Use `docs/custom/` so upstream updates cannot overwrite it.
