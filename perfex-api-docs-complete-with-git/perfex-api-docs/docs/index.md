# Documentation architecture

This project turns an OpenAPI document and a set of Markdown guides into a completely static documentation website. The generated `site/` directory has no server-side runtime and can be hosted by Nginx, Apache, a CDN, object storage, or any ordinary web server.

## Source of truth

The generated HTML is **not** the source of truth. Edit these inputs instead:

| Content | Source directory |
|---|---|
| Standard eAD-CRM API | `openapi/vendor/eAD-CRM.openapi.json` |
| Your module endpoints | `openapi/custom/*.json` |
| Imported upstream guides | `docs/vendor/*.md` |
| Your own guides | `docs/custom/*.md` |
| Brand and site metadata | `config/site.json` |
| Layout and components | `templates/*.html` |
| Visual design | `assets/css/site.css` |
| Search and UI behaviour | `assets/js/site.js` |

The build merges the vendor and custom OpenAPI documents, validates internal references, then generates a page for every operation.

## Recommended workflow

```bash
make install
make bootstrap
make build
make serve
```

Open `http://127.0.0.1:8080` to preview the result.

For the most accurate reference, import the OpenAPI document directly from your licensed eAD-CRM installation instead of relying only on the public companion repository:

```bash
export EAD_CRM_BASE_URL="https://crm.example.com"
export EAD_CRM_API_TOKEN="replace-me"
make bootstrap-crm
make build
```

## Safe customisation model

Keep upstream material and company-owned material separate:

- Never edit files in `openapi/vendor/` by hand.
- Never place company endpoints in the upstream checkout.
- Add one or more uniquely named JSON files to `openapi/custom/`.
- Add your guides to `docs/custom/`.
- Change the original theme in `templates/` and `assets/`.

This separation allows upstream updates without overwriting your module documentation.

## Next steps

1. Read [Getting started](custom/getting-started.md).
2. Import the [upstream API reference](custom/importing-the-upstream.md).
3. Replace the sample endpoints using [Adding endpoints](custom/add-endpoints.md).
4. Apply your identity using [Branding and design](custom/branding.md).
5. Publish the generated site using [Deployment](custom/deployment.md).
