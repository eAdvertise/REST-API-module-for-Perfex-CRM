import React from 'react'
import type { JSX } from 'react/jsx-runtime'

import Chips from './Chips.tsx'
import CopyButton from './CopyButton.tsx'
import TightList from './TightList.tsx'
import CodeValue from './CodeValue.tsx'


// Component
function IntroSection() {
    return <section id={"intro"} className={"intro-section"} data-astro-cid-j7pv25f6={""}>
    	<div className={"hero"} data-astro-cid-j7pv25f6={""}>
    		<div className={"hero-copy"} data-astro-cid-j7pv25f6={""}>
    			<h1 data-astro-cid-j7pv25f6={""}>
					eAD-CRM REST API
    			</h1>
    			<p className={"intro-lede"} data-astro-cid-j7pv25f6={""}>
    				{`
    Connect anything to your eAD-CRM. Read and write your customers, invoices, leads,
     projects and tasks over a clean HTTP/JSON API - or let AI assistants and
     automation tools do it for you.
    `}
    			</p>
    			<p className={"hero-actions"} data-astro-cid-j7pv25f6={""}>
				<a className={"btn-solid"} href={"#api-mcp"} data-astro-cid-j7pv25f6={""}>
    					Browse the API
    				</a>
				<a className={"btn-ghost"} href={"https://github.com/eAdvertise/eAD-CRM-rest-api-examples"} target={"_blank"} rel={"noopener noreferrer"} data-astro-cid-j7pv25f6={""}>
    					{`
    Postman & code examples
    `}
    				</a>
    			</p>
    		</div>
    		<dl className={"hero-stats"} data-astro-cid-j7pv25f6={""}>
    			<div data-astro-cid-j7pv25f6={""}>
    				<dt data-astro-cid-j7pv25f6={""}>
    					Endpoints
    				</dt>
    				<dd data-astro-cid-j7pv25f6={""}>
    					146
    				</dd>
    			</div>
    			<div data-astro-cid-j7pv25f6={""}>
    				<dt data-astro-cid-j7pv25f6={""}>
    					Sections
    				</dt>
    				<dd data-astro-cid-j7pv25f6={""}>
    					31
    				</dd>
    			</div>
    			<div data-astro-cid-j7pv25f6={""}>
    				<dt data-astro-cid-j7pv25f6={""}>
    					MCP tools
    				</dt>
    				<dd data-astro-cid-j7pv25f6={""}>
    					148
    				</dd>
    			</div>
    			<div data-astro-cid-j7pv25f6={""}>
    				<dt data-astro-cid-j7pv25f6={""}>
    					Webhook events
    				</dt>
    				<dd data-astro-cid-j7pv25f6={""}>
    					124
    				</dd>
    			</div>
    		</dl>
    	</div>
    	
            <Chips />
        
    	<div className={"headers"} data-astro-cid-j7pv25f6={""}>
    		{`
    🚀 `}
    		<strong data-astro-cid-j7pv25f6={""}>
    			Fastest way to start:
    		</strong>
    		{` import our
    `}
		<a href={"https://github.com/eAdvertise/eAD-CRM-rest-api-examples"} target={"_blank"} rel={"noopener noreferrer"} data-astro-cid-j7pv25f6={""}>
    			Postman collection and code examples
    		</a>
    		{`
    (cURL, PHP, Python, JavaScript) - set your token and send your first request in minutes.
    `}
    	</div>
    	<div className={"intro-cols"} data-astro-cid-j7pv25f6={""}>
    		<div data-astro-cid-j7pv25f6={""}>
    			<h2 data-astro-cid-j7pv25f6={""}>
    				👋 Welcome
    			</h2>
    			<p data-astro-cid-j7pv25f6={""}>
    				{`
    This is the complete reference for the `}
    				<strong data-astro-cid-j7pv25f6={""}>
					eAD-CRM REST API
    				</strong>
    				{` - and it
     is friendlier than it looks. If you have ever sent an HTTP request, you already know
     enough to get started.
    `}
    			</p>
    			<p data-astro-cid-j7pv25f6={""}>
    				{`
    Everything runs over plain HTTP/HTTPS and speaks JSON, using the standard verbs
     (`}
    				<code data-astro-cid-j7pv25f6={""}>
    					GET
    				</code>
    				{`, `}
    				<code data-astro-cid-j7pv25f6={""}>
    					POST
    				</code>
    				{`, `}
    				<code data-astro-cid-j7pv25f6={""}>
    					PUT
    				</code>
    				{`, `}
    				<code data-astro-cid-j7pv25f6={""}>
    					DELETE
    				</code>
    				{`) and standard
     HTTP status codes. Every endpoint lives under your installation's base URL:
    `}
    			</p>
    			<pre data-astro-cid-j7pv25f6={""}>
    				<code data-astro-cid-j7pv25f6={""}>
    					https://yoursite.com/api/
    				</code>
    				
            <CopyButton hidden={false} />
        
    			</pre>
    		</div>
    		<div data-astro-cid-j7pv25f6={""}>
    			<h2 data-astro-cid-j7pv25f6={""}>
    				🔑 Authentication
    			</h2>
    			<p data-astro-cid-j7pv25f6={""}>
    				{`Send your token with `}
    				<strong data-astro-cid-j7pv25f6={""}>
    					every
    				</strong>
    				{` request - either way works:`}
    			</p>
    			
                <TightList dataId="0" />
            
    			<p className={"small"} data-astro-cid-j7pv25f6={""}>
    				{`
    Write operations (`}
    				<code data-astro-cid-j7pv25f6={""}>
    					POST
    				</code>
    				{` / `}
    				<code data-astro-cid-j7pv25f6={""}>
    					PUT
    				</code>
    				{`) are sent as
    `}
    				<code data-astro-cid-j7pv25f6={""}>
    					multipart/form-data
    				</code>
    				{`.
    `}
    			</p>
    		</div>
    	</div>
    	<div className={"intro-cols setup-cols"} data-astro-cid-j7pv25f6={""}>
    		<div data-astro-cid-j7pv25f6={""}>
    			<h2 data-astro-cid-j7pv25f6={""}>
    				👨‍💻 Installation
    			</h2>
    			<ol className={"tight"} data-astro-cid-j7pv25f6={""}>
    				<li data-astro-cid-j7pv25f6={""}>
    					{`Upload the module under `}
    					<strong data-astro-cid-j7pv25f6={""}>
    						Modules
    					</strong>
					{` in your eAD-CRM installation.`}
    				</li>
    				<li data-astro-cid-j7pv25f6={""}>
    					{`Press `}
    					<strong data-astro-cid-j7pv25f6={""}>
    						Activate
    					</strong>
    					{` to activate the product.`}
    				</li>
    				<li data-astro-cid-j7pv25f6={""}>
    					{`Enter your `}
    					<strong data-astro-cid-j7pv25f6={""}>
    						license key
    					</strong>
    					.
    				</li>
    			</ol>
    		</div>
    		<div data-astro-cid-j7pv25f6={""}>
    			<h2 data-astro-cid-j7pv25f6={""}>
    				✏️ Create an API token
    			</h2>
    			<ol className={"tight"} data-astro-cid-j7pv25f6={""}>
    				<li data-astro-cid-j7pv25f6={""}>
					{`Sign in to your eAD-CRM backend as an `}
    					<strong data-astro-cid-j7pv25f6={""}>
    						administrator
    					</strong>
    					.
    				</li>
    				<li data-astro-cid-j7pv25f6={""}>
    					{`Go to `}
    					<strong data-astro-cid-j7pv25f6={""}>
    						{`API > API Management`}
    					</strong>
    					{` and create a new token.`}
    				</li>
    				<li data-astro-cid-j7pv25f6={""}>
    					{`Copy the token and set its `}
    					<strong data-astro-cid-j7pv25f6={""}>
    						permissions
    					</strong>
    					{` - grant only what each integration needs.`}
    				</li>
    			</ol>
    		</div>
    	</div>
    	<div className={"intro-cols setup-cols"} data-astro-cid-j7pv25f6={""}>
    		<div data-astro-cid-j7pv25f6={""}>
    			<h2 data-astro-cid-j7pv25f6={""}>
    				📄 Lists and pagination
    			</h2>
    			<p data-astro-cid-j7pv25f6={""}>
    				Every list endpoint accepts these, and all of them are optional:
    			</p>
    			
                <TightList dataId="1" />
            
    			<p className={"small"} data-astro-cid-j7pv25f6={""}>
    				<strong data-astro-cid-j7pv25f6={""}>
    					{`Note on `}
    					
                <CodeValue text="limit" hasAstroCid={true} />
            
    					:
    				</strong>
    				{` it acts as an alias for
    `}
    				<code data-astro-cid-j7pv25f6={""}>
    					per_page
    				</code>
    				{` only when `}
    				<code data-astro-cid-j7pv25f6={""}>
    					page
    				</code>
    				{` is sent too. A bare
    `}
    				<code data-astro-cid-j7pv25f6={""}>
    					?limit=5
    				</code>
    				{` keeps its older meaning and does not paginate, so
     use `}
    				<code data-astro-cid-j7pv25f6={""}>
    					?page=1&per_page=5
    				</code>
    				{`.
    `}
    			</p>
    		</div>
    		<div data-astro-cid-j7pv25f6={""}>
    			<h2 data-astro-cid-j7pv25f6={""}>
    				⬆️ Upgrading from 2.x
    			</h2>
    			<p data-astro-cid-j7pv25f6={""}>
    				<strong data-astro-cid-j7pv25f6={""}>
    					There are no breaking changes.
    				</strong>
    				{` Every v3 list feature is
     opt-in: send none of the parameters above and the response is the same
     plain array 2.x returned.
    `}
    			</p>
    			
                <TightList dataId="2" />
            
    			<p className={"small"} data-astro-cid-j7pv25f6={""}>
    				{`
    Custom tables live under `}
    				<code data-astro-cid-j7pv25f6={""}>
    					{`/api/thirdparty/customtable/{table_name}`}
    				</code>
    				{` -
     the `}
    				<code data-astro-cid-j7pv25f6={""}>
    					customtable
    				</code>
    				{` segment is required, and the table must be
     allowlisted in `}
    				<strong data-astro-cid-j7pv25f6={""}>
    					{`Setup > API > Settings`}
    				</strong>
    				{`.
    `}
    			</p>
    		</div>
    	</div>
    	<h2 data-astro-cid-j7pv25f6={""}>
    		🚀 What's new in v3.0
    	</h2>
    	<div className={"news-grid"} data-astro-cid-j7pv25f6={""}>
    		<div data-astro-cid-j7pv25f6={""}>
    			<strong data-astro-cid-j7pv25f6={""}>
    				🤖 MCP Server for AI agents
    			</strong>
    			<p data-astro-cid-j7pv25f6={""}>
    				<code data-astro-cid-j7pv25f6={""}>
    					POST /api/mcp
    				</code>
    				{` speaks the Model Context Protocol and exposes 148 permission-filtered CRM tools to Claude Desktop, ChatGPT, Cursor, n8n AI Agent and any MCP-compatible client.`}
    			</p>
    		</div>
    		<div data-astro-cid-j7pv25f6={""}>
    			<strong data-astro-cid-j7pv25f6={""}>
    				🪝 Webhooks 2.0
    			</strong>
    			<p data-astro-cid-j7pv25f6={""}>
    				124 events across 22 resource groups, REST management, optional async delivery with retries, SSRF protection and HMAC-signed requests.
    			</p>
    		</div>
    		<div data-astro-cid-j7pv25f6={""}>
    			<strong data-astro-cid-j7pv25f6={""}>
    				🔌 Automation platforms
    			</strong>
    			<p data-astro-cid-j7pv25f6={""}>
    				{`Polling triggers under `}
    				<code data-astro-cid-j7pv25f6={""}>
    					/api/zapier/*
    				</code>
    				{` for Zapier, Make.com and n8n.`}
    			</p>
    		</div>
    		<div data-astro-cid-j7pv25f6={""}>
    			<strong data-astro-cid-j7pv25f6={""}>
    				📄 Smarter lists
    			</strong>
    			<p data-astro-cid-j7pv25f6={""}>
    				Opt-in pagination, field selection, sorting and date-range filters, with the legacy response shape preserved by default.
    			</p>
    		</div>
    		<div data-astro-cid-j7pv25f6={""}>
    			<strong data-astro-cid-j7pv25f6={""}>
    				🧮 Server-side invoice math
    			</strong>
    			<p data-astro-cid-j7pv25f6={""}>
    				Totals calculated automatically from line items, taxes, discounts and adjustments.
    			</p>
    		</div>
    		<div data-astro-cid-j7pv25f6={""}>
    			<strong data-astro-cid-j7pv25f6={""}>
    				🛡️ Safe writes
    			</strong>
    			<p data-astro-cid-j7pv25f6={""}>
    				<code data-astro-cid-j7pv25f6={""}>
    					Idempotency-Key
    				</code>
    				{` replay, tolerant `}
    				<code data-astro-cid-j7pv25f6={""}>
    					PUT
    				</code>
    				, and rate-limit headers on every response.
    			</p>
    		</div>
    		<div data-astro-cid-j7pv25f6={""}>
    			<strong data-astro-cid-j7pv25f6={""}>
    				⚡ Batch operations
    			</strong>
    			<p data-astro-cid-j7pv25f6={""}>
    				{`Up to 50 operations in one request via `}
    				<code data-astro-cid-j7pv25f6={""}>
    					POST /api/batch
    				</code>
    				.
    			</p>
    		</div>
    		<div data-astro-cid-j7pv25f6={""}>
    			<strong data-astro-cid-j7pv25f6={""}>
    				🧾 Machine-readable spec
    			</strong>
    			<p data-astro-cid-j7pv25f6={""}>
    				<code data-astro-cid-j7pv25f6={""}>
    					GET /api/openapi.json
    				</code>
    				{` for Postman, Insomnia or Stoplight.`}
    			</p>
    		</div>
    	</div>
    </section>}


export default IntroSection
