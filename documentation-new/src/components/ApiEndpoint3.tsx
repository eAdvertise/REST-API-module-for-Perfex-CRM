import React from 'react'
import type { JSX } from 'react/jsx-runtime'

import CopyButton from './CopyButton.tsx'
import ExampleTabs from './ExampleTabs.tsx'
import ParameterTable from './ParameterTable.tsx'
import ApiEndpoint from './ApiEndpoint.tsx'


        type ApiEndpointData = {
            articleId: string;
            exampleId: string;
            title: string;
            method: "GET" | "POST" | "PUT" | "DELETE";
            endpoint: string;
            parameterDataId?: string;
            curlUrl: string;
            requestBody?: string;
        };
    
// Component

        function ApiEndpoint3({ dataId }: { dataId: string }) {
            const {
                articleId,
                exampleId,
                title,
                method,
                endpoint,
                parameterDataId,
                curlUrl,
                requestBody
            }: ApiEndpointData = getApiEndpointData(dataId);

            return (
                <article id={articleId}>
                    <h1>
                        {title}
                    </h1>
                    <div className={`row pre-${method.toLowerCase()}`}>
                        <div className={"col-md-7 no-float"}>
                            <pre className={"full-pre"}>
                                <span className={`typ typ-${method.toLowerCase()}`}>
                                    {method}
                                </span>
                                <span className={"url"}>
                                    {endpoint}
                                </span>
                                <CopyButton hidden={true} />
                            </pre>
                            <h2 className={"sub"}>
                                Headers
                            </h2>
                            <div className={"table-responsive-wrapper"}>
                                <ParameterTable dataId="0" />
                            </div>
                            {parameterDataId !== undefined && (
                                <>
                                    <h2 className={"sub"}>
                                        Parameters
                                    </h2>
                                    <div className={"table-responsive-wrapper"}>
                                        <ParameterTable dataId={parameterDataId} />
                                    </div>
                                </>
                            )}
                        </div>
                        <div className={"col-md-4 section-example no-float"}>
                            <ExampleTabs dataId="2" />
                            <CurlExample
                                exampleId={exampleId}
                                method={method}
                                curlUrl={curlUrl}
                                requestBody={requestBody}
                            />
                        </div>
                    </div>
                </article>
            );
        }
    

// Subcomponents

        function CurlExample({
            exampleId,
            method,
            curlUrl,
            requestBody
        }: {
            exampleId: string;
            method: "GET" | "POST" | "PUT" | "DELETE";
            curlUrl: string;
            requestBody?: string;
        }) {
            const hasBody = requestBody !== undefined;

            return (
                <div className={"tab-content"}>
                    <div className={"tab-pane active"} id={`${exampleId}-curl`}>
                        <pre
                            className={"astro-code catppuccin-mocha"}
                            style={{backgroundColor:"#1e1e2e", color:"#cdd6f4", overflowX:"auto"}}
                            tabIndex={"0"}
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
                                    <span style={{color:"#A6E3A1"}}>
                                        {` "${curlUrl}"`}
                                    </span>
                                    <span style={{color:"#F5C2E7"}}>
                                        {" \\"}
                                    </span>
                                </span>
                                <span className={"line"}>
                                    <span style={{color:"#A6E3A1"}}>
                                        {`  -H`}
                                    </span>
                                    <span style={{color:"#A6E3A1"}}>
                                        {` "authtoken: YOUR_API_TOKEN"`}
                                    </span>
                                    {hasBody && (
                                        <span style={{color:"#F5C2E7"}}>
                                            {" \\"}
                                        </span>
                                    )}
                                </span>
                                {hasBody && (
                                    <>
                                        <span className={"line"}>
                                            <span style={{color:"#A6E3A1"}}>
                                                {`  -H`}
                                            </span>
                                            <span style={{color:"#A6E3A1"}}>
                                                {` "Content-Type: application/json"`}
                                            </span>
                                            <span style={{color:"#F5C2E7"}}>
                                                {" \\"}
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
                                )}
                            </code>
                            <CopyButton hidden={false} />
                        </pre>
                    </div>
                    <div className={"tab-pane sf-hidden"} id={`${exampleId}-0`}>
                    </div>
                    <div className={"tab-pane sf-hidden"} id={`${exampleId}-1`}>
                    </div>
                </div>
            );
        }
    

function getApiEndpointData(id): ApiEndpointData  {
    switch (String(id)) {
    case "0":
        return 
                {
                  "articleId": "api-milestones-get-milestones",
                  "exampleId": "ex-milestones-get-milestones",
                  "title": "Request Milestones information",
                  "method": "GET",
                  "endpoint": "/api/milestones/{id}",
                  "parameterDataId": "18",
                  "curlUrl": "https://yoursite.com/api/milestones/123",
                  "requestBody": undefined
                }
            ;
    case "1":
        return 
                {
                    "articleId": "api-milestones-put-milestone",
                    "exampleId": "ex-milestones-put-milestone",
                    "title": "Update a Milestone",
                    "method": "PUT",
                    "endpoint": "/api/milestones/{id}",
                    "parameterDataId": "69",
                    "curlUrl": "https://yoursite.com/api/milestones/123",
                    "requestBody": '{ "project_id": "...", "name": "...", "due_date": "..." }'
                }
            ;
    case "2":
        return 
                {
                    "articleId": "api-milestones-get-milestone-search",
                    "exampleId": "ex-milestones-get-milestone-search",
                    "title": "Search Milestones Information",
                    "method": "GET",
                    "endpoint": "/api/milestones/search/{keysearch}",
                    "parameterDataId": "4",
                    "curlUrl": "https://yoursite.com/api/milestones/search/acme",
                    "requestBody": undefined
                }
            ;
    case "3":
        return 
                {
                    "articleId": "api-notes-post-note",
                    "exampleId": "ex-notes-post-note",
                    "title": "Add New Note",
                    "method": "POST",
                    "endpoint": "/api/notes",
                    "parameterDataId": "70",
                    "curlUrl": "https://yoursite.com/api/notes",
                    "requestBody": "{ \"rel_type\": \"...\", \"rel_id\": \"...\", \"description\": \"...\" }"
                }
            ;
    case "4":
        return 
                {
                    "articleId": "api-notes-delete-note",
                    "exampleId": "ex-notes-delete-note",
                    "title": "Delete a Note",
                    "method": "DELETE",
                    "endpoint": "/api/notes/{id}",
                    "parameterDataId": undefined,
                    "curlUrl": "https://yoursite.com/api/notes/123",
                    "requestBody": undefined
                }
            ;
    case "5":
        return 
            {
              "articleId": "api-notes-get-note",
              "exampleId": "ex-notes-get-note",
              "title": "Request Note Information",
              "method": "GET",
              "endpoint": "/api/notes/{id}",
              "parameterDataId": undefined,
              "curlUrl": "https://yoursite.com/api/notes/123",
              "requestBody": undefined
            }
          ;
    case "6":
        return 
                {
                    "articleId": "api-notes-put-note",
                    "exampleId": "ex-notes-put-note",
                    "title": "Update a Note",
                    "method": "PUT",
                    "endpoint": "/api/notes/{id}",
                    "parameterDataId": "19",
                    "curlUrl": "https://yoursite.com/api/notes/123",
                    "requestBody": "{ \"description\": \"...\" }"
                }
            ;
    case "7":
        return 
                {
                    "articleId": "api-notes-get-notes-by-relation",
                    "exampleId": "ex-notes-get-notes-by-relation",
                    "title": "List Notes of an Entity",
                    "method": "GET",
                    "endpoint": "/api/notes/{rel_type}/{rel_id}",
                    "parameterDataId": "52",
                    "curlUrl": "https://yoursite.com/api/notes/customer/123",
                    "requestBody": undefined
                }
            ;
    case "8":
        return 
                {
                    "articleId": "api-payment-modes-get-payment-mode",
                    "exampleId": "ex-payment-modes-get-payment-mode",
                    "title": "Request Payment Modes",
                    "method": "GET",
                    "endpoint": "/api/common/payment_mode",
                    "parameterDataId": undefined,
                    "curlUrl": "https://yoursite.com/api/common/payment_mode",
                    "requestBody": undefined
                }
            ;
    case "9":
        return 
                {
                    "articleId": "api-payments-post-payment",
                    "exampleId": "ex-payments-post-payment",
                    "title": "Add New Payment",
                    "method": "POST",
                    "endpoint": "/api/payments",
                    "parameterDataId": "72",
                    "curlUrl": "https://yoursite.com/api/payments",
                    "requestBody": "{ \"invoiceid\": \"...\", \"amount\": \"...\", \"paymentmode\": \"...\" }"
                }
            ;
    case "10":
        return 
                {
                    "articleId": "api-payments-get-payment",
                    "exampleId": "ex-payments-get-payment",
                    "title": "List all Payments",
                    "method": "GET",
                    "endpoint": "/api/payments/{id}",
                    "parameterDataId": "20",
                    "curlUrl": "https://yoursite.com/api/payments/123",
                    "requestBody": undefined
                }
            ;
    case "11":
        return 
                {
                  "articleId": "api-payments-get-payment-search",
                  "exampleId": "ex-payments-get-payment-search",
                  "title": "Search Payments Information",
                  "method": "GET",
                  "endpoint": "/api/payments/search/{keysearch}",
                  "parameterDataId": "12",
                  "curlUrl": "https://yoursite.com/api/payments/search/acme",
                  "requestBody": undefined
                }
            ;
    case "12":
        return 
                {
                    "articleId": "api-projects-delete-project",
                    "exampleId": "ex-projects-delete-project",
                    "title": "Delete a Project",
                    "method": "DELETE",
                    "endpoint": "/api/delete/projects/{id}",
                    "parameterDataId": "21",
                    "curlUrl": "https://yoursite.com/api/delete/projects/123",
                    "requestBody": undefined
                }
            ;
    case "13":
        return 
                {
                    "articleId": "api-projects-post-project",
                    "exampleId": "ex-projects-post-project",
                    "title": "Add New Project",
                    "method": "POST",
                    "endpoint": "/api/projects",
                    "parameterDataId": "73",
                    "curlUrl": "https://yoursite.com/api/projects",
                    "requestBody": "{ \"name\": \"...\", \"rel_type\": \"...\", \"clientid\": \"...\", \"billing_type\": \"...\", \"start_date\": \"...\", \"status\": \"...\" }"
                }
            ;
    case "14":
        return 
                {
                  "articleId": "api-projects-get-project",
                  "exampleId": "ex-projects-get-project",
                  "title": "Request project information",
                  "method": "GET",
                  "endpoint": "/api/projects/{id}",
                  "parameterDataId": "21",
                  "curlUrl": "https://yoursite.com/api/projects/123",
                  "requestBody": undefined
                }
            ;
    case "15":
        return 
                {
                    "articleId": "api-projects-put-project",
                    "exampleId": "ex-projects-put-project",
                    "title": "Update a project",
                    "method": "PUT",
                    "endpoint": "/api/projects/{id}",
                    "parameterDataId": "73",
                    "curlUrl": "https://yoursite.com/api/projects/123",
                    "requestBody": '{ "name": "...", "rel_type": "...", "clientid": "...", "billing_type": "...", "start_date": "...", "status": "..." }'
                }
            ;
    case "16":
        return 
                {
                  "articleId": "api-projects-get-project-search",
                  "exampleId": "ex-projects-get-project-search",
                  "title": "Search Project Information",
                  "method": "GET",
                  "endpoint": "/api/projects/search/{keysearch}",
                  "parameterDataId": "22",
                  "curlUrl": "https://yoursite.com/api/projects/search/acme",
                  "requestBody": undefined
                }
            ;
    case "17":
        return 
                {
                    "articleId": "api-proposals-put-proposal",
                    "exampleId": "ex-proposals-put-proposal",
                    "title": "Update a proposal",
                    "method": "PUT",
                    "endpoint": "/api/proposal/{id}",
                    "parameterDataId": "74",
                    "curlUrl": "https://yoursite.com/api/proposal/123",
                    "requestBody": "{ \"subject\": \"...\", \"Mandatory.\": \"...\", \"rel_id\": \"...\", \"proposal_to\": \"...\", \"date\": \"...\", \"open_till\": \"...\", \"currency\": \"...\", \"discount_type\": \"...\", \"status\": \"...\", \"Assigned\": \"...\", \"Email\": \"...\", \"newitems\": \"...\", \"items\": \"...\", \"removed_items\": \"...\" }"
                }
            ;
    case "18":
        return 
                {
                    "articleId": "api-proposals-get-proposal",
                    "exampleId": "ex-proposals-get-proposal",
                    "title": "Request Proposal information",
                    "method": "GET",
                    "endpoint": "/api/proposals",
                    "parameterDataId": "23",
                    "curlUrl": "https://yoursite.com/api/proposals",
                    "requestBody": undefined
                }
            ;
    case "19":
        return 
                {
                    "articleId": "api-proposals-post-proposals",
                    "exampleId": "ex-proposals-post-proposals",
                    "title": "Add New Proposals",
                    "method": "POST",
                    "endpoint": "/api/proposals",
                    "parameterDataId": "75",
                    "curlUrl": "https://yoursite.com/api/proposals",
                    "requestBody": "{ \"subject\": \"...\", \"Related\": \"...\", \"rel_id\": \"...\", \"proposal_to\": \"...\", \"date\": \"...\", \"open_till\": \"...\", \"currency\": \"...\", \"discount_type\": \"...\", \"status\": \"...\", \"Assigned\": \"...\", \"Email\": \"...\", \"newitems\": \"...\" }"
                }
            ;
    case "20":
        return 
                {
                  "articleId": "api-proposals-delete-proposal",
                  "exampleId": "ex-proposals-delete-proposal",
                  "title": "Delete Proposal",
                  "method": "DELETE",
                  "endpoint": "/api/proposals/{id}",
                  "parameterDataId": "24",
                  "curlUrl": "https://yoursite.com/api/proposals/123",
                  "requestBody": undefined
                }
            ;
    case "21":
        return 
                {
                  "articleId": "api-proposals-get-proposal-search",
                  "exampleId": "ex-proposals-get-proposal-search",
                  "title": "Search proposals information",
                  "method": "GET",
                  "endpoint": "/api/proposals/search/{keysearch}",
                  "parameterDataId": "4",
                  "curlUrl": "https://yoursite.com/api/proposals/search/acme",
                  "requestBody": undefined
                }
            ;
    case "22":
        return 
                {
                    "articleId": "api-staffs-delete-staff",
                    "exampleId": "ex-staffs-delete-staff",
                    "title": "Delete a Staff",
                    "method": "DELETE",
                    "endpoint": "/api/delete/staffs/{id}",
                    "parameterDataId": "25",
                    "curlUrl": "https://yoursite.com/api/delete/staffs/123",
                    "requestBody": undefined
                }
            ;
    case "23":
        return 
                {
                    "articleId": "api-staffs-post-staffs",
                    "exampleId": "ex-staffs-post-staffs",
                    "title": "Add New Staff",
                    "method": "POST",
                    "endpoint": "/api/staffs",
                    "parameterDataId": "76",
                    "curlUrl": "https://yoursite.com/api/staffs",
                    "requestBody": "{ \"firstname\": \"...\", \"email\": \"...\", \"password\": \"...\" }"
                }
            ;
    case "24":
        return 
                {
                    "articleId": "api-staffs-get-staff",
                    "exampleId": "ex-staffs-get-staff",
                    "title": "Request Staff information",
                    "method": "GET",
                    "endpoint": "/api/staffs/{id}",
                    "parameterDataId": "25",
                    "curlUrl": "https://yoursite.com/api/staffs/123",
                    "requestBody": undefined
                }
            ;
    case "25":
        return 
                {
                  "articleId": "api-staffs-put-staff",
                  "exampleId": "ex-staffs-put-staff",
                  "title": "Update a Staff",
                  "method": "PUT",
                  "endpoint": "/api/staffs/{id}",
                  "parameterDataId": "77",
                  "curlUrl": "https://yoursite.com/api/staffs/123",
                  "requestBody": "{ \"firstname\": \"...\", \"email\": \"...\", \"password\": \"...\" }"
                }
            ;
    case "26":
        return 
                {
                  "articleId": "api-staffs-get-staff-search",
                  "exampleId": "ex-staffs-get-staff-search",
                  "title": "Search Staff Information",
                  "method": "GET",
                  "endpoint": "/api/staffs/search/{keysearch}",
                  "parameterDataId": "22",
                  "curlUrl": "https://yoursite.com/api/staffs/search/acme",
                  "requestBody": undefined
                }
            ;
    case "27":
        return 
                {
                    "articleId": "api-subscriptions-request-subscriptions",
                    "exampleId": "ex-subscriptions-request-subscriptions",
                    "title": "Request all Subscriptions",
                    "method": "GET",
                    "endpoint": "/api/subscriptions/",
                    "parameterDataId": undefined,
                    "curlUrl": "https://yoursite.com/api/subscriptions/",
                    "requestBody": undefined
                }
            ;
    case "28":
        return 
                {
                  "articleId": "api-subscriptions-add-new-subscription",
                  "exampleId": "ex-subscriptions-add-new-subscription",
                  "title": "Add New Subscription",
                  "method": "POST",
                  "endpoint": "/api/subscriptions/",
                  "parameterDataId": "78",
                  "curlUrl": "https://yoursite.com/api/subscriptions/",
                  "requestBody": "{ \"name\": \"...\", \"description\": \"...\", \"description_in_item\": \"...\", \"clientid\": \"...\", \"date\": \"...\", \"terms\": \"...\", \"currency\": \"...\", \"tax_id\": \"...\", \"stripe_tax_id_2\": \"...\", \"stripe_plan_id\": \"...\", \"stripe_subscription_id\": \"...\", \"tax_id_2\": \"...\", \"next_billing_cycle\": \"...\", \"ends_at\": \"...\", \"status\": \"...\", \"quantity\": \"...\", \"project_id\": \"...\", \"hash\": \"...\", \"created\": \"...\", \"created_from\": \"...\", \"date_subscribed\": \"...\", \"in_test_environment\": \"...\", \"last_sent_at\": \"...\" }"
                }
            ;
    case "29":
        return 
                {
                    "articleId": "api-subscriptions-delete-a-subscription",
                    "exampleId": "ex-subscriptions-delete-a-subscription",
                    "title": "Delete a Subscription",
                    "method": "DELETE",
                    "endpoint": "/api/subscriptions/{id}",
                    "parameterDataId": "26",
                    "curlUrl": "https://yoursite.com/api/subscriptions/123",
                    "requestBody": undefined
                }
            ;
    case "30":
        return 
                {
                  "articleId": "api-subscriptions-request-subscription-information",
                  "exampleId": "ex-subscriptions-request-subscription-information",
                  "title": "Request Subscription Information",
                  "method": "GET",
                  "endpoint": "/api/subscriptions/{id}",
                  "parameterDataId": "27",
                  "curlUrl": "https://yoursite.com/api/subscriptions/123",
                  "requestBody": undefined
                }
            ;
    case "31":
        return 
                {
                  "articleId": "api-subscriptions-update-a-subscription",
                  "exampleId": "ex-subscriptions-update-a-subscription",
                  "title": "Update a Subscription",
                  "method": "PUT",
                  "endpoint": "/api/subscriptions/{id}",
                  "parameterDataId": "28",
                  "curlUrl": "https://yoursite.com/api/subscriptions/123",
                  "requestBody": '{ "field": "value" }'
                }
            ;
    case "32":
        return 
                {
                  "articleId": "api-tasks-delete-task",
                  "exampleId": "ex-tasks-delete-task",
                  "title": "Delete a Task",
                  "method": "DELETE",
                  "endpoint": "/api/delete/tasks/{id}",
                  "parameterDataId": "29",
                  "curlUrl": "https://yoursite.com/api/delete/tasks/123",
                  "requestBody": undefined
                }
            ;
    case "33":
        return 
                {
                    "articleId": "api-tasks-post-task",
                    "exampleId": "ex-tasks-post-task",
                    "title": "Add New Task",
                    "method": "POST",
                    "endpoint": "/api/tasks",
                    "parameterDataId": "79",
                    "curlUrl": "https://yoursite.com/api/tasks",
                    "requestBody": '{ "name": "...", "startdate": "...", "rel_type": "...", "rel_id": "..." }'
                }
            ;
    case "34":
        return 
                {
                    "articleId": "api-tasks-get-task",
                    "exampleId": "ex-tasks-get-task",
                    "title": "Request Task information",
                    "method": "GET",
                    "endpoint": "/api/tasks/{id}",
                    "parameterDataId": "29",
                    "curlUrl": "https://yoursite.com/api/tasks/123",
                    "requestBody": undefined
                }
            ;
    default:
        return 
                {
                  "articleId": "api-milestones-get-milestones",
                  "exampleId": "ex-milestones-get-milestones",
                  "title": "Request Milestones information",
                  "method": "GET",
                  "endpoint": "/api/milestones/{id}",
                  "parameterDataId": "18",
                  "curlUrl": "https://yoursite.com/api/milestones/123",
                  "requestBody": undefined
                }
            ;
    }
}


export default ApiEndpoint3
