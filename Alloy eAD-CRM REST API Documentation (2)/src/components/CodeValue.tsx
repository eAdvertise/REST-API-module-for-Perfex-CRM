import React from 'react'
import type { JSX } from 'react/jsx-runtime'



    
// Component

        function CodeValue({
            text,
            hasAstroCid = false
        }: {
            text: string;
            hasAstroCid?: boolean;
        }) {
            return (
                <code {...(hasAstroCid ? { "data-astro-cid-j7pv25f6": "" } : {})}>
                    {text}
                </code>
            )
        }
    

export default CodeValue
