# MyShopify full synchronization

The complete, deployable English guide is available in [`documentation/index.html`](documentation/index.html). Upload the entire `documentation` directory and open `index.html`; it requires no build step or external dependencies.

## Behaviour

- Customers are linked by normalized email. The newest `updated_at` wins; Perfex wins ties.
- Every Shopify variant must have a unique SKU. SKU links it to a Perfex item (`commodity_code`).
- Shopify collections become Perfex item groups.
- A paid Shopify order creates exactly one Perfex invoice. Unpaid orders are retained but do not create invoices.
- When the `warehouse` module is active, stock is read/written through its `inventory_manage` table and linked by SKU.
- Perfex customer/item changes and approved Warehouse receipts/deliveries are pushed to Shopify.
- Shopify webhooks update Perfex immediately; the Perfex cron performs reconciliation for missed events.

## Shopify configuration

Grant the custom app read/write access for customers, products, inventory, orders and collections. Configure these webhooks as JSON, using the URL shown in **Setup > Settings > Integrations > MyShopify**:

- `customers/create`, `customers/update`
- `products/create`, `products/update`
- `orders/create`, `orders/updated`, `orders/paid`
- `collections/create`, `collections/update`
- `inventory_levels/update`

Enter the webhook signing secret, Shopify location ID and Perfex Warehouse ID. The webhook endpoint rejects unsigned requests using Shopify HMAC verification.
The API version defaults to `2026-07` and can be changed from the same settings screen when Shopify rotates supported versions.

Run **Synchronize Shopify now** once after configuration. This registers any missing webhooks and performs the initial reconciliation. Afterwards keep the normal Perfex cron enabled.

Existing 1.x installations are upgraded through the `2.0.0` migration. The migration declaration is guarded against a duplicate include—the cause of the previous `Cannot declare class Migration_Version_200` fatal error—and the admin schema check remains as an idempotent fallback for manually copied or interrupted deployments.

The module bootstrap is also idempotent: every global callback is conditionally declared and a bootstrap sentinel prevents duplicate hook registration when Perfex loads `myshopify.php` more than once in the same request. Schema fallback uses an anonymous callback, so `myshopify_ensure_v2_schema()` no longer exists and cannot be redeclared.
