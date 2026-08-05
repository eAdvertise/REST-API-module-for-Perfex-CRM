<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo html_escape($title ?? 'eAD-CRM API Documentation'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f7fb; color: #172033; }
        .hero { background: linear-gradient(135deg, #315efb, #0ea5e9); color: #fff; border-radius: 18px; padding: 36px; }
        .card { border: 1px solid #e4e7ec; border-radius: 16px; box-shadow: 0 8px 26px rgba(16,24,40,.04); }
        code, pre { background: #0f172a; color: #e5e7eb; border-radius: 10px; }
        pre { padding: 16px; overflow: auto; }
        .method { display:inline-block; min-width:58px; text-align:center; color:#fff; border-radius:6px; padding:3px 8px; font-weight:700; font-size:12px; }
        .post { background:#315efb; } .get { background:#12b76a; } .put { background:#f79009; } .delete { background:#f04438; }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="hero mb-4">
        <h1 class="mb-2">eAD-CRM REST API Documentation</h1>
        <p class="lead mb-0">Official local guide for our fork, including Guest Invoice checkout and a path for future custom endpoints.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card p-3 h-100"><strong>Base URL</strong><br><code class="d-inline-block p-2 mt-2">/api</code></div></div>
        <div class="col-md-4"><div class="card p-3 h-100"><strong>Authentication</strong><br><code class="d-inline-block p-2 mt-2">authtoken: YOUR_API_TOKEN</code></div></div>
        <div class="col-md-4"><div class="card p-3 h-100"><strong>Machine-readable spec</strong><br><a class="btn btn-outline-primary btn-sm mt-2" href="<?php echo base_url('api/openapi.json'); ?>" target="_blank">Open OpenAPI JSON</a></div></div>
    </div>

    <div class="card p-4 mb-4" id="authentication">
        <h2>Authentication</h2>
        <p>Every protected API request should include an API token in the <code class="p-1">authtoken</code> header. Token permissions control which resources and capabilities are available.</p>
<pre><code>curl -X GET "<?php echo rtrim(site_url('api/customers'), '/'); ?>" \
  -H "authtoken: YOUR_API_TOKEN" \
  -H "Accept: application/json"</code></pre>
        <p class="mb-0 text-muted">No external vendor activation is required in this fork.</p>
    </div>

    <div class="card p-4 mb-4" id="guest-invoices">
        <h2>Guest Invoices / Checkout</h2>
        <p>Use these eAD endpoints to create or reuse a guest by email, generate an invoice, record a payment and optionally send invoice/receipt PDFs.</p>
        <p><span class="method post">POST</span> <code class="p-1">/api/guest_invoices</code> Create/reuse guest and create invoice.</p>
        <p><span class="method post">POST</span> <code class="p-1">/api/guest_invoices/checkout</code> Create/reuse guest, invoice and payment.</p>
<pre><code>{
  "email": "guest@example.com",
  "name": "Guest Customer",
  "payment_mode": 1,
  "send_email": true,
  "items": [
    {"description": "Online order", "qty": 1, "rate": 49.90, "taxname": ["VAT|24.00"]}
  ],
  "transaction_id": "web-1001"
}</code></pre>
    </div>

    <div class="card p-4 mb-4" id="resources">
        <h2>Core resources</h2>
        <p>Most CRM resources follow standard CRUD routes: customers, contacts, leads, invoices, estimates, proposals, credit notes, payments, projects, tasks, milestones, tickets, contracts, expenses, items, staff, subscriptions, timesheets, calendar, notes, knowledge base and webhooks.</p>
        <p><span class="method get">GET</span> <code class="p-1">/api/{resource}</code></p>
        <p><span class="method post">POST</span> <code class="p-1">/api/{resource}</code></p>
        <p><span class="method get">GET</span> <code class="p-1">/api/{resource}/{id}</code></p>
        <p><span class="method put">PUT</span> <code class="p-1">/api/{resource}/{id}</code></p>
        <p><span class="method delete">DELETE</span> <code class="p-1">/api/{resource}/{id}</code></p>
    </div>

    <div class="card p-4 mb-4" id="tooling">
        <h2>Tooling</h2>
        <p>Use the OpenAPI contract and Swagger UI for the full endpoint list and request/response schemas.</p>
        <a href="<?php echo base_url('api/playground/swagger'); ?>" class="btn btn-primary">Open Swagger UI</a>
        <a href="<?php echo base_url('api/openapi.json'); ?>" class="btn btn-outline-primary ms-2" target="_blank">Open OpenAPI JSON</a>
    </div>
</div>
</body>
</html>
