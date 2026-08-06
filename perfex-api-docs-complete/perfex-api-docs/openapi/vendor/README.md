# Vendor OpenAPI

`eAD-CRM.openapi.json` is generated/imported and is intentionally ignored by Git
until you decide to commit a pinned copy.

Populate it with either:

```bash
make bootstrap
```

or, preferably, from the exact module installed in your own CRM:

```bash
EAD_CRM_BASE_URL=https://crm.example.com \
EAD_CRM_API_TOKEN=secret \
make bootstrap-crm
```

Never edit the vendor file directly. Put additions in `../custom/`.
