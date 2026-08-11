import React from 'react'
import type { JSX } from 'react/jsx-runtime'



        type ParameterRowData = {
            field: string;
            type: string;
            description: string;
        };
    
// Component

        function ParameterRow({ dataId }: { dataId: string }) {
            const { field, type, description }: ParameterRowData =
                getParameterRowData(dataId);

            return (
                <tr>
                    <td className={"code"}>
                        {field}
                    </td>
                    <td>
                        {type}
                    </td>
                    <td>
                        <span>
                            {description}
                        </span>
                    </td>
                </tr>
            );
        }
    

function getParameterRowData(id): ParameterRowData  {
    switch (String(id)) {
    case "0":
        return 
                {
                  "field": "authtoken",
                  "type": "String",
                  "description": "Authentication token, generated from admin area"
                }
            ;
    case "1":
        return 
                {
                  "field": "method",
                  "type": "String",
                  "description": "Method name (poll, test, resources)"
                }
            ;
    case "2":
        return 
                {
                  "field": "resource",
                  "type": "String",
                  "description": "Resource name. Must be one of: customers, invoices, leads, tasks, tickets"
                }
            ;
    case "3":
        return 
                {
                    "field": "id",
                    "type": "Number",
                    "description": "lead unique ID."
                }
            ;
    case "4":
        return 
                {
                    "field": "source",
                    "type": "String",
                    "description": "Mandatory Lead source."
                }
            ;
    case "5":
        return 
                {
                    "field": "status",
                    "type": "String",
                    "description": "Mandatory Lead Status."
                }
            ;
    case "6":
        return 
                {
                  "field": "name",
                  "type": "String",
                  "description": "Mandatory Lead Name."
                }
            ;
    case "7":
        return 
                {
                  "field": "assigned",
                  "type": "String",
                  "description": "Mandatory Lead assigned."
                }
            ;
    case "8":
        return 
                {
                  "field": "id",
                  "type": "Number",
                  "description": "Lead unique ID."
                }
            ;
    case "9":
        return 
                {
                  "field": "keysearch",
                  "type": "String",
                  "description": "Search Keywords."
                }
            ;
    case "10":
        return 
                {
                  "field": "clientid",
                  "type": "Number",
                  "description": "Mandatory. Customer id"
                }
            ;
    case "11":
        return 
                {
                  "field": "number",
                  "type": "Number",
                  "description": "Mandatory. Invoice Number"
                }
            ;
    case "12":
        return 
                {
                  "field": "date",
                  "type": "Date",
                  "description": "Mandatory. Invoice Date"
                }
            ;
    case "13":
        return 
                {
                  "field": "currency",
                  "type": "Number",
                  "description": "Mandatory. currency field"
                }
            ;
    case "14":
        return 
                {
                  "field": "newitems",
                  "type": "Array",
                  "description": "Mandatory. New Items to be added"
                }
            ;
    case "15":
        return 
                {
                  "field": "subtotal",
                  "type": "Decimal",
                  "description": "Mandatory. calculation based on item Qty, Rate and Tax"
                }
            ;
    case "16":
        return 
                {
                  "field": "total",
                  "type": "Decimal",
                  "description": "Mandatory. calculation based on subtotal, Discount and Adjustment"
                }
            ;
    case "17":
        return 
                {
                  "field": "billing_street",
                  "type": "String",
                  "description": "Mandatory. Street Address"
                }
            ;
    case "18":
        return 
                {
                    "field": "allowed_payment_modes",
                    "type": "Array",
                    "description": "Mandatory. Payment modes"
                }
            ;
    case "19":
        return 
                {
                    "field": "id",
                    "type": "Number",
                    "description": "Contact unique ID"
                }
            ;
    case "20":
        return 
                {
                    "field": "clientid",
                    "type": "Number",
                    "description": "Mandatory Customer id."
                }
            ;
    case "21":
        return 
                {
                  "field": "company",
                  "type": "String",
                  "description": "Mandatory Customer company."
                }
            ;
    case "22":
        return 
                {
                  "field": "id",
                  "type": "Number",
                  "description": "customer unique ID."
                }
            ;
    case "23":
        return 
                {
                  "field": "id",
                  "type": "Number",
                  "description": "Customer unique ID."
                }
            ;
    case "24":
        return 
                {
                  "field": "title",
                  "type": "String",
                  "description": "Required event title."
                }
            ;
    case "25":
        return 
                {
                  "field": "description",
                  "type": "String",
                  "description": "Optional event description."
                }
            ;
    case "26":
        return 
                {
                  "field": "start",
                  "type": "Date",
                  "description": "Required event start date."
                }
            ;
    case "27":
        return 
                {
                    "field": "reminder_before_type",
                    "type": "String",
                    "description": "Required value of reminder before type."
                }
            ;
    case "28":
        return 
                {
                    "field": "reminder_before",
                    "type": "Number",
                    "description": "Required value of reminder before."
                }
            ;
    case "29":
        return 
                {
                    "field": "color",
                    "type": "String",
                    "description": "Optional event color."
                }
            ;
    case "30":
        return 
                {
                    "field": "userid",
                    "type": "Number",
                    "description": "Required user id."
                }
            ;
    case "31":
        return 
                {
                    "field": "isstartnotified",
                    "type": "Number",
                    "description": "Required isstartnotified status."
                }
            ;
    case "32":
        return 
                {
                    "field": "public",
                    "type": "Number",
                    "description": "Required public status."
                }
            ;
    case "33":
        return 
                {
                    "field": "ID",
                    "type": "Number",
                    "description": "ID for data deletion."
                }
            ;
    case "34":
        return 
                {
                    "field": "id",
                    "type": "id",
                    "description": "Event data by id."
                }
            ;
    case "35":
        return 
                {
                    "field": "unique",
                    "type": "id",
                    "description": "ID for update data."
                }
            ;
    case "36":
        return 
                {
                    "field": "type",
                    "type": "String",
                    "description": "Data type. Must be one of: expense_category, payment_mode, tax_data"
                }
            ;
    case "37":
        return 
                {
                    "field": "customer_id",
                    "type": "Number",
                    "description": "Mandatory Customer id."
                }
            ;
    case "38":
        return 
                {
                    "field": "firstname",
                    "type": "String",
                    "description": "Mandatory First Name"
                }
            ;
    case "39":
        return 
                {
                  "field": "lastname",
                  "type": "String",
                  "description": "Mandatory Last Name"
                }
            ;
    case "40":
        return 
                {
                  "field": "email",
                  "type": "String",
                  "description": "Mandatory E-mail"
                }
            ;
    case "41":
        return 
                {
                  "field": "customer_id",
                  "type": "Number",
                  "description": "Mandatory Customer unique ID"
                }
            ;
    case "42":
        return 
                {
                    "field": "contact_id",
                    "type": "Number",
                    "description": "Optional Contact unique ID Note : if you don't pass Contact id then it will list all contacts of the customer"
                }
            ;
    case "43":
        return 
                {
                    "field": "id",
                    "type": "Number",
                    "description": "Mandatory Customer Contact id."
                }
            ;
    case "44":
        return 
                {
                    "field": "keysearch",
                    "type": "String",
                    "description": "Search Keywords"
                }
            ;
    case "45":
        return 
                {
                    "field": "customer_id",
                    "type": "Number",
                    "description": "unique Customer id"
                }
            ;
    case "46":
        return 
                {
                    "field": "subject",
                    "type": "String",
                    "description": "Mandatory. Contract subject"
                }
            ;
    case "47":
        return 
                {
                    "field": "datestart",
                    "type": "Date",
                    "description": "Mandatory. Contract start date"
                }
            ;
    case "48":
        return 
                {
                    "field": "client",
                    "type": "Number",
                    "description": "Mandatory. Customer ID"
                }
            ;
    case "49":
        return 
                {
                    "field": "dateend",
                    "type": "Date",
                    "description": "Optional. Contract end date"
                }
            ;
    case "50":
        return 
                {
                    "field": "contract_type",
                    "type": "Number",
                    "description": "Optional. Contract type"
                }
            ;
    case "51":
        return 
                {
                    "field": "contract_value",
                    "type": "Number",
                    "description": "Optional. Contract value"
                }
            ;
    case "52":
        return 
                {
                    "field": "description",
                    "type": "String",
                    "description": "Optional. Contract description"
                }
            ;
    case "53":
        return 
                {
                    "field": "content",
                    "type": "String",
                    "description": "Optional. Contract content"
                }
            ;
    case "54":
        return 
                {
                    "field": "keysearch",
                    "type": "String",
                    "description": "Search keywords, matched against subject and description."
                }
            ;
    case "55":
        return 
                {
                    "field": "date",
                    "type": "Date",
                    "description": "Mandatory. Credit Note Date"
                }
            ;
    case "56":
        return 
                {
                    "field": "number",
                    "type": "Number",
                    "description": "Mandatory. Credit Note Number"
                }
            ;
    case "57":
        return 
                {
                  "field": "billing_street",
                  "type": "String",
                  "description": "Optional. Street Address"
                }
            ;
    case "58":
        return 
                {
                  "field": "total",
                  "type": "Decimal",
                  "description": "Mandatory. calculation based on subtotal, Discount and"
                }
            ;
    case "59":
        return 
                {
                  "field": "items",
                  "type": "Array",
                  "description": "Mandatory. Existing items with Id"
                }
            ;
    case "60":
        return 
                {
                  "field": "removed_items",
                  "type": "Array",
                  "description": "Optional. Items to be removed"
                }
            ;
    case "61":
        return 
                {
                  "field": "number",
                  "type": "Number",
                  "description": "Mandatory. Estimates Number"
                }
            ;
    case "62":
        return 
                {
                  "field": "date",
                  "type": "Date",
                  "description": "Mandatory. Estimates Date"
                }
            ;
    case "63":
        return 
                {
                  "field": "clientid",
                  "type": "String",
                  "description": "Mandatory. Customer."
                }
            ;
    case "64":
        return 
                {
                  "field": "number",
                  "type": "Number",
                  "description": "Mandatory. Estimate Number"
                }
            ;
    case "65":
        return 
                {
                  "field": "date",
                  "type": "Date",
                  "description": "Mandatory. Estimate Date"
                }
            ;
    case "66":
        return 
                {
                  "field": "status",
                  "type": "Number",
                  "description": "Mandatory. Estimate Status(eg. Draft, Sent)"
                }
            ;
    case "67":
        return 
                {
                  "field": "category",
                  "type": "Number",
                  "description": "Mandatory. Expense Category"
                }
            ;
    case "68":
        return 
                {
                  "field": "amount",
                  "type": "Decimal",
                  "description": "Mandatory. Expense Amount"
                }
            ;
    case "69":
        return 
                {
                    "field": "date",
                    "type": "Date",
                    "description": "Mandatory. Expense Date"
                }
            ;
    case "70":
        return 
                {
                    "field": "clientid",
                    "type": "Number",
                    "description": "Optional. Customer id"
                }
            ;
    case "71":
        return 
                {
                    "field": "currency",
                    "type": "Number",
                    "description": "Mandatory. Currency Field"
                }
            ;
    case "72":
        return 
                {
                    "field": "tax",
                    "type": "Number",
                    "description": "Optional. Tax 1"
                }
            ;
    case "73":
        return 
                {
                    "field": "tax2",
                    "type": "Number",
                    "description": "Optional. Tax 2"
                }
            ;
    case "74":
        return 
                {
                    "field": "paymentmode",
                    "type": "Number",
                    "description": "Optional. Payment mode"
                }
            ;
    case "75":
        return 
            {
              "field": "id",
              "type": "Number",
              "description": "Expense unique ID."
            }
          ;
    case "76":
        return 
            {
              "field": "id",
              "type": "Number",
              "description": "Item unique ID"
            }
          ;
    case "77":
        return 
            {
              "field": "description",
              "type": "String",
              "description": "Mandatory item description/name"
            }
          ;
    case "78":
        return 
                {
                  "field": "rate",
                  "type": "Number",
                  "description": "Mandatory item rate/price"
                }
            ;
    case "79":
        return 
                {
                  "field": "id",
                  "type": "Number",
                  "description": "Item unique ID (in URL)"
                }
            ;
    case "80":
        return 
                {
                  "field": "subject",
                  "type": "String",
                  "description": "Article subject (slug auto-generated, unique)."
                }
            ;
    case "81":
        return 
                {
                  "field": "articlegroup",
                  "type": "Number",
                  "description": "Existing knowledge base group id."
                }
            ;
    case "82":
        return 
                {
                  "field": "name",
                  "type": "String",
                  "description": "Group name (slug auto-generated, unique)."
                }
            ;
    case "83":
        return 
                {
                  "field": "id",
                  "type": "Number",
                  "description": "Milestone unique ID."
                }
            ;
    case "84":
        return 
                {
                    "field": "project_id",
                    "type": "String",
                    "description": "Mandatory project id."
                }
            ;
    case "85":
        return 
                {
                    "field": "name",
                    "type": "String",
                    "description": "Mandatory Milestone Name."
                }
            ;
    case "86":
        return 
                {
                    "field": "due_date",
                    "type": "Date",
                    "description": "Mandatory Milestone Due date."
                }
            ;
    case "87":
        return 
                {
                  "field": "id",
                  "type": "Number",
                  "description": "Milestones unique ID."
                }
            ;
    case "88":
        return 
                {
                  "field": "rel_type",
                  "type": "String",
                  "description": "Entity type the note attaches to (12 supported types)."
                }
            ;
    case "89":
        return 
                {
                  "field": "rel_id",
                  "type": "Number",
                  "description": "The related record id."
                }
            ;
    case "90":
        return 
                {
                    "field": "description",
                    "type": "String",
                    "description": "Note text."
                }
            ;
    case "91":
        return 
                {
                    "field": "description",
                    "type": "String",
                    "description": "New note text."
                }
            ;
    case "92":
        return 
                {
                    "field": "rel_type",
                    "type": "String",
                    "description": "One of: customer, lead, contract, ticket, invoice, estimate, credit_note, staff, expense, proposal, project, task."
                }
            ;
    case "93":
        return 
                {
                  "field": "invoiceid",
                  "type": "String",
                  "description": "Mandatory Invoice ID associated with the payment."
                }
            ;
    case "94":
        return 
                {
                  "field": "amount",
                  "type": "String",
                  "description": "Mandatory Payment amount."
                }
            ;
    case "95":
        return 
                {
                  "field": "paymentmode",
                  "type": "String",
                  "description": "Mandatory Payment mode (e.g., cash, credit card, etc.)."
                }
            ;
    case "96":
        return 
                {
                  "field": "payment_id",
                  "type": "Number",
                  "description": "Optional payment unique ID Note : if you don't pass Payment id then it will list all payments records"
                }
            ;
    case "97":
        return 
                {
                  "field": "id",
                  "type": "Number",
                  "description": "project unique ID."
                }
            ;
    case "98":
        return 
                {
                  "field": "name",
                  "type": "String",
                  "description": "Mandatory Project Name."
                }
            ;
    case "99":
        return 
                {
                    "field": "rel_type",
                    "type": "string",
                    "description": "Mandatory Project Related."
                }
            ;
    case "100":
        return 
                {
                    "field": "clientid",
                    "type": "Number",
                    "description": "Mandatory Related ID."
                }
            ;
    case "101":
        return 
                {
                    "field": "billing_type",
                    "type": "Number",
                    "description": "Mandatory Billing Type."
                }
            ;
    case "102":
        return 
                {
                    "field": "start_date",
                    "type": "Date",
                    "description": "Mandatory Project Start Date."
                }
            ;
    case "103":
        return 
                {
                    "field": "status",
                    "type": "Number",
                    "description": "Mandatory Project Status."
                }
            ;
    case "104":
        return 
                {
                    "field": "keysearch",
                    "type": "String",
                    "description": "Search keywords."
                }
            ;
    case "105":
        return 
                {
                    "field": "subject",
                    "type": "String",
                    "description": "Mandatory. Proposal Subject Name."
                }
            ;
    case "106":
        return 
                {
                    "field": "Mandatory.",
                    "type": "string",
                    "description": "Proposal Related."
                }
            ;
    case "107":
        return 
                {
                    "field": "rel_id",
                    "type": "Number",
                    "description": "Mandatory. Related ID."
                }
            ;
    case "108":
        return 
                {
                  "field": "proposal_to",
                  "type": "string",
                  "description": "Mandatory. Lead / Customer name."
                }
            ;
    case "109":
        return 
                {
                  "field": "date",
                  "type": "Date",
                  "description": "Mandatory. Proposal Start Date."
                }
            ;
    case "110":
        return 
                {
                  "field": "open_till",
                  "type": "Date",
                  "description": "Optional. Proposal Open Till Date."
                }
            ;
    case "111":
        return 
                {
                    "field": "currency",
                    "type": "string",
                    "description": "Mandatory. currency id."
                }
            ;
    case "112":
        return 
                {
                    "field": "discount_type",
                    "type": "string",
                    "description": "Optional. Proposal Open Till Date."
                }
            ;
    case "113":
        return 
                {
                    "field": "status",
                    "type": "string",
                    "description": "Optional. status id."
                }
            ;
    case "114":
        return 
                {
                    "field": "Assigned",
                    "type": "string",
                    "description": "Optional. Assignee id."
                }
            ;
    case "115":
        return 
                {
                    "field": "Email",
                    "type": "string",
                    "description": "Mandatory. Email id."
                }
            ;
    case "116":
        return 
                {
                    "field": "newitems",
                    "type": "Array",
                    "description": "Mandatory. New Items to be added."
                }
            ;
    case "117":
        return 
                {
                    "field": "items",
                    "type": "Array",
                    "description": "Optional. Existing items with Id"
                }
            ;
    case "118":
        return 
                {
                    "field": "id",
                    "type": "Number",
                    "description": "Proposal unique ID"
                }
            ;
    case "119":
        return 
                {
                    "field": "Related",
                    "type": "string",
                    "description": "Mandatory. Proposal Related."
                }
            ;
    case "120":
        return 
                {
                  "field": "id",
                  "type": "Number",
                  "description": "Proposal unique ID."
                }
            ;
    case "121":
        return 
                {
                  "field": "id",
                  "type": "Number",
                  "description": "Staff unique ID."
                }
            ;
    case "122":
        return 
                {
                  "field": "firstname",
                  "type": "String",
                  "description": "Mandatory Staff Name."
                }
            ;
    case "123":
        return 
                {
                  "field": "email",
                  "type": "String",
                  "description": "Mandatory Staff Related."
                }
            ;
    case "124":
        return 
                {
                  "field": "password",
                  "type": "String",
                  "description": "Mandatory Staff password."
                }
            ;
    case "125":
        return 
                {
                  "field": "name",
                  "type": "String",
                  "description": "New subscription name."
                }
            ;
    case "126":
        return 
                {
                  "field": "description",
                  "type": "Text",
                  "description": "Detailed description of the subscription."
                }
            ;
    case "127":
        return 
                {
                  "field": "description_in_item",
                  "type": "TinyInt",
                  "description": "Indicates if the description is included in the item (1 or 0)."
                }
            ;
    case "128":
        return 
                {
                  "field": "clientid",
                  "type": "Int",
                  "description": "Client ID."
                }
            ;
    case "129":
        return 
                {
                    "field": "date",
                    "type": "Date",
                    "description": "Subscription start date (YYYY-MM-DD)."
                }
            ;
    case "130":
        return 
                {
                    "field": "terms",
                    "type": "Text",
                    "description": "Subscription terms."
                }
            ;
    case "131":
        return 
                {
                    "field": "currency",
                    "type": "Int",
                    "description": "Currency ID."
                }
            ;
    case "132":
        return 
                {
                  "field": "tax_id",
                  "type": "Int",
                  "description": "Tax ID."
                }
            ;
    case "133":
        return 
                {
                  "field": "stripe_tax_id_2",
                  "type": "Varchar",
                  "description": "Stripe tax ID."
                }
            ;
    case "134":
        return 
                {
                  "field": "stripe_plan_id",
                  "type": "Text",
                  "description": "Stripe plan ID."
                }
            ;
    case "135":
        return 
                {
                    "field": "stripe_subscription_id",
                    "type": "Text",
                    "description": "Stripe Subscription ID."
                }
            ;
    case "136":
        return 
                {
                    "field": "tax_id_2",
                    "type": "Int",
                    "description": "Second tax ID."
                }
            ;
    case "137":
        return 
                {
                    "field": "next_billing_cycle",
                    "type": "BigInt",
                    "description": "Next billing cycle timestamp."
                }
            ;
    case "138":
        return 
                {
                  "field": "ends_at",
                  "type": "BigInt",
                  "description": "Subscription end timestamp."
                }
            ;
    case "139":
        return 
                {
                  "field": "status",
                  "type": "Varchar",
                  "description": "Subscription status (e.g., active)."
                }
            ;
    case "140":
        return 
                {
                  "field": "quantity",
                  "type": "Int",
                  "description": "Subscription quantity."
                }
            ;
    case "141":
        return 
                {
                  "field": "project_id",
                  "type": "Int",
                  "description": "Associated project ID."
                }
            ;
    case "142":
        return 
                {
                  "field": "hash",
                  "type": "Varchar",
                  "description": "Unique hash identifier."
                }
            ;
    case "143":
        return 
                {
                  "field": "created",
                  "type": "DateTime",
                  "description": "Creation timestamp (YYYY-MM-DD HH:MM:SS)."
                }
            ;
    case "144":
        return 
                {
                    "field": "created_from",
                    "type": "Int",
                    "description": "ID of the creator."
                }
            ;
    case "145":
        return 
                {
                    "field": "date_subscribed",
                    "type": "DateTime",
                    "description": "Subscription date (YYYY-MM-DD HH:MM:SS)."
                }
            ;
    case "146":
        return 
                {
                    "field": "in_test_environment",
                    "type": "Int",
                    "description": "Indicates if the subscription is in a test environment (1 or 0)."
                }
            ;
    case "147":
        return 
                {
                    "field": "last_sent_at",
                    "type": "DateTime",
                    "description": "Last sent timestamp (YYYY-MM-DD HH:MM:SS)."
                }
            ;
    case "148":
        return 
                {
                    "field": "id",
                    "type": "id",
                    "description": "ID for data Deletion."
                }
            ;
    case "149":
        return 
                {
                    "field": "id",
                    "type": "id",
                    "description": "Data id ID."
                }
            ;
    case "150":
        return 
                {
                    "field": "id",
                    "type": "id",
                    "description": "ID for update data."
                }
            ;
    case "151":
        return 
                {
                    "field": "id",
                    "type": "Number",
                    "description": "Task unique ID."
                }
            ;
    case "152":
        return 
                {
                    "field": "name",
                    "type": "String",
                    "description": "Mandatory Task Name."
                }
            ;
    case "153":
        return 
                {
                    "field": "startdate",
                    "type": "Date",
                    "description": "Mandatory Task Start Date."
                }
            ;
    case "154":
        return 
                {
                    "field": "rel_type",
                    "type": "string",
                    "description": "Mandatory Task Related."
                }
            ;
    case "155":
        return 
                {
                    "field": "rel_id",
                    "type": "Number",
                    "description": "Optional Related ID."
                }
            ;
    case "156":
        return 
                {
                  "field": "rel_type",
                  "type": "string",
                  "description": "Optional Task Related."
                }
            ;
    case "157":
        return 
                {
                  "field": "description",
                  "type": "String",
                  "description": "Mandatory Checklist item description."
                }
            ;
    case "158":
        return 
                {
                  "field": "id",
                  "type": "Number",
                  "description": "Task unique ID (URL segment)."
                }
            ;
    case "159":
        return 
                {
                  "field": "content",
                  "type": "String",
                  "description": "Mandatory comment text."
                }
            ;
    case "160":
        return 
                {
                  "field": "task_id",
                  "type": "Number",
                  "description": "Task unique ID."
                }
            ;
    case "161":
        return 
                {
                  "field": "item_id",
                  "type": "Number",
                  "description": "Checklist item ID."
                }
            ;
    case "162":
        return 
                {
                  "field": "task_id",
                  "type": "Number",
                  "description": "Task unique ID (URL segment)."
                }
            ;
    case "163":
        return 
                {
                  "field": "comment_id",
                  "type": "Number",
                  "description": "Comment unique ID (URL segment)."
                }
            ;
    case "164":
        return 
                {
                  "field": "content",
                  "type": "String",
                  "description": "Mandatory new comment text."
                }
            ;
    case "165":
        return 
                {
                    "field": "table_name",
                    "type": "String",
                    "description": "Name of the custom database table (exact table name as it exists in the database)"
                }
            ;
    case "166":
        return 
                {
                    "field": "Content-Type",
                    "type": "String",
                    "description": "application/json"
                }
            ;
    case "167":
        return 
                {
                    "field": "id",
                    "type": "Number",
                    "description": "Record ID"
                }
            ;
    case "168":
        return 
                {
                    "field": "id",
                    "type": "Number",
                    "description": "Ticket unique ID."
                }
            ;
    case "169":
        return 
                {
                    "field": "subject",
                    "type": "String",
                    "description": "Mandatory Ticket name ."
                }
            ;
    case "170":
        return 
                {
                    "field": "department",
                    "type": "String",
                    "description": "Mandatory Ticket Department."
                }
            ;
    case "171":
        return 
                {
                  "field": "contactid",
                  "type": "String",
                  "description": "Mandatory Ticket Contact."
                }
            ;
    case "172":
        return 
                {
                  "field": "userid",
                  "type": "String",
                  "description": "Mandatory Ticket user."
                }
            ;
    case "173":
        return 
                {
                  "field": "priority",
                  "type": "String",
                  "description": "Mandatory Priority."
                }
            ;
    case "174":
        return 
                {
                    "field": "message",
                    "type": "String",
                    "description": "Mandatory Reply message."
                }
            ;
    case "175":
        return 
                {
                    "field": "name",
                    "type": "String",
                    "description": "Webhook name."
                }
            ;
    case "176":
        return 
                {
                    "field": "url",
                    "type": "String",
                    "description": "Target URL (SSRF-checked: https/http public hosts only)."
                }
            ;
    case "177":
        return 
                {
                    "field": "events",
                    "type": "String",
                    "description": "Comma list of event names, or \"*\" for all. See GET api/webhooks/events."
                }
            ;
    case "178":
        return 
                {
                    "field": "FieldBelongsto",
                    "type": "string",
                    "description": "Belongs to Mandatory Field Belongs to."
                }
            ;
    case "179":
        return 
                {
                    "field": "custom_fields[customFieldType]",
                    "type": "string/array",
                    "description": "Custom Field Key should be same as field_name returned from Search custom field values' information"
                }
            ;
    case "180":
        return 
                {
                    "field": "custom_fields[customFieldType]",
                    "type": "string/array",
                    "description": "Custom Field JSON should be same as below with field_name and custom_field_id returned from Search custom field values' information"
                }
            ;
    default:
        return 
                {
                  "field": "authtoken",
                  "type": "String",
                  "description": "Authentication token, generated from admin area"
                }
            ;
    }
}


export default ParameterRow
