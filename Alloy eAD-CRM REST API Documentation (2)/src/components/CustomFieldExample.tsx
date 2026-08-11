import React from 'react'
import type { JSX } from 'react/jsx-runtime'

import CopyButton from './CopyButton.tsx'
import CodeValue from './CodeValue.tsx'
import ExampleTabs from './ExampleTabs.tsx'
import LineBreak from './LineBreak.tsx'


        type CustomFieldExampleData = {
            slug: string;
            title: string;
            method: "GET" | "DELETE";
            rowClassName: string;
            description: JSX.Element;
        };
    
// Component

        function CustomFieldExample({ dataId }: { dataId: string }) {
            const {
                slug,
                title,
                method,
                rowClassName,
                description
            }: CustomFieldExampleData = getCustomFieldExampleData(dataId);

            return (
                <article id={`api-custom-fields-${slug}-action-example`}>
                    <h1>
                        {title}
                    </h1>
                    <div className={rowClassName}>
                        <div className={"col-md-7 no-float"}>
                            <pre className={"full-pre"}>
                                <span className={`typ typ-${method.toLowerCase()}`}>
                                    {method}
                                </span>
                                <span className={"url"}>
                                    /N/A
                                </span>
                                <CopyButton hidden={true} />
                            </pre>
                            <div className={"endpoint-desc"}>
                                {description}
                            </div>
                        </div>
                        <div className={"col-md-4 section-example no-float"}>
                            <ExampleTabs dataId="2" />
                            <CurlExample slug={slug} method={method} />
                        </div>
                    </div>
                </article>
            );
        }
    

// Subcomponents

        function CurlExample({
            slug,
            method
        }: {
            slug: string;
            method: "GET" | "DELETE";
        }) {
            return (
                <div className={"tab-content"}>
                    <div
                        className={"tab-pane active"}
                        id={`ex-custom-fields-${slug}-action-example-curl`}
                    >
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
                                    <span style={{color: "#89B4FA", fontStyle: "italic"}}>
                                        curl
                                    </span>
                                    <span style={{color: "#A6E3A1"}}>
                                        {` -X`}
                                    </span>
                                    <span style={{color: "#A6E3A1"}}>
                                        {` ${method}`}
                                    </span>
                                    <span style={{color: "#A6E3A1"}}>
                                        {` "https://yoursite.com/N/A"`}
                                    </span>
                                    <span style={{color: "#F5C2E7"}}>
                                        {" \\"}
                                    </span>
                                </span>
                                <span className={"line"}>
                                    <span style={{color: "#A6E3A1"}}>
                                        {`  -H`}
                                    </span>
                                    <span style={{color: "#A6E3A1"}}>
                                        {` "authtoken: YOUR_API_TOKEN"`}
                                    </span>
                                </span>
                            </code>
                            <CopyButton hidden={false} />
                        </pre>
                    </div>
                    <div
                        className={"tab-pane sf-hidden"}
                        id={`ex-custom-fields-${slug}-action-example-0`}
                    >

                    </div>
                    <div
                        className={"tab-pane sf-hidden"}
                        id={`ex-custom-fields-${slug}-action-example-1`}
                    >

                    </div>
                </div>
            );
        }
    


        function getCustomFieldExampleData(id: string): CustomFieldExampleData {
            const stringId = String(id);

            const data: Record<string, CustomFieldExampleData> = {
                "0": {
                    slug: "delete",
                    title: "Delete Custom Fields",
                    method: "DELETE",
                    rowClassName: "row pre-delete",
                    description: (
                        <p>
                            {`To remove particular custom field value you can use `}
                            <strong>
                                Update
                            </strong>
                            {` action and an `}
                            <strong>
                                empty
                            </strong>
                            {` value in the custom field.`}
                            <LineBreak />
                            {` Note: When you delete any record the corresponding custom field data will be `}
                            <strong>
                                automatically deleted
                            </strong>
                            .
                        </p>
                    )
                },
                "1": {
                    slug: "get",
                    title: "Request Custom Fields",
                    method: "GET",
                    rowClassName: "row pre-get",
                    description: (
                        <p>
                            {`Custom fields' data will be returned combined with other request's information during the initial GET request of each available endpoint (Contacts, Invoices etc) with their respective `}
                            <CodeValue text="label" />
                            {` and `}
                            <CodeValue text="value" />
                            {` key`}
                        </p>
                    )
                },
                "2": {
                    slug: "search",
                    title: "Search custom field values' information",
                    method: "GET",
                    rowClassName: "row pre-get",
                    description: (
                        <p>
                            {`Custom fields' data will be returned combined with other request's information during the initial SEARCH request of each available endpoint (Contacts, Invoices etc) with their respective `}
                            <CodeValue text="label" />
                            {` and `}
                            <CodeValue text="value" />
                            {` key`}
                        </p>
                    )
                }
            };

            return data[stringId] ?? data["0"];
        }
    

export default CustomFieldExample
