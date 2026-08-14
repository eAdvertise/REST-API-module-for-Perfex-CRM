# Optional module API examples

All calls use the API v3 base URL `https://yourdomain.com/api` and the `authtoken` header. The corresponding module must be installed and active, and the token needs the matching method capability.

| Module | Catalog/base endpoint | Representative workflow |
| --- | --- | --- |
| Warehouse | `GET /warehouse` | `GET /warehouse/inventory`, `POST /warehouse/receipts` |
| Payments On Account | `GET /paymentsonaccount` | `POST /paymentsonaccount/receipts` |
| Delivery Notes | `GET /delivery_notes` | `POST /delivery_notes/notes`, `PUT /delivery_notes/notes/:id/status` |
| Sales Commission | `GET /commission` | `GET /commission/commissions`, `POST /commission/recalculate` |
| MyShopify | `GET /myshopify` | `POST /myshopify/sync`, `GET /myshopify/logs` |
| Purchase Management | `GET /purchase` | `POST /purchase/requests`, `POST /purchase/orders/:id/payments` |
| Guest Invoices | `POST /guest_invoices` | `POST /guest_invoices/checkout` |

Runnable examples are available in [`snippets/curl/modules.sh`](../snippets/curl/modules.sh), [`snippets/php/modules.php`](../snippets/php/modules.php), [`snippets/python/modules.py`](../snippets/python/modules.py), and [`snippets/javascript/modules.js`](../snippets/javascript/modules.js). Replace sample IDs and payload fields with records from your installation before executing write operations.
