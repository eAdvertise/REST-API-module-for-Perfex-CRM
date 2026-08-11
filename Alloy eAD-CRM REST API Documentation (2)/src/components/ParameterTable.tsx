import React from 'react'
import type { JSX } from 'react/jsx-runtime'

import TableHeader from './TableHeader.tsx'
import ParameterRow from './ParameterRow.tsx'
import OptionalParameterRow from './OptionalParameterRow.tsx'
import DefaultParameterRow from './DefaultParameterRow.tsx'
import SignatureParameterRow from './SignatureParameterRow.tsx'
import TableHeaderRow from './TableHeaderRow.tsx'


        type ParameterTableData = {
            rows: (
                | { type: "parameter"; dataId: string }
                | { type: "optional"; dataId: string }
                | { type: "default"; dataId: string }
                | { type: "signature" }
            )[];
        };
    
// Component

        function ParameterTable({
            dataId
        }: {
            dataId: string;
        }) {
            const { rows }: ParameterTableData = getParameterTableData(dataId);

            return (
                <table className={"table table-hover"}>
                    <thead>
                        <TableHeaderRow />
                    </thead>
                    <tbody>
                        {rows.map((row, index) => {
                            if (row.type === "parameter") {
                                return <ParameterRow key={index} dataId={row.dataId} />;
                            }
                            if (row.type === "optional") {
                                return <OptionalParameterRow key={index} dataId={row.dataId} />;
                            }
                            if (row.type === "default") {
                                return <DefaultParameterRow key={index} dataId={row.dataId} />;
                            }
                            return <SignatureParameterRow key={index} />;
                        })}
                    </tbody>
                </table>
            );
        }
    

function getParameterTableData(id): ParameterTableData  {
    switch (String(id)) {
    case "0":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "0" }
                    ]
                }
            ;
    case "1":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "2" }
                    ]
                }
            ;
    case "2":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "3" }
                    ]
                }
            ;
    case "3":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "8" }
                    ]
                }
            ;
    case "4":
        return 
                {
                  "rows": [
                    { "type": "parameter", "dataId": "9" }
                  ]
                }
            ;
    case "5":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "19" }
                    ]
                }
            ;
    case "6":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "22" }
                    ]
                }
            ;
    case "7":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "23" }
                    ]
                }
            ;
    case "8":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "33" }
                    ]
                }
            ;
    case "9":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "34" }
                    ]
                }
            ;
    case "10":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "35" }
                    ]
                }
            ;
    case "11":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "36" }
                    ]
                }
            ;
    case "12":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "44" }
                    ]
                }
            ;
    case "13":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "45" }
                    ]
                }
            ;
    case "14":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "54" }
                    ]
                }
            ;
    case "15":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "75" }
                    ]
                }
            ;
    case "16":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "76" }
                    ]
                }
            ;
    case "17":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "83" }
                    ]
                }
            ;
    case "18":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "87" }
                    ]
                }
            ;
    case "19":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "91" }
                    ]
                }
            ;
    case "20":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "96" }
                    ]
                }
            ;
    case "21":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "97" }
                    ]
                }
            ;
    case "22":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "104" }
                    ]
                }
            ;
    case "23":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "118" }
                    ]
                }
            ;
    case "24":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "120" }
                    ]
                }
            ;
    case "25":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "121" }
                    ]
                }
            ;
    case "26":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "148" }
                    ]
                }
            ;
    case "27":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "149" }
                    ]
                }
            ;
    case "28":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "150" }
                    ]
                }
            ;
    case "29":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "151" }
                    ]
                }
            ;
    case "30":
        return 
                {
                  "rows": [
                    { "type": "parameter", "dataId": "158" }
                  ]
                }
            ;
    case "31":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "165" }
                    ]
                }
            ;
    case "32":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "168" }
                    ]
                }
            ;
    case "33":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "179" }
                    ]
                }
            ;
    case "34":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "180" }
                    ]
                }
            ;
    case "35":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "0" },
                        { "type": "optional", "dataId": "181" }
                    ]
                }
            ;
    case "36":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "1" },
                        { "type": "optional", "dataId": "182" }
                    ]
                }
            ;
    case "37":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "178" },
                        { "type": "optional", "dataId": "343" }
                    ]
                }
            ;
    case "38":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "2" },
                        { "type": "optional", "dataId": "183" },
                        { "type": "optional", "dataId": "184" }
                    ]
                }
            ;
    case "39":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "4" },
                        { "type": "parameter", "dataId": "5" },
                        { "type": "parameter", "dataId": "6" },
                        { "type": "parameter", "dataId": "7" },
                        { "type": "optional", "dataId": "185" },
                        { "type": "optional", "dataId": "186" },
                        { "type": "optional", "dataId": "187" },
                        { "type": "optional", "dataId": "188" },
                        { "type": "optional", "dataId": "189" },
                        { "type": "optional", "dataId": "190" },
                        { "type": "optional", "dataId": "191" },
                        { "type": "optional", "dataId": "192" },
                        { "type": "optional", "dataId": "193" },
                        { "type": "optional", "dataId": "194" },
                        { "type": "optional", "dataId": "195" },
                        { "type": "optional", "dataId": "196" },
                        { "type": "optional", "dataId": "197" },
                        { "type": "optional", "dataId": "198" },
                        { "type": "optional", "dataId": "199" },
                        { "type": "optional", "dataId": "200" },
                        { "type": "optional", "dataId": "201" },
                        { "type": "optional", "dataId": "202" }
                    ]
                }
            ;
    case "40":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "4" },
                        { "type": "parameter", "dataId": "5" },
                        { "type": "parameter", "dataId": "6" },
                        { "type": "parameter", "dataId": "7" },
                        { "type": "optional", "dataId": "185" },
                        { "type": "optional", "dataId": "186" },
                        { "type": "optional", "dataId": "187" },
                        { "type": "optional", "dataId": "188" },
                        { "type": "optional", "dataId": "189" },
                        { "type": "optional", "dataId": "190" },
                        { "type": "optional", "dataId": "191" },
                        { "type": "optional", "dataId": "192" },
                        { "type": "optional", "dataId": "193" },
                        { "type": "optional", "dataId": "194" },
                        { "type": "optional", "dataId": "203" },
                        { "type": "optional", "dataId": "196" },
                        { "type": "optional", "dataId": "197" },
                        { "type": "optional", "dataId": "198" },
                        { "type": "optional", "dataId": "199" },
                        { "type": "optional", "dataId": "204" },
                        { "type": "optional", "dataId": "202" }
                    ]
                }
            ;
    case "41":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "10" },
                        { "type": "parameter", "dataId": "11" },
                        { "type": "parameter", "dataId": "12" },
                        { "type": "parameter", "dataId": "13" },
                        { "type": "parameter", "dataId": "14" },
                        { "type": "parameter", "dataId": "15" },
                        { "type": "parameter", "dataId": "16" },
                        { "type": "parameter", "dataId": "17" },
                        { "type": "parameter", "dataId": "18" },
                        { "type": "optional", "dataId": "205" },
                        { "type": "optional", "dataId": "206" },
                        { "type": "optional", "dataId": "207" },
                        { "type": "optional", "dataId": "208" },
                        { "type": "default", "dataId": "344" },
                        { "type": "optional", "dataId": "209" },
                        { "type": "optional", "dataId": "210" },
                        { "type": "optional", "dataId": "211" },
                        { "type": "optional", "dataId": "212" },
                        { "type": "optional", "dataId": "213" },
                        { "type": "optional", "dataId": "214" },
                        { "type": "optional", "dataId": "215" },
                        { "type": "optional", "dataId": "216" },
                        { "type": "optional", "dataId": "217" },
                        { "type": "optional", "dataId": "218" },
                        { "type": "optional", "dataId": "219" },
                        { "type": "optional", "dataId": "220" },
                        { "type": "optional", "dataId": "221" },
                        { "type": "optional", "dataId": "222" },
                        { "type": "optional", "dataId": "223" },
                        { "type": "optional", "dataId": "224" },
                        { "type": "optional", "dataId": "225" },
                        { "type": "optional", "dataId": "226" },
                        { "type": "optional", "dataId": "227" }
                    ]
                }
            ;
    case "42":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "20" },
                        { "type": "parameter", "dataId": "11" },
                        { "type": "parameter", "dataId": "12" },
                        { "type": "parameter", "dataId": "13" },
                        { "type": "parameter", "dataId": "14" },
                        { "type": "parameter", "dataId": "15" },
                        { "type": "parameter", "dataId": "16" },
                        { "type": "parameter", "dataId": "17" },
                        { "type": "parameter", "dataId": "18" },
                        { "type": "optional", "dataId": "205" },
                        { "type": "optional", "dataId": "206" },
                        { "type": "optional", "dataId": "207" },
                        { "type": "optional", "dataId": "208" },
                        { "type": "default", "dataId": "344" },
                        { "type": "optional", "dataId": "209" },
                        { "type": "optional", "dataId": "210" },
                        { "type": "optional", "dataId": "211" },
                        { "type": "optional", "dataId": "212" },
                        { "type": "optional", "dataId": "213" },
                        { "type": "optional", "dataId": "214" },
                        { "type": "optional", "dataId": "215" },
                        { "type": "optional", "dataId": "216" },
                        { "type": "optional", "dataId": "217" },
                        { "type": "optional", "dataId": "218" },
                        { "type": "optional", "dataId": "219" },
                        { "type": "optional", "dataId": "220" },
                        { "type": "optional", "dataId": "221" },
                        { "type": "optional", "dataId": "222" },
                        { "type": "optional", "dataId": "223" },
                        { "type": "optional", "dataId": "224" },
                        { "type": "optional", "dataId": "228" },
                        { "type": "optional", "dataId": "225" },
                        { "type": "optional", "dataId": "226" },
                        { "type": "optional", "dataId": "227" }
                    ]
                }
            ;
    case "43":
        return 
                {
                    "rows": [
                        { "type": "optional", "dataId": "229" }
                    ]
                }
            ;
    case "44":
        return 
                {
                    "rows": [
                        { "type": "optional", "dataId": "275" }
                    ]
                }
            ;
    case "45":
        return 
                {
                    "rows": [
                        { "type": "optional", "dataId": "279" }
                    ]
                }
            ;
    case "46":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "21" },
                        { "type": "optional", "dataId": "230" },
                        { "type": "optional", "dataId": "231" },
                        { "type": "optional", "dataId": "232" },
                        { "type": "optional", "dataId": "233" },
                        { "type": "optional", "dataId": "234" },
                        { "type": "optional", "dataId": "235" },
                        { "type": "optional", "dataId": "236" },
                        { "type": "optional", "dataId": "237" },
                        { "type": "optional", "dataId": "238" },
                        { "type": "optional", "dataId": "203" },
                        { "type": "optional", "dataId": "239" },
                        { "type": "optional", "dataId": "240" },
                        { "type": "optional", "dataId": "241" },
                        { "type": "optional", "dataId": "242" },
                        { "type": "optional", "dataId": "243" },
                        { "type": "optional", "dataId": "244" },
                        { "type": "optional", "dataId": "245" },
                        { "type": "optional", "dataId": "246" },
                        { "type": "optional", "dataId": "247" },
                        { "type": "optional", "dataId": "248" },
                        { "type": "optional", "dataId": "249" },
                        { "type": "optional", "dataId": "250" }
                    ]
                }
            ;
    case "47":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "21" },
                        { "type": "optional", "dataId": "230" },
                        { "type": "optional", "dataId": "231" },
                        { "type": "optional", "dataId": "232" },
                        { "type": "optional", "dataId": "233" },
                        { "type": "optional", "dataId": "234" },
                        { "type": "optional", "dataId": "235" },
                        { "type": "optional", "dataId": "236" },
                        { "type": "optional", "dataId": "237" },
                        { "type": "optional", "dataId": "238" },
                        { "type": "optional", "dataId": "203" },
                        { "type": "optional", "dataId": "240" },
                        { "type": "optional", "dataId": "241" },
                        { "type": "optional", "dataId": "242" },
                        { "type": "optional", "dataId": "243" },
                        { "type": "optional", "dataId": "244" },
                        { "type": "optional", "dataId": "245" },
                        { "type": "optional", "dataId": "246" },
                        { "type": "optional", "dataId": "247" },
                        { "type": "optional", "dataId": "248" },
                        { "type": "optional", "dataId": "249" },
                        { "type": "optional", "dataId": "250" }
                    ]
                }
            ;
    case "48":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "24" },
                        { "type": "parameter", "dataId": "25" },
                        { "type": "parameter", "dataId": "26" },
                        { "type": "parameter", "dataId": "27" },
                        { "type": "parameter", "dataId": "28" },
                        { "type": "parameter", "dataId": "29" },
                        { "type": "parameter", "dataId": "30" },
                        { "type": "parameter", "dataId": "31" },
                        { "type": "parameter", "dataId": "32" }
                    ]
                }
            ;
    case "49":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "37" },
                        { "type": "parameter", "dataId": "38" },
                        { "type": "parameter", "dataId": "39" },
                        { "type": "parameter", "dataId": "40" },
                        { "type": "optional", "dataId": "251" },
                        { "type": "optional", "dataId": "252" },
                        { "type": "default", "dataId": "345" },
                        { "type": "optional", "dataId": "253" },
                        { "type": "default", "dataId": "346" },
                        { "type": "optional", "dataId": "254" },
                        { "type": "optional", "dataId": "255" },
                        { "type": "optional", "dataId": "256" },
                        { "type": "default", "dataId": "347" },
                        { "type": "default", "dataId": "348" },
                        { "type": "default", "dataId": "349" },
                        { "type": "default", "dataId": "350" },
                        { "type": "default", "dataId": "351" },
                        { "type": "default", "dataId": "352" },
                        { "type": "default", "dataId": "353" }
                    ]
                }
            ;
    case "50":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "43" },
                        { "type": "parameter", "dataId": "38" },
                        { "type": "parameter", "dataId": "39" },
                        { "type": "parameter", "dataId": "40" },
                        { "type": "optional", "dataId": "251" },
                        { "type": "optional", "dataId": "252" },
                        { "type": "default", "dataId": "345" },
                        { "type": "optional", "dataId": "253" },
                        { "type": "default", "dataId": "346" },
                        { "type": "optional", "dataId": "254" },
                        { "type": "optional", "dataId": "255" },
                        { "type": "optional", "dataId": "256" },
                        { "type": "default", "dataId": "347" },
                        { "type": "default", "dataId": "348" },
                        { "type": "default", "dataId": "349" },
                        { "type": "default", "dataId": "350" },
                        { "type": "default", "dataId": "351" },
                        { "type": "default", "dataId": "352" },
                        { "type": "default", "dataId": "353" }
                    ]
                }
            ;
    case "51":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "41" },
                        { "type": "parameter", "dataId": "42" }
                    ]
                }
            ;
    case "52":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "92" },
                        { "type": "parameter", "dataId": "89" }
                    ]
                }
            ;
    case "53":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "158" },
                        { "type": "parameter", "dataId": "159" }
                    ]
                }
            ;
    case "54":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "160" },
                        { "type": "parameter", "dataId": "161" }
                    ]
                }
            ;
    case "55":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "162" },
                        { "type": "parameter", "dataId": "163" }
                    ]
                }
            ;
    case "56":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "0" },
                        { "type": "parameter", "dataId": "166" }
                    ]
                }
            ;
    case "57":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "165" },
                        { "type": "parameter", "dataId": "167" }
                    ]
                }
            ;
    case "58":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "46" },
                        { "type": "parameter", "dataId": "47" },
                        { "type": "parameter", "dataId": "48" },
                        { "type": "parameter", "dataId": "49" },
                        { "type": "parameter", "dataId": "50" },
                        { "type": "parameter", "dataId": "51" },
                        { "type": "parameter", "dataId": "52" },
                        { "type": "parameter", "dataId": "53" }
                    ]
                }
            ;
    case "59":
        return 
            {
              "rows": [
                { "type": "parameter", "dataId": "10" },
                { "type": "parameter", "dataId": "55" },
                { "type": "parameter", "dataId": "56" },
                { "type": "parameter", "dataId": "13" },
                { "type": "parameter", "dataId": "14" },
                { "type": "parameter", "dataId": "57" },
                { "type": "optional", "dataId": "205" },
                { "type": "optional", "dataId": "206" },
                { "type": "optional", "dataId": "207" },
                { "type": "optional", "dataId": "208" },
                { "type": "optional", "dataId": "210" },
                { "type": "optional", "dataId": "211" },
                { "type": "optional", "dataId": "212" },
                { "type": "optional", "dataId": "213" },
                { "type": "optional", "dataId": "214" },
                { "type": "optional", "dataId": "220" },
                { "type": "optional", "dataId": "257" },
                { "type": "parameter", "dataId": "15" },
                { "type": "parameter", "dataId": "58" },
                { "type": "optional", "dataId": "226" },
                { "type": "optional", "dataId": "227" }
              ]
            }
          ;
    case "60":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "10" },
                        { "type": "parameter", "dataId": "55" },
                        { "type": "parameter", "dataId": "56" },
                        { "type": "parameter", "dataId": "13" },
                        { "type": "parameter", "dataId": "14" },
                        { "type": "parameter", "dataId": "59" },
                        { "type": "parameter", "dataId": "60" },
                        { "type": "parameter", "dataId": "57" },
                        { "type": "optional", "dataId": "205" },
                        { "type": "optional", "dataId": "206" },
                        { "type": "optional", "dataId": "207" },
                        { "type": "optional", "dataId": "208" },
                        { "type": "optional", "dataId": "210" },
                        { "type": "optional", "dataId": "211" },
                        { "type": "optional", "dataId": "212" },
                        { "type": "optional", "dataId": "213" },
                        { "type": "optional", "dataId": "214" },
                        { "type": "optional", "dataId": "220" },
                        { "type": "optional", "dataId": "257" },
                        { "type": "parameter", "dataId": "15" },
                        { "type": "parameter", "dataId": "58" },
                        { "type": "optional", "dataId": "226" },
                        { "type": "optional", "dataId": "227" }
                    ]
                }
            ;
    case "61":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "10" },
                        { "type": "parameter", "dataId": "61" },
                        { "type": "parameter", "dataId": "62" },
                        { "type": "optional", "dataId": "258" },
                        { "type": "parameter", "dataId": "13" },
                        { "type": "parameter", "dataId": "14" },
                        { "type": "parameter", "dataId": "15" },
                        { "type": "parameter", "dataId": "16" },
                        { "type": "parameter", "dataId": "57" },
                        { "type": "optional", "dataId": "205" },
                        { "type": "optional", "dataId": "206" },
                        { "type": "optional", "dataId": "207" },
                        { "type": "optional", "dataId": "208" },
                        { "type": "optional", "dataId": "210" },
                        { "type": "optional", "dataId": "211" },
                        { "type": "optional", "dataId": "212" },
                        { "type": "optional", "dataId": "213" },
                        { "type": "optional", "dataId": "214" },
                        { "type": "optional", "dataId": "217" },
                        { "type": "optional", "dataId": "259" },
                        { "type": "optional", "dataId": "260" },
                        { "type": "optional", "dataId": "218" },
                        { "type": "optional", "dataId": "224" },
                        { "type": "optional", "dataId": "226" },
                        { "type": "optional", "dataId": "227" }
                    ]
                }
            ;
    case "62":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "63" },
                        { "type": "parameter", "dataId": "17" },
                        { "type": "optional", "dataId": "205" },
                        { "type": "optional", "dataId": "206" },
                        { "type": "optional", "dataId": "207" },
                        { "type": "optional", "dataId": "208" },
                        { "type": "default", "dataId": "344" },
                        { "type": "optional", "dataId": "261" },
                        { "type": "optional", "dataId": "210" },
                        { "type": "optional", "dataId": "211" },
                        { "type": "optional", "dataId": "212" },
                        { "type": "optional", "dataId": "213" },
                        { "type": "optional", "dataId": "214" },
                        { "type": "parameter", "dataId": "64" },
                        { "type": "parameter", "dataId": "65" },
                        { "type": "optional", "dataId": "262" },
                        { "type": "optional", "dataId": "217" },
                        { "type": "parameter", "dataId": "13" },
                        { "type": "parameter", "dataId": "66" },
                        { "type": "optional", "dataId": "263" },
                        { "type": "optional", "dataId": "218" },
                        { "type": "optional", "dataId": "220" },
                        { "type": "optional", "dataId": "224" },
                        { "type": "optional", "dataId": "264" },
                        { "type": "optional", "dataId": "225" },
                        { "type": "optional", "dataId": "265" },
                        { "type": "parameter", "dataId": "15" },
                        { "type": "parameter", "dataId": "16" },
                        { "type": "optional", "dataId": "226" },
                        { "type": "optional", "dataId": "227" }
                    ]
                }
            ;
    case "63":
        return 
                {
                    "rows": [
                        { "type": "optional", "dataId": "266" },
                        { "type": "optional", "dataId": "267" },
                        { "type": "parameter", "dataId": "67" },
                        { "type": "parameter", "dataId": "68" },
                        { "type": "parameter", "dataId": "69" },
                        { "type": "parameter", "dataId": "70" },
                        { "type": "parameter", "dataId": "71" },
                        { "type": "parameter", "dataId": "72" },
                        { "type": "parameter", "dataId": "73" },
                        { "type": "parameter", "dataId": "74" },
                        { "type": "optional", "dataId": "263" },
                        { "type": "optional", "dataId": "219" },
                        { "type": "optional", "dataId": "221" },
                        { "type": "optional", "dataId": "222" }
                    ]
                }
            ;
    case "64":
        return 
                {
                    "rows": [
                        { "type": "optional", "dataId": "268" },
                        { "type": "optional", "dataId": "269" },
                        { "type": "parameter", "dataId": "67" },
                        { "type": "parameter", "dataId": "68" },
                        { "type": "parameter", "dataId": "69" },
                        { "type": "parameter", "dataId": "70" },
                        { "type": "parameter", "dataId": "13" },
                        { "type": "parameter", "dataId": "72" },
                        { "type": "parameter", "dataId": "73" },
                        { "type": "parameter", "dataId": "74" },
                        { "type": "optional", "dataId": "263" },
                        { "type": "optional", "dataId": "219" },
                        { "type": "optional", "dataId": "221" },
                        { "type": "optional", "dataId": "222" }
                    ]
                }
            ;
    case "65":
        return 
                {
                  "rows": [
                    { "type": "parameter", "dataId": "77" },
                    { "type": "parameter", "dataId": "78" },
                    { "type": "optional", "dataId": "270" },
                    { "type": "optional", "dataId": "271" },
                    { "type": "optional", "dataId": "272" },
                    { "type": "optional", "dataId": "273" },
                    { "type": "optional", "dataId": "274" }
                  ]
                }
            ;
    case "66":
        return 
                {
                    "rows": [
                        {"type": "parameter", "dataId": "79"},
                        {"type": "optional", "dataId": "276"},
                        {"type": "optional", "dataId": "277"},
                        {"type": "optional", "dataId": "270"},
                        {"type": "optional", "dataId": "271"},
                        {"type": "optional", "dataId": "272"},
                        {"type": "optional", "dataId": "278"},
                        {"type": "optional", "dataId": "274"}
                    ]
                }
            ;
    case "67":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "80" },
                        { "type": "parameter", "dataId": "81" },
                        { "type": "optional", "dataId": "280" },
                        { "type": "default", "dataId": "354" },
                        { "type": "default", "dataId": "355" }
                    ]
                }
            ;
    case "68":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "82" },
                        { "type": "optional", "dataId": "281" },
                        { "type": "default", "dataId": "356" }
                    ]
                }
            ;
    case "69":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "84" },
                        { "type": "parameter", "dataId": "85" },
                        { "type": "parameter", "dataId": "86" },
                        { "type": "optional", "dataId": "282" },
                        { "type": "optional", "dataId": "283" },
                        { "type": "optional", "dataId": "284" }
                    ]
                }
            ;
    case "70":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "88" },
                        { "type": "parameter", "dataId": "89" },
                        { "type": "parameter", "dataId": "90" }
                    ]
                }
            ;
    case "71":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "162" },
                        { "type": "parameter", "dataId": "163" },
                        { "type": "parameter", "dataId": "164" }
                    ]
                }
            ;
    case "72":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "93" },
                        { "type": "parameter", "dataId": "94" },
                        { "type": "parameter", "dataId": "95" },
                        { "type": "optional", "dataId": "285" },
                        { "type": "optional", "dataId": "286" },
                        { "type": "optional", "dataId": "287" },
                        { "type": "optional", "dataId": "288" }
                    ]
                }
            ;
    case "73":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "98" },
                        { "type": "parameter", "dataId": "99" },
                        { "type": "parameter", "dataId": "100" },
                        { "type": "parameter", "dataId": "101" },
                        { "type": "parameter", "dataId": "102" },
                        { "type": "parameter", "dataId": "103" },
                        { "type": "optional", "dataId": "289" },
                        { "type": "optional", "dataId": "290" },
                        { "type": "optional", "dataId": "291" },
                        { "type": "optional", "dataId": "292" },
                        { "type": "optional", "dataId": "293" },
                        { "type": "optional", "dataId": "294" },
                        { "type": "optional", "dataId": "295" },
                        { "type": "optional", "dataId": "296" },
                        { "type": "optional", "dataId": "297" }
                    ]
                }
            ;
    case "74":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "105" },
                        { "type": "parameter", "dataId": "106" },
                        { "type": "parameter", "dataId": "107" },
                        { "type": "parameter", "dataId": "108" },
                        { "type": "parameter", "dataId": "109" },
                        { "type": "parameter", "dataId": "110" },
                        { "type": "parameter", "dataId": "111" },
                        { "type": "parameter", "dataId": "112" },
                        { "type": "parameter", "dataId": "113" },
                        { "type": "parameter", "dataId": "114" },
                        { "type": "parameter", "dataId": "115" },
                        { "type": "parameter", "dataId": "116" },
                        { "type": "parameter", "dataId": "117" },
                        { "type": "parameter", "dataId": "60" }
                    ]
                }
            ;
    case "75":
        return 
                {
                  "rows": [
                    { "type": "parameter", "dataId": "105" },
                    { "type": "parameter", "dataId": "119" },
                    { "type": "parameter", "dataId": "107" },
                    { "type": "parameter", "dataId": "108" },
                    { "type": "parameter", "dataId": "109" },
                    { "type": "parameter", "dataId": "110" },
                    { "type": "parameter", "dataId": "111" },
                    { "type": "parameter", "dataId": "112" },
                    { "type": "parameter", "dataId": "113" },
                    { "type": "parameter", "dataId": "114" },
                    { "type": "parameter", "dataId": "115" },
                    { "type": "parameter", "dataId": "116" }
                  ]
                }
            ;
    case "76":
        return 
                {
                  "rows": [
                    { "type": "parameter", "dataId": "122" },
                    { "type": "parameter", "dataId": "123" },
                    { "type": "parameter", "dataId": "124" },
                    { "type": "optional", "dataId": "298" },
                    { "type": "optional", "dataId": "299" },
                    { "type": "optional", "dataId": "300" },
                    { "type": "optional", "dataId": "301" },
                    { "type": "optional", "dataId": "302" },
                    { "type": "optional", "dataId": "303" },
                    { "type": "optional", "dataId": "304" },
                    { "type": "optional", "dataId": "305" },
                    { "type": "optional", "dataId": "306" },
                    { "type": "optional", "dataId": "307" }
                  ]
                }
            ;
    case "77":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "122" },
                        { "type": "parameter", "dataId": "123" },
                        { "type": "parameter", "dataId": "124" },
                        { "type": "optional", "dataId": "298" },
                        { "type": "optional", "dataId": "299" },
                        { "type": "optional", "dataId": "300" },
                        { "type": "optional", "dataId": "301" },
                        { "type": "optional", "dataId": "302" },
                        { "type": "optional", "dataId": "303" },
                        { "type": "optional", "dataId": "304" },
                        { "type": "optional", "dataId": "305" },
                        { "type": "optional", "dataId": "307" }
                    ]
                }
            ;
    case "78":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "125" },
                        { "type": "parameter", "dataId": "126" },
                        { "type": "parameter", "dataId": "127" },
                        { "type": "parameter", "dataId": "128" },
                        { "type": "parameter", "dataId": "129" },
                        { "type": "parameter", "dataId": "130" },
                        { "type": "parameter", "dataId": "131" },
                        { "type": "parameter", "dataId": "132" },
                        { "type": "parameter", "dataId": "133" },
                        { "type": "parameter", "dataId": "134" },
                        { "type": "parameter", "dataId": "135" },
                        { "type": "parameter", "dataId": "136" },
                        { "type": "parameter", "dataId": "137" },
                        { "type": "parameter", "dataId": "138" },
                        { "type": "parameter", "dataId": "139" },
                        { "type": "parameter", "dataId": "140" },
                        { "type": "parameter", "dataId": "141" },
                        { "type": "parameter", "dataId": "142" },
                        { "type": "parameter", "dataId": "143" },
                        { "type": "parameter", "dataId": "144" },
                        { "type": "parameter", "dataId": "145" },
                        { "type": "parameter", "dataId": "146" },
                        { "type": "parameter", "dataId": "147" }
                    ]
                }
            ;
    case "79":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "152" },
                        { "type": "parameter", "dataId": "153" },
                        { "type": "optional", "dataId": "308" },
                        { "type": "optional", "dataId": "309" },
                        { "type": "optional", "dataId": "310" },
                        { "type": "optional", "dataId": "311" },
                        { "type": "optional", "dataId": "312" },
                        { "type": "optional", "dataId": "313" },
                        { "type": "optional", "dataId": "314" },
                        { "type": "optional", "dataId": "315" },
                        { "type": "optional", "dataId": "316" },
                        { "type": "optional", "dataId": "317" },
                        { "type": "parameter", "dataId": "154" },
                        { "type": "parameter", "dataId": "155" },
                        { "type": "optional", "dataId": "318" },
                        { "type": "optional", "dataId": "319" },
                        { "type": "optional", "dataId": "320" }
                    ]
                }
            ;
    case "80":
        return 
                {
                    "rows": [
                        { "type": "optional", "dataId": "321" },
                        { "type": "optional", "dataId": "322" },
                        { "type": "optional", "dataId": "323" },
                        { "type": "optional", "dataId": "308" },
                        { "type": "optional", "dataId": "309" },
                        { "type": "optional", "dataId": "310" },
                        { "type": "optional", "dataId": "311" },
                        { "type": "optional", "dataId": "312" },
                        { "type": "optional", "dataId": "313" },
                        { "type": "optional", "dataId": "314" },
                        { "type": "optional", "dataId": "315" },
                        { "type": "optional", "dataId": "316" },
                        { "type": "optional", "dataId": "317" },
                        { "type": "parameter", "dataId": "156" },
                        { "type": "parameter", "dataId": "155" },
                        { "type": "optional", "dataId": "318" },
                        { "type": "optional", "dataId": "319" },
                        { "type": "optional", "dataId": "320" }
                    ]
                }
            ;
    case "81":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "151" },
                        { "type": "parameter", "dataId": "157" },
                        { "type": "optional", "dataId": "324" },
                        { "type": "optional", "dataId": "325" }
                    ]
                }
            ;
    case "82":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "160" },
                        { "type": "parameter", "dataId": "161" },
                        { "type": "optional", "dataId": "326" },
                        { "type": "optional", "dataId": "327" },
                        { "type": "optional", "dataId": "328" },
                        { "type": "optional", "dataId": "329" }
                    ]
                }
            ;
    case "83":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "168" },
                        { "type": "parameter", "dataId": "174" },
                        { "type": "optional", "dataId": "338" },
                        { "type": "optional", "dataId": "339" },
                        { "type": "optional", "dataId": "340" },
                        { "type": "optional", "dataId": "341" }
                    ]
                }
            ;
    case "84":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "169" },
                        { "type": "parameter", "dataId": "170" },
                        { "type": "parameter", "dataId": "171" },
                        { "type": "parameter", "dataId": "172" },
                        { "type": "optional", "dataId": "330" },
                        { "type": "optional", "dataId": "331" },
                        { "type": "optional", "dataId": "332" },
                        { "type": "optional", "dataId": "333" },
                        { "type": "optional", "dataId": "334" },
                        { "type": "optional", "dataId": "335" },
                        { "type": "optional", "dataId": "336" }
                    ]
                }
            ;
    case "85":
        return 
                {
                    "rows": [
                        { "type": "optional", "dataId": "337" },
                        { "type": "default", "dataId": "357" },
                        { "type": "default", "dataId": "358" }
                    ]
                }
            ;
    case "86":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "169" },
                        { "type": "parameter", "dataId": "170" },
                        { "type": "parameter", "dataId": "171" },
                        { "type": "parameter", "dataId": "172" },
                        { "type": "parameter", "dataId": "173" },
                        { "type": "optional", "dataId": "330" },
                        { "type": "optional", "dataId": "331" },
                        { "type": "optional", "dataId": "332" },
                        { "type": "optional", "dataId": "333" },
                        { "type": "optional", "dataId": "336" }
                    ]
                }
            ;
    case "87":
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "175" },
                        { "type": "parameter", "dataId": "176" },
                        { "type": "parameter", "dataId": "177" },
                        { "type": "signature" },
                        { "type": "default", "dataId": "359" },
                        { "type": "default", "dataId": "360" },
                        { "type": "optional", "dataId": "342" }
                    ]
                }
            ;
    default:
        return 
                {
                    "rows": [
                        { "type": "parameter", "dataId": "0" }
                    ]
                }
            ;
    }
}


export default ParameterTable
