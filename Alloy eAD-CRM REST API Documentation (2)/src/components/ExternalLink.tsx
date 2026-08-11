import React from 'react'
import type { JSX } from 'react/jsx-runtime'



    
// Component

        function ExternalLink({
          className,
          title,
          label,
          href
        }: {
          className?: string;
          title: string;
          label: string;
          href: string;
        }) {
          return (
            <a className={className} href={href} target={"_blank"} rel={"noopener noreferrer"} title={title}>
              {label}
            </a>
          )
        }
    

export default ExternalLink
