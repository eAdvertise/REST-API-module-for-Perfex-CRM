(function () {
  'use strict';

  var endpoints = [
    ['GET', 'Discover endpoints', '/api/paymentsonaccount', 'Return the PaymentsOnAccount endpoint catalog.'],
    ['GET', 'List receipts', '/api/paymentsonaccount/receipts', 'List receipts with page/per_page and optional client_id, payment_mode, transaction_id, receipt_number, from and to filters.'],
    ['POST', 'Create receipt', '/api/paymentsonaccount/receipts', 'Create a receipt, optionally allocate it to invoice_ids and send the receipt email.'],
    ['GET', 'Get receipt', '/api/paymentsonaccount/receipts/:id', 'Retrieve one receipt together with its invoice applications.'],
    ['PUT', 'Update receipt', '/api/paymentsonaccount/receipts/:id', 'Update amount, payment date and mode, payment method, transaction ID, note or receipt number.'],
    ['DELETE', 'Delete receipt', '/api/paymentsonaccount/receipts/:id', 'Delete a receipt and its linked core payments through the module model.'],
    ['GET', 'List receipt applications', '/api/paymentsonaccount/receipts/:id/applications', 'List the invoice payments created from a receipt.'],
    ['POST', 'Apply receipt to invoices', '/api/paymentsonaccount/receipts/:id/applications', 'Allocate a receipt using invoice_ids or explicit invoice_id and amount allocations.'],
    ['DELETE', 'Delete receipt application', '/api/paymentsonaccount/receipts/:id/applications/:payment_id', 'Remove an applied core payment and synchronize the receipt allocations.'],
    ['POST', 'Send receipt email', '/api/paymentsonaccount/receipts/:id/email', 'Send the standard receipt email with its PDF attachment.'],
    ['GET', 'Get receipt PDF', '/api/paymentsonaccount/receipts/:id/pdf', 'Return the receipt PDF as Base64 with its filename and content type.'],
    ['GET', 'List unpaid invoices', '/api/paymentsonaccount/clients/:id/unpaid-invoices', 'List unpaid invoices available for allocation for a client.'],
    ['GET', 'Get client payment modes', '/api/paymentsonaccount/clients/:id/payment-modes', 'Return the payment modes enabled for a client.'],
    ['PUT', 'Update client payment modes', '/api/paymentsonaccount/clients/:id/payment-modes', 'Replace the payment modes enabled for a client.'],
    ['GET', 'Get client statement', '/api/paymentsonaccount/clients/:id/statement', 'Return invoices, receipts, credits and totals for the requested date range.'],
    ['GET', 'Get receipts report', '/api/paymentsonaccount/reports/receipts', 'Return the paginated receipts report using standard receipt filters.'],
    ['GET', 'Get credits report', '/api/paymentsonaccount/reports/credits', 'Return the paginated credits report with client and date filters.']
  ];

  function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, function (character) {
      return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[character];
    });
  }

  function anchor(method, title) {
    return 'api-paymentsonaccount-' + method.toLowerCase() + '-' + title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
  }

  function example(method, path) {
    var url = path.replace(':payment_id', '77').replace(':id', '42');
    var body = '';
    if (method === 'POST' && path === '/api/paymentsonaccount/receipts') {
      body = ' -H "Content-Type: application/json" -d \'{"client_id":12,"amount":150,"payment_mode":"1","invoice_ids":[35],"send_email":true}\'';
    } else if (method === 'POST' && /applications$/.test(path)) {
      body = ' -H "Content-Type: application/json" -d \'{"allocations":[{"invoice_id":35,"amount":100}]}\'';
    } else if (method === 'PUT' && /payment-modes$/.test(path)) {
      body = ' -H "Content-Type: application/json" -d \'{"payment_mode_ids":[1,2]}\'';
    }
    return 'curl -X ' + method + ' -H "authtoken: YOUR_API_TOKEN"' + body + ' "https://yoursite.com' + url + '"';
  }

  function endpointHtml(row) {
    return '<article id="' + anchor(row[0], row[1]) + '"><h1>' + escapeHtml(row[1]) + '</h1>' +
      '<div class="row pre-post"><div class="col-md-7 no-float"><pre class="full-pre">' +
      '<span class="typ typ-' + row[0].toLowerCase() + '">' + row[0] + '</span><span class="url">' + escapeHtml(row[2]) + '</span></pre>' +
      '<div class="endpoint-desc"><p>' + escapeHtml(row[3]) + '</p></div></div>' +
      '<div class="col-md-4 section-example no-float"><pre class="astro-code catppuccin-mocha" style="background:#1e1e2e;color:#cdd6f4;overflow-x:auto"><code>' + escapeHtml(example(row[0], row[2])) + '</code></pre></div></div></article>';
  }

  function install() {
    var content = document.getElementById('content');
    if (!content || document.getElementById('api-paymentsonaccount')) return false;

    var section = document.createElement('section');
    section.id = 'api-paymentsonaccount';
    section.innerHTML = '<h2>Payments On Account</h2><article id="api-paymentsonaccount-overview"><h1>PaymentsOnAccount endpoints</h1>' +
      '<div class="row pre-post"><div class="col-md-7 no-float"><div class="endpoint-desc">' +
      '<p>API v3 exposes receipt management, invoice allocation, email and PDF delivery, client payment-mode settings, statements and reports from PaymentsOnAccount 3.1.1.</p>' +
      '<p>Authenticate with <code>authtoken</code> and grant the API user the Payments On Account capability matching each HTTP method.</p></div>' +
      '<div class="table-responsive-wrapper"><table class="table table-hover"><thead><tr><th>Method</th><th>Endpoint</th><th>Operation</th></tr></thead><tbody>' +
      endpoints.map(function (row) { return '<tr><td>' + row[0] + '</td><td><code>' + escapeHtml(row[2]) + '</code></td><td>' + escapeHtml(row[1]) + '</td></tr>'; }).join('') +
      '</tbody></table></div></div><div class="col-md-4 section-example no-float"><pre class="astro-code catppuccin-mocha" style="background:#1e1e2e;color:#cdd6f4;overflow-x:auto"><code>' + escapeHtml(example('GET', '/api/paymentsonaccount')) + '</code></pre></div></div></article>' +
      endpoints.map(endpointHtml).join('');

    var paymentModes = document.getElementById('api-payment-modes');
    content.insertBefore(section, paymentModes || document.getElementById('api-custom-fields') || null);

    var nav = document.querySelector('#sidenav ul.sidenav');
    if (nav) {
      var header = document.createElement('li');
      header.className = 'nav-header nav-list-item';
      header.innerHTML = '<a href="#api-paymentsonaccount">Payments On Account</a>';
      nav.appendChild(header);
      endpoints.forEach(function (row) {
        var item = document.createElement('li');
        item.className = 'nav-list-item';
        item.innerHTML = '<a href="#' + anchor(row[0], row[1]) + '"><span class="typ-name typ-' + row[0].toLowerCase() + '">' + row[0] + '</span><span class="nav-title">' + escapeHtml(row[1]) + '</span></a>';
        nav.appendChild(item);
      });
    }
    return true;
  }

  if (!install()) {
    var observer = new MutationObserver(function () {
      if (install()) observer.disconnect();
    });
    observer.observe(document.documentElement, {childList: true, subtree: true});
  }
}());
