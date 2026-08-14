#!/usr/bin/env bash
# eAD-CRM REST API v3 — optional module examples
set -euo pipefail
BASE="${BASE:-https://yourdomain.com/api}"
TOKEN="${TOKEN:-YOUR_API_TOKEN}"
AUTH=(-H "authtoken: ${TOKEN}")
JSON=(-H "Content-Type: application/json")

curl -sS "${AUTH[@]}" "${BASE}/warehouse/inventory?page=1&per_page=25"
curl -sS -X POST "${BASE}/paymentsonaccount/receipts" "${AUTH[@]}" "${JSON[@]}" -d '{"client_id":12,"amount":150,"payment_mode":"1","invoice_ids":[35]}'
curl -sS -X POST "${BASE}/delivery_notes/notes" "${AUTH[@]}" "${JSON[@]}" -d '{"clientid":12,"currency":1,"date":"2026-08-14","newitems":[{"description":"Delivered item","qty":2,"rate":25}]}'
curl -sS "${AUTH[@]}" "${BASE}/commission/commissions?page=1&per_page=25"
curl -sS -X POST "${BASE}/myshopify/sync" "${AUTH[@]}"
curl -sS -X POST "${BASE}/purchase/requests" "${AUTH[@]}" "${JSON[@]}" -d '{"currency":1,"subtotal":100,"newitems":[{"item_code":10,"quantity":2,"unit_price":50}]}'
curl -sS -X POST "${BASE}/guest_invoices/checkout" "${AUTH[@]}" "${JSON[@]}" -d '{"email":"guest@example.com","amount":100,"paymentmode":1}'
