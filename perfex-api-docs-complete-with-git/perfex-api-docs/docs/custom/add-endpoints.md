# Adding custom endpoints

Document custom-module operations in one or more JSON files under `openapi/custom/`. Every file is merged into the vendor OpenAPI document during the build.

## Minimal operation

Create `openapi/custom/my-module.openapi.json`:

```json
{
  "openapi": "3.0.3",
  "info": {
    "title": "My module additions",
    "version": "1.0.0"
  },
  "tags": [
    {
      "name": "My Module",
      "description": "Endpoints supplied by our module."
    }
  ],
  "paths": {
    "/my-module/records": {
      "get": {
        "tags": ["My Module"],
        "summary": "List records",
        "operationId": "myModuleListRecords",
        "responses": {
          "200": {
            "description": "Records returned successfully."
          }
        }
      }
    }
  }
}
```

Build and inspect the new page:

```bash
make build
make serve
```

## Paths must be relative to `/api`

The configured server normally ends with `/api`:

```text
https://crm.example.com/api
```

Therefore a real endpoint at:

```text
https://crm.example.com/api/my-module/records
```

uses this OpenAPI path:

```json
"/my-module/records"
```

Do not include `/api` a second time.

## Authentication

The merged specification uses an API-key security scheme named `authtoken`. Apply it globally in the custom document:

```json
"security": [
  { "authtoken": [] }
]
```

Or apply it to one operation. To mark a public operation, explicitly use:

```json
"security": []
```

## Path parameters

Every `{placeholder}` in a path must have a matching required path parameter:

```json
{
  "name": "id",
  "in": "path",
  "required": true,
  "schema": {
    "type": "integer",
    "minimum": 1
  },
  "example": 42
}
```

The validator warns when a path placeholder is undeclared.

## Reusable schemas and responses

Use a company- or module-specific prefix for all component names:

```json
"components": {
  "schemas": {
    "AcmeModuleRecord": {
      "type": "object",
      "properties": {
        "id": { "type": "integer", "example": 42 },
        "name": { "type": "string", "example": "Example" }
      }
    }
  }
}
```

Reference the component with:

```json
"$ref": "#/components/schemas/AcmeModuleRecord"
```

The merger intentionally rejects duplicate component names. This prevents a custom file from silently replacing an upstream schema.

## Rich documentation

For useful generated pages, include:

- a clear `summary`;
- an explanatory `description`;
- a unique `operationId`;
- parameter descriptions and examples;
- request-body schemas;
- success and failure responses;
- realistic JSON examples;
- reusable component definitions.

## Multiple module files

You can split custom documentation by feature:

```text
openapi/custom/
├── inventory.openapi.json
├── provisioning.openapi.json
├── reporting.openapi.json
└── webhooks.openapi.json
```

Files are merged in alphabetical order. Endpoint and component collisions stop the build with a clear error.

## Validate before publishing

```bash
make validate
```

The bundled validator checks the basic OpenAPI structure, response definitions, duplicate operation IDs, path parameters, and broken internal `$ref` pointers.
