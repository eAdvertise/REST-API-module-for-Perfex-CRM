import React from 'react'
import type { JSX } from 'react/jsx-runtime'



// Component

        function Chips() {
            return (
                <ul className={"chips"} data-astro-cid-j7pv25f6={""}>
                    <Chip label="MCP" />
                    <Chip label="Webhooks" />
                    <Chip label="Automation" />
                    <Chip label="Batch" />
                    <Chip label="Customers" />
                    <Chip label="Contacts" />
                    <Chip label="Leads" />
                    <Chip label="Invoices" />
                    <Chip label="Estimates" />
                    <Chip label="Payments" />
                    <Chip label="Proposals" />
                    <Chip label="Projects" />
                    <Chip label="Tasks" />
                    <Chip label="Tickets" />
                    <Chip label="Subscriptions" />
                    <Chip label="Items" />
                    <Chip label="Notes" />
                    <Chip label="Knowledge Base" />
                    <Chip label="Custom Fields" />
                </ul>
            )
        }
    

// Subcomponents

        function Chip({ label }: { label: string }) {
            return (
                <li data-astro-cid-j7pv25f6={""}>
                    <code data-astro-cid-j7pv25f6={""}>
                        {label}
                    </code>
                </li>
            )
        }
    

export default Chips
