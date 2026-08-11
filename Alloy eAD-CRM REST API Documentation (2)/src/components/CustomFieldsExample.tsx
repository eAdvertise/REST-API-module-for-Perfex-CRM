import React from 'react'
import type { JSX } from 'react/jsx-runtime'

import CopyButton from './CopyButton.tsx'
import CodeValue from './CodeValue.tsx'
import ExampleTabs from './ExampleTabs.tsx'
import LineBreak from './LineBreak.tsx'
import ParameterTable from './ParameterTable.tsx'


        type CustomFieldsExampleData = {
            articleId: string;
            title: string;
            method: "POST" | "PUT";
            rowClassName: "row pre-post" | "row pre-put";
            methodClassName: "typ typ-post" | "typ typ-put";
            parameterTableDataId: string;
            curlPaneId: string;
            emptyPane0Id: string;
            emptyPane1Id: string;
        };
    
// Component

        function CustomFieldsExample({
            dataId
        }: {
            dataId: string;
        }) {
            const data: CustomFieldsExampleData = getCustomFieldsExampleData(dataId);

            return (
                <article id={data.articleId}>
                    <h1>{data.title}</h1>
                    <div className={data.rowClassName}>
                        <div className={"col-md-7 no-float"}>
                            <pre className={"full-pre"}>
                                <span className={data.methodClassName}>
                                    {data.method}
                                </span>
                                <span className={"url"}>
                                    /N/A
                                </span>
                                <CopyButton hidden={true} />
                            </pre>
                            <div className={"endpoint-desc"}>
                                <p>
                                    {`Submit URL for ${data.method} request of the custom fields remains the same for each endpoint (ie `}
                                    <CodeValue text="api/contacts" />
                                    {` for Contacts endpoint, `}
                                    <CodeValue text="api/invoices" />
                                    {` for Invoices endpoint, etc..) `}
                                    <LineBreak />
                                </p>
                                <h2>
                                    In this example, we will use the following form data which corresponds to the following custom field types:
                                </h2>
                                <CustomFieldType index={1} label="Input Type" />
                                <CustomFieldType index={2} label="Number" />
                                <CustomFieldType index={3} label="Textarea" />
                                <CustomFieldType index={4} label="Radio" />
                                <CustomFieldType index={5} label="Checkbox" />
                                <CustomFieldType index={6} label="Multiselect" />
                                <CustomFieldType index={7} label="Date" />
                                <CustomFieldType index={8} label="Datetime" />
                                <CustomFieldType index={9} label="Color" />
                                <code>
                                    custom_fields[invoice][10]
                                </code>
                                {` = `}
                                <strong>
                                    Link
                                </strong>
                                <p>
                                </p>
                            </div>
                            <h2 className={"sub"}>
                                Parameters
                            </h2>
                            <div className={"table-responsive-wrapper"}>
                                <ParameterTable dataId={data.parameterTableDataId} />
                            </div>
                        </div>
                        <div className={"col-md-4 section-example no-float"}>
                            <ExampleTabs dataId="2" />
                            <div className={"tab-content"}>
                                <div className={"tab-pane active"} id={data.curlPaneId}>
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
                                                    {` ${data.method}`}
                                                </span>
                                                <span style={{color:"#A6E3A1"}}>
                                                    {` "https://yoursite.com/N/A"`}
                                                </span>
                                                <span style={{color:"#F5C2E7"}}>
                                                    {" \\"}
                                                </span>
                                            </span>
                                            <CurlHeaderLine text={` "authtoken: YOUR_API_TOKEN"`} />
                                            <CurlHeaderLine text={` "Content-Type: application/json"`} />
                                            <CurlLine
                                                flag={`  -d`}
                                                text={` '{ "custom_fields[customFieldType]": "..." }'`}
                                            />
                                        </code>
                                        <CopyButton hidden={false} />
                                    </pre>
                                </div>
                                <div className={"tab-pane sf-hidden"} id={data.emptyPane0Id}>
                                </div>
                                <div className={"tab-pane sf-hidden"} id={data.emptyPane1Id}>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            );
        }
    

// Subcomponents

        function CustomFieldType({
            index,
            label
        }: {
            index: number;
            label: string;
        }) {
            return (
                <>
                    <code>
                        {`custom_fields[invoice][${index}]`}
                    </code>
                    {` = `}
                    <strong>
                        {label}
                    </strong>
                    <br>
                    </br>
                </>
            );
        }

        function CurlHeaderLine({
            text
        }: {
            text: string;
        }) {
            return (
                <span className={"line"}>
                    <span style={{color:"#A6E3A1"}}>
                        {`  -H`}
                    </span>
                    <span style={{color:"#A6E3A1"}}>
                        {text}
                    </span>
                    <span style={{color:"#F5C2E7"}}>
                        {" \\"}
                    </span>
                </span>
            );
        }

        function CurlLine({
            flag,
            text
        }: {
            flag: string;
            text: string;
        }) {
            return (
                <span className={"line"}>
                    <span style={{color:"#A6E3A1"}}>
                        {flag}
                    </span>
                    <span style={{color:"#A6E3A1"}}>
                        {text}
                    </span>
                </span>
            );
        }
    


        function getCustomFieldsExampleData(id: string): CustomFieldsExampleData {
            const stringId = String(id);

            if (stringId === "1") {
                return {
                    articleId: "api-custom-fields-put-action-example",
                    title: "Update Custom Fields",
                    method: "PUT",
                    rowClassName: "row pre-put",
                    methodClassName: "typ typ-put",
                    parameterTableDataId: "34",
                    curlPaneId: "ex-custom-fields-put-action-example-curl",
                    emptyPane0Id: "ex-custom-fields-put-action-example-0",
                    emptyPane1Id: "ex-custom-fields-put-action-example-1"
                };
            }

            return {
                articleId: "api-custom-fields-post-action-example",
                title: "Add Custom Fields",
                method: "POST",
                rowClassName: "row pre-post",
                methodClassName: "typ typ-post",
                parameterTableDataId: "33",
                curlPaneId: "ex-custom-fields-post-action-example-curl",
                emptyPane0Id: "ex-custom-fields-post-action-example-0",
                emptyPane1Id: "ex-custom-fields-post-action-example-1"
            };
        }
    

export default CustomFieldsExample
