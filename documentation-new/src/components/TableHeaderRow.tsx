import React from 'react'
import type { JSX } from 'react/jsx-runtime'

import TableHeader from './TableHeader.tsx'


// Component
function TableHeaderRow() {
    return <tr>
    	
                <TableHeader label="Field" />
            
    	
                <TableHeader label="Type" />
            
    	
                <TableHeader label="Description" />
            
    </tr>}


export default TableHeaderRow
