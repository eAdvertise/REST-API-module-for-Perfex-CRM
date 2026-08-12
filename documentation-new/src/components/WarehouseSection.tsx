import React from 'react'

import CopyButton from './CopyButton.tsx'

const resources = [
    ['warehouses', 'Warehouse locations', 'GET, POST, PUT, DELETE'],
    ['items', 'Warehouse products and variants', 'GET, POST, PUT, DELETE'],
    ['inventory', 'Computed stock balances', 'GET only'],
    ['receipts', 'Goods receipts and their lines', 'GET, POST, PUT, DELETE'],
    ['deliveries', 'Goods deliveries and their lines', 'GET, POST, PUT, DELETE'],
    ['transfers', 'Internal warehouse transfers', 'GET, POST, PUT, DELETE'],
    ['adjustments', 'Stock loss adjustments', 'GET, POST, PUT, DELETE'],
];

const listExample = `curl "https://yoursite.com/api/warehouse/inventory?warehouse_id=1&commodity_id=42&page=1&per_page=25" \\
  -H "authtoken: YOUR_API_TOKEN"`;

const writeExample = `curl -X POST "https://yoursite.com/api/warehouse/receipts" \\
  -H "authtoken: YOUR_API_TOKEN" \\
  -H "Content-Type: application/json" \\
  -d '{"date_c":"2026-08-12","date_add":"2026-08-12","newitems":[{"commodity_code":42,"warehouse_id":1,"quantities":10,"unit_price":5}]}'`;

function CodeExample({ children }: { children: string }) {
    return <pre className="astro-code catppuccin-mocha" style={{ backgroundColor: '#1e1e2e', color: '#cdd6f4', overflowX: 'auto' }} tabIndex={0}>
        <code>{children}</code><CopyButton hidden={false} />
    </pre>;
}

function WarehouseSection() {
    return <section id="api-warehouse" data-astro-cid-j7pv25f6="">
        <h2 data-astro-cid-j7pv25f6="">Warehouse</h2>
        <article id="api-warehouse-resources">
            <h1>Warehouse resources</h1>
            <div className="row pre-post">
                <div className="col-md-7 no-float">
                    <pre className="full-pre"><span className="typ typ-get">GET</span><span className="url">/api/warehouse/:resource/:id?</span></pre>
                    <div className="endpoint-desc">
                        <p>API v3 exposes the complete Warehouse workflow through authenticated resource endpoints. Grant the API user the Warehouse capability matching the HTTP method.</p>
                        <p>Receipt, delivery, transfer and adjustment writes use the native Warehouse model, preserving approval rules, document numbering, activity logs, hooks and inventory movements. Inventory balances cannot be written directly.</p>
                    </div>
                    <h2 className="sub">Resources</h2>
                    <div className="table-responsive-wrapper"><table className="table table-hover">
                        <thead><tr><th>Resource</th><th>Description</th><th>Methods</th></tr></thead>
                        <tbody>{resources.map(([resource, description, methods]) => <tr key={resource}>
                            <td><code>{resource}</code></td><td>{description}</td><td>{methods}</td>
                        </tr>)}</tbody>
                    </table></div>
                    <h2 className="sub">List filters</h2>
                    <p>Use <code>page</code>, <code>per_page</code> (maximum 100), <code>warehouse_id</code>, <code>commodity_id</code>, <code>active</code>, <code>approval</code>, <code>from</code> and <code>to</code>. Unsupported filters are ignored for resources without the corresponding column.</p>
                    <h2 className="sub">Write payloads</h2>
                    <p>Send JSON or form data. Operational documents accept the same master fields and <code>newitems</code> lines as the Warehouse module forms. A record endpoint is formed by appending its numeric ID, for example <code>PUT /api/warehouse/items/42</code>.</p>
                </div>
                <div className="col-md-4 section-example no-float">
                    <h3>List inventory</h3><CodeExample>{listExample}</CodeExample>
                    <h3>Create a receipt</h3><CodeExample>{writeExample}</CodeExample>
                </div>
            </div>
        </article>
    </section>;
}

export default WarehouseSection
