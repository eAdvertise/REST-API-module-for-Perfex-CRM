import React from 'react'
import type { JSX } from 'react/jsx-runtime'

import CopyButton from './CopyButton.tsx'
import CodeValue from './CodeValue.tsx'
import ExampleTabs from './ExampleTabs.tsx'
import ParameterTable from './ParameterTable.tsx'
import ApiEndpoint from './ApiEndpoint.tsx'


type ApiEndpointData = {
  articleId: string;
  title: string;
  method: "GET" | "POST" | "PUT" | "DELETE";
  path: string;
  description?: React.ReactNode;
  parameterTableIds: string[];
  exampleTabsDataId: string;
  exampleBaseId: string;
  curlUrl: string;
  curlBody?: string;
  hiddenPaneSuffixes: string[];
};
  
// Component

function ApiEndpoint1({ dataId }: { dataId: string }) {
  const {
    articleId,
    title,
    method,
    path,
    description,
    parameterTableIds,
    exampleTabsDataId,
    exampleBaseId,
    curlUrl,
    curlBody,
    hiddenPaneSuffixes,
  }: ApiEndpointData = getApiEndpointData(dataId);

  return (
    <article id={articleId}>
      <h1>{title}</h1>
      <div className={`row pre-${method.toLowerCase()}`}>
        <div className={"col-md-7 no-float"}>
          <pre className={"full-pre"}>
            <span className={`typ typ-${method.toLowerCase()}`}>
              {method}
            </span>
            <span className={"url"}>
              {path}
            </span>
            <CopyButton hidden={true} />
          </pre>
          {description !== undefined && (
            <div className={"endpoint-desc"}>
              <p>{description}</p>
            </div>
          )}
          <EndpointTables parameterTableIds={parameterTableIds} />
        </div>
        <div className={"col-md-4 section-example no-float"}>
          <ExampleTabs dataId={exampleTabsDataId} />
          <div className={"tab-content"}>
            <div className={"tab-pane active"} id={`${exampleBaseId}-curl`}>
              <CurlExample method={method} url={curlUrl} body={curlBody} />
            </div>
            <HiddenExamplePanes
              exampleBaseId={exampleBaseId}
              suffixes={hiddenPaneSuffixes}
            />
          </div>
        </div>
      </div>
    </article>
  );
}
  

// Subcomponents

function EndpointTables({
  parameterTableIds,
}: {
  parameterTableIds: string[];
}) {
  return (
    <>
      <h2 className={"sub"}>Headers</h2>
      <div className={"table-responsive-wrapper"}>
        <ParameterTable dataId={parameterTableIds[0]} />
      </div>
      {parameterTableIds.slice(1).map((tableId) => (
        <div key={tableId}>
          <h2 className={"sub"}>Parameters</h2>
          <div className={"table-responsive-wrapper"}>
            <ParameterTable dataId={tableId} />
          </div>
        </div>
      ))}
    </>
  );
}

function CurlExample({
  method,
  url,
  body,
}: {
  method: "GET" | "POST" | "PUT" | "DELETE";
  url: string;
  body?: string;
}) {
  const hasBody = body !== undefined;

  return (
    <pre
      className={"astro-code catppuccin-mocha"}
      style={{
        backgroundColor: "#1e1e2e",
        color: "#cdd6f4",
        overflowX: "auto",
      }}
      tabIndex={"0"}
    >
      <code>
        <span className={"line"}>
          <span style={{ color: "#89B4FA", fontStyle: "italic" }}>
            curl
          </span>
          <span style={{ color: "#A6E3A1" }}>
            {` -X`}
          </span>
          <span style={{ color: "#A6E3A1" }}>
            {` ${method}`}
          </span>
          <span style={{ color: "#A6E3A1" }}>
            {` "${url}"`}
          </span>
          <span style={{ color: "#F5C2E7" }}>
            {` \\`}
          </span>
        </span>
        <span className={"line"}>
          <span style={{ color: "#A6E3A1" }}>
            {`  -H`}
          </span>
          <span style={{ color: "#A6E3A1" }}>
            {` "authtoken: YOUR_API_TOKEN"`}
          </span>
          {hasBody && (
            <span style={{ color: "#F5C2E7" }}>
              {` \\`}
            </span>
          )}
        </span>
        {hasBody && (
          <>
            <span className={"line"}>
              <span style={{ color: "#A6E3A1" }}>
                {`  -H`}
              </span>
              <span style={{ color: "#A6E3A1" }}>
                {` "Content-Type: application/json"`}
              </span>
              <span style={{ color: "#F5C2E7" }}>
                {` \\`}
              </span>
            </span>
            <span className={"line"}>
              <span style={{ color: "#A6E3A1" }}>
                {`  -d`}
              </span>
              <span style={{ color: "#A6E3A1" }}>
                {` '${body}'`}
              </span>
            </span>
          </>
        )}
      </code>
      <CopyButton hidden={false} />
    </pre>
  );
}

function HiddenExamplePanes({
  exampleBaseId,
  suffixes,
}: {
  exampleBaseId: string;
  suffixes: string[];
}) {
  return (
    <>
      {suffixes.map((suffix) => (
        <div
          className={"tab-pane sf-hidden"}
          id={`${exampleBaseId}-${suffix}`}
          key={suffix}
        >
        </div>
      ))}
    </>
  );
}
  

function getApiEndpointData(id): ApiEndpointData  {
    switch (String(id)) {
    case "0":
        return 
                {
                  "articleId": "api-mcp-mcp-server",
                  "title": "MCP Server (AI Agents)",
                  "method": "POST",
                  "path": "/api/mcp",
                  "description": `Model Context Protocol server over Streamable HTTP (JSON-RPC 2.0). Exposes 148 permission-filtered CRM tools (list/get/search/create/update/delete across 22 resources, plus lookups and utilities) to Claude Desktop, ChatGPT, Cursor, n8n AI Agent and any MCP client. Supported methods: initialize, ping, tools/list (single page), tools/call. Batches and notifications follow the JSON-RPC spec. Tool errors are returned as isError results so agents can react. Disabled by default - enable it under Setup > API > Settings. Configure your MCP client with this URL and the authtoken header.`,
                  "parameterTableIds": ["0"],
                  "exampleTabsDataId": "0",
                  "exampleBaseId": "ex-mcp-mcp-server",
                  "curlUrl": "https://yoursite.com/api/mcp",
                  "curlBody": `{ "field": "value" }`,
                  "hiddenPaneSuffixes": ["0", "1", "2"]
                }
            ;
    case "1":
        return 
                {
                  "articleId": "api-automation-zapier-route-handler",
                  "title": "Automation Route Handler",
                  "method": "GET",
                  "path": "/api/zapier/{method}/{resource}",
                  "description": (
                    <p>
                      This is a routing method that maps to specific Zapier endpoints. Use the direct endpoints (poll_get, test_get, resources_get) instead.
                    </p>
                  ),
                  "parameterTableIds": ["35", "36"],
                  "exampleTabsDataId": "2",
                  "exampleBaseId": "ex-automation-zapier-route-handler",
                  "curlUrl": "https://yoursite.com/api/zapier/poll/customers",
                  "curlBody": undefined,
                  "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    case "2":
        return 
            {
              "articleId": "api-automation-poll-data",
              "title": "Poll for New Data",
              "method": "GET",
              "path": "/api/zapier/poll/{resource}",
              "description": (
                <p>
                  Polling endpoint used by automation platforms (Zapier, Make.com, n8n) to retrieve new or updated records. Returns records that were created or modified after the specified timestamp.
                </p>
              ),
              "parameterTableIds": ["35", "38"],
              "exampleTabsDataId": "2",
              "exampleBaseId": "ex-automation-poll-data",
              "curlUrl": "https://yoursite.com/api/zapier/poll/customers",
              "curlBody": undefined,
              "hiddenPaneSuffixes": ["0", "1"]
            }
          ;
    case "3":
        return 
                {
                  "articleId": "api-automation-list-resources",
                  "title": "List Available Resources",
                  "method": "GET",
                  "path": "/api/zapier/resources",
                  "description": "Returns a list of all available resources that can be used for polling and testing. This endpoint is used by automation platforms to discover available triggers.",
                  "parameterTableIds": ["35"],
                  "exampleTabsDataId": "3",
                  "exampleBaseId": "ex-automation-list-resources",
                  "curlUrl": "https://yoursite.com/api/zapier/resources",
                  "curlBody": undefined,
                  "hiddenPaneSuffixes": ["0"]
                }
            ;
    case "4":
        return 
                {
                  "articleId": "api-automation-test-trigger",
                  "title": "Test Trigger",
                  "method": "GET",
                  "path": "/api/zapier/test/{resource}",
                  "description": (
                    <p>
                      Returns sample data for testing automation triggers. Used by automation platforms to validate trigger configuration and show sample data structure to users.
                    </p>
                  ),
                  "parameterTableIds": ["35", "1"],
                  "exampleTabsDataId": "2",
                  "exampleBaseId": "ex-automation-test-trigger",
                  "curlUrl": "https://yoursite.com/api/zapier/test/customers",
                  "curlBody": undefined,
                  "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    case "5":
        return 
                {
                  "articleId": "api-batch-batch-operations",
                  "title": "Batch Operations",
                  "method": "POST",
                  "path": "/api/batch",
                  "description": "Execute up to 50 operations in one request, in order, with continue-on-error. Operations use the same names, arguments and per-operation permission rules as the MCP tools (e.g. customers_create, invoices_get, leads_search). Each item returns status plus result or error, with completed/failed counters in the envelope.",
                  "parameterTableIds": ["0"],
                  "exampleTabsDataId": "0",
                  "exampleBaseId": "ex-batch-batch-operations",
                  "curlUrl": "https://yoursite.com/api/batch",
                  "curlBody": `{ "field": "value" }`,
                  "hiddenPaneSuffixes": ["0", "1", "2"]
                }
            ;
    case "6":
        return 
                {
                  "articleId": "api-leads-delete-lead",
                  "title": "Delete a Lead",
                  "method": "DELETE",
                  "path": "/api/delete/leads/{id}",
                  "description": undefined,
                  "parameterTableIds": ["0", "2"],
                  "exampleTabsDataId": "2",
                  "exampleBaseId": "ex-leads-delete-lead",
                  "curlUrl": "https://yoursite.com/api/delete/leads/123",
                  "curlBody": undefined,
                  "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    case "7":
        return 
                {
                  "articleId": "api-leads-post-lead",
                  "title": "Add New Lead",
                  "method": "POST",
                  "path": "/api/leads",
                  "description": undefined,
                  "parameterTableIds": ["0", "39"],
                  "exampleTabsDataId": "2",
                  "exampleBaseId": "ex-leads-post-lead",
                  "curlUrl": "https://yoursite.com/api/leads",
                  "curlBody": `{ "source": "...", "status": "...", "name": "...", "assigned": "..." }`,
                  "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    case "8":
        return 
                {
                  "articleId": "api-leads-get-leads",
                  "title": "Request all Leads",
                  "method": "GET",
                  "path": "/api/leads/",
                  "description": undefined,
                  "parameterTableIds": ["0"],
                  "exampleTabsDataId": "2",
                  "exampleBaseId": "ex-leads-get-leads",
                  "curlUrl": "https://yoursite.com/api/leads/",
                  "curlBody": undefined,
                  "hiddenPaneSuffixes": ["0","1"]
                }
            ;
    case "9":
        return 
                {
                  "articleId": "api-leads-get-lead",
                  "title": "Request Lead information",
                  "method": "GET",
                  "path": "/api/leads/{id}",
                  "description": undefined,
                  "parameterTableIds": ["0", "3"],
                  "exampleTabsDataId": "2",
                  "exampleBaseId": "ex-leads-get-lead",
                  "curlUrl": "https://yoursite.com/api/leads/123",
                  "curlBody": undefined,
                  "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    case "10":
        return 
            {
              "articleId": "api-leads-put-lead",
              "title": "Update a lead",
              "method": "PUT",
              "path": "/api/leads/{id}",
              "description": undefined,
              "parameterTableIds": ["0", "40"],
              "exampleTabsDataId": "2",
              "exampleBaseId": "ex-leads-put-lead",
              "curlUrl": "https://yoursite.com/api/leads/123",
              "curlBody": '{ "source": "...", "status": "...", "name": "...", "assigned": "..." }',
              "hiddenPaneSuffixes": ["0", "1"]
            }
          ;
    case "11":
        return 
                {
                  "articleId": "api-leads-get-lead-search",
                  "title": "Search Lead Information",
                  "method": "GET",
                  "path": "/api/leads/search/{keysearch}",
                  "description": undefined,
                  "parameterTableIds": ["0", "4"],
                  "exampleTabsDataId": "2",
                  "exampleBaseId": "ex-leads-get-lead-search",
                  "curlUrl": "https://yoursite.com/api/leads/search/acme",
                  "curlBody": undefined,
                  "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    case "12":
        return 
                {
                  "articleId": "api-invoices-post-invoice",
                  "title": "Add New invoice",
                  "method": "POST",
                  "path": "/api/invoices",
                  "description": undefined,
                  "parameterTableIds": ["0", "41"],
                  "exampleTabsDataId": "4",
                  "exampleBaseId": "ex-invoices-post-invoice",
                  "curlUrl": "https://yoursite.com/api/invoices",
                  "curlBody": "{ \"clientid\": \"...\", \"number\": \"...\", \"date\": \"...\", \"currency\": \"...\", \"newitems\": \"...\", \"subtotal\": \"...\", \"total\": \"...\", \"billing_street\": \"...\", \"allowed_payment_modes\": \"...\" }",
                  "hiddenPaneSuffixes": ["0","1","2","3","4","5","6","7"]
                }
            ;
    case "13":
        return 
                {
                  "articleId": "api-invoices-delete-invoice",
                  "title": "Delete invoice",
                  "method": "DELETE",
                  "path": "/api/invoices/{id}",
                  "description": undefined,
                  "parameterTableIds": ["0"],
                  "exampleTabsDataId": "2",
                  "exampleBaseId": "ex-invoices-delete-invoice",
                  "curlUrl": "https://yoursite.com/api/invoices/123",
                  "curlBody": undefined,
                  "hiddenPaneSuffixes": ["0","1"]
                }
            ;
    case "14":
        return 
                {
                  "articleId": "api-invoices-get-invoice",
                  "title": "Request invoice information",
                  "method": "GET",
                  "path": "/api/invoices/{id}",
                  "description": undefined,
                  "parameterTableIds": ["0", "5"],
                  "exampleTabsDataId": "2",
                  "exampleBaseId": "ex-invoices-get-invoice",
                  "curlUrl": "https://yoursite.com/api/invoices/123",
                  "curlBody": undefined,
                  "hiddenPaneSuffixes": ["0","1"]
                }
            ;
    case "15":
        return 
                {
                  "articleId": "api-invoices-put-invoice",
                  "title": "Update invoice",
                  "method": "PUT",
                  "path": "/api/invoices/{id}",
                  "description": undefined,
                  "parameterTableIds": ["0", "42"],
                  "exampleTabsDataId": "1",
                  "exampleBaseId": "ex-invoices-put-invoice",
                  "curlUrl": "https://yoursite.com/api/invoices/123",
                  "curlBody": `{ "clientid": "...", "number": "...", "date": "...", "currency": "...", "newitems": "...", "subtotal": "...", "total": "...", "billing_street": "...", "allowed_payment_modes": "..." }`,
                  "hiddenPaneSuffixes": ["0", "1", "2"]
                }
            ;
    case "16":
        return 
                {
                    "articleId": "api-invoices-send-invoice",
                    "title": "Send invoice by email",
                    "method": "POST",
                    "path": "/api/invoices/{id}/send",
                    "description": (
                        <>
                            Emails the invoice to the customer using Perfex's own invoice email template, PDF attachment and SMTP configuration, and marks the invoice as sent. Requires the Invoices "Send email" permission. Send an{" "}
                            <CodeValue text="Idempotency-Key" />
                            {" "}header to make retries safe. The customer must have at least one contact flagged to receive invoice emails, and Setup -&gt; Email (SMTP) must be configured or nothing is sent.
                        </>
                    ),
                    "parameterTableIds": ["0", "43"],
                    "exampleTabsDataId": "2",
                    "exampleBaseId": "ex-invoices-send-invoice",
                    "curlUrl": "https://yoursite.com/api/invoices/123/send",
                    "curlBody": `{ "field": "value" }`,
                    "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    case "17":
        return 
                {
                  "articleId": "api-invoices-get-invoice-search",
                  "title": "Search invoice information",
                  "method": "GET",
                  "path": "/api/invoices/search/{keysearch}",
                  "description": undefined,
                  "parameterTableIds": ["0", "4"],
                  "exampleTabsDataId": "2",
                  "exampleBaseId": "ex-invoices-get-invoice-search",
                  "curlUrl": "https://yoursite.com/api/invoices/search/acme",
                  "curlBody": undefined,
                  "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    case "18":
        return 
            {
              "articleId": "api-customers-post-customer",
              "title": "Add New Customer",
              "method": "POST",
              "path": "/api/customers",
              "description": undefined,
              "parameterTableIds": ["0", "46"],
              "exampleTabsDataId": "2",
              "exampleBaseId": "ex-customers-post-customer",
              "curlUrl": "https://yoursite.com/api/customers",
              "curlBody": '{ "company": "..." }',
              "hiddenPaneSuffixes": ["0", "1"]
            }
          ;
    case "19":
        return 
            {
              "articleId": "api-customers-get-customer",
              "title": "Request customer information",
              "method": "GET",
              "path": "/api/customers/{id}",
              "description": undefined,
              "parameterTableIds": ["0", "6"],
              "exampleTabsDataId": "2",
              "exampleBaseId": "ex-customers-get-customer",
              "curlUrl": "https://yoursite.com/api/customers/123",
              "curlBody": undefined,
              "hiddenPaneSuffixes": ["0", "1"]
            }
          ;
    case "20":
        return 
                {
                  "articleId": "api-customers-put-customer",
                  "title": "Update a Customer",
                  "method": "PUT",
                  "path": "/api/customers/{id}",
                  "description": undefined,
                  "parameterTableIds": ["0", "47"],
                  "exampleTabsDataId": "2",
                  "exampleBaseId": "ex-customers-put-customer",
                  "curlUrl": "https://yoursite.com/api/customers/123",
                  "curlBody": '{ "company": "..." }',
                  "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    case "21":
        return 
                {
                    "articleId": "api-customers-get-customer-search",
                    "title": "Search Customer Information",
                    "method": "GET",
                    "path": "/api/customers/search/{keysearch}",
                    "description": undefined,
                    "parameterTableIds": ["0", "4"],
                    "exampleTabsDataId": "2",
                    "exampleBaseId": "ex-customers-get-customer-search",
                    "curlUrl": "https://yoursite.com/api/customers/search/acme",
                    "curlBody": undefined,
                    "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    case "22":
        return 
                {
                  "articleId": "api-customers-delete-customer",
                  "title": "Delete a Customer",
                  "method": "DELETE",
                  "path": "/api/delete/customers/{id}",
                  "description": undefined,
                  "parameterTableIds": ["0", "7"],
                  "exampleTabsDataId": "2",
                  "exampleBaseId": "ex-customers-delete-customer",
                  "curlUrl": "https://yoursite.com/api/delete/customers/123",
                  "curlBody": undefined,
                  "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    case "23":
        return 
            {
              "articleId": "api-calendar-events-get-calendar-events",
              "title": "Get All Calendar Events",
              "method": "GET",
              "path": "/api/calendar/",
              "description": undefined,
              "parameterTableIds": ["0"],
              "exampleTabsDataId": "2",
              "exampleBaseId": "ex-calendar-events-get-calendar-events",
              "curlUrl": "https://yoursite.com/api/calendar/",
              "curlBody": undefined,
              "hiddenPaneSuffixes": ["0", "1"]
            }
          ;
    case "24":
        return 
            {
              "articleId": "api-calendar-events-post-calendar-event",
              "title": "Create a new Calendar Event",
              "method": "POST",
              "path": "/api/calendar/",
              "description": undefined,
              "parameterTableIds": ["0", "48"],
              "exampleTabsDataId": "2",
              "exampleBaseId": "ex-calendar-events-post-calendar-event",
              "curlUrl": "https://yoursite.com/api/calendar/",
              "curlBody": '{ "title": "...", "description": "...", "start": "...", "reminder_before_type": "...", "reminder_before": "...", "color": "...", "userid": "...", "isstartnotified": "...", "public": "..." }',
              "hiddenPaneSuffixes": ["0", "1"]
            }
          ;
    case "25":
        return 
                {
                  "articleId": "api-calendar-events-delete-calendar-event",
                  "title": "Delete a Calendar Event",
                  "method": "DELETE",
                  "path": "/api/calendar/{id}",
                  "description": undefined,
                  "parameterTableIds": ["0", "8"],
                  "exampleTabsDataId": "2",
                  "exampleBaseId": "ex-calendar-events-delete-calendar-event",
                  "curlUrl": "https://yoursite.com/api/calendar/123",
                  "curlBody": undefined,
                  "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    case "26":
        return 
            {
              "articleId": "api-calendar-events-get-calendar-event",
              "title": "Request Specific Event Information",
              "method": "GET",
              "path": "/api/calendar/{id}",
              "description": undefined,
              "parameterTableIds": ["0", "9"],
              "exampleTabsDataId": "2",
              "exampleBaseId": "ex-calendar-events-get-calendar-event",
              "curlUrl": "https://yoursite.com/api/calendar/123",
              "curlBody": undefined,
              "hiddenPaneSuffixes": ["0", "1"]
            }
          ;
    case "27":
        return 
                {
                  "articleId": "api-calendar-events-update-calendar-event",
                  "title": "Update a Calendar Event",
                  "method": "PUT",
                  "path": "/api/calendar/{id}",
                  "description": undefined,
                  "parameterTableIds": ["0", "10"],
                  "exampleTabsDataId": "2",
                  "exampleBaseId": "ex-calendar-events-update-calendar-event",
                  "curlUrl": "https://yoursite.com/api/calendar/123",
                  "curlBody": '{ "unique": "..." }',
                  "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    case "28":
        return 
                {
                  "articleId": "api-common-get-common-data",
                  "title": "Get Common Data",
                  "method": "GET",
                  "path": "/api/common/{type}",
                  "description": (
                    <p>
                      Retrieves common system data based on the specified type. This is a routing method that calls the appropriate helper method.
                    </p>
                  ),
                  "parameterTableIds": ["0", "11"],
                  "exampleTabsDataId": "5",
                  "exampleBaseId": "ex-common-get-common-data",
                  "curlUrl": "https://yoursite.com/api/common/tax_data",
                  "curlBody": undefined,
                  "hiddenPaneSuffixes": ["0", "1", "2", "3", "4"]
                }
            ;
    case "29":
        return 
                {
                  "articleId": "api-contacts-post-contact",
                  "title": "Add New Contact",
                  "method": "POST",
                  "path": "/api/contacts/",
                  "description": undefined,
                  "parameterTableIds": ["0", "49"],
                  "exampleTabsDataId": "1",
                  "exampleBaseId": "ex-contacts-post-contact",
                  "curlUrl": "https://yoursite.com/api/contacts/",
                  "curlBody": `{ "customer_id": "...", "firstname": "...", "lastname": "...", "email": "..." }`,
                  "hiddenPaneSuffixes": ["0", "1", "2"]
                }
            ;
    case "30":
        return 
                {
                  "articleId": "api-contacts-get-contact",
                  "title": "List all Contacts of a Customer",
                  "method": "GET",
                  "path": "/api/contacts/{customer_id}/{contact_id}",
                  "description": undefined,
                  "parameterTableIds": ["0", "51"],
                  "exampleTabsDataId": "2",
                  "exampleBaseId": "ex-contacts-get-contact",
                  "curlUrl": "https://yoursite.com/api/contacts/123/123",
                  "curlBody": undefined,
                  "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    case "31":
        return 
                {
                  "articleId": "api-contacts-put-contact",
                  "title": "Update Contact Information",
                  "method": "PUT",
                  "path": "/api/contacts/{id}",
                  "description": undefined,
                  "parameterTableIds": ["0", "50"],
                  "exampleTabsDataId": "1",
                  "exampleBaseId": "ex-contacts-put-contact",
                  "curlUrl": "https://yoursite.com/api/contacts/123",
                  "curlBody": '{ "firstname": "...", "lastname": "...", "email": "..." }',
                  "hiddenPaneSuffixes": ["0", "1", "2"]
                }
            ;
    case "32":
        return 
                {
                  "articleId": "api-contacts-get-contact-search",
                  "title": "Search Contact Information",
                  "method": "GET",
                  "path": "/api/contacts/search/{keysearch}",
                  "description": undefined,
                  "parameterTableIds": ["0", "12"],
                  "exampleTabsDataId": "2",
                  "exampleBaseId": "ex-contacts-get-contact-search",
                  "curlUrl": "https://yoursite.com/api/contacts/search/acme",
                  "curlBody": undefined,
                  "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    case "33":
        return 
                {
                  "articleId": "api-contacts-delete-contact",
                  "title": "Delete Contact",
                  "method": "DELETE",
                  "path": "/api/delete/contacts/{id}",
                  "description": undefined,
                  "parameterTableIds": ["0", "13"],
                  "exampleTabsDataId": "2",
                  "exampleBaseId": "ex-contacts-delete-contact",
                  "curlUrl": "https://yoursite.com/api/delete/contacts/123",
                  "curlBody": undefined,
                  "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    case "34":
        return 
            {
              "articleId": "api-contracts-post-contract",
              "title": "Add New Contract",
              "method": "POST",
              "path": "/api/contracts",
              "description": undefined,
              "parameterTableIds": ["0", "58"],
              "exampleTabsDataId": "6",
              "exampleBaseId": "ex-contracts-post-contract",
              "curlUrl": "https://yoursite.com/api/contracts",
              "curlBody": '{ "subject": "...", "datestart": "...", "client": "...", "dateend": "...", "contract_type": "...", "contract_value": "...", "description": "...", "content": "..." }',
              "hiddenPaneSuffixes": ["0", "1", "2", "3", "4"]
            }
          ;
    default:
        return 
                {
                  "articleId": "api-mcp-mcp-server",
                  "title": "MCP Server (AI Agents)",
                  "method": "POST",
                  "path": "/api/mcp",
                  "description": `Model Context Protocol server over Streamable HTTP (JSON-RPC 2.0). Exposes 148 permission-filtered CRM tools (list/get/search/create/update/delete across 22 resources, plus lookups and utilities) to Claude Desktop, ChatGPT, Cursor, n8n AI Agent and any MCP client. Supported methods: initialize, ping, tools/list (single page), tools/call. Batches and notifications follow the JSON-RPC spec. Tool errors are returned as isError results so agents can react. Disabled by default - enable it under Setup > API > Settings. Configure your MCP client with this URL and the authtoken header.`,
                  "parameterTableIds": ["0"],
                  "exampleTabsDataId": "0",
                  "exampleBaseId": "ex-mcp-mcp-server",
                  "curlUrl": "https://yoursite.com/api/mcp",
                  "curlBody": `{ "field": "value" }`,
                  "hiddenPaneSuffixes": ["0", "1", "2"]
                }
            ;
    }
}


export default ApiEndpoint1
