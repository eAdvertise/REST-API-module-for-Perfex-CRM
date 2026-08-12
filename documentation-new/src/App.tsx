import React from 'react'
import type { JSX } from 'react/jsx-runtime'

import NavToggleButton from './components/NavToggleButton.tsx'
import Footer from './components/Footer.tsx'
import IntroSection from './components/IntroSection.tsx'
import ApiSidenav from './components/ApiSidenav.tsx'
import ApiEndpoint1 from './components/ApiEndpoint1.tsx'
import ApiEndpoint from './components/ApiEndpoint.tsx'
import ApiEndpoint3 from './components/ApiEndpoint3.tsx'
import ApiEndpoint2 from './components/ApiEndpoint2.tsx'
import ApiSection from './components/ApiSection.tsx'
import CustomFieldsSection from './components/CustomFieldsSection.tsx'
import BrandLink from './components/BrandLink.tsx'

import { BrowserRouter as Router, Routes, Route, useLocation, Navigate } from 'react-router-dom';

function AppContent() {
    const location = useLocation();

    React.useEffect(() => {
        const targetId = location.hash.slice(1);
        if (!targetId) {
            return;
        }

        document.getElementById(targetId)?.scrollIntoView({ block: 'start' });
    }, [location.hash]);

    return (
        <body>
        <a className={"skip-link"} href={"#content"}>
        Skip to content
        </a>
        <header className={"docs-topbar"}>
            <BrandLink />
            <div className={"docs-topbar-actions"}>
                <button className={"docs-search-trigger"} type={"button"} onClick={() => document.querySelector<HTMLInputElement>('.sidenav-search .search')?.focus()}>
                    Search documentation
                    <kbd>Ctrl K</kbd>
                </button>
                <a className={"docs-github-link"} href={"https://github.com/eAdvertise/eAD-CRM-rest-api-examples"} target={"_blank"} rel={"noopener noreferrer"}>
                    Examples
                </a>
            </div>
        </header>
        <NavToggleButton />
        <div className={"app-layout"}>
        <ApiSidenav />
        <div id={"content"}>
        <IntroSection />
        <section id={"api-mcp"} data-astro-cid-j7pv25f6={""}>
        <h2 data-astro-cid-j7pv25f6={""}>
        MCP
        </h2>
        
                <ApiEndpoint1 dataId="0" />
            
        </section>
        
                    <ApiSection dataId="0" />
                
        <section id={"api-batch"} data-astro-cid-j7pv25f6={""}>
        <h2 data-astro-cid-j7pv25f6={""}>
        Batch
        </h2>
        
                <ApiEndpoint1 dataId="5" />
            
        </section>
        
                    <ApiSection dataId="1" />
                
        
                    <ApiSection dataId="2" />
                
        
                    <ApiSection dataId="3" />
                
        
                    <ApiSection dataId="4" />
                
        <section id={"api-common"} data-astro-cid-j7pv25f6={""}>
        <h2 data-astro-cid-j7pv25f6={""}>
        Common
        </h2>
        
                <ApiEndpoint1 dataId="28" />
            
        </section>
        
                    <ApiSection dataId="5" />
                
        
                    <ApiSection dataId="6" />
                
        
                    <ApiSection dataId="7" />
                
        
                    <ApiSection dataId="8" />
                
        <section id={"api-expense-categories"} data-astro-cid-j7pv25f6={""}>
        <h2 data-astro-cid-j7pv25f6={""}>
        Expense Categories
        </h2>
        
                <ApiEndpoint dataId="13" />
            
        </section>
        
                    <ApiSection dataId="9" />
                
        
                    <ApiSection dataId="10" />
                
        
                    <ApiSection dataId="11" />
                
        
                    <ApiSection dataId="12" />
                
        
                    <ApiSection dataId="13" />
                
        <section id={"api-payment-modes"} data-astro-cid-j7pv25f6={""}>
        <h2 data-astro-cid-j7pv25f6={""}>
        Payment Modes
        </h2>
        
                <ApiEndpoint3 dataId="8" />
            
        </section>
        
                    <ApiSection dataId="14" />
                
        
                    <ApiSection dataId="15" />
                
        
                    <ApiSection dataId="16" />
                
        
                    <ApiSection dataId="17" />
                
        
                    <ApiSection dataId="18" />
                
        
                    <ApiSection dataId="19" />
                
        <section id={"api-taxes"} data-astro-cid-j7pv25f6={""}>
        <h2 data-astro-cid-j7pv25f6={""}>
        Taxes
        </h2>
        
            <ApiEndpoint2 dataId="10" />
          
        </section>
        
                    <ApiSection dataId="20" />
                
        
                    <ApiSection dataId="21" />
                
        
                    <ApiSection dataId="22" />
                
        
                    <ApiSection dataId="23" />
                
        <CustomFieldsSection />
        <Footer />
        </div>
        </div>
        </body>
    );
}

function App() {
    const defaultRoute = "/apiguide/";

    return (
        <Router>
            <Routes>
                {defaultRoute !== '/' && <Route path="/" element={<Navigate to={defaultRoute} replace />} />}
                <Route path="*" element={<AppContent />} />
            </Routes>
        </Router>
    );
}

export default App
