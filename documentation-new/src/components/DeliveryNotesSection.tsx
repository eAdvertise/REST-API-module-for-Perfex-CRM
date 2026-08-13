import React from 'react'

import CopyButton from './CopyButton.tsx'

export const deliveryNotesEndpoints = [
    ['GET', 'Discover endpoints', '/api/delivery_notes', 'Return the complete Delivery Notes endpoint catalog.'],
    ['GET', 'List delivery notes', '/api/delivery_notes/notes', 'List delivery notes with pagination and optional clientid, status, currency, sale_agent, project_id, from and to filters.'],
    ['POST', 'Create delivery note', '/api/delivery_notes/notes', 'Create a delivery note using standard sales-document fields and a newitems array.'],
    ['GET', 'Get delivery note', '/api/delivery_notes/notes/:id', 'Retrieve a delivery note with its items, attachments, client and related records.'],
    ['PUT', 'Update delivery note', '/api/delivery_notes/notes/:id', 'Update document fields, existing items, new items, removed items, tags and custom fields.'],
    ['DELETE', 'Delete delivery note', '/api/delivery_notes/notes/:id', 'Delete a delivery note when the module business rules allow it.'],
    ['GET', 'List statuses', '/api/delivery_notes/statuses', 'Return all valid Delivery Notes status IDs.'],
    ['PUT', 'Change status', '/api/delivery_notes/notes/:id/status', 'Change the status and run the module notifications and lifecycle hooks.'],
    ['POST', 'Send delivery note email', '/api/delivery_notes/notes/:id/email', 'Email the delivery note to enabled client contacts, optionally attaching its PDF and adding a CC address.'],
    ['GET', 'Get delivery note PDF', '/api/delivery_notes/notes/:id/pdf', 'Generate and return the PDF as Base64 together with its filename and content type.'],
    ['POST', 'Copy delivery note', '/api/delivery_notes/notes/:id/copy', 'Create a new delivery note by copying an existing one.'],
    ['POST', 'Convert to invoice', '/api/delivery_notes/notes/:id/convert-to-invoice', 'Convert a delivery note to an invoice; use draft=true to create a draft invoice.'],
    ['POST', 'Create from invoice', '/api/delivery_notes/from-invoice/:id', 'Create a delivery note from an invoice; use draft=true for draft status.'],
    ['POST', 'Create from estimate', '/api/delivery_notes/from-estimate/:id', 'Create a delivery note from an estimate; use draft=true for draft status.'],
    ['POST', 'Create from purchase order', '/api/delivery_notes/from-purchase-order/:id', 'Create a delivery note from a Purchase Orders module record; use draft=true for draft status.'],
] as const;

export function deliveryNotesAnchor(method: string, title: string) {
    return `api-delivery-notes-${method.toLowerCase()}-${title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '')}`;
}

function requestBody(method: string, path: string) {
    if (method === 'POST' && path === '/api/delivery_notes/notes') {
        return '{"clientid":12,"currency":1,"date":"2026-08-13","newitems":[{"description":"Delivered item","qty":2,"rate":25}]}'
    }
    if (method === 'PUT' && path.endsWith('/status')) return '{"status":4}'
    if (method === 'POST' && path.endsWith('/email')) return '{"attach_pdf":true,"cc":"warehouse@example.com"}'
    if (method === 'POST' && (path.includes('/from-') || path.endsWith('/convert-to-invoice'))) return '{"draft":false}'
    if (method === 'PUT') return '{"adminnote":"Updated through API v3"}'
    return ''
}

function example(method: string, path: string) {
    const body = requestBody(method, path)
    return `curl -X ${method} "https://yoursite.com${path.replace(':id', '42')}" \
  -H "authtoken: YOUR_API_TOKEN"${body ? ` \\\n  -H "Content-Type: application/json" \\\n  -d '${body}'` : ''}`;
}

function CodeExample({ children }: { children: string }) {
    return <pre className="astro-code catppuccin-mocha" style={{ backgroundColor: '#1e1e2e', color: '#cdd6f4', overflowX: 'auto' }} tabIndex={0}>
        <code>{children}</code><CopyButton hidden={false} />
    </pre>;
}

function DeliveryNotesSection() {
    return <section id="api-delivery-notes" data-astro-cid-j7pv25f6="">
        <h2 data-astro-cid-j7pv25f6="">Delivery Notes</h2>
        <article id="api-delivery-notes-overview">
            <h1>Delivery Notes endpoints</h1>
            <div className="row pre-post"><div className="col-md-7 no-float">
                <div className="endpoint-desc">
                    <p>API v3 exposes the complete Delivery Notes workflow: CRUD, filtering, status transitions, email delivery, PDF generation, copying and document conversions.</p>
                    <p>The Delivery Notes module must be installed and active. Authenticate using the <code>authtoken</code> header and grant the API user the matching GET, POST, PUT or DELETE Delivery Notes capability.</p>
                    <p>Creation accepts the same sales-document structure as the module, including <code>clientid</code>, <code>currency</code>, <code>date</code>, billing and shipping fields, and <code>newitems</code>.</p>
                </div>
                <div className="table-responsive-wrapper"><table className="table table-hover">
                    <thead><tr><th>Method</th><th>Endpoint</th><th>Operation</th></tr></thead>
                    <tbody>{deliveryNotesEndpoints.map(([method, title, path]) => <tr key={`${method}-${path}`}>
                        <td>{method}</td><td><code>{path}</code></td><td>{title}</td>
                    </tr>)}</tbody>
                </table></div>
            </div><div className="col-md-4 section-example no-float"><CodeExample>{example('GET', '/api/delivery_notes')}</CodeExample></div></div>
        </article>
        {deliveryNotesEndpoints.map(([method, title, path, description]) =>
            <article id={deliveryNotesAnchor(method, title)} key={`${method}-${path}`}>
                <h1>{title}</h1><div className="row pre-post">
                    <div className="col-md-7 no-float"><pre className="full-pre"><span className={`typ typ-${method.toLowerCase()}`}>{method}</span><span className="url">{path}</span></pre><div className="endpoint-desc"><p>{description}</p></div></div>
                    <div className="col-md-4 section-example no-float"><CodeExample>{example(method, path)}</CodeExample></div>
                </div>
            </article>
        )}
    </section>;
}

export default DeliveryNotesSection
