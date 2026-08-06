FROM python:3.12-slim AS builder
WORKDIR /app
COPY requirements.txt ./
RUN pip install --no-cache-dir -r requirements.txt
COPY . .
RUN if [ ! -f openapi/vendor/eAD-CRM.openapi.json ]; then python scripts/bootstrap.py --source github; fi \
 && python scripts/merge_openapi.py \
 && python scripts/validate_openapi.py openapi/combined.openapi.json \
 && python scripts/build_site.py

FROM nginx:1.27-alpine
COPY deploy/nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=builder /app/site /usr/share/nginx/html
EXPOSE 80
