# Importing the upstream reference

The project deliberately keeps the standard Perfex API in a vendor layer. Your own documentation remains separate and survives upstream refreshes.

## Locked public snapshot

The starter records a known upstream commit in `config/upstream.json`. Import that reproducible snapshot with:

```bash
make bootstrap
```

The importer performs the following work:

1. fetches the configured repository at the locked commit;
2. copies the OpenAPI JSON into `openapi/vendor/perfex.openapi.json`;
3. copies upstream Markdown guides into `docs/vendor/`;
4. copies Postman and code examples into `extras/` when available;
5. preserves the upstream MIT notice under `vendor/licenses/`;
6. writes import metadata to `vendor/UPSTREAM_METADATA.json`.

The imported checkout is cached under `vendor/upstream/` and ignored by your Git repository.

## Exact import from your installation

The public reference may differ from the module version installed on your CRM. Use the CRM import when accuracy matters:

```bash
export PERFEX_BASE_URL="https://crm.example.com"
export PERFEX_API_TOKEN="your-api-token"
make bootstrap-crm
```

The importer tries both of the commonly exposed routes:

```text
/api/openapi.json
/api/openapi
```

It sends the token in the `authtoken` header and stores only the returned OpenAPI document. The secret itself is never persisted.

## Import from a specific commit

Use the Python command directly when you need a particular upstream commit or tag:

```bash
.venv/bin/python scripts/bootstrap.py \
  --source github \
  --ref COMMIT_OR_TAG \
  --force
```

## Import the latest upstream branch

```bash
make update-upstream
```

Review the generated diff before committing. An upstream change can add, remove, or rename operations and schemas.

## Rebuild after every import

```bash
make merge
make validate
make build
```

`make build` already runs merge and validation, so the shorter equivalent is:

```bash
make build
```

## What not to edit

Do not hand-edit:

```text
openapi/vendor/perfex.openapi.json
docs/vendor/*.md
vendor/upstream/
```

Those files are replaceable vendor inputs. Put durable changes in `openapi/custom/`, `docs/custom/`, `templates/`, and `assets/`.
