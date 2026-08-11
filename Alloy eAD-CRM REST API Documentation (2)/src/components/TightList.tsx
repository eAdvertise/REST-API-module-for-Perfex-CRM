import React from 'react'
import type { JSX } from 'react/jsx-runtime'



        type TightListData = {
            items: JSX.Element[];
        };
    
// Component

        function TightList({ dataId }: { dataId: string }) {
            const { items }: TightListData = getTightListData(dataId);

            return (
                <ul className={"tight"} data-astro-cid-j7pv25f6={""}>
                    {items}
                </ul>
            );
        }
    


        function getTightListData(id: string): TightListData {
            const dataId = String(id);

            if (dataId === "0") {
                return {
                    items: [
                        <li key="header" data-astro-cid-j7pv25f6={""}>
                            {`✅ `}
                            <strong data-astro-cid-j7pv25f6={""}>
                                Header
                            </strong>
                            {` (recommended)`}
                            <br data-astro-cid-j7pv25f6={""}>

                            </br>
                            <code data-astro-cid-j7pv25f6={""}>
                                authtoken: YOUR_API_TOKEN
                            </code>
                        </li>,
                        <li key="query-parameter" data-astro-cid-j7pv25f6={""}>
                            {`🔁 `}
                            <strong data-astro-cid-j7pv25f6={""}>
                                Query parameter
                            </strong>
                            <br data-astro-cid-j7pv25f6={""}>

                            </br>
                            <code data-astro-cid-j7pv25f6={""}>
                                ?authtoken=YOUR_API_TOKEN
                            </code>
                        </li>
                    ]
                };
            }

            if (dataId === "1") {
                return {
                    items: [
                        <li key="pagination" data-astro-cid-j7pv25f6={""}>
                            <code data-astro-cid-j7pv25f6={""}>
                                ?page=1&amp;per_page=25
                            </code>
                            {` - paginate. `}
                            <strong data-astro-cid-j7pv25f6={""}>
                                <code data-astro-cid-j7pv25f6={""}>
                                    per_page
                                </code>
                                {` sizes the page`}
                            </strong>
                            {` (default 25, max 100).`}
                        </li>,
                        <li key="sorting" data-astro-cid-j7pv25f6={""}>
                            <code data-astro-cid-j7pv25f6={""}>
                                ?sort=-datecreated,company
                            </code>
                            {` - sort, `}
                            <code data-astro-cid-j7pv25f6={""}>
                                -
                            </code>
                            {` for descending.`}
                        </li>,
                        <li key="fields" data-astro-cid-j7pv25f6={""}>
                            <code data-astro-cid-j7pv25f6={""}>
                                ?fields=id,company
                            </code>
                            {` - pick columns.`}
                        </li>,
                        <li key="created-after" data-astro-cid-j7pv25f6={""}>
                            <code data-astro-cid-j7pv25f6={""}>
                                ?created_after=2026-01-01
                            </code>
                            {` - date range.`}
                        </li>
                    ]
                };
            }

            if (dataId === "2") {
                return {
                    items: [
                        <li key="envelope" data-astro-cid-j7pv25f6={""}>
                            {`The `}
                            <code data-astro-cid-j7pv25f6={""}>
                                {`{ data, meta }`}
                            </code>
                            {` envelope appears `}
                            <em data-astro-cid-j7pv25f6={""}>
                                only
                            </em>
                            {` when you send `}
                            <code data-astro-cid-j7pv25f6={""}>
                                page
                            </code>
                            {` or `}
                            <code data-astro-cid-j7pv25f6={""}>
                                per_page
                            </code>
                            .
                        </li>,
                        <li key="customers-endpoint" data-astro-cid-j7pv25f6={""}>
                            {`No endpoint was renamed. Customers have always been at `}
                            <code data-astro-cid-j7pv25f6={""}>
                                /api/customers
                            </code>
                            .
                        </li>,
                        <li key="put-fields" data-astro-cid-j7pv25f6={""}>
                            {`Unknown fields on `}
                            <code data-astro-cid-j7pv25f6={""}>
                                PUT
                            </code>
                            {` are ignored rather than rejected.`}
                        </li>
                    ]
                };
            }

            return {
                items: []
            };
        }
    

export default TightList
