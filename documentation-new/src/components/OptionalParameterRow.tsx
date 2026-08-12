import React from 'react'
import type { JSX } from 'react/jsx-runtime'

import ParameterRow from './ParameterRow.tsx'


        type OptionalParameterRowData = {
            field: string;
            type: string;
            description: string;
        };
    
// Component

        function OptionalParameterRow({ dataId }: { dataId: string }) {
            const { field, type, description }: OptionalParameterRowData =
                getOptionalParameterRowData(dataId);

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
                    </td>
                </tr>
            );
        }
    

function getOptionalParameterRowData(id): OptionalParameterRowData  {
    switch (String(id)) {
    case "0":
        return 
                {
                  "field": "Authorization",
                  "type": "String",
                  "description": "Bearer token (alternative to authtoken header)"
                }
            ;
    case "1":
        return 
                {
                  "field": "resource",
                  "type": "String",
                  "description": "Resource name (customers, invoices, leads, tasks, tickets) - required for poll and test methods"
                }
            ;
    case "2":
        return 
                {
                  "field": "since",
                  "type": "Number",
                  "description": "Unix timestamp to filter records created/updated after this time. Default: last 24 hours"
                }
            ;
    case "3":
        return 
                {
                    "field": "limit",
                    "type": "Number",
                    "description": "Maximum number of records to return. Default: 50"
                }
            ;
    case "4":
        return 
                {
                    "field": "client_id",
                    "type": "String",
                    "description": "Optional Lead From Customer."
                }
            ;
    case "5":
        return 
                {
                    "field": "tags",
                    "type": "String",
                    "description": "Optional Lead tags."
                }
            ;
    case "6":
        return 
                {
                  "field": "contact",
                  "type": "String",
                  "description": "Optional Lead contact."
                }
            ;
    case "7":
        return 
                {
                  "field": "title",
                  "type": "String",
                  "description": "Optional Position."
                }
            ;
    case "8":
        return 
                {
                  "field": "email",
                  "type": "String",
                  "description": "Optional Lead Email Address."
                }
            ;
    case "9":
        return 
                {
                    "field": "website",
                    "type": "String",
                    "description": "Optional Lead Website."
                }
            ;
    case "10":
        return 
                {
                    "field": "phonenumber",
                    "type": "String",
                    "description": "Optional Lead Phone."
                }
            ;
    case "11":
        return 
                {
                    "field": "company",
                    "type": "String",
                    "description": "Optional Lead company."
                }
            ;
    case "12":
        return 
                {
                  "field": "address",
                  "type": "String",
                  "description": "Optional Lead address."
                }
            ;
    case "13":
        return 
                {
                  "field": "city",
                  "type": "String",
                  "description": "Optional Lead City."
                }
            ;
    case "14":
        return 
                {
                  "field": "zip",
                  "type": "String",
                  "description": "Optional Zip code."
                }
            ;
    case "15":
        return 
                {
                  "field": "state",
                  "type": "String",
                  "description": "Optional Lead state."
                }
            ;
    case "16":
        return 
                {
                  "field": "country",
                  "type": "String",
                  "description": "Optional Lead Country."
                }
            ;
    case "17":
        return 
                {
                  "field": "default_language",
                  "type": "String",
                  "description": "Optional Lead Default Language."
                }
            ;
    case "18":
        return 
                {
                  "field": "description",
                  "type": "String",
                  "description": "Optional Lead description."
                }
            ;
    case "19":
        return 
                {
                  "field": "custom_contact_date",
                  "type": "String",
                  "description": "Optional Lead From Customer."
                }
            ;
    case "20":
        return 
                {
                  "field": "contacted_today",
                  "type": "String",
                  "description": "Optional Lead Contacted Today."
                }
            ;
    case "21":
        return 
                {
                    "field": "is_public",
                    "type": "String",
                    "description": "Optional Lead google sheet id."
                }
            ;
    case "22":
        return 
                {
                    "field": "zip",
                    "type": "String",
                    "description": "Optional Zip Code."
                }
            ;
    case "23":
        return 
                {
                    "field": "lastcontact",
                    "type": "String",
                    "description": "Optional Lead Last Contact."
                }
            ;
    case "24":
        return 
                {
                  "field": "billing_city",
                  "type": "String",
                  "description": "Optional. City Name for billing"
                }
            ;
    case "25":
        return 
                {
                  "field": "billing_state",
                  "type": "String",
                  "description": "Optional. Name of state for billing"
                }
            ;
    case "26":
        return 
                {
                  "field": "billing_zip",
                  "type": "Number",
                  "description": "Optional. Zip code"
                }
            ;
    case "27":
        return 
                {
                  "field": "billing_country",
                  "type": "Number",
                  "description": "Optional. Country code"
                }
            ;
    case "28":
        return 
                {
                  "field": "show_shipping_on_invoice",
                  "type": "boolean",
                  "description": "Optional. Shows shipping details in invoice."
                }
            ;
    case "29":
        return 
                {
                  "field": "shipping_street",
                  "type": "String",
                  "description": "Optional. Address of shipping"
                }
            ;
    case "30":
        return 
                {
                    "field": "shipping_city",
                    "type": "String",
                    "description": "Optional. City name for shipping"
                }
            ;
    case "31":
        return 
                {
                    "field": "shipping_state",
                    "type": "String",
                    "description": "Optional. Name of state for shipping"
                }
            ;
    case "32":
        return 
                {
                    "field": "shipping_zip",
                    "type": "Number",
                    "description": "Optional. Zip code for shipping"
                }
            ;
    case "33":
        return 
                {
                    "field": "shipping_country",
                    "type": "Number",
                    "description": "Optional. Country code"
                }
            ;
    case "34":
        return 
                {
                    "field": "duedate",
                    "type": "Date",
                    "description": "Optional. Due date for Invoice"
                }
            ;
    case "35":
        return 
                {
                    "field": "cancel_overdue_reminders",
                    "type": "boolean",
                    "description": "Optional. Prevent sending overdue remainders for invoice"
                }
            ;
    case "36":
        return 
                {
                    "field": "tags",
                    "type": "String",
                    "description": "Optional. TAGS comma separated"
                }
            ;
    case "37":
        return 
                {
                    "field": "sale_agent",
                    "type": "Number",
                    "description": "Optional. Sale Agent name"
                }
            ;
    case "38":
        return 
                {
                    "field": "recurring",
                    "type": "String",
                    "description": "Optional. recurring 1 to 12 or custom"
                }
            ;
    case "39":
        return 
                {
                    "field": "discount_type",
                    "type": "String",
                    "description": "Optional. before_tax / after_tax discount type"
                }
            ;
    case "40":
        return 
                {
                    "field": "repeat_every_custom",
                    "type": "Number",
                    "description": "Optional. if recurring is custom set number gap"
                }
            ;
    case "41":
        return 
                {
                    "field": "repeat_type_custom",
                    "type": "String",
                    "description": "Optional. if recurring is custom set gap option day/week/month/year"
                }
            ;
    case "42":
        return 
                {
                    "field": "cycles",
                    "type": "Number",
                    "description": "Optional. number of cycles 0 for infinite"
                }
            ;
    case "43":
        return 
                {
                    "field": "adminnote",
                    "type": "String",
                    "description": "Optional. notes by admin"
                }
            ;
    case "44":
        return 
                {
                    "field": "removed_items",
                    "type": "Array",
                    "description": "Optional. Items to be removed"
                }
            ;
    case "45":
        return 
                {
                  "field": "clientnote",
                  "type": "String",
                  "description": "Optional. client notes"
                }
            ;
    case "46":
        return 
                {
                  "field": "terms",
                  "type": "String",
                  "description": "Optional. Terms"
                }
            ;
    case "47":
        return 
                {
                  "field": "items",
                  "type": "Array",
                  "description": "Optional. Existing items with Id"
                }
            ;
    case "48":
        return 
                {
                  "field": "cc",
                  "type": "String",
                  "description": "Optional comma-separated CC email addresses."
                }
            ;
    case "49":
        return 
                {
                  "field": "vat",
                  "type": "String",
                  "description": "Optional Vat."
                }
            ;
    case "50":
        return 
                {
                  "field": "phonenumber",
                  "type": "String",
                  "description": "Optional Customer Phone."
                }
            ;
    case "51":
        return 
                {
                  "field": "website",
                  "type": "String",
                  "description": "Optional Customer Website."
                }
            ;
    case "52":
        return 
                {
                  "field": "groups_in",
                  "type": "Number[]",
                  "description": "Optional Customer groups."
                }
            ;
    case "53":
        return 
                {
                  "field": "default_language",
                  "type": "String",
                  "description": "Optional Customer Default Language."
                }
            ;
    case "54":
        return 
                {
                  "field": "default_currency",
                  "type": "String",
                  "description": "Optional default currency."
                }
            ;
    case "55":
        return 
                {
                  "field": "address",
                  "type": "String",
                  "description": "Optional Customer address."
                }
            ;
    case "56":
        return 
                {
                  "field": "city",
                  "type": "String",
                  "description": "Optional Customer City."
                }
            ;
    case "57":
        return 
                {
                  "field": "state",
                  "type": "String",
                  "description": "Optional Customer state."
                }
            ;
    case "58":
        return 
                {
                  "field": "partnership_type",
                  "type": "String",
                  "description": "Optional Customer partnership type."
                }
            ;
    case "59":
        return 
                {
                  "field": "country",
                  "type": "String",
                  "description": "Optional country."
                }
            ;
    case "60":
        return 
                {
                    "field": "billing_street",
                    "type": "String",
                    "description": "Optional Billing Address: Street."
                }
            ;
    case "61":
        return 
                {
                    "field": "billing_city",
                    "type": "String",
                    "description": "Optional Billing Address: City."
                }
            ;
    case "62":
        return 
                {
                    "field": "billing_state",
                    "type": "Number",
                    "description": "Optional Billing Address: State."
                }
            ;
    case "63":
        return 
                {
                  "field": "billing_zip",
                  "type": "String",
                  "description": "Optional Billing Address: Zip."
                }
            ;
    case "64":
        return 
                {
                  "field": "billing_country",
                  "type": "String",
                  "description": "Optional Billing Address: Country."
                }
            ;
    case "65":
        return 
                {
                  "field": "shipping_street",
                  "type": "String",
                  "description": "Optional Shipping Address: Street."
                }
            ;
    case "66":
        return 
                {
                  "field": "shipping_city",
                  "type": "String",
                  "description": "Optional Shipping Address: City."
                }
            ;
    case "67":
        return 
                {
                  "field": "shipping_state",
                  "type": "String",
                  "description": "Optional Shipping Address: State."
                }
            ;
    case "68":
        return 
                {
                  "field": "shipping_zip",
                  "type": "String",
                  "description": "Optional Shipping Address: Zip."
                }
            ;
    case "69":
        return 
                {
                    "field": "shipping_country",
                    "type": "String",
                    "description": "Optional Shipping Address: Country."
                }
            ;
    case "70":
        return 
                {
                    "field": "title",
                    "type": "String",
                    "description": "Optional Position"
                }
            ;
    case "71":
        return 
                {
                    "field": "phonenumber",
                    "type": "String",
                    "description": "Optional Phone Number"
                }
            ;
    case "72":
        return 
                {
                  "field": "password",
                  "type": "String",
                  "description": "Optional password (only required if you pass send_set_password_email parameter)"
                }
            ;
    case "73":
        return 
                {
                  "field": "donotsendwelcomeemail",
                  "type": "String",
                  "description": "Optional Do Not Send Welcome Email (set on or don't pass it)"
                }
            ;
    case "74":
        return 
                {
                  "field": "send_set_password_email",
                  "type": "String",
                  "description": "Optional Send Set Password Email (set on or don't pass it)"
                }
            ;
    case "75":
        return 
                {
                  "field": "permissions",
                  "type": "Array",
                  "description": "Optional Permissions for this contact([\"1\", \"2\", \"3\", \"4\", \"5\", \"6\" ]) [ \"1\", // Invoices permission \"2\", // Estimates permission \"3\", // Contracts permission \"4\", // Proposals permission \"5\", // Support permission \"6\" // Projects permission ]"
                }
            ;
    case "76":
        return 
                {
                  "field": "Admin",
                  "type": "String",
                  "description": "Note] Optional. Admin Note"
                }
            ;
    case "77":
        return 
                {
                  "field": "duedate",
                  "type": "Date",
                  "description": "Optional. Expiry Date of Estimates"
                }
            ;
    case "78":
        return 
                {
                  "field": "status",
                  "type": "Number",
                  "description": "Optional. Status id (default status is Accepted)"
                }
            ;
    case "79":
        return 
                {
                  "field": "Reference",
                  "type": "String",
                  "description": "Optional. Reference name"
                }
            ;
    case "80":
        return 
                {
                  "field": "show_shipping_on_estimate",
                  "type": "boolean",
                  "description": "Optional. Shows shipping details in estimate."
                }
            ;
    case "81":
        return 
                {
                    "field": "expirydate",
                    "type": "Date",
                    "description": "Optional. Expiry Date of Estimate"
                }
            ;
    case "82":
        return 
                {
                    "field": "reference_no",
                    "type": "String",
                    "description": "Optional. Reference #"
                }
            ;
    case "83":
        return 
                {
                    "field": "items",
                    "type": "Array",
                    "description": "Mandatory. Existing items with Id"
                }
            ;
    case "84":
        return 
                {
                  "field": "newitems",
                  "type": "Array",
                  "description": "Optional. New Items to be added"
                }
            ;
    case "85":
        return 
                {
                  "field": "expense_name",
                  "type": "String",
                  "description": "Optional. Expanse Name"
                }
            ;
    case "86":
        return 
                {
                  "field": "note",
                  "type": "String",
                  "description": "Optional. Expanse Note"
                }
            ;
    case "87":
        return 
            {
              "field": "expense_name",
              "type": "String",
              "description": "Optional. Name"
            }
          ;
    case "88":
        return 
            {
              "field": "note",
              "type": "String",
              "description": "Optional. Note"
            }
          ;
    case "89":
        return 
            {
              "field": "long_description",
              "type": "String",
              "description": "Optional long description"
            }
          ;
    case "90":
        return 
                {
                    "field": "tax",
                    "type": "Number",
                    "description": "Optional primary tax ID"
                }
            ;
    case "91":
        return 
                {
                    "field": "tax2",
                    "type": "Number",
                    "description": "Optional secondary tax ID"
                }
            ;
    case "92":
        return 
                {
                    "field": "group_id",
                    "type": "Number",
                    "description": "Optional item group ID (default: 0)"
                }
            ;
    case "93":
        return 
                {
                  "field": "unit",
                  "type": "String",
                  "description": "Optional unit of measurement"
                }
            ;
    case "94":
        return 
                {
                  "field": "id",
                  "type": "Number",
                  "description": "Optional Item unique ID. If not provided, returns all items"
                }
            ;
    case "95":
        return 
                {
                  "field": "description",
                  "type": "String",
                  "description": "Optional item description/name"
                }
            ;
    case "96":
        return 
                {
                    "field": "rate",
                    "type": "Number",
                    "description": "Optional item rate/price"
                }
            ;
    case "97":
        return 
                {
                    "field": "group_id",
                    "type": "Number",
                    "description": "Optional item group ID"
                }
            ;
    case "98":
        return 
                {
                    "field": "group_id",
                    "type": "Number",
                    "description": "Filter articles by group."
                }
            ;
    case "99":
        return 
                {
                  "field": "description",
                  "type": "String",
                  "description": "Article body (HTML allowed)."
                }
            ;
    case "100":
        return 
                {
                  "field": "description",
                  "type": "String",
                  "description": "Group description."
                }
            ;
    case "101":
        return 
                {
                  "field": "description",
                  "type": "String",
                  "description": "Optional Milestone Description."
                }
            ;
    case "102":
        return 
                {
                  "field": "description_visible_to_customer",
                  "type": "String",
                  "description": "Show description to customer."
                }
            ;
    case "103":
        return 
                {
                  "field": "milestone_order",
                  "type": "String",
                  "description": "Optional Milestone Order."
                }
            ;
    case "104":
        return 
                {
                  "field": "paymentmethod",
                  "type": "String",
                  "description": "Optional Payment method details."
                }
            ;
    case "105":
        return 
                {
                  "field": "note",
                  "type": "String",
                  "description": "Optional Additional payment note."
                }
            ;
    case "106":
        return 
                {
                  "field": "transactionid",
                  "type": "String",
                  "description": "Optional Transaction ID."
                }
            ;
    case "107":
        return 
                {
                  "field": "custom_fields",
                  "type": "String",
                  "description": "Optional Custom fields data."
                }
            ;
    case "108":
        return 
                {
                    "field": "progress_from_tasks",
                    "type": "String",
                    "description": "Optional on or off progress from tasks."
                }
            ;
    case "109":
        return 
                {
                    "field": "project_cost",
                    "type": "String",
                    "description": "Optional Project Cost."
                }
            ;
    case "110":
        return 
                {
                    "field": "progress",
                    "type": "String",
                    "description": "Optional project progress."
                }
            ;
    case "111":
        return 
                {
                    "field": "project_rate_per_hour",
                    "type": "String",
                    "description": "Optional project rate per hour."
                }
            ;
    case "112":
        return 
                {
                    "field": "estimated_hours",
                    "type": "String",
                    "description": "Optional Project estimated hours."
                }
            ;
    case "113":
        return 
                {
                    "field": "project_members",
                    "type": "Number[]",
                    "description": "Optional Project members."
                }
            ;
    case "114":
        return 
                {
                  "field": "deadline",
                  "type": "Date",
                  "description": "Optional Project deadline."
                }
            ;
    case "115":
        return 
                {
                  "field": "tags",
                  "type": "String",
                  "description": "Optional Project tags."
                }
            ;
    case "116":
        return 
                {
                  "field": "description",
                  "type": "String",
                  "description": "Optional Project description."
                }
            ;
    case "117":
        return 
                {
                  "field": "hourly_rate",
                  "type": "Number",
                  "description": "Optional hourly rate."
                }
            ;
    case "118":
        return 
                {
                  "field": "phonenumber",
                  "type": "String",
                  "description": "Optional Staff phonenumber."
                }
            ;
    case "119":
        return 
                {
                  "field": "facebook",
                  "type": "String",
                  "description": "Optional Staff facebook."
                }
            ;
    case "120":
        return 
                {
                    "field": "linkedin",
                    "type": "String",
                    "description": "Optional Staff linkedin."
                }
            ;
    case "121":
        return 
                {
                    "field": "skype",
                    "type": "String",
                    "description": "Optional Staff skype."
                }
            ;
    case "122":
        return 
                {
                    "field": "default_language",
                    "type": "String",
                    "description": "Optional Staff default language."
                }
            ;
    case "123":
        return 
                {
                    "field": "email_signature",
                    "type": "String",
                    "description": "Optional Staff email signature."
                }
            ;
    case "124":
        return 
                {
                    "field": "direction",
                    "type": "String",
                    "description": "Optional Staff direction."
                }
            ;
    case "125":
        return 
                {
                    "field": "send_welcome_email",
                    "type": "String",
                    "description": "Optional Staff send welcome email."
                }
            ;
    case "126":
        return 
                {
                    "field": "departments",
                    "type": "Number[]",
                    "description": "Optional Staff departments."
                }
            ;
    case "127":
        return 
                {
                    "field": "is_public",
                    "type": "String",
                    "description": "Optional Task public."
                }
            ;
    case "128":
        return 
                {
                    "field": "billable",
                    "type": "String",
                    "description": "Optional Task billable."
                }
            ;
    case "129":
        return 
                {
                  "field": "hourly_rate",
                  "type": "String",
                  "description": "Optional Task hourly rate."
                }
            ;
    case "130":
        return 
                {
                  "field": "milestone",
                  "type": "String",
                  "description": "Optional Task milestone."
                }
            ;
    case "131":
        return 
                {
                  "field": "duedate",
                  "type": "Date",
                  "description": "Optional Task deadline."
                }
            ;
    case "132":
        return 
                {
                    "field": "priority",
                    "type": "String",
                    "description": "Optional Task priority."
                }
            ;
    case "133":
        return 
                {
                    "field": "repeat_every",
                    "type": "String",
                    "description": "Optional Task repeat every."
                }
            ;
    case "134":
        return 
                {
                    "field": "repeat_every_custom",
                    "type": "Number",
                    "description": "Optional Task repeat every custom."
                }
            ;
    case "135":
        return 
                {
                  "field": "repeat_type_custom",
                  "type": "String",
                  "description": "Optional Task repeat type custom."
                }
            ;
    case "136":
        return 
                {
                  "field": "cycles",
                  "type": "Number",
                  "description": "Optional cycles."
                }
            ;
    case "137":
        return 
                {
                  "field": "tags",
                  "type": "String",
                  "description": "Optional Task tags."
                }
            ;
    case "138":
        return 
                {
                  "field": "description",
                  "type": "String",
                  "description": "Optional Task description."
                }
            ;
    case "139":
        return 
                {
                  "field": "assignees",
                  "type": "Mixed",
                  "description": "Optional Task assignees. Can be: array of staff IDs, comma-separated string \"1,2,3\", or JSON array \"[1,2,3]\"."
                }
            ;
    case "140":
        return 
                {
                  "field": "name",
                  "type": "String",
                  "description": "Optional Task Name."
                }
            ;
    case "141":
        return 
                {
                  "field": "startdate",
                  "type": "Date",
                  "description": "Optional Task Start Date."
                }
            ;
    case "142":
        return 
                {
                  "field": "status",
                  "type": "Number",
                  "description": "Optional Task status (0-5)."
                }
            ;
    case "143":
        return 
                {
                  "field": "assigned",
                  "type": "Number",
                  "description": "Optional Staff ID to assign this item to."
                }
            ;
    case "144":
        return 
                {
                  "field": "list_order",
                  "type": "Number",
                  "description": "Optional Sort order (defaults to highest + 1)."
                }
            ;
    case "145":
        return 
                {
                  "field": "description",
                  "type": "String",
                  "description": "Optional Updated description."
                }
            ;
    case "146":
        return 
                {
                  "field": "finished",
                  "type": "Number",
                  "description": "Optional 0=unchecked, 1=checked."
                }
            ;
    case "147":
        return 
                {
                  "field": "assigned",
                  "type": "Number",
                  "description": "Optional Staff ID to assign."
                }
            ;
    case "148":
        return 
                {
                  "field": "list_order",
                  "type": "Number",
                  "description": "Optional Sort order."
                }
            ;
    case "149":
        return 
                {
                  "field": "project_id",
                  "type": "String",
                  "description": "Optional Ticket Project."
                }
            ;
    case "150":
        return 
                {
                  "field": "message",
                  "type": "String",
                  "description": "Optional Ticket message."
                }
            ;
    case "151":
        return 
                {
                  "field": "service",
                  "type": "String",
                  "description": "Optional Ticket Service."
                }
            ;
    case "152":
        return 
                {
                  "field": "assigned",
                  "type": "String",
                  "description": "Optional Assign ticket."
                }
            ;
    case "153":
        return 
                {
                  "field": "cc",
                  "type": "String",
                  "description": "Optional Ticket CC."
                }
            ;
    case "154":
        return 
                {
                  "field": "priority",
                  "type": "String",
                  "description": "Optional Priority."
                }
            ;
    case "155":
        return 
                {
                  "field": "tags",
                  "type": "String",
                  "description": "Optional ticket tags."
                }
            ;
    case "156":
        return 
                {
                  "field": "id",
                  "type": "Number",
                  "description": "Optional Ticket unique ID. If not provided, returns all tickets with pagination"
                }
            ;
    case "157":
        return 
                {
                  "field": "admin",
                  "type": "Number",
                  "description": "Optional Staff ID (if provided, reply is from staff member)."
                }
            ;
    case "158":
        return 
                {
                  "field": "status",
                  "type": "Number",
                  "description": "Optional Ticket status after reply (default: 1 for customer, 3 for staff)."
                }
            ;
    case "159":
        return 
                {
                    "field": "cc",
                    "type": "String",
                    "description": "Optional CC email addresses."
                }
            ;
    case "160":
        return 
                {
                    "field": "file",
                    "type": "File",
                    "description": "Optional File attachments."
                }
            ;
    case "161":
        return 
                {
                    "field": "headers",
                    "type": "String",
                    "description": "JSON object of custom headers."
                }
            ;
    case "162":
        return 
                {
                  "field": "id",
                  "type": "Number",
                  "description": "Optional unique ID."
                }
            ;
    default:
        return 
                {
                  "field": "Authorization",
                  "type": "String",
                  "description": "Bearer token (alternative to authtoken header)"
                }
            ;
    }
}


export default OptionalParameterRow
