"""eAD-CRM API v3 optional-module examples. Requires: pip install requests"""
import requests

BASE = "https://yourdomain.com/api"
HEADERS = {"authtoken": "YOUR_API_TOKEN"}

def call(method, path, payload=None):
    response = requests.request(method, BASE + path, headers=HEADERS, json=payload, timeout=60)
    response.raise_for_status()
    return response.json()

print(call("GET", "/warehouse/inventory?page=1&per_page=25"))
print(call("POST", "/paymentsonaccount/receipts", {"client_id": 12, "amount": 150, "payment_mode": "1", "invoice_ids": [35]}))
print(call("POST", "/delivery_notes/notes", {"clientid": 12, "currency": 1, "date": "2026-08-14", "newitems": [{"description": "Delivered item", "qty": 2, "rate": 25}]}))
print(call("GET", "/commission/commissions?page=1&per_page=25"))
print(call("POST", "/myshopify/sync"))
print(call("POST", "/purchase/requests", {"currency": 1, "subtotal": 100, "newitems": [{"item_code": 10, "quantity": 2, "unit_price": 50}]}))
print(call("POST", "/guest_invoices/checkout", {"email": "guest@example.com", "amount": 100, "paymentmode": 1}))
