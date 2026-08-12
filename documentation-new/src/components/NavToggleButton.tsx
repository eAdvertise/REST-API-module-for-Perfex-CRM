import React from 'react'
import type { JSX } from 'react/jsx-runtime'



// Component
function NavToggleButton() {
    const [isOpen, setIsOpen] = React.useState(false)

    const toggleNavigation = () => {
        const nextState = !isOpen
        setIsOpen(nextState)
        document.getElementById('sidenav')?.classList.toggle('is-open', nextState)
    }

    return <button type={"button"} className={"nav-toggle-btn"} aria-label={isOpen ? "Close navigation" : "Open navigation"} aria-expanded={isOpen} onClick={toggleNavigation}>
    	☰
    </button>}


export default NavToggleButton
