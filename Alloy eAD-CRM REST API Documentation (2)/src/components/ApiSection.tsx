import React from 'react'
import type { JSX } from 'react/jsx-runtime'

import ApiEndpoint1 from './ApiEndpoint1.tsx'
import ApiEndpoint from './ApiEndpoint.tsx'
import ApiEndpoint3 from './ApiEndpoint3.tsx'
import ApiEndpoint2 from './ApiEndpoint2.tsx'


        type ApiSectionData = {
            sectionId: string;
            title: string;
            endpoints: Array<{
                type: "ApiEndpoint1" | "ApiEndpoint" | "ApiEndpoint3" | "ApiEndpoint2";
                dataId: string;
            }>;
        };
    
// Component

        function ApiSection({ dataId }: { dataId: string }) {
            const { sectionId, title, endpoints }: ApiSectionData = getApiSectionData(dataId);

            return (
                <section id={sectionId} data-astro-cid-j7pv25f6={""}>
                    <h2 data-astro-cid-j7pv25f6={""}>
                        {title}
                    </h2>
                    {endpoints.map((endpoint, index) => {
                        if (endpoint.type === "ApiEndpoint1") {
                            return <ApiEndpoint1 key={index} dataId={endpoint.dataId} />;
                        }
                        if (endpoint.type === "ApiEndpoint") {
                            return <ApiEndpoint key={index} dataId={endpoint.dataId} />;
                        }
                        if (endpoint.type === "ApiEndpoint3") {
                            return <ApiEndpoint3 key={index} dataId={endpoint.dataId} />;
                        }
                        return <ApiEndpoint2 key={index} dataId={endpoint.dataId} />;
                    })}
                </section>
            );
        }
    


        function getApiSectionData(id: string): ApiSectionData {
            const stringId = String(id);

            const sections: Record<string, ApiSectionData> = {
                "0": {
                    sectionId: "api-automation",
                    title: "Automation",
                    endpoints: [
                        { type: "ApiEndpoint1", dataId: "1" },
                        { type: "ApiEndpoint1", dataId: "2" },
                        { type: "ApiEndpoint1", dataId: "3" },
                        { type: "ApiEndpoint1", dataId: "4" }
                    ]
                },
                "1": {
                    sectionId: "api-leads",
                    title: "Leads",
                    endpoints: [
                        { type: "ApiEndpoint1", dataId: "6" },
                        { type: "ApiEndpoint1", dataId: "7" },
                        { type: "ApiEndpoint1", dataId: "8" },
                        { type: "ApiEndpoint1", dataId: "9" },
                        { type: "ApiEndpoint1", dataId: "10" },
                        { type: "ApiEndpoint1", dataId: "11" }
                    ]
                },
                "2": {
                    sectionId: "api-invoices",
                    title: "Invoices",
                    endpoints: [
                        { type: "ApiEndpoint1", dataId: "12" },
                        { type: "ApiEndpoint1", dataId: "13" },
                        { type: "ApiEndpoint1", dataId: "14" },
                        { type: "ApiEndpoint1", dataId: "15" },
                        { type: "ApiEndpoint1", dataId: "16" },
                        { type: "ApiEndpoint1", dataId: "17" }
                    ]
                },
                "3": {
                    sectionId: "api-customers",
                    title: "Customers",
                    endpoints: [
                        { type: "ApiEndpoint1", dataId: "18" },
                        { type: "ApiEndpoint1", dataId: "19" },
                        { type: "ApiEndpoint1", dataId: "20" },
                        { type: "ApiEndpoint1", dataId: "21" },
                        { type: "ApiEndpoint1", dataId: "22" }
                    ]
                },
                "4": {
                    sectionId: "api-calendar-events",
                    title: "Calendar Events",
                    endpoints: [
                        { type: "ApiEndpoint1", dataId: "23" },
                        { type: "ApiEndpoint1", dataId: "24" },
                        { type: "ApiEndpoint1", dataId: "25" },
                        { type: "ApiEndpoint1", dataId: "26" },
                        { type: "ApiEndpoint1", dataId: "27" }
                    ]
                },
                "5": {
                    sectionId: "api-contacts",
                    title: "Contacts",
                    endpoints: [
                        { type: "ApiEndpoint1", dataId: "29" },
                        { type: "ApiEndpoint1", dataId: "30" },
                        { type: "ApiEndpoint1", dataId: "31" },
                        { type: "ApiEndpoint1", dataId: "32" },
                        { type: "ApiEndpoint1", dataId: "33" }
                    ]
                },
                "6": {
                    sectionId: "api-contracts",
                    title: "Contracts",
                    endpoints: [
                        { type: "ApiEndpoint1", dataId: "34" },
                        { type: "ApiEndpoint", dataId: "0" },
                        { type: "ApiEndpoint", dataId: "1" },
                        { type: "ApiEndpoint", dataId: "2" }
                    ]
                },
                "7": {
                    sectionId: "api-credit-notes",
                    title: "Credit Notes",
                    endpoints: [
                        { type: "ApiEndpoint", dataId: "3" },
                        { type: "ApiEndpoint", dataId: "4" },
                        { type: "ApiEndpoint", dataId: "5" },
                        { type: "ApiEndpoint", dataId: "6" },
                        { type: "ApiEndpoint", dataId: "7" }
                    ]
                },
                "8": {
                    sectionId: "api-estimates",
                    title: "Estimates",
                    endpoints: [
                        { type: "ApiEndpoint", dataId: "8" },
                        { type: "ApiEndpoint", dataId: "9" },
                        { type: "ApiEndpoint", dataId: "10" },
                        { type: "ApiEndpoint", dataId: "11" },
                        { type: "ApiEndpoint", dataId: "12" }
                    ]
                },
                "9": {
                    sectionId: "api-expenses",
                    title: "Expenses",
                    endpoints: [
                        { type: "ApiEndpoint", dataId: "14" },
                        { type: "ApiEndpoint", dataId: "15" },
                        { type: "ApiEndpoint", dataId: "16" },
                        { type: "ApiEndpoint", dataId: "17" },
                        { type: "ApiEndpoint", dataId: "18" }
                    ]
                },
                "10": {
                    sectionId: "api-items",
                    title: "Items",
                    endpoints: [
                        { type: "ApiEndpoint", dataId: "19" },
                        { type: "ApiEndpoint", dataId: "20" },
                        { type: "ApiEndpoint", dataId: "21" },
                        { type: "ApiEndpoint", dataId: "22" },
                        { type: "ApiEndpoint", dataId: "23" }
                    ]
                },
                "11": {
                    sectionId: "api-knowledge-base",
                    title: "Knowledge Base",
                    endpoints: [
                        { type: "ApiEndpoint", dataId: "24" },
                        { type: "ApiEndpoint", dataId: "25" },
                        { type: "ApiEndpoint", dataId: "26" },
                        { type: "ApiEndpoint", dataId: "27" },
                        { type: "ApiEndpoint", dataId: "28" },
                        { type: "ApiEndpoint", dataId: "29" },
                        { type: "ApiEndpoint", dataId: "30" },
                        { type: "ApiEndpoint", dataId: "31" },
                        { type: "ApiEndpoint", dataId: "32" }
                    ]
                },
                "12": {
                    sectionId: "api-milestones",
                    title: "Milestones",
                    endpoints: [
                        { type: "ApiEndpoint", dataId: "33" },
                        { type: "ApiEndpoint", dataId: "34" },
                        { type: "ApiEndpoint3", dataId: "0" },
                        { type: "ApiEndpoint3", dataId: "1" },
                        { type: "ApiEndpoint3", dataId: "2" }
                    ]
                },
                "13": {
                    sectionId: "api-notes",
                    title: "Notes",
                    endpoints: [
                        { type: "ApiEndpoint3", dataId: "3" },
                        { type: "ApiEndpoint3", dataId: "4" },
                        { type: "ApiEndpoint3", dataId: "5" },
                        { type: "ApiEndpoint3", dataId: "6" },
                        { type: "ApiEndpoint3", dataId: "7" }
                    ]
                },
                "14": {
                    sectionId: "api-payments",
                    title: "Payments",
                    endpoints: [
                        { type: "ApiEndpoint3", dataId: "9" },
                        { type: "ApiEndpoint3", dataId: "10" },
                        { type: "ApiEndpoint3", dataId: "11" }
                    ]
                },
                "15": {
                    sectionId: "api-projects",
                    title: "Projects",
                    endpoints: [
                        { type: "ApiEndpoint3", dataId: "12" },
                        { type: "ApiEndpoint3", dataId: "13" },
                        { type: "ApiEndpoint3", dataId: "14" },
                        { type: "ApiEndpoint3", dataId: "15" },
                        { type: "ApiEndpoint3", dataId: "16" }
                    ]
                },
                "16": {
                    sectionId: "api-proposals",
                    title: "Proposals",
                    endpoints: [
                        { type: "ApiEndpoint3", dataId: "17" },
                        { type: "ApiEndpoint3", dataId: "18" },
                        { type: "ApiEndpoint3", dataId: "19" },
                        { type: "ApiEndpoint3", dataId: "20" },
                        { type: "ApiEndpoint3", dataId: "21" }
                    ]
                },
                "17": {
                    sectionId: "api-staffs",
                    title: "Staffs",
                    endpoints: [
                        { type: "ApiEndpoint3", dataId: "22" },
                        { type: "ApiEndpoint3", dataId: "23" },
                        { type: "ApiEndpoint3", dataId: "24" },
                        { type: "ApiEndpoint3", dataId: "25" },
                        { type: "ApiEndpoint3", dataId: "26" }
                    ]
                },
                "18": {
                    sectionId: "api-subscriptions",
                    title: "Subscriptions",
                    endpoints: [
                        { type: "ApiEndpoint3", dataId: "27" },
                        { type: "ApiEndpoint3", dataId: "28" },
                        { type: "ApiEndpoint3", dataId: "29" },
                        { type: "ApiEndpoint3", dataId: "30" },
                        { type: "ApiEndpoint3", dataId: "31" }
                    ]
                },
                "19": {
                    sectionId: "api-tasks",
                    title: "Tasks",
                    endpoints: [
                        { type: "ApiEndpoint3", dataId: "32" },
                        { type: "ApiEndpoint3", dataId: "33" },
                        { type: "ApiEndpoint3", dataId: "34" },
                        { type: "ApiEndpoint2", dataId: "0" },
                        { type: "ApiEndpoint2", dataId: "1" },
                        { type: "ApiEndpoint2", dataId: "2" },
                        { type: "ApiEndpoint2", dataId: "3" },
                        { type: "ApiEndpoint2", dataId: "4" },
                        { type: "ApiEndpoint2", dataId: "5" },
                        { type: "ApiEndpoint2", dataId: "6" },
                        { type: "ApiEndpoint2", dataId: "7" },
                        { type: "ApiEndpoint2", dataId: "8" },
                        { type: "ApiEndpoint2", dataId: "9" }
                    ]
                },
                "20": {
                    sectionId: "api-thirdparty",
                    title: "Thirdparty",
                    endpoints: [
                        { type: "ApiEndpoint2", dataId: "11" },
                        { type: "ApiEndpoint2", dataId: "12" },
                        { type: "ApiEndpoint2", dataId: "13" },
                        { type: "ApiEndpoint2", dataId: "14" },
                        { type: "ApiEndpoint2", dataId: "15" }
                    ]
                },
                "21": {
                    sectionId: "api-tickets",
                    title: "Tickets",
                    endpoints: [
                        { type: "ApiEndpoint2", dataId: "16" },
                        { type: "ApiEndpoint2", dataId: "17" },
                        { type: "ApiEndpoint2", dataId: "18" },
                        { type: "ApiEndpoint2", dataId: "19" },
                        { type: "ApiEndpoint2", dataId: "20" },
                        { type: "ApiEndpoint2", dataId: "21" }
                    ]
                },
                "22": {
                    sectionId: "api-timesheets",
                    title: "Timesheets",
                    endpoints: [
                        { type: "ApiEndpoint2", dataId: "22" },
                        { type: "ApiEndpoint2", dataId: "23" },
                        { type: "ApiEndpoint2", dataId: "24" },
                        { type: "ApiEndpoint2", dataId: "25" },
                        { type: "ApiEndpoint2", dataId: "26" }
                    ]
                },
                "23": {
                    sectionId: "api-webhooks",
                    title: "Webhooks",
                    endpoints: [
                        { type: "ApiEndpoint2", dataId: "27" },
                        { type: "ApiEndpoint2", dataId: "28" },
                        { type: "ApiEndpoint2", dataId: "29" },
                        { type: "ApiEndpoint2", dataId: "30" },
                        { type: "ApiEndpoint2", dataId: "31" },
                        { type: "ApiEndpoint2", dataId: "32" },
                        { type: "ApiEndpoint2", dataId: "33" },
                        { type: "ApiEndpoint2", dataId: "34" }
                    ]
                }
            };

            return sections[stringId] ?? sections["0"];
        }
    

export default ApiSection
