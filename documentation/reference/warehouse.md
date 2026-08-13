# Warehouse API

The Warehouse module is available through the authenticated API at
`/api/warehouse`. Enable the required **Warehouse** capabilities for each API
user before making requests.

## Resources

| Resource | GET | POST | PUT | DELETE |
| --- | --- | --- | --- | --- |
| `warehouses` | Yes | Yes | Yes | Yes |
| `items` | Yes | Yes | Yes | Yes |
| `inventory` | Yes | No | No | No |
| `receipts` | Yes | Yes | Yes | Yes |
| `deliveries` | Yes | Yes | Yes | Yes |
| `transfers` | Yes | Yes | Yes | Yes |
| `adjustments` | Yes | Yes | Yes | Yes |

All requests require the normal `authtoken` header. List endpoints accept
`limit` (maximum 500), `offset`, `warehouse_id`, `commodity_id`, `active`,
`approval`, `from`, and `to`. Filters are applied only when the corresponding
column exists on the resource.

```bash
# List stock, filtered by warehouse and item
curl -H "authtoken: $TOKEN" \
  "$BASE_URL/api/warehouse/inventory?warehouse_id=1&commodity_id=42"

# Get a receipt together with its line items
curl -H "authtoken: $TOKEN" "$BASE_URL/api/warehouse/receipts/25"

# Create an item (Warehouse module item payload)
curl -X POST -H "authtoken: $TOKEN" -H "Content-Type: application/json" \
  -d '{"commodity_code":"SKU-42","description":"Example","unit_id":1,"group_id":1,"sku_code":"SKU-42","rate":10,"purchase_price":5}' \
  "$BASE_URL/api/warehouse/items"
```

Receipt, delivery, transfer, and adjustment writes accept the same master and
`newitems` payload used by the Warehouse module forms. They are processed by
the Warehouse model, including approval rules, stock movements, activity logs,
number sequences, and module hooks. Inventory rows therefore cannot be edited
directly.
