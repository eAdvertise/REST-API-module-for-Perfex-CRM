import React from 'react'
import type { JSX } from 'react/jsx-runtime'

import Img from './Img.tsx'


// Component
function BrandLink() {
    return <a className={"brand-link"} href={"#intro"}>
    	<Img id="0" />
    	<h3>
		eAD-CRM REST API
    	</h3>
    </a>}


export default BrandLink
