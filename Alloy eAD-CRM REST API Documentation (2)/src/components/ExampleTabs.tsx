import React from 'react'
import type { JSX } from 'react/jsx-runtime'



        type ExampleTabsData = {
            tabs: Array<{
                label: string;
                active: boolean;
            }>;
        };
    
// Component

        function ExampleTabs({
            dataId
        }: {
            dataId: string;
        }) {
            const { tabs }: ExampleTabsData = getExampleTabsData(dataId);

            return (
                <ul className={"nav nav-tabs nav-tabs-examples"}>
                    {tabs.map((tab, index) => (
                        <ExampleTab
                            key={index}
                            label={tab.label}
                            active={tab.active}
                        />
                    ))}
                </ul>
            );
        }
    

// Subcomponents

        function ExampleTab({
            label,
            active
        }: {
            label: string;
            active: boolean;
        }) {
            if (active) {
                return (
                    <li className={"active"}>
                        <a>
                            {label}
                        </a>
                    </li>
                );
            }

            return (
                <li>
                    <a>
                        {label}
                    </a>
                </li>
            );
        }
    

function getExampleTabsData(id): ExampleTabsData  {
    switch (String(id)) {
    case "0":
        return 
                {
                  "tabs": [
                    { "label": "Request", "active": true },
                    { "label": "Success", "active": false },
                    { "label": "Success", "active": false },
                    { "label": "Error", "active": false }
                  ]
                }
            ;
    case "1":
        return 
                {
                  "tabs": [
                    { "label": "Request", "active": true },
                    { "label": "Success", "active": false },
                    { "label": "Error", "active": false },
                    { "label": "Error", "active": false }
                  ]
                }
            ;
    case "2":
        return 
                {
                  "tabs": [
                    { "label": "Request", "active": true },
                    { "label": "Success", "active": false },
                    { "label": "Error", "active": false }
                  ]
                }
            ;
    case "3":
        return 
                {
                    "tabs": [
                        { "label": "Request", "active": true },
                        { "label": "Success", "active": false }
                    ]
                }
            ;
    case "4":
        return 
                {
                    "tabs": [
                        { "label": "Request", "active": true },
                        { "label": "Success", "active": false },
                        { "label": "Error", "active": false },
                        { "label": "Error", "active": false },
                        { "label": "Error", "active": false },
                        { "label": "Error", "active": false },
                        { "label": "Error", "active": false },
                        { "label": "Error", "active": false },
                        { "label": "Error", "active": false }
                    ]
                }
            ;
    case "5":
        return 
                {
                    "tabs": [
                        { "label": "Request", "active": true },
                        { "label": "Success", "active": false },
                        { "label": "Success", "active": false },
                        { "label": "Success", "active": false },
                        { "label": "Error", "active": false },
                        { "label": "Error", "active": false }
                    ]
                }
            ;
    case "6":
        return 
                {
                  "tabs": [
                    { "label": "Request", "active": true },
                    { "label": "Success", "active": false },
                    { "label": "Error", "active": false },
                    { "label": "Error", "active": false },
                    { "label": "Error", "active": false },
                    { "label": "Error", "active": false }
                  ]
                }
            ;
    case "7":
        return 
                {
                  "tabs": [
                    { "label": "Request", "active": true },
                    { "label": "Success", "active": false },
                    { "label": "Error", "active": false },
                    { "label": "Error", "active": false },
                    { "label": "Error", "active": false },
                    { "label": "Error", "active": false },
                    { "label": "Error", "active": false }
                  ]
                }
            ;
    default:
        return 
                {
                  "tabs": [
                    { "label": "Request", "active": true },
                    { "label": "Success", "active": false },
                    { "label": "Success", "active": false },
                    { "label": "Error", "active": false }
                  ]
                }
            ;
    }
}


export default ExampleTabs
