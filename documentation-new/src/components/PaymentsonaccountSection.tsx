import React from 'react'

import CopyButton from './CopyButton.tsx'

export const paymentsonaccountEndpoints = [
    ['GET', 'Discover endpoints', '/api/paymentsonaccount', 'Return the PaymentsOnAccount endpoint catalog.'],
    ['GET', 'List receipts', '/api/paymentsonaccount/receipts', 'List receipts with pagination and optional client, payment mode, transaction, receipt number and date filters.'],
    ['POST', 'Create receipt', '/api/paymentsonaccount/receipts', 'Create a receipt, optionally allocate it to invoices and send the receipt email.'],
    ['GET', 'Get receipt', '/api/paymentsonaccount/receipts/:id', 'Retrieve one receipt together with its invoice applications.'],
    ['PUT', 'Update receipt', '/api/paymentsonaccount/receipts/:id', 'Update writable receipt fields, including amount, payment details, note and receipt number.'],
    ['DELETE', 'Delete receipt', '/api/paymentsonaccount/receipts/:id', 'Delete a receipt through the module model, including its linked core payments.'],
    ['GET', 'List receipt applications', '/api/paymentsonaccount/receipts/:id/applications', 'List the invoice payments created from a receipt.'],
    ['POST', 'Apply receipt to invoices', '/api/paymentsonaccount/receipts/:id/applications', 'Allocate a receipt using invoice_ids or explicit invoice_id and amount allocations.'],
    ['DELETE', 'Delete receipt application', '/api/paymentsonaccount/receipts/:id/applications/:payment_id', 'Remove an applied core payment and synchronize the receipt allocations.'],
    ['POST', 'Send receipt email', '/api/paymentsonaccount/receipts/:id/email', 'Send the standard PaymentsOnAccount receipt email with its PDF attachment.'],
    ['GET', 'Get receipt PDF', '/api/paymentsonaccount/receipts/:id/pdf', 'Return the generated receipt PDF as Base64 with filename and content type.'],
    ['GET', 'List unpaid invoices', '/api/paymentsonaccount/clients/:id/unpaid-invoices', 'List unpaid invoices available for allocation for a client.'],
    ['GET', 'Get client payment modes', '/api/paymentsonaccount/clients/:id/payment-modes', 'Return the payment modes enabled for a client.'],
    ['PUT', 'Update client payment modes', '/api/paymentsonaccount/clients/:id/payment-modes', 'Replace the payment modes enabled for a client.'],
    ['GET', 'Get client statement', '/api/paymentsonaccount/clients/:id/statement', 'Return invoices, receipts, credits and totals for a date range.'],
    ['GET', 'Get receipts report', '/api/paymentsonaccount/reports/receipts', 'Return the paginated receipts report using the standard receipt filters.'],
    ['GET', 'Get credits report', '/api/paymentsonaccount/reports/credits', 'Return the paginated credits report with client and date filters.'],
] as const;

export function paymentsonaccountAnchor(method: string, title: string) {
    return `api-paymentsonaccount-${method.toLowerCase()}-${title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '')}`;
}

function example(method: string, path: string) {
    const url = path.replace(':payment_id', '77').replace(':id', '42');
    const body = method === 'POST' && path.endsWith('/receipts')
        ? ` \\\n  -H "Content-Type: application/json" \\\n  -d '{"client_id":12,"amount":150,"payment_mode":"1","invoice_ids":[35],"send_email":true}'`
        : method === 'POST' && path.endsWith('/applications')
            ? ` \\\n  -H "Content-Type: application/json" \\\n  -d '{"allocations":[{"invoice_id":35,"amount":100}]}'`
            : method === 'PUT' && path.endsWith('/payment-modes')
                ? ` \\\n  -H "Content-Type: application/json" \\\n  -d '{"payment_mode_ids":[1,2]}'`
                : '';
    return `curl -X ${method} "https://yoursite.com${url}" \\\n  -H "authtoken: YOUR_API_TOKEN"${body}`;
}

function CodeExample({ children }: { children: string }) {
    return <pre className="astro-code catppuccin-mocha" style={{ backgroundColor: '#1e1e2e', color: '#cdd6f4', overflowX: 'auto' }} tabIndex={0}>
        <code>{children}</code><CopyButton hidden={false} />
    </pre>;
}

function PaymentsonaccountSection() {
    return <section id="api-paymentsonaccount" data-astro-cid-j7pv25f6="">
        <h2 data-astro-cid-j7pv25f6="">Payments On Account</h2>
        <article id="api-paymentsonaccount-overview">
            <h1>PaymentsOnAccount endpoints</h1>
            <div className="row pre-post"><div className="col-md-7 no-float">
                <div className="endpoint-desc">
                    <p>API v3 exposes receipt management, invoice allocation, email and PDF delivery, client payment-mode settings, statements and reports from PaymentsOnAccount 3.1.1.</p>
                    <p>Authenticate with <code>authtoken</code> and grant the API user the Payments On Account capability matching each HTTP method.</p>
                </div>
                <div className="table-responsive-wrapper"><table className="table table-hover">
                    <thead><tr><th>Method</th><th>Endpoint</th><th>Operation</th></tr></thead>
                    <tbody>{paymentsonaccountEndpoints.map(([method, title, path]) => <tr key={`${method}-${path}`}>
                        <td>{method}</td><td><code>{path}</code></td><td>{title}</td>
                    </tr>)}</tbody>
                </table></div>
            </div><div className="col-md-4 section-example no-float"><CodeExample>{example('GET', '/api/paymentsonaccount')}</CodeExample></div></div>
        </article>
        {paymentsonaccountEndpoints.map(([method, title, path, description]) =>
            <article id={paymentsonaccountAnchor(method, title)} key={`${method}-${path}`}>
                <h1>{title}</h1><div className="row pre-post">
                    <div className="col-md-7 no-float"><pre className="full-pre"><span className={`typ typ-${method.toLowerCase()}`}>{method}</span><span className="url">{path}</span></pre><div className="endpoint-desc"><p>{description}</p></div></div>
                    <div className="col-md-4 section-example no-float"><CodeExample>{example(method, path)}</CodeExample></div>
                </div>
            </article>
        )}
    </section>;
}

export default PaymentsonaccountSection
