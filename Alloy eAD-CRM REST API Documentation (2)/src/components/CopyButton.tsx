import React from 'react'
import type { JSX } from 'react/jsx-runtime'



// Component

        function CopyButton({
          hidden = false
        }: {
          hidden?: boolean;
        }) {
          return (
            <button
              type={"button"}
              className={hidden ? "pre-copy sf-hidden" : "pre-copy"}
            >
              Copy
            </button>
          )
        }
    

export default CopyButton
