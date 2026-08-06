# Deployment

The generated `site/` directory is a static website. Upload that directory—not the Python build environment—to your production server.

## Build for production

```bash
make build
```

The build creates:

```text
site/
├── index.html
├── assets/
├── guides/
├── reference/
├── downloads/combined.openapi.json
└── extras/
```

## Nginx

Copy the site to your web root:

```bash
sudo mkdir -p /var/www/api-docs
sudo rsync -a --delete site/ /var/www/api-docs/
```

A complete Nginx example is provided at `deploy/nginx.conf`. For a dedicated virtual host, adapt it as follows:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name docs.example.com;

    root /var/www/api-docs;
    index index.html;

    location / {
        try_files $uri $uri/ $uri/index.html =404;
    }

    location ~* \.(css|js|json|svg|png|jpg|jpeg|webp|ico|woff2?)$ {
        expires 7d;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }
}
```

Validate and reload:

```bash
sudo nginx -t
sudo systemctl reload nginx
```

Add TLS using your normal certificate process before exposing API documentation publicly.

## Included rsync helper

Edit the variables in `deploy/deploy-rsync.sh`, then run:

```bash
bash deploy/deploy-rsync.sh
```

The script builds the site and synchronises it to the configured host.

## Docker

Build and run the included multi-stage image:

```bash
make docker-up
```

The documentation becomes available at:

```text
http://127.0.0.1:8080
```

The final container contains only Nginx and the generated static site.

## Host under a subdirectory

When publishing at `https://example.com/developers/`, set:

```json
"base_path": "/developers"
```

Rebuild and configure Nginx so the generated files are served from the same path.

## GitHub Actions

The included `.github/workflows/build.yml` runs validation and builds `site/` on every push and pull request. The generated site is uploaded as a workflow artifact.

You can extend that workflow to deploy to your server using your organisation's preferred SSH, S3, Pages, or CDN process. Keep production credentials in encrypted repository secrets.

## Security notes

- Never commit your Perfex API token.
- Do not put production secrets in examples.
- Review downloadable Postman collections before publishing.
- Protect internal documentation with your reverse proxy or identity provider when required.
- Use HTTPS in production.
