import React from 'react'
import type { JSX } from 'react/jsx-runtime'

import ApiNavItem from './ApiNavItem.tsx'


// Component

        function ApiNavigation() {
            const sections: Array<[string, string[]]> = [
                ["MCP", ["0"]],
                ["Automation", ["32", "33", "34", "35"]],
                ["Batch", ["1"]],
                ["Leads", ["95", "2", "36", "37", "121", "38"]],
                ["Invoices", ["3", "96", "39", "122", "4", "40"]],
                ["Guest Invoices", ["146", "147"]],
                ["Customers", ["5", "41", "123", "42", "97"]],
                ["Calendar Events", ["43", "6", "98", "44", "124"]],
                ["Common", ["45"]],
                ["Contacts", ["7", "46", "125", "47", "99"]],
                ["Contracts", ["8", "100", "48", "49"]],
                ["Credit Notes", ["9", "126", "101", "50", "51"]],
                ["Estimates", ["10", "102", "52", "127", "53"]],
                ["Expense Categories", ["54"]],
                ["Expenses", ["11", "128", "103", "55", "56"]],
                ["Items", ["104", "12", "57", "129", "58"]],
                ["Knowledge Base", ["59", "13", "105", "60", "130", "61", "14", "106", "131"]],
                ["Milestones", ["107", "15", "62", "132", "63"]],
                ["Notes", ["16", "108", "64", "133", "65"]],
                ["Payment Modes", ["66"]],
                ["Payments", ["17", "67", "68"]],
                ["Projects", ["109", "18", "69", "134", "70"]],
                ["Proposals", ["135", "71", "19", "110", "72"]],
                ["Staffs", ["111", "20", "73", "136", "74"]],
                ["Subscriptions", ["75", "21", "112", "76", "137"]],
                ["Tasks", ["113", "22", "77", "138", "78", "23", "79", "24", "114", "139", "115", "140", "80"]],
                ["Taxes", ["81"]],
                ["Thirdparty", ["82", "25", "116", "83", "141"]],
                ["Tickets", ["117", "26", "84", "142", "27", "85"]],
                ["Timesheets", ["86", "28", "118", "87", "143"]],
                ["Webhooks", ["88", "29", "119", "89", "144", "90", "30", "91"]],
                ["Custom Fields", ["92", "120", "93", "94", "31", "145"]]
            ];

            return (
                <ul className={"sidenav nav nav-list"}>
                    {sections.map(([title, dataIds]) => (
                        <>
                        <ApiNavHeader key={`${title}-header`} title={title} />
                            {dataIds.map((dataId) => (
                                <ApiNavEntry key={dataId} dataId={dataId} />
                            ))}
                        </>
                    ))}
                </ul>
            );
        }
    

// Subcomponents

        function ApiNavHeader({ title }: { title: string }) {
            const sectionId = `api-${title.toLowerCase().replace(/[^a-z0-9]+/g, '-')}`;

            return (
                <li className={"nav-header nav-list-item"}>
                    <a href={`#${sectionId}`}>
                        {title}
                    </a>
                </li>
            );
        }

        function ApiNavEntry({ dataId }: { dataId: string }) {
            return (
                <li className={"nav-list-item"}>
                    <ApiNavItem dataId={dataId} />
                </li>
            );
        }
    

export default ApiNavigation
