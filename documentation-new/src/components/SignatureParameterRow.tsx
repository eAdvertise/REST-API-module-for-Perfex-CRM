import React from 'react'
import type { JSX } from 'react/jsx-runtime'

import ParameterRow from './ParameterRow.tsx'


// Component
function SignatureParameterRow() {
    return <tr>
    	<td className={"code"}>
    		secret
    	</td>
    	<td>
    		String
    	</td>
    	<td>
    		<span className={"label-optional"}>
    			{`optional `}
    		</span>
    		<span>
    			HMAC secret. Deliveries then carry X-Perfex-Signature: t=
    			<unix>
    				,v1=
    				<hmac_sha256>
    					.
    				</hmac_sha256>
    			</unix>
    		</span>
    	</td>
    </tr>}


export default SignatureParameterRow
