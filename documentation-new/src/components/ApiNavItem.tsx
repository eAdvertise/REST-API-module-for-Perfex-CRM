import React from 'react'
import type { JSX } from 'react/jsx-runtime'



        type ApiNavItemData = {
            method: "POST" | "GET" | "DELETE" | "PUT";
            methodClass: "typ-post" | "typ-get" | "typ-delete" | "typ-put";
            title: string;
        };
    
// Component

        function ApiNavItem({
            dataId
        }: {
            dataId: string;
        }) {
            const {
                method,
                methodClass,
                title
            }: ApiNavItemData = getApiNavItemData(dataId);
            const [anchorId, setAnchorId] = React.useState('content');

            React.useEffect(() => {
                setAnchorId(findEndpoint(title)?.id ?? 'content');
            }, [title]);

            const handleClick = (event: React.MouseEvent<HTMLAnchorElement>) => {
                const endpoint = findEndpoint(title);

                if (!endpoint) {
                    return;
                }

                event.preventDefault();
                window.history.pushState(null, '', `#${endpoint.id}`);
                endpoint.scrollIntoView({ block: 'start' });
            };

            return (
                <a href={`#${anchorId}`} onClick={handleClick}>
                    <span className={`typ-name ${methodClass}`}>
                        {method}
                    </span>
                    <span className={"nav-title"}>
                        {title}
                    </span>
                </a>
            );
        }

function findEndpoint(title: string) {
    return Array.from(document.querySelectorAll<HTMLElement>('article')).find(
        (article) => article.querySelector('h1')?.textContent?.trim() === title
    );
}
    

function getApiNavItemData(id): ApiNavItemData  {
    switch (String(id)) {
    case "0":
        return 
                {
                    "method": "POST",
                    "methodClass": "typ-post",
                    "title": "MCP Server (AI Agents)"
                }
            ;
    case "1":
        return 
                {
                    "method": "POST",
                    "methodClass": "typ-post",
                    "title": "Batch Operations"
                }
            ;
    case "2":
        return 
                {
                    "method": "POST",
                    "methodClass": "typ-post",
                    "title": "Add New Lead"
                }
            ;
    case "3":
        return 
                {
                    "method": "POST",
                    "methodClass": "typ-post",
                    "title": "Add New invoice"
                }
            ;
    case "4":
        return 
                {
                    "method": "POST",
                    "methodClass": "typ-post",
                    "title": "Send invoice by email"
                }
            ;
    case "5":
        return 
                {
                    "method": "POST",
                    "methodClass": "typ-post",
                    "title": "Add New Customer"
                }
            ;
    case "6":
        return 
                {
                  "method": "POST",
                  "methodClass": "typ-post",
                  "title": "Create a new Calendar Event"
                }
            ;
    case "7":
        return 
                {
                  "method": "POST",
                  "methodClass": "typ-post",
                  "title": "Add New Contact"
                }
            ;
    case "8":
        return 
                {
                  "method": "POST",
                  "methodClass": "typ-post",
                  "title": "Add New Contract"
                }
            ;
    case "9":
        return 
                {
                    "method": "POST",
                    "methodClass": "typ-post",
                    "title": "Add New Credit Notes"
                }
            ;
    case "10":
        return 
                {
                    "method": "POST",
                    "methodClass": "typ-post",
                    "title": "Add New Estimates"
                }
            ;
    case "11":
        return 
                {
                    "method": "POST",
                    "methodClass": "typ-post",
                    "title": "Add Expense"
                }
            ;
    case "12":
        return 
                {
                    "method": "POST",
                    "methodClass": "typ-post",
                    "title": "Create New Item"
                }
            ;
    case "13":
        return 
                {
                    "method": "POST",
                    "methodClass": "typ-post",
                    "title": "Add New Article"
                }
            ;
    case "14":
        return 
                {
                    "method": "POST",
                    "methodClass": "typ-post",
                    "title": "Add New Group"
                }
            ;
    case "15":
        return 
                {
                    "method": "POST",
                    "methodClass": "typ-post",
                    "title": "Add New Milestone"
                }
            ;
    case "16":
        return 
                {
                    "method": "POST",
                    "methodClass": "typ-post",
                    "title": "Add New Note"
                }
            ;
    case "17":
        return 
                {
                    "method": "POST",
                    "methodClass": "typ-post",
                    "title": "Add New Payment"
                }
            ;
    case "18":
        return 
                {
                  "method": "POST",
                  "methodClass": "typ-post",
                  "title": "Add New Project"
                }
            ;
    case "19":
        return 
                {
                  "method": "POST",
                  "methodClass": "typ-post",
                  "title": "Add New Proposals"
                }
            ;
    case "20":
        return 
                {
                  "method": "POST",
                  "methodClass": "typ-post",
                  "title": "Add New Staff"
                }
            ;
    case "21":
        return 
                {
                    "method": "POST",
                    "methodClass": "typ-post",
                    "title": "Add New Subscription"
                }
            ;
    case "22":
        return 
                {
                    "method": "POST",
                    "methodClass": "typ-post",
                    "title": "Add New Task"
                }
            ;
    case "23":
        return 
                {
                    "method": "POST",
                    "methodClass": "typ-post",
                    "title": "Add Checklist Item to Task"
                }
            ;
    case "24":
        return 
                {
                    "method": "POST",
                    "methodClass": "typ-post",
                    "title": "Add a Task Comment"
                }
            ;
    case "25":
        return 
                {
                    "method": "POST",
                    "methodClass": "typ-post",
                    "title": "Insert Record into Custom Table"
                }
            ;
    case "26":
        return 
                {
                    "method": "POST",
                    "methodClass": "typ-post",
                    "title": "Add New Ticket"
                }
            ;
    case "27":
        return 
                {
                  "method": "POST",
                  "methodClass": "typ-post",
                  "title": "Add reply to a ticket"
                }
            ;
    case "28":
        return 
                {
                  "method": "POST",
                  "methodClass": "typ-post",
                  "title": "Add New Timesheet"
                }
            ;
    case "29":
        return 
                {
                  "method": "POST",
                  "methodClass": "typ-post",
                  "title": "Create a Webhook"
                }
            ;
    case "30":
        return 
                {
                  "method": "POST",
                  "methodClass": "typ-post",
                  "title": "Enable/Disable a Webhook"
                }
            ;
    case "31":
        return 
                {
                  "method": "POST",
                  "methodClass": "typ-post",
                  "title": "Add Custom Fields"
                }
            ;
    case "32":
        return 
                {
                  "method": "GET",
                  "methodClass": "typ-get",
                  "title": "Automation Route Handler"
                }
            ;
    case "33":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Poll for New Data"
                }
            ;
    case "34":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "List Available Resources"
                }
            ;
    case "35":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Test Trigger"
                }
            ;
    case "36":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Request all Leads"
                }
            ;
    case "37":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Request Lead information"
                }
            ;
    case "38":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Search Lead Information"
                }
            ;
    case "39":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Request invoice information"
                }
            ;
    case "40":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Search invoice information"
                }
            ;
    case "41":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Request customer information"
                }
            ;
    case "42":
        return 
                {
                  "method": "GET",
                  "methodClass": "typ-get",
                  "title": "Search Customer Information"
                }
            ;
    case "43":
        return 
                {
                  "method": "GET",
                  "methodClass": "typ-get",
                  "title": "Get All Calendar Events"
                }
            ;
    case "44":
        return 
                {
                  "method": "GET",
                  "methodClass": "typ-get",
                  "title": "Request Specific Event Information"
                }
            ;
    case "45":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Get Common Data"
                }
            ;
    case "46":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "List all Contacts of a Customer"
                }
            ;
    case "47":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Search Contact Information"
                }
            ;
    case "48":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Request Contract information"
                }
            ;
    case "49":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Search contracts"
                }
            ;
    case "50":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Request Credit notes information"
                }
            ;
    case "51":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Search credit notes item information"
                }
            ;
    case "52":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Request Estimate information"
                }
            ;
    case "53":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Search Estimate information"
                }
            ;
    case "54":
        return 
                {
                  "method": "GET",
                  "methodClass": "typ-get",
                  "title": "Request Expense category"
                }
            ;
    case "55":
        return 
                {
                  "method": "GET",
                  "methodClass": "typ-get",
                  "title": "Request Expense information"
                }
            ;
    case "56":
        return 
                {
                  "method": "GET",
                  "methodClass": "typ-get",
                  "title": "Search Expenses information"
                }
            ;
    case "57":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Request Item information"
                }
            ;
    case "58":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Search Items"
                }
            ;
    case "59":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "List Knowledge Base Articles"
                }
            ;
    case "60":
        return 
                {
                  "method": "GET",
                  "methodClass": "typ-get",
                  "title": "Request Article Information"
                }
            ;
    case "61":
        return 
                {
                  "method": "GET",
                  "methodClass": "typ-get",
                  "title": "List Knowledge Base Groups"
                }
            ;
    case "62":
        return 
                {
                  "method": "GET",
                  "methodClass": "typ-get",
                  "title": "Request Milestones information"
                }
            ;
    case "63":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Search Milestones Information"
                }
            ;
    case "64":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Request Note Information"
                }
            ;
    case "65":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "List Notes of an Entity"
                }
            ;
    case "66":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Request Payment Modes"
                }
            ;
    case "67":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "List all Payments"
                }
            ;
    case "68":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Search Payments Information"
                }
            ;
    case "69":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Request project information"
                }
            ;
    case "70":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Search Project Information"
                }
            ;
    case "71":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Request Proposal information"
                }
            ;
    case "72":
        return 
                {
                  "method": "GET",
                  "methodClass": "typ-get",
                  "title": "Search proposals information"
                }
            ;
    case "73":
        return 
                {
                  "method": "GET",
                  "methodClass": "typ-get",
                  "title": "Request Staff information"
                }
            ;
    case "74":
        return 
                {
                  "method": "GET",
                  "methodClass": "typ-get",
                  "title": "Search Staff Information"
                }
            ;
    case "75":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Request all Subscriptions"
                }
            ;
    case "76":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Request Subscription Information"
                }
            ;
    case "77":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Request Task information"
                }
            ;
    case "78":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Get Task Checklist Items"
                }
            ;
    case "79":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Get Task Comments"
                }
            ;
    case "80":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Search Tasks Information"
                }
            ;
    case "81":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Request Taxes"
                }
            ;
    case "82":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Get All Records from Custom Table"
                }
            ;
    case "83":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Get Record from Custom Table by ID"
                }
            ;
    case "84":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Get Ticket(s)"
                }
            ;
    case "85":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Search Ticket Information"
                }
            ;
    case "86":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Request all Timesheets"
                }
            ;
    case "87":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Request Timesheet Information"
                }
            ;
    case "88":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "List Webhooks"
                }
            ;
    case "89":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Request Webhook Information"
                }
            ;
    case "90":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Webhook Delivery Logs"
                }
            ;
    case "91":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Webhook Event Catalog"
                }
            ;
    case "92":
        return 
                {
                    "method": "GET",
                    "methodClass": "typ-get",
                    "title": "Request Values of Custom Fields"
                }
            ;
    case "93":
        return 
                {
                  "method": "GET",
                  "methodClass": "typ-get",
                  "title": "Request Custom Fields"
                }
            ;
    case "94":
        return 
                {
                  "method": "GET",
                  "methodClass": "typ-get",
                  "title": "Search custom field values' information"
                }
            ;
    case "95":
        return 
                {
                  "method": "DELETE",
                  "methodClass": "typ-delete",
                  "title": "Delete a Lead"
                }
            ;
    case "96":
        return 
                {
                    "method": "DELETE",
                    "methodClass": "typ-delete",
                    "title": "Delete invoice"
                }
            ;
    case "97":
        return 
                {
                    "method": "DELETE",
                    "methodClass": "typ-delete",
                    "title": "Delete a Customer"
                }
            ;
    case "98":
        return 
                {
                    "method": "DELETE",
                    "methodClass": "typ-delete",
                    "title": "Delete a Calendar Event"
                }
            ;
    case "99":
        return 
                {
                  "method": "DELETE",
                  "methodClass": "typ-delete",
                  "title": "Delete Contact"
                }
            ;
    case "100":
        return 
                {
                  "method": "DELETE",
                  "methodClass": "typ-delete",
                  "title": "Delete Contract"
                }
            ;
    case "101":
        return 
                {
                  "method": "DELETE",
                  "methodClass": "typ-delete",
                  "title": "Delete Credit Note"
                }
            ;
    case "102":
        return 
                {
                    "method": "DELETE",
                    "methodClass": "typ-delete",
                    "title": "Delete Estimate"
                }
            ;
    case "103":
        return 
                {
                    "method": "DELETE",
                    "methodClass": "typ-delete",
                    "title": "Delete Expense"
                }
            ;
    case "104":
        return 
                {
                    "method": "DELETE",
                    "methodClass": "typ-delete",
                    "title": "Delete an Item"
                }
            ;
    case "105":
        return 
                {
                  "method": "DELETE",
                  "methodClass": "typ-delete",
                  "title": "Delete an Article"
                }
            ;
    case "106":
        return 
                {
                  "method": "DELETE",
                  "methodClass": "typ-delete",
                  "title": "Delete a Group"
                }
            ;
    case "107":
        return 
                {
                  "method": "DELETE",
                  "methodClass": "typ-delete",
                  "title": "Delete a Milestone"
                }
            ;
    case "108":
        return 
                {
                  "method": "DELETE",
                  "methodClass": "typ-delete",
                  "title": "Delete a Note"
                }
            ;
    case "109":
        return 
                {
                  "method": "DELETE",
                  "methodClass": "typ-delete",
                  "title": "Delete a Project"
                }
            ;
    case "110":
        return 
                {
                  "method": "DELETE",
                  "methodClass": "typ-delete",
                  "title": "Delete Proposal"
                }
            ;
    case "111":
        return 
                {
                  "method": "DELETE",
                  "methodClass": "typ-delete",
                  "title": "Delete a Staff"
                }
            ;
    case "112":
        return 
                {
                  "method": "DELETE",
                  "methodClass": "typ-delete",
                  "title": "Delete a Subscription"
                }
            ;
    case "113":
        return 
                {
                  "method": "DELETE",
                  "methodClass": "typ-delete",
                  "title": "Delete a Task"
                }
            ;
    case "114":
        return 
                {
                  "method": "DELETE",
                  "methodClass": "typ-delete",
                  "title": "Delete Checklist Item"
                }
            ;
    case "115":
        return 
                {
                  "method": "DELETE",
                  "methodClass": "typ-delete",
                  "title": "Delete a Task Comment"
                }
            ;
    case "116":
        return 
                {
                  "method": "DELETE",
                  "methodClass": "typ-delete",
                  "title": "Delete Record from Custom Table"
                }
            ;
    case "117":
        return 
                {
                  "method": "DELETE",
                  "methodClass": "typ-delete",
                  "title": "Delete a Ticket"
                }
            ;
    case "118":
        return 
                {
                  "method": "DELETE",
                  "methodClass": "typ-delete",
                  "title": "Delete a Timesheet"
                }
            ;
    case "119":
        return 
                {
                  "method": "DELETE",
                  "methodClass": "typ-delete",
                  "title": "Delete a Webhook"
                }
            ;
    case "120":
        return 
                {
                    "method": "DELETE",
                    "methodClass": "typ-delete",
                    "title": "Delete Custom Fields"
                }
            ;
    case "121":
        return 
                {
                    "method": "PUT",
                    "methodClass": "typ-put",
                    "title": "Update a lead"
                }
            ;
    case "122":
        return 
                {
                    "method": "PUT",
                    "methodClass": "typ-put",
                    "title": "Update invoice"
                }
            ;
    case "123":
        return 
                {
                    "method": "PUT",
                    "methodClass": "typ-put",
                    "title": "Update a Customer"
                }
            ;
    case "124":
        return 
                {
                    "method": "PUT",
                    "methodClass": "typ-put",
                    "title": "Update a Calendar Event"
                }
            ;
    case "125":
        return 
                {
                    "method": "PUT",
                    "methodClass": "typ-put",
                    "title": "Update Contact Information"
                }
            ;
    case "126":
        return 
                {
                    "method": "PUT",
                    "methodClass": "typ-put",
                    "title": "Update a Credit Note"
                }
            ;
    case "127":
        return 
                {
                    "method": "PUT",
                    "methodClass": "typ-put",
                    "title": "Update a estimate"
                }
            ;
    case "128":
        return 
                {
                    "method": "PUT",
                    "methodClass": "typ-put",
                    "title": "Update a Expense"
                }
            ;
    case "129":
        return 
                {
                    "method": "PUT",
                    "methodClass": "typ-put",
                    "title": "Update an Item"
                }
            ;
    case "130":
        return 
                {
                    "method": "PUT",
                    "methodClass": "typ-put",
                    "title": "Update an Article"
                }
            ;
    case "131":
        return 
                {
                    "method": "PUT",
                    "methodClass": "typ-put",
                    "title": "Update a Group"
                }
            ;
    case "132":
        return 
                {
                    "method": "PUT",
                    "methodClass": "typ-put",
                    "title": "Update a Milestone"
                }
            ;
    case "133":
        return 
                {
                    "method": "PUT",
                    "methodClass": "typ-put",
                    "title": "Update a Note"
                }
            ;
    case "134":
        return 
                {
                    "method": "PUT",
                    "methodClass": "typ-put",
                    "title": "Update a project"
                }
            ;
    case "135":
        return 
                {
                    "method": "PUT",
                    "methodClass": "typ-put",
                    "title": "Update a proposal"
                }
            ;
    case "136":
        return 
                {
                    "method": "PUT",
                    "methodClass": "typ-put",
                    "title": "Update a Staff"
                }
            ;
    case "137":
        return 
                {
                    "method": "PUT",
                    "methodClass": "typ-put",
                    "title": "Update a Subscription"
                }
            ;
    case "138":
        return 
                {
                    "method": "PUT",
                    "methodClass": "typ-put",
                    "title": "Update a task"
                }
            ;
    case "139":
        return 
                {
                    "method": "PUT",
                    "methodClass": "typ-put",
                    "title": "Update Checklist Item"
                }
            ;
    case "140":
        return 
                {
                    "method": "PUT",
                    "methodClass": "typ-put",
                    "title": "Update a Task Comment"
                }
            ;
    case "141":
        return 
                {
                    "method": "PUT",
                    "methodClass": "typ-put",
                    "title": "Update Record in Custom Table"
                }
            ;
    case "142":
        return 
                {
                    "method": "PUT",
                    "methodClass": "typ-put",
                    "title": "Update a ticket"
                }
            ;
    case "143":
        return 
                {
                    "method": "PUT",
                    "methodClass": "typ-put",
                    "title": "Update a Timesheet"
                }
            ;
    case "144":
        return 
                {
                  "method": "PUT",
                  "methodClass": "typ-put",
                  "title": "Update a Webhook"
                }
            ;
    case "145":
        return 
                {
                  "method": "PUT",
                  "methodClass": "typ-put",
                  "title": "Update Custom Fields"
                }
            ;
    default:
        return 
                {
                    "method": "POST",
                    "methodClass": "typ-post",
                    "title": "MCP Server (AI Agents)"
                }
            ;
    }
}


export default ApiNavItem
