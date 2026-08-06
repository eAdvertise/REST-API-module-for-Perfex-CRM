# Vendor OpenAPI

`perfex.openapi.json` is generated/imported and is intentionally ignored by Git
until you decide to commit a pinned copy.

Populate it with either:

```bash
make bootstrap
```

or, preferably, from the exact module installed in your own CRM:

```bash
PERFEX_BASE_URL=https://crm.example.com \
PERFEX_API_TOKEN=secret \
make bootstrap-crm
```

Never edit the vendor file directly. Put additions in `../custom/`.
