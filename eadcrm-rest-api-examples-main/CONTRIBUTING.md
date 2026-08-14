# Contributing

Thanks for helping improve the **eAD-CRM REST API examples**! These accompany the
[REST API for eAD-CRM](https://www.eadvertise.eu/) module.

## How to contribute

- Add a snippet under `snippets/<language>/`.
- Keep examples self-contained and runnable; use `YOUR_API_TOKEN` and `https://yourdomain.com/api`
  as placeholders.
- **Never commit a real `authtoken`** or any customer data.
- For new Postman requests, export and merge into `postman/eadcrm-rest-api.postman_collection.json`.

## Style

- One operation per snippet, with a short comment header describing the call.
- Prefer standard libraries (`curl`, `requests`, `fetch`) plus one popular client (Guzzle / axios).

By contributing you agree your contributions are licensed under the repository's [MIT License](LICENSE).
