"""
eAD-CRM REST API — Batch examples (Python / requests)
Module: https://www.eadvertise.eu/

Run up to 50 operations in a single request. The batch endpoint accepts
`json=` (application/json) and reuses the same tool names as the MCP server.

Install dependency:  pip install requests
"""
import requests

BASE = "https://yourdomain.com/api"
TOKEN = "YOUR_API_TOKEN"
HEADERS = {"authtoken": TOKEN}


def run_batch():
    body = {
        "operations": [
            {"tool": "customers_create", "args": {"company": "Acme LTD"}},
            {"tool": "invoices_get", "args": {"id": 1}},
        ]
    }
    r = requests.post(f"{BASE}/batch", headers=HEADERS, json=body, timeout=30)
    return r.status_code, r.json()


if __name__ == "__main__":
    print(run_batch())
