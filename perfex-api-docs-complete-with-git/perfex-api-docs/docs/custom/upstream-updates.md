# Updating the upstream base

Treat the public Perfex companion repository as a vendor dependency. Update it deliberately, review the result, and keep your custom material isolated.

## Check the current import

After `make bootstrap`, inspect:

```text
vendor/UPSTREAM_METADATA.json
```

It records the requested reference, resolved commit when available, import timestamp, API version, path count, and operation count.

## Update to the current main branch

```bash
make update-upstream
make build
```

The update replaces vendor inputs but does not touch:

```text
openapi/custom/
docs/custom/
config/site.json
templates/
assets/
```

## Review the result

Before committing:

```bash
git status
git diff --stat
git diff
```

Pay particular attention to:

- removed or renamed paths;
- changed request fields;
- changed response schemas;
- component-name collisions;
- authentication changes;
- version-specific guide changes.

## Resolve custom conflicts

The merger stops when a custom operation or component has the same identity as an upstream item. Rename your custom path, `operationId`, or component unless replacing the upstream definition is intentional.

An explicit override mode exists for controlled migrations:

```bash
.venv/bin/python scripts/merge_openapi.py --allow-overrides
```

Avoid using this as the normal workflow. Silent vendor overrides make future updates harder to audit.

## Pin a reviewed commit

After accepting an upstream version, update `locked_ref` in `config/upstream.json` to the exact reviewed commit. Future `make bootstrap` runs will then be reproducible.

## Commit the dependency update

```bash
git add config/upstream.json vendor/UPSTREAM_METADATA.json
# Add vendor files too only if your repository policy tracks them.
git commit -m "Update Perfex API documentation base"
```

The starter ignores the large cached checkout and imported OpenAPI file by default. This keeps your repository small and allows CI or developers to re-import the pinned source.
