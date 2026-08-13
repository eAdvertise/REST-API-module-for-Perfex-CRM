import React from 'react'

import CopyButton from './CopyButton.tsx'

const resources = [
    ['warehouses', 'Warehouse locations and staff assignments', 'CRUD'],
    ['items', 'Products and variants', 'CRUD'],
    ['inventory', 'Computed stock balances', 'GET only'],
    ['receipts', 'Goods receipt documents', 'CRUD'],
    ['receipt_details', 'Goods receipt lines', 'CRUD'],
    ['deliveries', 'Goods delivery documents', 'CRUD'],
    ['delivery_details', 'Goods delivery lines', 'CRUD'],
    ['transfers', 'Internal transfer documents', 'CRUD'],
    ['adjustments', 'Loss adjustment documents', 'CRUD'],
    ['adjustment_details', 'Loss adjustment lines', 'CRUD'],
    ['commodity_types', 'Commodity types', 'CRUD'],
    ['commodity_groups', 'Commodity groups', 'CRUD'],
    ['sub_groups', 'Commodity subgroups', 'CRUD'],
    ['units', 'Measurement units', 'CRUD'],
    ['sizes', 'Item sizes', 'CRUD'],
    ['styles', 'Item styles', 'CRUD'],
    ['bodies', 'Item bodies', 'CRUD'],
    ['colors', 'Item colors', 'CRUD'],
    ['brands', 'Product brands', 'CRUD'],
    ['models', 'Product models', 'CRUD'],
    ['series', 'Product series', 'CRUD'],
    ['inventory_minimums', 'Minimum and maximum stock rules', 'CRUD'],
    ['serial_numbers', 'Inventory serial numbers', 'CRUD'],
    ['stock_takes', 'Stock take documents', 'CRUD'],
    ['stock_take_details', 'Stock take lines', 'CRUD'],
    ['packing_lists', 'Packing list documents', 'CRUD'],
    ['packing_list_details', 'Packing list lines', 'CRUD'],
    ['order_returns', 'Order return documents', 'CRUD'],
    ['return_details', 'Order return lines', 'CRUD'],
    ['approval_settings', 'Approval workflows', 'CRUD'],
    ['approval_details', 'Approval workflow state', 'CRUD'],
    ['warehouse_custom_fields', 'Warehouse custom-field configuration', 'CRUD'],
    ['staff_warehouses', 'Staff-to-warehouse assignments', 'CRUD'],
    ['activity_logs', 'Warehouse activity log', 'CRUD'],
    ['delivery_activity_logs', 'Delivery activity log', 'CRUD'],
    ['transaction_details', 'Inventory transaction details', 'CRUD'],
    ['delivery_order_links', 'Delivery-to-order relations', 'CRUD'],
    ['item_relations', 'Warehouse item relations', 'CRUD'],
    ['omni_shipments', 'Omnichannel shipment records', 'CRUD'],
];

const listExample = `curl "https://yoursite.com/api/warehouse/inventory?warehouse_id=1&commodity_id=42&page=1&per_page=25" \\
  -H "authtoken: YOUR_API_TOKEN"`;

const writeExample = `curl -X POST "https://yoursite.com/api/warehouse/receipts" \\
  -H "authtoken: YOUR_API_TOKEN" \\
  -H "Content-Type: application/json" \\
  -d '{"date_c":"2026-08-12","date_add":"2026-08-12","newitems":[{"commodity_code":42,"warehouse_id":1,"quantities":10,"unit_price":5}]}'`;

const updateExample = `curl -X PUT "https://yoursite.com/api/warehouse/items/42" \\
  -H "authtoken: YOUR_API_TOKEN" \\
  -H "Content-Type: application/json" \\
  -d '{"description":"Updated warehouse item","rate":15}'`;

const deleteExample = `curl -X DELETE "https://yoursite.com/api/warehouse/items/42" \\
  -H "authtoken: YOUR_API_TOKEN"`;

function CodeExample({ children }: { children: string }) {
    return <pre className="astro-code catppuccin-mocha" style={{ backgroundColor: '#1e1e2e', color: '#cdd6f4', overflowX: 'auto' }} tabIndex={0}>
        <code>{children}</code><CopyButton hidden={false} />
    </pre>;
}

function Endpoint({ id, method, path, title, description, example }: {
    id: string;
    method: 'GET' | 'POST' | 'PUT' | 'DELETE';
    path: string;
    title: string;
    description: string;
    example: string;
}) {
    const methodClass = method === 'GET' ? 'typ-get' : method === 'POST' ? 'typ-post' : method === 'PUT' ? 'typ-put' : 'typ-delete';

    return <article id={id}>
        <h1>{title}</h1>
        <div className="row pre-post">
            <div className="col-md-7 no-float">
                <pre className="full-pre"><span className={`typ ${methodClass}`}>{method}</span><span className="url">{path}</span></pre>
                <div className="endpoint-desc"><p>{description}</p></div>
            </div>
            <div className="col-md-4 section-example no-float"><CodeExample>{example}</CodeExample></div>
        </div>
    </article>;
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
                        <p>API v3 exposes all Warehouse data groups through authenticated resource endpoints. Grant the API user the Warehouse capability matching the HTTP method.</p>
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
                    <p>Use <code>page</code>, <code>per_page</code> (maximum 100), <code>from</code>, <code>to</code>, or any real field of the selected resource as an exact-match filter. Call <code>GET /api/warehouse</code> to discover every resource, method and field.</p>
                    <h2 className="sub">Write payloads</h2>
                    <p>Send JSON or form data. Operational documents accept the same master fields and <code>newitems</code> lines as the Warehouse module forms. A record endpoint is formed by appending its numeric ID, for example <code>PUT /api/warehouse/items/42</code>.</p>
                </div>
                <div className="col-md-4 section-example no-float">
                    <h3>List inventory</h3><CodeExample>{listExample}</CodeExample>
                    <h3>Create a receipt</h3><CodeExample>{writeExample}</CodeExample>
                </div>
            </div>
        </article>
        <Endpoint id="api-warehouse-get" method="GET" path="/api/warehouse/:resource/:id?" title="List or retrieve Warehouse records" description="List a resource with pagination and exact-field filters, or append an ID to retrieve one record. Document records include their detail lines." example={listExample} />
        <Endpoint id="api-warehouse-post" method="POST" path="/api/warehouse/:resource" title="Create a Warehouse record" description="Create any writable Warehouse resource. Operational documents are processed by the native Warehouse model; inventory balances are read-only." example={writeExample} />
        <Endpoint id="api-warehouse-put" method="PUT" path="/api/warehouse/:resource/:id" title="Update a Warehouse record" description="Update an existing writable resource. Only real table fields are accepted for schema-backed resources." example={updateExample} />
        <Endpoint id="api-warehouse-delete" method="DELETE" path="/api/warehouse/:resource/:id" title="Delete a Warehouse record" description="Delete an existing writable resource. Native Warehouse deletion handlers are used where they provide relationship and cascade checks." example={deleteExample} />
    </section>;
}

export default WarehouseSection
