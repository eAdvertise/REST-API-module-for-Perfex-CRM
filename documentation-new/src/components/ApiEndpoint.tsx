import React from 'react'
import type { JSX } from 'react/jsx-runtime'

import CopyButton from './CopyButton.tsx'
import ExampleTabs from './ExampleTabs.tsx'
import ParameterTable from './ParameterTable.tsx'


        type ApiEndpointData = {
            articleId: string;
            title: string;
            method: "GET" | "POST" | "PUT" | "DELETE";
            path: string;
            endpointDescription?: string;
            parameterTableDataId?: string;
            exampleTabsDataId: string;
            exampleBaseId: string;
            curlUrl: string;
            curlBody?: string;
            hiddenPaneSuffixes: string[];
        };
    
// Component

        function ApiEndpoint({ dataId }: { dataId: string }) {
            const data: ApiEndpointData = getApiEndpointData(dataId);

            return (
                <article id={data.articleId}>
                    <h1>{data.title}</h1>
                    <div className={`row pre-${data.method.toLowerCase()}`}>
                        <div className={"col-md-7 no-float"}>
                            <pre className={"full-pre"}>
                                <span className={`typ typ-${data.method.toLowerCase()}`}>
                                    {data.method}
                                </span>
                                <span className={"url"}>
                                    {data.path}
                                </span>
                                <CopyButton hidden={true} />
                            </pre>
                            {data.endpointDescription !== undefined && (
                                <div className={"endpoint-desc"}>
                                    <p>{data.endpointDescription}</p>
                                </div>
                            )}
                            <h2 className={"sub"}>Headers</h2>
                            <div className={"table-responsive-wrapper"}>
                                <ParameterTable dataId="0" />
                            </div>
                            {data.parameterTableDataId !== undefined && (
                                <>
                                    <h2 className={"sub"}>Parameters</h2>
                                    <div className={"table-responsive-wrapper"}>
                                        <ParameterTable dataId={data.parameterTableDataId} />
                                    </div>
                                </>
                            )}
                        </div>
                        <div className={"col-md-4 section-example no-float"}>
                            <ExampleTabs dataId={data.exampleTabsDataId} />
                            <div className={"tab-content"}>
                                <div
                                    className={"tab-pane active"}
                                    id={`${data.exampleBaseId}-curl`}
                                >
                                    <CurlExample
                                        method={data.method}
                                        url={data.curlUrl}
                                        body={data.curlBody}
                                    />
                                </div>
                                {data.hiddenPaneSuffixes.map((suffix) => (
                                    <HiddenExamplePane
                                        key={suffix}
                                        id={`${data.exampleBaseId}-${suffix}`}
                                    />
                                ))}
                            </div>
                        </div>
                    </div>
                </article>
            );
        }
    

// Subcomponents

        function HiddenExamplePane({ id }: { id: string }) {
            return <div className={"tab-pane sf-hidden"} id={id}></div>;
        }

        function CurlExample({
            method,
            url,
            body
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
                        overflowX: "auto"
                    }}
                    tabIndex={"0"}
                >
                    <code>
                        <span className={"line"}>
                            <span style={{ color: "#89B4FA", fontStyle: "italic" }}>
                                curl
                            </span>
                            <span style={{ color: "#A6E3A1" }}>
                                {" -X"}
                            </span>
                            <span style={{ color: "#A6E3A1" }}>
                                {` ${method}`}
                            </span>
                            <span style={{ color: "#A6E3A1" }}>
                                {` "${url}"`}
                            </span>
                            <span style={{ color: "#F5C2E7" }}>
                                {" \\"}
                            </span>
                        </span>
                        <span className={"line"}>
                            <span style={{ color: "#A6E3A1" }}>
                                {"  -H"}
                            </span>
                            <span style={{ color: "#A6E3A1" }}>
                                {' "authtoken: YOUR_API_TOKEN"'}
                            </span>
                            {hasBody && (
                                <span style={{ color: "#F5C2E7" }}>
                                    {" \\"}
                                </span>
                            )}
                        </span>
                        {hasBody && (
                            <>
                                <span className={"line"}>
                                    <span style={{ color: "#A6E3A1" }}>
                                        {"  -H"}
                                    </span>
                                    <span style={{ color: "#A6E3A1" }}>
                                        {' "Content-Type: application/json"'}
                                    </span>
                                    <span style={{ color: "#F5C2E7" }}>
                                        {" \\"}
                                    </span>
                                </span>
                                <span className={"line"}>
                                    <span style={{ color: "#A6E3A1" }}>
                                        {"  -d"}
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
    

function getApiEndpointData(id): ApiEndpointData  {
    switch (String(id)) {
    case "0":
        return void 0 ||
                {
                  "articleId": "api-contracts-delete-contract",
                  "title": "Delete Contract",
                  "method": "DELETE",
                  "path": "/api/contracts/{id}",
                  "endpointDescription": undefined,
                  "parameterTableDataId": undefined,
                  "exampleTabsDataId": "2",
                  "exampleBaseId": "ex-contracts-delete-contract",
                  "curlUrl": "https://yoursite.com/api/contracts/123",
                  "curlBody": undefined,
                  "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    case "1":
        return 
                {
                    "articleId": "api-contracts-get-contract",
                    "title": "Request Contract information",
                    "method": "GET",
                    "path": "/api/contracts/{id}",
                    "endpointDescription": undefined,
                    "parameterTableDataId": "5",
                    "exampleTabsDataId": "3",
                    "exampleBaseId": "ex-contracts-get-contract",
                    "curlUrl": "https://yoursite.com/api/contracts/123",
                    "curlBody": undefined,
                    "hiddenPaneSuffixes": ["0"]
                }
            ;
    case "2":
        return 
                {
                    "articleId": "api-contracts-get-contract-search",
                    "title": "Search contracts",
                    "method": "GET",
                    "path": "/api/contracts/search/{keysearch}",
                    "endpointDescription": undefined,
                    "parameterTableDataId": "14",
                    "exampleTabsDataId": "1",
                    "exampleBaseId": "ex-contracts-get-contract-search",
                    "curlUrl": "https://yoursite.com/api/contracts/search/acme",
                    "curlBody": undefined,
                    "hiddenPaneSuffixes": ["0", "1", "2"]
                }
            ;
    case "3":
        return 
                {
                    "articleId": "api-credit-notes-post-credit-notes",
                    "title": "Add New Credit Notes",
                    "method": "POST",
                    "path": "/api/credit_notes",
                    "endpointDescription": undefined,
                    "parameterTableDataId": "59",
                    "exampleTabsDataId": "7",
                    "exampleBaseId": "ex-credit-notes-post-credit-notes",
                    "curlUrl": "https://yoursite.com/api/credit_notes",
                    "curlBody": '{ "clientid": "...", "date": "...", "number": "...", "currency": "...", "newitems": "...", "billing_street": "...", "subtotal": "...", "total": "..." }',
                    "hiddenPaneSuffixes": ["0", "1", "2", "3", "4", "5"]
                }
            ;
    case "4":
        return 
            {
              "articleId": "api-credit-notes-put-credit-notes",
              "title": "Update a Credit Note",
              "method": "PUT",
              "path": "/api/credit_notes",
              "endpointDescription": undefined,
              "parameterTableDataId": "60",
              "exampleTabsDataId": "7",
              "exampleBaseId": "ex-credit-notes-put-credit-notes",
              "curlUrl": "https://yoursite.com/api/credit_notes",
              "curlBody": '{ "clientid": "...", "date": "...", "number": "...", "currency": "...", "newitems": "...", "items": "...", "removed_items": "...", "billing_street": "...", "subtotal": "...", "total": "..." }',
              "hiddenPaneSuffixes": ["0", "1", "2", "3", "4", "5"]
            }
          ;
    case "5":
        return 
                {
                    "articleId": "api-credit-notes-delete-credit-note",
                    "title": "Delete Credit Note",
                    "method": "DELETE",
                    "path": "/api/credit_notes/{id}",
                    "endpointDescription": undefined,
                    "parameterTableDataId": undefined,
                    "exampleTabsDataId": "2",
                    "exampleBaseId": "ex-credit-notes-delete-credit-note",
                    "curlUrl": "https://yoursite.com/api/credit_notes/123",
                    "curlBody": undefined,
                    "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    case "6":
        return 
                {
                    "articleId": "api-credit-notes-get-credit-notes",
                    "title": "Request Credit notes information",
                    "method": "GET",
                    "path": "/api/credit_notes/{id}",
                    "endpointDescription": undefined,
                    "parameterTableDataId": undefined,
                    "exampleTabsDataId": "2",
                    "exampleBaseId": "ex-credit-notes-get-credit-notes",
                    "curlUrl": "https://yoursite.com/api/credit_notes/123",
                    "curlBody": undefined,
                    "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    case "7":
        return 
                {
                    "articleId": "api-credit-notes-get-credit-notes-search",
                    "title": "Search credit notes item information",
                    "method": "GET",
                    "path": "/api/credit_notes/search/{keysearch}",
                    "endpointDescription": undefined,
                    "parameterTableDataId": "12",
                    "exampleTabsDataId": "2",
                    "exampleBaseId": "ex-credit-notes-get-credit-notes-search",
                    "curlUrl": "https://yoursite.com/api/credit_notes/search/acme",
                    "curlBody": undefined,
                    "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    case "8":
        return 
                {
                    "articleId": "api-estimates-post-estimates",
                    "title": "Add New Estimates",
                    "method": "POST",
                    "path": "/api/estimates",
                    "endpointDescription": undefined,
                    "parameterTableDataId": "61",
                    "exampleTabsDataId": "7",
                    "exampleBaseId": "ex-estimates-post-estimates",
                    "curlUrl": "https://yoursite.com/api/estimates",
                    "curlBody": '{ "clientid": "...", "number": "...", "date": "...", "currency": "...", "newitems": "...", "subtotal": "...", "total": "...", "billing_street": "..." }',
                    "hiddenPaneSuffixes": ["0","1","2","3","4","5"]
                }
            ;
    case "9":
        return 
                {
                    "articleId": "api-estimates-delete-estimate",
                    "title": "Delete Estimate",
                    "method": "DELETE",
                    "path": "/api/estimates/{id}",
                    "endpointDescription": undefined,
                    "parameterTableDataId": undefined,
                    "exampleTabsDataId": "2",
                    "exampleBaseId": "ex-estimates-delete-estimate",
                    "curlUrl": "https://yoursite.com/api/estimates/123",
                    "curlBody": undefined,
                    "hiddenPaneSuffixes": ["0","1"]
                }
            ;
    case "10":
        return 
                {
                    "articleId": "api-estimates-get-estimate",
                    "title": "Request Estimate information",
                    "method": "GET",
                    "path": "/api/estimates/{id}",
                    "endpointDescription": undefined,
                    "parameterTableDataId": "5",
                    "exampleTabsDataId": "2",
                    "exampleBaseId": "ex-estimates-get-estimate",
                    "curlUrl": "https://yoursite.com/api/estimates/123",
                    "curlBody": undefined,
                    "hiddenPaneSuffixes": ["0","1"]
                }
            ;
    case "11":
        return 
                {
                    "articleId": "api-estimates-put-estimate",
                    "title": "Update a estimate",
                    "method": "PUT",
                    "path": "/api/estimates/{id}",
                    "endpointDescription": undefined,
                    "parameterTableDataId": "62",
                    "exampleTabsDataId": "1",
                    "exampleBaseId": "ex-estimates-put-estimate",
                    "curlUrl": "https://yoursite.com/api/estimates/123",
                    "curlBody": '{ "clientid": "...", "billing_street": "...", "number": "...", "date": "...", "currency": "...", "status": "...", "subtotal": "...", "total": "..." }',
                    "hiddenPaneSuffixes": ["0", "1", "2"]
                }
            ;
    case "12":
        return 
                {
                    "articleId": "api-estimates-get-estimate-search",
                    "title": "Search Estimate information",
                    "method": "GET",
                    "path": "/api/estimates/search/{keysearch}",
                    "endpointDescription": undefined,
                    "parameterTableDataId": "4",
                    "exampleTabsDataId": "2",
                    "exampleBaseId": "ex-estimates-get-estimate-search",
                    "curlUrl": "https://yoursite.com/api/estimates/search/acme",
                    "curlBody": undefined,
                    "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    case "13":
        return 
                {
                  "articleId": "api-expense-categories-get-expense-category",
                  "title": "Request Expense category",
                  "method": "GET",
                  "path": "/api/common/expense_category",
                  "endpointDescription": undefined,
                  "parameterTableDataId": undefined,
                  "exampleTabsDataId": "2",
                  "exampleBaseId": "ex-expense-categories-get-expense-category",
                  "curlUrl": "https://yoursite.com/api/common/expense_category",
                  "curlBody": undefined,
                  "hiddenPaneSuffixes": ["0","1"]
                }
            ;
    case "14":
        return 
                {
                    "articleId": "api-expenses-add-expense",
                    "title": "Add Expense",
                    "method": "POST",
                    "path": "/api/expenses",
                    "endpointDescription": undefined,
                    "parameterTableDataId": "63",
                    "exampleTabsDataId": "6",
                    "exampleBaseId": "ex-expenses-add-expense",
                    "curlUrl": "https://yoursite.com/api/expenses",
                    "curlBody": '{ "category": "...", "amount": "...", "date": "...", "clientid": "...", "currency": "...", "tax": "...", "tax2": "...", "paymentmode": "..." }',
                    "hiddenPaneSuffixes": ["0","1","2","3","4"]
                }
            ;
    case "15":
        return 
                {
                    "articleId": "api-expenses-put-expense",
                    "title": "Update a Expense",
                    "method": "PUT",
                    "path": "/api/expenses",
                    "endpointDescription": undefined,
                    "parameterTableDataId": "64",
                    "exampleTabsDataId": "6",
                    "exampleBaseId": "ex-expenses-put-expense",
                    "curlUrl": "https://yoursite.com/api/expenses",
                    "curlBody": '{ "category": "...", "amount": "...", "date": "...", "clientid": "...", "currency": "...", "tax": "...", "tax2": "...", "paymentmode": "..." }',
                    "hiddenPaneSuffixes": ["0", "1", "2", "3", "4"]
                }
            ;
    case "16":
        return 
                {
                  "articleId": "api-expenses-delete-expenses",
                  "title": "Delete Expense",
                  "method": "DELETE",
                  "path": "/api/expenses/{id}",
                  "endpointDescription": undefined,
                  "parameterTableDataId": undefined,
                  "exampleTabsDataId": "2",
                  "exampleBaseId": "ex-expenses-delete-expenses",
                  "curlUrl": "https://yoursite.com/api/expenses/123",
                  "curlBody": undefined,
                  "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    case "17":
        return 
                {
                    "articleId": "api-expenses-get-expense",
                    "title": "Request Expense information",
                    "method": "GET",
                    "path": "/api/expenses/{id}",
                    "endpointDescription": undefined,
                    "parameterTableDataId": "15",
                    "exampleTabsDataId": "2",
                    "exampleBaseId": "ex-expenses-get-expense",
                    "curlUrl": "https://yoursite.com/api/expenses/123",
                    "curlBody": undefined,
                    "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    case "18":
        return 
                {
                  "articleId": "api-expenses-get-expense-search",
                  "title": "Search Expenses information",
                  "method": "GET",
                  "path": "/api/expenses/search/{keysearch}",
                  "endpointDescription": undefined,
                  "parameterTableDataId": "12",
                  "exampleTabsDataId": "2",
                  "exampleBaseId": "ex-expenses-get-expense-search",
                  "curlUrl": "https://yoursite.com/api/expenses/search/acme",
                  "curlBody": undefined,
                  "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    case "19":
        return 
                {
                    "articleId": "api-items-delete-item",
                    "title": "Delete an Item",
                    "method": "DELETE",
                    "path": "/api/delete/items/{id}",
                    "endpointDescription": undefined,
                    "parameterTableDataId": "16",
                    "exampleTabsDataId": "2",
                    "exampleBaseId": "ex-items-delete-item",
                    "curlUrl": "https://yoursite.com/api/delete/items/123",
                    "curlBody": undefined,
                    "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    case "20":
        return 
                {
                    "articleId": "api-items-post-item",
                    "title": "Create New Item",
                    "method": "POST",
                    "path": "/api/items",
                    "endpointDescription": undefined,
                    "parameterTableDataId": "65",
                    "exampleTabsDataId": "2",
                    "exampleBaseId": "ex-items-post-item",
                    "curlUrl": "https://yoursite.com/api/items",
                    "curlBody": '{ "description": "...", "rate": "..." }',
                    "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    case "21":
        return 
                {
                    "articleId": "api-items-get-item",
                    "title": "Request Item information",
                    "method": "GET",
                    "path": "/api/items/{id}",
                    "endpointDescription": undefined,
                    "parameterTableDataId": "44",
                    "exampleTabsDataId": "0",
                    "exampleBaseId": "ex-items-get-item",
                    "curlUrl": "https://yoursite.com/api/items/123",
                    "curlBody": undefined,
                    "hiddenPaneSuffixes": ["0", "1", "2"]
                }
            ;
    case "22":
        return 
                {
                    "articleId": "api-items-put-item",
                    "title": "Update an Item",
                    "method": "PUT",
                    "path": "/api/items/{id}",
                    "endpointDescription": undefined,
                    "parameterTableDataId": "66",
                    "exampleTabsDataId": "2",
                    "exampleBaseId": "ex-items-put-item",
                    "curlUrl": "https://yoursite.com/api/items/123",
                    "curlBody": '{ "field": "value" }',
                    "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    case "23":
        return 
                {
                  "articleId": "api-items-get-item-search",
                  "title": "Search Items",
                  "method": "GET",
                  "path": "/api/items/search/{keysearch}",
                  "endpointDescription": undefined,
                  "parameterTableDataId": "12",
                  "exampleTabsDataId": "2",
                  "exampleBaseId": "ex-items-get-item-search",
                  "curlUrl": "https://yoursite.com/api/items/search/acme",
                  "curlBody": undefined,
                  "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    case "24":
        return 
                {
                    "articleId": "api-knowledge-base-get-kb-articles",
                    "title": "List Knowledge Base Articles",
                    "method": "GET",
                    "path": "/api/knowledge_base",
                    "endpointDescription": undefined,
                    "parameterTableDataId": "45",
                    "exampleTabsDataId": "2",
                    "exampleBaseId": "ex-knowledge-base-get-kb-articles",
                    "curlUrl": "https://yoursite.com/api/knowledge_base",
                    "curlBody": undefined,
                    "hiddenPaneSuffixes": ["0","1"]
                }
            ;
    case "25":
        return 
                {
                    "articleId": "api-knowledge-base-post-kb-article",
                    "title": "Add New Article",
                    "method": "POST",
                    "path": "/api/knowledge_base",
                    "endpointDescription": undefined,
                    "parameterTableDataId": "67",
                    "exampleTabsDataId": "2",
                    "exampleBaseId": "ex-knowledge-base-post-kb-article",
                    "curlUrl": "https://yoursite.com/api/knowledge_base",
                    "curlBody": '{ "subject": "...", "articlegroup": "..." }',
                    "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    case "26":
        return 
                {
                    "articleId": "api-knowledge-base-delete-kb-article",
                    "title": "Delete an Article",
                    "method": "DELETE",
                    "path": "/api/knowledge_base/{id}",
                    "endpointDescription": undefined,
                    "parameterTableDataId": undefined,
                    "exampleTabsDataId": "2",
                    "exampleBaseId": "ex-knowledge-base-delete-kb-article",
                    "curlUrl": "https://yoursite.com/api/knowledge_base/123",
                    "curlBody": undefined,
                    "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    case "27":
        return 
                {
                    "articleId": "api-knowledge-base-get-kb-article",
                    "title": "Request Article Information",
                    "method": "GET",
                    "path": "/api/knowledge_base/{id}",
                    "endpointDescription": undefined,
                    "parameterTableDataId": undefined,
                    "exampleTabsDataId": "2",
                    "exampleBaseId": "ex-knowledge-base-get-kb-article",
                    "curlUrl": "https://yoursite.com/api/knowledge_base/123",
                    "curlBody": undefined,
                    "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    case "28":
        return 
                {
                    "articleId": "api-knowledge-base-put-kb-article",
                    "title": "Update an Article",
                    "method": "PUT",
                    "path": "/api/knowledge_base/{id}",
                    "endpointDescription": "Partial update - unknown fields are ignored.",
                    "parameterTableDataId": undefined,
                    "exampleTabsDataId": "2",
                    "exampleBaseId": "ex-knowledge-base-put-kb-article",
                    "curlUrl": "https://yoursite.com/api/knowledge_base/123",
                    "curlBody": '{ "field": "value" }',
                    "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    case "29":
        return 
                {
                    "articleId": "api-knowledge-base-get-kb-groups",
                    "title": "List Knowledge Base Groups",
                    "method": "GET",
                    "path": "/api/knowledge_base/groups",
                    "endpointDescription": undefined,
                    "parameterTableDataId": undefined,
                    "exampleTabsDataId": "2",
                    "exampleBaseId": "ex-knowledge-base-get-kb-groups",
                    "curlUrl": "https://yoursite.com/api/knowledge_base/groups",
                    "curlBody": undefined,
                    "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    case "30":
        return 
                {
                    "articleId": "api-knowledge-base-post-kb-group",
                    "title": "Add New Group",
                    "method": "POST",
                    "path": "/api/knowledge_base/groups",
                    "endpointDescription": undefined,
                    "parameterTableDataId": "68",
                    "exampleTabsDataId": "2",
                    "exampleBaseId": "ex-knowledge-base-post-kb-group",
                    "curlUrl": "https://yoursite.com/api/knowledge_base/groups",
                    "curlBody": '{ "name": "..." }',
                    "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    case "31":
        return 
                {
                    "articleId": "api-knowledge-base-delete-kb-group",
                    "title": "Delete a Group",
                    "method": "DELETE",
                    "path": "/api/knowledge_base/groups/{id}",
                    "endpointDescription": "Returns 409 while articles are still attached to the group.",
                    "parameterTableDataId": undefined,
                    "exampleTabsDataId": "2",
                    "exampleBaseId": "ex-knowledge-base-delete-kb-group",
                    "curlUrl": "https://yoursite.com/api/knowledge_base/groups/123",
                    "curlBody": undefined,
                    "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    case "32":
        return 
                {
                    "articleId": "api-knowledge-base-put-kb-group",
                    "title": "Update a Group",
                    "method": "PUT",
                    "path": "/api/knowledge_base/groups/{id}",
                    "endpointDescription": undefined,
                    "parameterTableDataId": undefined,
                    "exampleTabsDataId": "2",
                    "exampleBaseId": "ex-knowledge-base-put-kb-group",
                    "curlUrl": "https://yoursite.com/api/knowledge_base/groups/123",
                    "curlBody": '{ "field": "value" }',
                    "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    case "33":
        return 
                {
                    "articleId": "api-milestones-delete-milestone",
                    "title": "Delete a Milestone",
                    "method": "DELETE",
                    "path": "/api/delete/milestones/{id}",
                    "endpointDescription": undefined,
                    "parameterTableDataId": "17",
                    "exampleTabsDataId": "2",
                    "exampleBaseId": "ex-milestones-delete-milestone",
                    "curlUrl": "https://yoursite.com/api/delete/milestones/123",
                    "curlBody": undefined,
                    "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    case "34":
        return 
                {
                    "articleId": "api-milestones-post-milestone",
                    "title": "Add New Milestone",
                    "method": "POST",
                    "path": "/api/milestones",
                    "endpointDescription": undefined,
                    "parameterTableDataId": "69",
                    "exampleTabsDataId": "2",
                    "exampleBaseId": "ex-milestones-post-milestone",
                    "curlUrl": "https://yoursite.com/api/milestones",
                    "curlBody": '{ "project_id": "...", "name": "...", "due_date": "..." }',
                    "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    default:
        return 
                {
                  "articleId": "api-contracts-delete-contract",
                  "title": "Delete Contract",
                  "method": "DELETE",
                  "path": "/api/contracts/{id}",
                  "endpointDescription": undefined,
                  "parameterTableDataId": undefined,
                  "exampleTabsDataId": "2",
                  "exampleBaseId": "ex-contracts-delete-contract",
                  "curlUrl": "https://yoursite.com/api/contracts/123",
                  "curlBody": undefined,
                  "hiddenPaneSuffixes": ["0", "1"]
                }
            ;
    }
}


export default ApiEndpoint
