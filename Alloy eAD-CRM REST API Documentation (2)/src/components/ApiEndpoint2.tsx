import React from 'react'
import type { JSX } from 'react/jsx-runtime'

import CopyButton from './CopyButton.tsx'
import ExampleTabs from './ExampleTabs.tsx'
import ParameterTable from './ParameterTable.tsx'
import ApiEndpoint from './ApiEndpoint.tsx'


        type ApiEndpointData = {
            articleId: string;
            title: string;
            method: "GET" | "POST" | "PUT" | "DELETE";
            path: string;
            description?: string;
            headerTableDataId: string;
            parameterTableDataId?: string;
            exampleTabsDataId: string;
            curlPaneId: string;
            hiddenPaneIds: string[];
            requestBody?: string;
        };
    
// Component

        function ApiEndpoint2({
          dataId
        }: {
          dataId: string;
        }) {
          const data: ApiEndpointData = getApiEndpointData(dataId);

          return (
            <article id={data.articleId}>
              <h1>
                {data.title}
              </h1>
              <div className={`row pre-${data.method.toLowerCase()}`}>
                <div className={"col-md-7 no-float"}>
                  <EndpointRequest method={data.method} path={data.path} />
                  {data.description !== undefined ? (
                    <div className={"endpoint-desc"}>
                      <p>
                        {data.description}
                      </p>
                    </div>
                  ) : null}
                  <h2 className={"sub"}>
                    Headers
                  </h2>
                  <div className={"table-responsive-wrapper"}>
                    <ParameterTable dataId={data.headerTableDataId} />
                  </div>
                  {data.parameterTableDataId !== undefined ? (
                    <>
                      <h2 className={"sub"}>
                        Parameters
                      </h2>
                      <div className={"table-responsive-wrapper"}>
                        <ParameterTable dataId={data.parameterTableDataId} />
                      </div>
                    </>
                  ) : null}
                </div>
                <div className={"col-md-4 section-example no-float"}>
                  <ExampleTabs dataId={data.exampleTabsDataId} />
                  <div className={"tab-content"}>
                    <CurlExample
                      method={data.method}
                      paneId={data.curlPaneId}
                      requestBody={data.requestBody}
                    />
                    {data.hiddenPaneIds.map((paneId) => (
                      <div className={"tab-pane sf-hidden"} id={paneId} key={paneId}>
                      </div>
                    ))}
                  </div>
                </div>
              </div>
            </article>
          );
        }
    

// Subcomponents

        function EndpointRequest({
          method,
          path
        }: {
          method: "GET" | "POST" | "PUT" | "DELETE";
          path: string;
        }) {
          return (
            <pre className={"full-pre"}>
              <span className={`typ typ-${method.toLowerCase()}`}>
                {method}
              </span>
              <span className={"url"}>
                {path}
              </span>
              <CopyButton hidden={true} />
            </pre>
          );
        }

        function CurlExample({
          method,
          paneId,
          requestBody
        }: {
          method: "GET" | "POST" | "PUT" | "DELETE";
          paneId: string;
          requestBody?: string;
        }) {
          return (
            <div className={"tab-pane active"} id={paneId}>
              <pre
                className={"astro-code catppuccin-mocha"}
                style={{backgroundColor:"#1e1e2e", color:"#cdd6f4", overflowX:"auto"}}
                tabIndex={0}
              >
                <code>
                  <span className={"line"}>
                    <span style={{color:"#89B4FA", fontStyle:"italic"}}>
                      curl
                    </span>
                    <span style={{color:"#A6E3A1"}}>
                      {` -X`}
                    </span>
                    <span style={{color:"#A6E3A1"}}>
                      {` ${method}`}
                    </span>
                    <span style={{color:"#F5C2E7"}}>
                      {` \\`}
                    </span>
                  </span>
                  <span className={"line"}>
                    <span style={{color:"#A6E3A1"}}>
                      {`  -H`}
                    </span>
                    <span style={{color:"#A6E3A1"}}>
                      {` "authtoken: YOUR_API_TOKEN"`}
                    </span>
                    {requestBody !== undefined ? (
                      <span style={{color:"#F5C2E7"}}>
                        {` \\`}
                      </span>
                    ) : null}
                  </span>
                  {requestBody !== undefined ? (
                    <>
                      <span className={"line"}>
                        <span style={{color:"#A6E3A1"}}>
                          {`  -H`}
                        </span>
                        <span style={{color:"#A6E3A1"}}>
                          {` "Content-Type: application/json"`}
                        </span>
                        <span style={{color:"#F5C2E7"}}>
                          {` \\`}
                        </span>
                      </span>
                      <span className={"line"}>
                        <span style={{color:"#A6E3A1"}}>
                          {`  -d`}
                        </span>
                        <span style={{color:"#A6E3A1"}}>
                          {` '${requestBody}'`}
                        </span>
                      </span>
                    </>
                  ) : null}
                </code>
                <CopyButton hidden={false} />
              </pre>
            </div>
          );
        }
    

function getApiEndpointData(id): ApiEndpointData  {
    switch (String(id)) {
    case "0":
        return 
                {
                    "articleId": "api-tasks-put-task",
                    "title": "Update a task",
                    "method": "PUT",
                    "path": "/api/tasks/{id}",
                    "description": undefined,
                    "headerTableDataId": "0",
                    "parameterTableDataId": "80",
                    "exampleTabsDataId": "2",
                    "curlPaneId": "ex-tasks-put-task-curl",
                    "hiddenPaneIds": [
                        "ex-tasks-put-task-0",
                        "ex-tasks-put-task-1"
                    ],
                    "requestBody": `{ "rel_type": "...", "rel_id": "..." }`
                }
            ;
    case "1":
        return 
                {
                  "articleId": "api-tasks-get-task-checklist",
                  "title": "Get Task Checklist Items",
                  "method": "GET",
                  "path": "/api/tasks/{id}/checklist",
                  "description": undefined,
                  "headerTableDataId": "0",
                  "parameterTableDataId": "29",
                  "exampleTabsDataId": "2",
                  "curlPaneId": "ex-tasks-get-task-checklist-curl",
                  "hiddenPaneIds": [
                    "ex-tasks-get-task-checklist-0",
                    "ex-tasks-get-task-checklist-1"
                  ],
                  "requestBody": undefined
                }
            ;
    case "2":
        return 
            {
              "articleId": "api-tasks-post-task-checklist",
              "title": "Add Checklist Item to Task",
              "method": "POST",
              "path": "/api/tasks/{id}/checklist",
              "description": undefined,
              "headerTableDataId": "0",
              "parameterTableDataId": "81",
              "exampleTabsDataId": "2",
              "curlPaneId": "ex-tasks-post-task-checklist-curl",
              "hiddenPaneIds": [
                "ex-tasks-post-task-checklist-0",
                "ex-tasks-post-task-checklist-1"
              ],
              "requestBody": `{ "description": "..." }`
            }
          ;
    case "3":
        return 
                {
                  "articleId": "api-tasks-get-task-comments",
                  "title": "Get Task Comments",
                  "method": "GET",
                  "path": "/api/tasks/{id}/comments",
                  "description": "Returns the comment thread of a task, oldest first. Requires the Tasks \"get\" permission.",
                  "headerTableDataId": "0",
                  "parameterTableDataId": "30",
                  "exampleTabsDataId": "2",
                  "curlPaneId": "ex-tasks-get-task-comments-curl",
                  "hiddenPaneIds": [
                    "ex-tasks-get-task-comments-0",
                    "ex-tasks-get-task-comments-1"
                  ],
                  "requestBody": undefined
                }
            ;
    case "4":
        return 
                {
                  "articleId": "api-tasks-post-task-comment",
                  "title": "Add a Task Comment",
                  "method": "POST",
                  "path": "/api/tasks/{id}/comments",
                  "description": "Adds a comment to a task, authored by the token's staff member. Requires the Tasks \"post\" permission. Send an Idempotency-Key header to make retries safe.",
                  "headerTableDataId": "0",
                  "parameterTableDataId": "53",
                  "exampleTabsDataId": "2",
                  "curlPaneId": "ex-tasks-post-task-comment-curl",
                  "hiddenPaneIds": [
                    "ex-tasks-post-task-comment-0",
                    "ex-tasks-post-task-comment-1"
                  ],
                  "requestBody": `{ "content": "..." }`
                }
            ;
    case "5":
        return 
            {
              "articleId": "api-tasks-delete-task-checklist",
              "title": "Delete Checklist Item",
              "method": "DELETE",
              "path": "/api/tasks/{task_id}/checklist/{item_id}",
              "description": undefined,
              "headerTableDataId": "0",
              "parameterTableDataId": "54",
              "exampleTabsDataId": "3",
              "curlPaneId": "ex-tasks-delete-task-checklist-curl",
              "hiddenPaneIds": ["ex-tasks-delete-task-checklist-0"],
              "requestBody": undefined
            }
          ;
    case "6":
        return 
                {
                  "articleId": "api-tasks-put-task-checklist",
                  "title": "Update Checklist Item",
                  "method": "PUT",
                  "path": "/api/tasks/{task_id}/checklist/{item_id}",
                  "description": undefined,
                  "headerTableDataId": "0",
                  "parameterTableDataId": "82",
                  "exampleTabsDataId": "3",
                  "curlPaneId": "ex-tasks-put-task-checklist-curl",
                  "hiddenPaneIds": ["ex-tasks-put-task-checklist-0"],
                  "requestBody": '{ "field": "value" }'
                }
            ;
    case "7":
        return 
                {
                  "articleId": "api-tasks-delete-task-comment",
                  "title": "Delete a Task Comment",
                  "method": "DELETE",
                  "path": "/api/tasks/{task_id}/comments/{comment_id}",
                  "description": "Deletes a task comment. Requires the Tasks \"delete\" permission.",
                  "headerTableDataId": "0",
                  "parameterTableDataId": "55",
                  "exampleTabsDataId": "3",
                  "curlPaneId": "ex-tasks-delete-task-comment-curl",
                  "hiddenPaneIds": [
                    "ex-tasks-delete-task-comment-0"
                  ],
                  "requestBody": undefined
                }
            ;
    case "8":
        return 
                {
                  "articleId": "api-tasks-put-task-comment",
                  "title": "Update a Task Comment",
                  "method": "PUT",
                  "path": "/api/tasks/{task_id}/comments/{comment_id}",
                  "description": "Updates the text of an existing task comment. Requires the Tasks \"put\" permission.",
                  "headerTableDataId": "0",
                  "parameterTableDataId": "71",
                  "exampleTabsDataId": "3",
                  "curlPaneId": "ex-tasks-put-task-comment-curl",
                  "hiddenPaneIds": [
                    "ex-tasks-put-task-comment-0"
                  ],
                  "requestBody": "{ \"content\": \"...\" }"
                }
            ;
    case "9":
        return 
            {
              "articleId": "api-tasks-get-task-search",
              "title": "Search Tasks Information",
              "method": "GET",
              "path": "/api/tasks/search/{keysearch}",
              "description": undefined,
              "headerTableDataId": "0",
              "parameterTableDataId": "4",
              "exampleTabsDataId": "2",
              "curlPaneId": "ex-tasks-get-task-search-curl",
              "hiddenPaneIds": [
                "ex-tasks-get-task-search-0",
                "ex-tasks-get-task-search-1"
              ],
              "requestBody": undefined
            }
          ;
    case "10":
        return 
            {
              "articleId": "api-taxes-get-taxes",
              "title": "Request Taxes",
              "method": "GET",
              "path": "/api/common/tax_data",
              "description": undefined,
              "headerTableDataId": "0",
              "parameterTableDataId": undefined,
              "exampleTabsDataId": "2",
              "curlPaneId": "ex-taxes-get-taxes-curl",
              "hiddenPaneIds": [
                "ex-taxes-get-taxes-0",
                "ex-taxes-get-taxes-1"
              ],
              "requestBody": undefined
            }
          ;
    case "11":
        return 
                {
                  "articleId": "api-thirdparty-get-custom-table-records",
                  "title": "Get All Records from Custom Table",
                  "method": "GET",
                  "path": "/api/thirdparty/customtable/{table_name}",
                  "description": "Retrieves all records from the specified custom table. The table name should be the exact name as it exists in the database (no prefix will be added).",
                  "headerTableDataId": "0",
                  "parameterTableDataId": "31",
                  "exampleTabsDataId": "2",
                  "curlPaneId": "ex-thirdparty-get-custom-table-records-curl",
                  "hiddenPaneIds": [
                    "ex-thirdparty-get-custom-table-records-0",
                    "ex-thirdparty-get-custom-table-records-1"
                  ],
                  "requestBody": undefined
                }
            ;
    case "12":
        return 
                {
                  "articleId": "api-thirdparty-post-custom-table-record",
                  "title": "Insert Record into Custom Table",
                  "method": "POST",
                  "path": "/api/thirdparty/customtable/{table_name}",
                  "description": "Inserts a new record into the specified custom table. All columns in the request body must exist in the table, otherwise an error will be returned.",
                  "headerTableDataId": "56",
                  "parameterTableDataId": "31",
                  "exampleTabsDataId": "2",
                  "curlPaneId": "ex-thirdparty-post-custom-table-record-curl",
                  "hiddenPaneIds": [
                    "ex-thirdparty-post-custom-table-record-0",
                    "ex-thirdparty-post-custom-table-record-1"
                  ],
                  "requestBody": '{ "field": "value" }'
                }
            ;
    case "13":
        return 
                {
                  "articleId": "api-thirdparty-delete-custom-table-record",
                  "title": "Delete Record from Custom Table",
                  "method": "DELETE",
                  "path": "/api/thirdparty/customtable/{table_name}/{id}",
                  "description": "Deletes a record from the specified custom table by its ID.",
                  "headerTableDataId": "0",
                  "parameterTableDataId": "57",
                  "exampleTabsDataId": "2",
                  "curlPaneId": "ex-thirdparty-delete-custom-table-record-curl",
                  "hiddenPaneIds": [
                    "ex-thirdparty-delete-custom-table-record-0",
                    "ex-thirdparty-delete-custom-table-record-1"
                  ],
                  "requestBody": undefined
                }
            ;
    case "14":
        return 
                {
                  "articleId": "api-thirdparty-get-custom-table-record-by-id",
                  "title": "Get Record from Custom Table by ID",
                  "method": "GET",
                  "path": "/api/thirdparty/customtable/{table_name}/{id}",
                  "description": "Retrieves a specific record from the custom table by its ID.",
                  "headerTableDataId": "0",
                  "parameterTableDataId": "57",
                  "exampleTabsDataId": "2",
                  "curlPaneId": "ex-thirdparty-get-custom-table-record-by-id-curl",
                  "hiddenPaneIds": [
                    "ex-thirdparty-get-custom-table-record-by-id-0",
                    "ex-thirdparty-get-custom-table-record-by-id-1"
                  ],
                  "requestBody": undefined
                }
            ;
    case "15":
        return 
                {
                  "articleId": "api-thirdparty-put-custom-table-record",
                  "title": "Update Record in Custom Table",
                  "method": "PUT",
                  "path": "/api/thirdparty/customtable/{table_name}/{id}",
                  "description": "Updates an existing record in the specified custom table. All columns in the request body must exist in the table, otherwise an error will be returned.",
                  "headerTableDataId": "56",
                  "parameterTableDataId": "57",
                  "exampleTabsDataId": "2",
                  "curlPaneId": "ex-thirdparty-put-custom-table-record-curl",
                  "hiddenPaneIds": [
                    "ex-thirdparty-put-custom-table-record-0",
                    "ex-thirdparty-put-custom-table-record-1"
                  ],
                  "requestBody": "{ \"field\": \"value\" }"
                }
            ;
    case "16":
        return 
                {
                    "articleId": "api-tickets-delete-ticket",
                    "title": "Delete a Ticket",
                    "method": "DELETE",
                    "path": "/api/delete/tickets/{id}",
                    "description": undefined,
                    "headerTableDataId": "0",
                    "parameterTableDataId": "32",
                    "exampleTabsDataId": "2",
                    "curlPaneId": "ex-tickets-delete-ticket-curl",
                    "hiddenPaneIds": [
                        "ex-tickets-delete-ticket-0",
                        "ex-tickets-delete-ticket-1"
                    ],
                    "requestBody": undefined
                }
            ;
    case "17":
        return 
            {
              "articleId": "api-tickets-post-ticket",
              "title": "Add New Ticket",
              "method": "POST",
              "path": "/api/tickets",
              "description": undefined,
              "headerTableDataId": "0",
              "parameterTableDataId": "84",
              "exampleTabsDataId": "2",
              "curlPaneId": "ex-tickets-post-ticket-curl",
              "hiddenPaneIds": [
                "ex-tickets-post-ticket-0",
                "ex-tickets-post-ticket-1"
              ],
              "requestBody": `{ "subject": "...", "department": "...", "contactid": "...", "userid": "..." }`
            }
          ;
    case "18":
        return 
            {
              "articleId": "api-tickets-get-ticket",
              "title": "Get Ticket(s)",
              "method": "GET",
              "path": "/api/tickets/{id}",
              "description": undefined,
              "headerTableDataId": "0",
              "parameterTableDataId": "85",
              "exampleTabsDataId": "0",
              "curlPaneId": "ex-tickets-get-ticket-curl",
              "hiddenPaneIds": [
                "ex-tickets-get-ticket-0",
                "ex-tickets-get-ticket-1",
                "ex-tickets-get-ticket-2"
              ],
              "requestBody": undefined
            }
          ;
    case "19":
        return 
                {
                  "articleId": "api-tickets-put-ticket",
                  "title": "Update a ticket",
                  "method": "PUT",
                  "path": "/api/tickets/{id}",
                  "description": undefined,
                  "headerTableDataId": "0",
                  "parameterTableDataId": "86",
                  "exampleTabsDataId": "2",
                  "curlPaneId": "ex-tickets-put-ticket-curl",
                  "hiddenPaneIds": [
                    "ex-tickets-put-ticket-0",
                    "ex-tickets-put-ticket-1"
                  ],
                  "requestBody": `{ "subject": "...", "department": "...", "contactid": "...", "userid": "...", "priority": "..." }`
                }
            ;
    case "20":
        return 
                {
                    "articleId": "api-tickets-post-ticket-reply",
                    "title": "Add reply to a ticket",
                    "method": "POST",
                    "path": "/api/tickets/reply/{id}",
                    "description": undefined,
                    "headerTableDataId": "0",
                    "parameterTableDataId": "83",
                    "exampleTabsDataId": "2",
                    "curlPaneId": "ex-tickets-post-ticket-reply-curl",
                    "hiddenPaneIds": [
                        "ex-tickets-post-ticket-reply-0",
                        "ex-tickets-post-ticket-reply-1"
                    ],
                    "requestBody": `{ "message": "..." }`
                }
            ;
    case "21":
        return 
            {
              "articleId": "api-tickets-get-ticket-search",
              "title": "Search Ticket Information",
              "method": "GET",
              "path": "/api/tickets/search/{keysearch}",
              "description": undefined,
              "headerTableDataId": "0",
              "parameterTableDataId": "22",
              "exampleTabsDataId": "2",
              "curlPaneId": "ex-tickets-get-ticket-search-curl",
              "hiddenPaneIds": [
                "ex-tickets-get-ticket-search-0",
                "ex-tickets-get-ticket-search-1"
              ],
              "requestBody": undefined
            }
          ;
    case "22":
        return 
                {
                    "articleId": "api-timesheets-request-all-timesheets",
                    "title": "Request all Timesheets",
                    "method": "GET",
                    "path": "/api/timesheets/",
                    "description": undefined,
                    "headerTableDataId": "0",
                    "parameterTableDataId": undefined,
                    "exampleTabsDataId": "2",
                    "curlPaneId": "ex-timesheets-request-all-timesheets-curl",
                    "hiddenPaneIds": [
                        "ex-timesheets-request-all-timesheets-0",
                        "ex-timesheets-request-all-timesheets-1"
                    ],
                    "requestBody": undefined
                }
            ;
    case "23":
        return 
                {
                  "articleId": "api-timesheets-add-new-timesheet",
                  "title": "Add New Timesheet",
                  "method": "POST",
                  "path": "/api/timesheets/",
                  "description": undefined,
                  "headerTableDataId": "0",
                  "parameterTableDataId": undefined,
                  "exampleTabsDataId": "2",
                  "curlPaneId": "ex-timesheets-add-new-timesheet-curl",
                  "hiddenPaneIds": [
                    "ex-timesheets-add-new-timesheet-0",
                    "ex-timesheets-add-new-timesheet-1"
                  ],
                  "requestBody": "{ \"field\": \"value\" }"
                }
            ;
    case "24":
        return 
                {
                  "articleId": "api-timesheets-delete-a-timesheet",
                  "title": "Delete a Timesheet",
                  "method": "DELETE",
                  "path": "/api/timesheets/{id}",
                  "description": undefined,
                  "headerTableDataId": "0",
                  "parameterTableDataId": "26",
                  "exampleTabsDataId": "2",
                  "curlPaneId": "ex-timesheets-delete-a-timesheet-curl",
                  "hiddenPaneIds": [
                    "ex-timesheets-delete-a-timesheet-0",
                    "ex-timesheets-delete-a-timesheet-1"
                  ],
                  "requestBody": undefined
                }
            ;
    case "25":
        return 
                {
                  "articleId": "api-timesheets-request-timesheet-information",
                  "title": "Request Timesheet Information",
                  "method": "GET",
                  "path": "/api/timesheets/{id}",
                  "description": undefined,
                  "headerTableDataId": "0",
                  "parameterTableDataId": "27",
                  "exampleTabsDataId": "2",
                  "curlPaneId": "ex-timesheets-request-timesheet-information-curl",
                  "hiddenPaneIds": [
                    "ex-timesheets-request-timesheet-information-0",
                    "ex-timesheets-request-timesheet-information-1"
                  ],
                  "requestBody": undefined
                }
            ;
    case "26":
        return 
                {
                  "articleId": "api-timesheets-update-a-timesheet",
                  "title": "Update a Timesheet",
                  "method": "PUT",
                  "path": "/api/timesheets/{id}",
                  "description": undefined,
                  "headerTableDataId": "0",
                  "parameterTableDataId": "28",
                  "exampleTabsDataId": "2",
                  "curlPaneId": "ex-timesheets-update-a-timesheet-curl",
                  "hiddenPaneIds": [
                    "ex-timesheets-update-a-timesheet-0",
                    "ex-timesheets-update-a-timesheet-1"
                  ],
                  "requestBody": '{ "field": "value" }'
                }
            ;
    case "27":
        return 
                {
                  "articleId": "api-webhooks-get-webhooks",
                  "title": "List Webhooks",
                  "method": "GET",
                  "path": "/api/webhooks",
                  "description": "List all configured webhooks. Secrets are never returned (has_secret flag only). Supports page/per_page, sort, fields and date filters.",
                  "headerTableDataId": "0",
                  "parameterTableDataId": undefined,
                  "exampleTabsDataId": "3",
                  "curlPaneId": "ex-webhooks-get-webhooks-curl",
                  "hiddenPaneIds": [
                    "ex-webhooks-get-webhooks-0"
                  ],
                  "requestBody": undefined
                }
            ;
    case "28":
        return 
                {
                  "articleId": "api-webhooks-post-webhook",
                  "title": "Create a Webhook",
                  "method": "POST",
                  "path": "/api/webhooks",
                  "description": undefined,
                  "headerTableDataId": "0",
                  "parameterTableDataId": "87",
                  "exampleTabsDataId": "2",
                  "curlPaneId": "ex-webhooks-post-webhook-curl",
                  "hiddenPaneIds": [
                    "ex-webhooks-post-webhook-0",
                    "ex-webhooks-post-webhook-1"
                  ],
                  "requestBody": '{ "name": "...", "url": "...", "events": "..." }'
                }
            ;
    case "29":
        return 
                {
                  "articleId": "api-webhooks-delete-webhook",
                  "title": "Delete a Webhook",
                  "method": "DELETE",
                  "path": "/api/webhooks/{id}",
                  "description": "Deletes the webhook together with its delivery logs and queued jobs.",
                  "headerTableDataId": "0",
                  "parameterTableDataId": undefined,
                  "exampleTabsDataId": "2",
                  "curlPaneId": "ex-webhooks-delete-webhook-curl",
                  "hiddenPaneIds": [
                    "ex-webhooks-delete-webhook-0",
                    "ex-webhooks-delete-webhook-1"
                  ],
                  "requestBody": undefined
                }
            ;
    case "30":
        return 
                {
                    "articleId": "api-webhooks-get-webhook",
                    "title": "Request Webhook Information",
                    "method": "GET",
                    "path": "/api/webhooks/{id}",
                    "description": undefined,
                    "headerTableDataId": "0",
                    "parameterTableDataId": undefined,
                    "exampleTabsDataId": "2",
                    "curlPaneId": "ex-webhooks-get-webhook-curl",
                    "hiddenPaneIds": [
                        "ex-webhooks-get-webhook-0",
                        "ex-webhooks-get-webhook-1"
                    ],
                    "requestBody": undefined
                }
            ;
    case "31":
        return 
            {
              "articleId": "api-webhooks-put-webhook",
              "title": "Update a Webhook",
              "method": "PUT",
              "path": "/api/webhooks/{id}",
              "description": "Partial update - only the provided fields change; same validation as create.",
              "headerTableDataId": "0",
              "parameterTableDataId": undefined,
              "exampleTabsDataId": "1",
              "curlPaneId": "ex-webhooks-put-webhook-curl",
              "hiddenPaneIds": [
                "ex-webhooks-put-webhook-0",
                "ex-webhooks-put-webhook-1",
                "ex-webhooks-put-webhook-2"
              ],
              "requestBody": `{ "field": "value" }`
            }
          ;
    case "32":
        return 
                {
                  "articleId": "api-webhooks-get-webhook-logs",
                  "title": "Webhook Delivery Logs",
                  "method": "GET",
                  "path": "/api/webhooks/{id}/logs",
                  "description": "Latest 500 delivery attempts with status, response code and error details. Paginated via page/per_page.",
                  "headerTableDataId": "0",
                  "parameterTableDataId": undefined,
                  "exampleTabsDataId": "2",
                  "curlPaneId": "ex-webhooks-get-webhook-logs-curl",
                  "hiddenPaneIds": [
                    "ex-webhooks-get-webhook-logs-0",
                    "ex-webhooks-get-webhook-logs-1"
                  ],
                  "requestBody": undefined
                }
            ;
    case "33":
        return 
                {
                  "articleId": "api-webhooks-toggle-webhook",
                  "title": "Enable/Disable a Webhook",
                  "method": "POST",
                  "path": "/api/webhooks/{id}/toggle",
                  "description": undefined,
                  "headerTableDataId": "0",
                  "parameterTableDataId": undefined,
                  "exampleTabsDataId": "2",
                  "curlPaneId": "ex-webhooks-toggle-webhook-curl",
                  "hiddenPaneIds": [
                    "ex-webhooks-toggle-webhook-0",
                    "ex-webhooks-toggle-webhook-1"
                  ],
                  "requestBody": '{ "field": "value" }'
                }
            ;
    case "34":
        return 
                {
                    "articleId": "api-webhooks-get-webhook-events",
                    "title": "Webhook Event Catalog",
                    "method": "GET",
                    "path": "/api/webhooks/events",
                    "description": "The authoritative catalog: 124 events across 22 resource groups (invoice_created, lead_status_changed, ticket_reply_created, kb_article_created...).",
                    "headerTableDataId": "0",
                    "parameterTableDataId": undefined,
                    "exampleTabsDataId": "3",
                    "curlPaneId": "ex-webhooks-get-webhook-events-curl",
                    "hiddenPaneIds": ["ex-webhooks-get-webhook-events-0"],
                    "requestBody": undefined
                }
            ;
    default:
        return 
                {
                    "articleId": "api-tasks-put-task",
                    "title": "Update a task",
                    "method": "PUT",
                    "path": "/api/tasks/{id}",
                    "description": undefined,
                    "headerTableDataId": "0",
                    "parameterTableDataId": "80",
                    "exampleTabsDataId": "2",
                    "curlPaneId": "ex-tasks-put-task-curl",
                    "hiddenPaneIds": [
                        "ex-tasks-put-task-0",
                        "ex-tasks-put-task-1"
                    ],
                    "requestBody": `{ "rel_type": "...", "rel_id": "..." }`
                }
            ;
    }
}


export default ApiEndpoint2
