import React from 'react'

import CopyButton from './CopyButton.tsx'

type Parameter = {
    name: string;
    type: string;
    required?: boolean;
    description: string;
};

const commonParameters: Parameter[] = [
    { name: 'email', type: 'string', required: true, description: 'Guest email address. The API finds an existing contact or creates a guest customer.' },
    { name: 'items', type: 'array', required: true, description: 'Invoice lines. Use item_id and qty, or description, qty, rate and optional taxes_id.' },
    { name: 'firstname', type: 'string', description: 'Guest first name.' },
    { name: 'lastname', type: 'string', description: 'Guest last name.' },
    { name: 'company', type: 'string', description: 'Guest company name.' },
    { name: 'date', type: 'YYYY-MM-DD', description: 'Invoice date. Defaults to today.' },
    { name: 'duedate', type: 'YYYY-MM-DD', description: 'Due date. Defaults to the configured invoice due period.' },
    { name: 'currency', type: 'integer', description: 'Perfex currency ID. Defaults to the configured currency.' },
    { name: 'update_existing_name', type: 'boolean', description: 'Update the matching guest contact name. Defaults to true.' },
];

const checkoutParameters: Parameter[] = [
    ...commonParameters,
    { name: 'payment_mode', type: 'integer', required: true, description: 'Perfex payment mode ID used to record the payment.' },
    { name: 'amount', type: 'number', description: 'Payment amount. Defaults to the invoice total.' },
    { name: 'payment_date', type: 'YYYY-MM-DD', description: 'Payment date. Defaults to today.' },
    { name: 'transaction_id', type: 'string', description: 'External payment transaction identifier.' },
    { name: 'payment_note', type: 'string', description: 'Optional note saved with the payment.' },
    { name: 'send_email_mode', type: 'combined | none', description: 'Send one email with invoice and receipt PDFs, or no email. Defaults to combined.' },
];

const invoiceBody = `{
  "email": "guest@example.com",
  "firstname": "Alex",
  "lastname": "Guest",
  "items": [
    { "item_id": 12, "qty": 2 },
    { "description": "Setup service", "qty": 1, "rate": 50, "taxes_id": [1] }
  ]
}`;

const checkoutBody = `{
  "email": "guest@example.com",
  "firstname": "Alex",
  "lastname": "Guest",
  "items": [{ "item_id": 12, "qty": 2, "taxes_id": [1] }],
  "payment_mode": 1,
  "transaction_id": "ORDER-1042",
  "send_email_mode": "combined"
}`;

function ParameterTable({ parameters }: { parameters: Parameter[] }) {
    return <table className="table table-hover">
        <thead><tr><th>Field</th><th>Type</th><th>Description</th></tr></thead>
        <tbody>{parameters.map((parameter) => <tr key={parameter.name}>
            <td><code>{parameter.name}</code>{parameter.required && <strong> *</strong>}</td>
            <td><code>{parameter.type}</code></td>
            <td>{parameter.description}</td>
        </tr>)}</tbody>
    </table>;
}

function RequestExample({ path, body }: { path: string; body: string }) {
    const command = `curl -X POST "https://yoursite.com${path}" \\\n  -H "authtoken: YOUR_API_TOKEN" \\\n  -H "Content-Type: application/json" \\\n  -d '${body}'`;

    return <div>
        <ul className="nav nav-tabs nav-tabs-examples"><li className="active"><a>Request</a></li></ul>
        <pre className="astro-code catppuccin-mocha" style={{ backgroundColor: '#1e1e2e', color: '#cdd6f4', overflowX: 'auto' }} tabIndex={0}>
            <code>{command}</code><CopyButton hidden={false} />
        </pre>
    </div>;
}

function Endpoint({ id, title, path, description, parameters, body }: {
    id: string;
    title: string;
    path: string;
    description: string;
    parameters: Parameter[];
    body: string;
}) {
    return <article id={id}>
        <h1>{title}</h1>
        <div className="row pre-post">
            <div className="col-md-7 no-float">
                <pre className="full-pre"><span className="typ typ-post">POST</span><span className="url">{path}</span></pre>
                <div className="endpoint-desc"><p>{description}</p></div>
                <h2 className="sub">Headers</h2>
                <div className="table-responsive-wrapper"><ParameterTable parameters={[{ name: 'authtoken', type: 'string', required: true, description: 'API authentication token with the matching Guest Invoices permission.' }]} /></div>
                <h2 className="sub">JSON body</h2>
                <p>Fields marked with <strong>*</strong> are required.</p>
                <div className="table-responsive-wrapper"><ParameterTable parameters={parameters} /></div>
            </div>
            <div className="col-md-4 section-example no-float"><RequestExample path={path} body={body} /></div>
        </div>
    </article>;
}

function GuestInvoicesSection() {
    return <section id="api-guest-invoices" data-astro-cid-j7pv25f6="">
        <h2 data-astro-cid-j7pv25f6="">Guest Invoices</h2>
        <Endpoint
            id="api-guest-invoices-create"
            title="Create Guest Invoice"
            path="/api/guest_invoices"
            description="Find or create a guest customer and contact, then create an unpaid invoice. Invoice numbering, dates and totals are filled automatically when omitted."
            parameters={commonParameters}
            body={invoiceBody}
        />
        <Endpoint
            id="api-guest-invoices-checkout"
            title="Guest Invoice Checkout"
            path="/api/guest_invoices/checkout"
            description="Create the guest invoice, record its payment and optionally email the guest one message containing both the invoice and payment receipt PDFs."
            parameters={checkoutParameters}
            body={checkoutBody}
        />
    </section>;
}

export default GuestInvoicesSection
