# Branding and design

The starter uses an original, dependency-free theme. No CSS, JavaScript, templates, logos, or other visual assets are copied from the commercial documentation website.

## Site metadata

Edit `config/site.json` to change the visible identity:

```json
{
  "site_name": "Acme CRM API",
  "site_description": "REST API documentation for Acme CRM integrations",
  "company_name": "Acme Ltd",
  "base_path": "",
  "default_api_server": "https://crm.acme.example/api",
  "logo_text": "AC",
  "copyright": "Copyright 2026 Acme Ltd",
  "home_eyebrow": "Developer platform",
  "home_title": "Integrate with Acme CRM",
  "home_intro": "Guides and API reference for our CRM platform.",
  "repository_url": "https://github.com/acme/api-docs",
  "support_url": "https://support.acme.example"
}
```

`base_path` remains empty when the site is hosted at the root of a domain. Use a value such as `/developers` when publishing under a subdirectory.

## Logo

The header currently renders the short text from `logo_text` inside a gradient mark. To use an SVG logo:

1. add the file under `assets/img/logo.svg`;
2. edit the `.brand` block in `templates/base.html`;
3. keep an accessible text label or `aria-label`;
4. rebuild the site.

## Colours and typography

All colour tokens are defined at the top of `assets/css/site.css`:

```css
:root {
  --brand: #6848ee;
  --brand-strong: #5131d8;
  --accent: #0cae91;
  --bg: #f7f8fc;
  --surface: #ffffff;
  --ink: #111827;
}
```

Dark-mode values are defined under:

```css
html[data-theme="dark"] { ... }
```

The project uses a system font stack and makes no request to Google Fonts or another third-party font service.

## Page templates

The main templates are:

| Template | Purpose |
|---|---|
| `templates/base.html` | Header, sidebar, search dialog, footer |
| `templates/home.html` | Landing page and resource cards |
| `templates/reference_index.html` | Filterable API catalogue |
| `templates/operation.html` | Generated endpoint page |
| `templates/guide.html` | Markdown guide wrapper and table of contents |

Jinja templates receive site configuration, guide navigation, endpoint groups, and current-page context from `scripts/build_site.py`.

## Client-side behaviour

`assets/js/site.js` implements:

- responsive navigation;
- light and dark themes;
- copy-to-clipboard buttons;
- searchable guides and endpoints;
- keyboard search shortcut (`Ctrl/⌘ + K`);
- reference filtering;
- Markdown table of contents;
- reading progress.

No analytics or tracking scripts are included.

## Rebuild after design changes

```bash
make build
```

Hard-refresh the browser when checking CSS or JavaScript changes. The included Nginx configuration caches static assets for performance.
