import React from 'react'
import type { JSX } from 'react/jsx-runtime'

import ParameterRow from './ParameterRow.tsx'


        type DefaultParameterRowData = {
            field: string;
            type: string;
            description: string;
            defaultValue: string;
        };
    
// Component

        function DefaultParameterRow({ dataId }: { dataId: string }) {
            const {
                field,
                type,
                description,
                defaultValue
            }: DefaultParameterRowData = getDefaultParameterRowData(dataId);

            return (
                <tr>
                    <td className={"code"}>
                        {field}
                    </td>
                    <td>
                        {type}
                    </td>
                    <td>
                        <span className={"label-optional"}>
                            {`optional `}
                        </span>
                        <span>
                            {description}
                        </span>
                        <p>
                            <code>
                                {defaultValue}
                            </code>
                        </p>
                    </td>
                </tr>
            );
        }
    

function getDefaultParameterRowData(id): DefaultParameterRowData  {
    switch (String(id)) {
    case "0":
        return 
                {
                    "field": "include_shipping",
                    "type": "boolean",
                    "description": "Optional. set yes if you want add Shipping Address",
                    "defaultValue": "default: no"
                }
            ;
    case "1":
        return 
                {
                  "field": "direction",
                  "type": "String",
                  "description": "Optional Direction (rtl or ltr)",
                  "defaultValue": "default: rtl"
                }
            ;
    case "2":
        return 
                {
                    "field": "is_primary",
                    "type": "String",
                    "description": "Optional Primary Contact (set on or don't pass it)",
                    "defaultValue": "default: on"
                }
            ;
    case "3":
        return 
                {
                  "field": "invoice_emails",
                  "type": "String",
                  "description": "Optional E-Mail Notification for Invoices (set value same as name or don't pass it)",
                  "defaultValue": "default: invoice_emails"
                }
            ;
    case "4":
        return 
                {
                    "field": "estimate_emails",
                    "type": "String",
                    "description": "Optional E-Mail Notification for Estimate (set value same as name or don't pass it)",
                    "defaultValue": "default: estimate_emails"
                }
            ;
    case "5":
        return 
                {
                    "field": "credit_note_emails",
                    "type": "String",
                    "description": "Optional E-Mail Notification for Credit Note (set value same as name or don't pass it)",
                    "defaultValue": "default: credit_note_emails"
                }
            ;
    case "6":
        return 
                {
                  "field": "project_emails",
                  "type": "String",
                  "description": "Optional E-Mail Notification for Project (set value same as name or don't pass it)",
                  "defaultValue": "default: project_emails"
                }
            ;
    case "7":
        return 
                {
                    "field": "ticket_emails",
                    "type": "String",
                    "description": "Optional E-Mail Notification for Tickets (set value same as name or don't pass it)",
                    "defaultValue": "default: ticket_emails"
                }
            ;
    case "8":
        return 
                {
                    "field": "task_emails",
                    "type": "String",
                    "description": "Optional E-Mail Notification for Task (set value same as name or don't pass it)",
                    "defaultValue": "default: task_emails"
                }
            ;
    case "9":
        return 
                {
                  "field": "contract_emails",
                  "type": "String",
                  "description": "Optional E-Mail Notification for Contract (set value same as name or don't pass it)",
                  "defaultValue": "default: contract_emails"
                }
            ;
    case "10":
        return 
            {
              "field": "active",
              "type": "Number",
              "description": "1 published / 0 hidden.",
              "defaultValue": "default: 1"
            }
          ;
    case "11":
        return 
                {
                    "field": "staff_article",
                    "type": "Number",
                    "description": "1 = internal (staff only).",
                    "defaultValue": "default: 0"
                }
            ;
    case "12":
        return 
                {
                  "field": "color",
                  "type": "String",
                  "description": "Group color.",
                  "defaultValue": "default: #28B8DA"
                }
            ;
    case "13":
        return 
                {
                    "field": "page",
                    "type": "Number",
                    "description": "Optional Page number for pagination (when ID is not provided)",
                    "defaultValue": "default: 1"
                }
            ;
    case "14":
        return 
                {
                  "field": "per_page",
                  "type": "Number",
                  "description": "Optional Number of items per page (min: 1, max: 100, when ID is not provided)",
                  "defaultValue": "default: 20"
                }
            ;
    case "15":
        return 
                {
                    "field": "timeout",
                    "type": "Number",
                    "description": "Request timeout in seconds (1-120).",
                    "defaultValue": "default: 30"
                }
            ;
    case "16":
        return 
                {
                    "field": "retry_count",
                    "type": "Number",
                    "description": "Retries in queued mode (0-10).",
                    "defaultValue": "default: 3"
                }
            ;
    default:
        return 
                {
                    "field": "include_shipping",
                    "type": "boolean",
                    "description": "Optional. set yes if you want add Shipping Address",
                    "defaultValue": "default: no"
                }
            ;
    }
}


export default DefaultParameterRow
