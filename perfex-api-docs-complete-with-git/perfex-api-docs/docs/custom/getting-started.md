# Getting started

Use this checklist to turn the starter into your own self-hosted API documentation project.

## Prerequisites

You need:

- Python 3.10 or newer;
- Git;
- network access when importing the public upstream repository;
- optional Docker and Docker Compose for containerised hosting.

No Node.js application or database is required. The final site consists only of HTML, CSS, JavaScript, JSON, and copied downloadable resources.

## First build

Create the Python environment and install the two lightweight build dependencies:

```bash
make install
```

Build the included custom-module example:

```bash
make build
```

Start the local preview server:

```bash
make serve
```

Visit `http://127.0.0.1:8080`.

> The ZIP includes a prebuilt `site/` directory, so you can inspect or upload the starter immediately. Rebuilding is still the normal workflow after edits.

## Import the standard API

To add the complete public eAD-CRM API reference and the upstream Markdown guides:

```bash
make bootstrap
make build
```

For an exact match with your installed module version, import from your CRM:

```bash
export EAD_CRM_BASE_URL="https://crm.example.com"
export EAD_CRM_API_TOKEN="your-token"
make bootstrap-crm
make build
```

The token is read from the environment and is never written to the generated site.

## Set your identity

Edit `config/site.json`:

```json
{
  "site_name": "Acme CRM API",
  "company_name": "Acme Ltd",
  "default_api_server": "https://crm.acme.example/api",
  "logo_text": "AC",
  "copyright": "Copyright 2026 Acme Ltd"
}
```

Then rebuild:

```bash
make build
```

## Replace the sample module

The file `openapi/custom/your-module.openapi.json` demonstrates five operations, reusable schemas, shared responses, authentication, pagination, examples, and validation errors.

Rename the file and replace:

- the `Your Module` tag;
- `/your-module/items` paths;
- `yourModule...` operation IDs;
- `YourModule...` component names;
- payload and response examples.

Keep custom component names unique to prevent collisions with the vendor specification.

## Create your new Git repository

```bash
git init -b main
git add .
git commit -m "Initial API documentation platform"
git remote add origin git@github.com:YOUR-ORG/YOUR-REPOSITORY.git
git push -u origin main
```

The generated `site/` directory is ignored by Git by default. The included GitHub Actions workflow builds it and publishes it as a CI artifact on every push.
