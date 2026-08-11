import React from 'react'
import type { JSX } from 'react/jsx-runtime'

import CustomFieldExample from './CustomFieldExample.tsx'
import CustomFieldsRequestValues from './CustomFieldsRequestValues.tsx'
import CustomFieldsExample from './CustomFieldsExample.tsx'


// Component
function CustomFieldsSection() {
    return <section id={"api-custom-fields"} data-astro-cid-j7pv25f6={""}>
    	<h2 data-astro-cid-j7pv25f6={""}>
    		Custom Fields
    	</h2>
    	<CustomFieldsRequestValues />
    	
                <CustomFieldExample dataId="0" />
            
    	
                <CustomFieldExample dataId="1" />
            
    	
                <CustomFieldExample dataId="2" />
            
    	
                <CustomFieldsExample dataId="0" />
            
    	
                <CustomFieldsExample dataId="1" />
            
    </section>}


export default CustomFieldsSection
