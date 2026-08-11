import React from 'react'
import type { JSX } from 'react/jsx-runtime'

import BrandLink from './BrandLink.tsx'
import ExternalLink from './ExternalLink.tsx'
import EndpointFilter from './EndpointFilter.tsx'
import SearchReset from './SearchReset.tsx'
import ApiNavigation from './ApiNavigation.tsx'


// Component
function ApiSidenav() {
    return <nav id={"sidenav"}>
    	<div id={"scrollingNav"}>
    		<div className={"sidenav-brand"}>
    			<div className={"sidenav-logo"}>
    				<BrandLink />
    				<span className={"sidenav-version"}>
    					v3.0.3
    				</span>
    				<span className={"sidenav-credit"}>
    					{`
    by `}
    					
                <ExternalLink href="https://eadvertise.eu/" title="eAD-CRM modules by eAdvertise" label={"eAdvertise"} />
				</span>
    			</div>
    		</div>
    		<div className={"sidenav-search-wrap"}>
    			<div className={"sidenav-search"}>
    				<EndpointFilter />
    				<SearchReset />
    			</div>
    		</div>
    		
            <ApiNavigation />
        
    	</div>
    </nav>}


export default ApiSidenav
