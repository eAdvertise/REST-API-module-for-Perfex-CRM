import React from 'react'
import type { JSX } from 'react/jsx-runtime'

import ExternalLink from './ExternalLink.tsx'
import EndpointFilter from './EndpointFilter.tsx'
import SearchReset from './SearchReset.tsx'
import ApiNavigation from './ApiNavigation.tsx'


// Component
function ApiSidenav() {
    return <nav id={"sidenav"}>
    	<div id={"scrollingNav"}>
    		<div className={"sidenav-search-wrap"}>
    			<div className={"sidenav-search"}>
    				<EndpointFilter />
    				<SearchReset />
    			</div>
    		</div>
    		
             <ApiNavigation />
            <div className={"sidenav-footer"}>
                <ExternalLink href="https://eadvertise.eu/" title="eAD-CRM modules by eAdvertise" label={"eAdvertise"} />
                <span>Self-hosted documentation</span>
            </div>
    	</div>
    </nav>}


export default ApiSidenav
