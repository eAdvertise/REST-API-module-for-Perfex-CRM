import React from 'react'
import type { JSX } from 'react/jsx-runtime'

import CopyButton from './CopyButton.tsx'
import ExampleTabs from './ExampleTabs.tsx'
import ParameterTable from './ParameterTable.tsx'


// Component
function CustomFieldsRequestValues() {
    return <article id={"api-custom-fields-get-custom-fieldswith-value"}>
    	<h1>
    		Request Values of Custom Fields
    	</h1>
    	<div className={"row pre-get"}>
    		<div className={"col-md-7 no-float"}>
    			<pre className={"full-pre"}>
    				<span className={"typ typ-get"}>
    					GET
    				</span>
    				<span className={"url"}>
    					{`/api/custom_fields/{FieldBelongsto}/{id}`}
    				</span>
    				
            <CopyButton hidden={true} />
        
    			</pre>
    			<h2 className={"sub"}>
    				Headers
    			</h2>
    			<div className={"table-responsive-wrapper"}>
    				
            <ParameterTable dataId="0" />
        
    			</div>
    			<h2 className={"sub"}>
    				Parameters
    			</h2>
    			<div className={"table-responsive-wrapper"}>
    				
            <ParameterTable dataId="37" />
        
    			</div>
    		</div>
    		<div className={"col-md-4 section-example no-float"}>
    			
            <ExampleTabs dataId="2" />
        
    			<div className={"tab-content"}>
    				<div className={"tab-pane active"} id={"ex-custom-fields-get-custom-fieldswith-value-curl"}>
    					<pre className={"astro-code catppuccin-mocha"} style={{backgroundColor:"#1e1e2e", color:"#cdd6f4", overflowX:"auto"}} tabIndex={"0"}>
    						<code>
    							<span className={"line"}>
    								<span style={{color:"#89B4FA", fontStyle:"italic"}}>
    									curl
    								</span>
    								<span style={{color:"#A6E3A1"}}>
    									{` -X`}
    								</span>
    								<span style={{color:"#A6E3A1"}}>
    									{` GET`}
    								</span>
    								<span style={{color:"#A6E3A1"}}>
    									{` "https://yoursite.com/api/custom_fields/FIELDBELONGSTO/123"`}
    								</span>
    								<span style={{color:"#F5C2E7"}}>
    									{" \\"}
    								</span>
    							</span>
    							<span className={"line"}>
    								<span style={{color:"#A6E3A1"}}>
    									{`  -H`}
    								</span>
    								<span style={{color:"#A6E3A1"}}>
    									{` "authtoken: YOUR_API_TOKEN"`}
    								</span>
    							</span>
    						</code>
    						
            <CopyButton hidden={false} />
        
    					</pre>
    				</div>
    				<div className={"tab-pane sf-hidden"} id={"ex-custom-fields-get-custom-fieldswith-value-0"}>
    
    				</div>
    				<div className={"tab-pane sf-hidden"} id={"ex-custom-fields-get-custom-fieldswith-value-1"}>
    
    				</div>
    			</div>
    		</div>
    	</div>
    </article>}


export default CustomFieldsRequestValues
