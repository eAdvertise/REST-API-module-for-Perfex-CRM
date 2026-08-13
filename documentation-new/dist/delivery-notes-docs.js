(function () {
  'use strict';

  var endpoints = [
    ['GET', 'Discover endpoints', '/api/delivery_notes', 'Return the complete Delivery Notes endpoint catalog.'],
    ['GET', 'List delivery notes', '/api/delivery_notes/notes', 'List delivery notes with pagination and optional clientid, status, currency, sale_agent, project_id, from and to filters.'],
    ['POST', 'Create delivery note', '/api/delivery_notes/notes', 'Create a delivery note using standard sales-document fields and a newitems array.'],
    ['GET', 'Get delivery note', '/api/delivery_notes/notes/:id', 'Retrieve a delivery note with its items, attachments, client and related records.'],
    ['PUT', 'Update delivery note', '/api/delivery_notes/notes/:id', 'Update document fields, items, tags and custom fields.'],
    ['DELETE', 'Delete delivery note', '/api/delivery_notes/notes/:id', 'Delete a delivery note when the module business rules allow it.'],
    ['GET', 'List statuses', '/api/delivery_notes/statuses', 'Return all valid Delivery Notes status IDs.'],
    ['PUT', 'Change status', '/api/delivery_notes/notes/:id/status', 'Change the status and run module notifications and lifecycle hooks.'],
    ['POST', 'Send delivery note email', '/api/delivery_notes/notes/:id/email', 'Email the delivery note, optionally attaching its PDF and adding a CC address.'],
    ['GET', 'Get delivery note PDF', '/api/delivery_notes/notes/:id/pdf', 'Return the generated PDF as Base64 with filename and content type.'],
    ['POST', 'Copy delivery note', '/api/delivery_notes/notes/:id/copy', 'Create a new delivery note by copying an existing one.'],
    ['POST', 'Convert to invoice', '/api/delivery_notes/notes/:id/convert-to-invoice', 'Convert the delivery note to an invoice; draft=true creates a draft.'],
    ['POST', 'Create from invoice', '/api/delivery_notes/from-invoice/:id', 'Create a delivery note from an invoice.'],
    ['POST', 'Create from estimate', '/api/delivery_notes/from-estimate/:id', 'Create a delivery note from an estimate.'],
    ['POST', 'Create from purchase order', '/api/delivery_notes/from-purchase-order/:id', 'Create a delivery note from a Purchase Orders module record.']
  ];

  function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, function (character) {
      return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[character];
    });
  }

  function anchor(method, title) {
    return 'api-delivery-notes-' + method.toLowerCase() + '-' + title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
  }

  function body(method, path) {
    if (method === 'POST' && path === '/api/delivery_notes/notes') return '{"clientid":12,"currency":1,"date":"2026-08-13","newitems":[{"description":"Delivered item","qty":2,"rate":25}]}';
    if (method === 'PUT' && /\/status$/.test(path)) return '{"status":4}';
    if (method === 'POST' && /\/email$/.test(path)) return '{"attach_pdf":true,"cc":"warehouse@example.com"}';
    if (method === 'POST' && (/\/from-/.test(path) || /convert-to-invoice$/.test(path))) return '{"draft":false}';
    if (method === 'PUT') return '{"adminnote":"Updated through API v3"}';
    return '';
  }

  function example(method, path) {
    var payload = body(method, path);
    return 'curl -X ' + method + ' -H "authtoken: YOUR_API_TOKEN"' + (payload ? ' -H "Content-Type: application/json" -d \'' + payload + '\'' : '') + ' "https://yoursite.com' + path.replace(':id', '42') + '"';
  }

  function endpointHtml(row) {
    return '<article id="' + anchor(row[0], row[1]) + '"><h1>' + escapeHtml(row[1]) + '</h1><div class="row pre-post">' +
      '<div class="col-md-7 no-float"><pre class="full-pre"><span class="typ typ-' + row[0].toLowerCase() + '">' + row[0] + '</span><span class="url">' + escapeHtml(row[2]) + '</span></pre>' +
      '<div class="endpoint-desc"><p>' + escapeHtml(row[3]) + '</p></div></div><div class="col-md-4 section-example no-float">' +
      '<pre class="astro-code catppuccin-mocha" style="background:#1e1e2e;color:#cdd6f4;overflow-x:auto"><code>' + escapeHtml(example(row[0], row[2])) + '</code></pre></div></div></article>';
  }

  function install() {
    var content = document.getElementById('content');
    if (!content || document.getElementById('api-delivery-notes')) return false;

    var section = document.createElement('section');
    section.id = 'api-delivery-notes';
    section.innerHTML = '<h2>Delivery Notes</h2><article id="api-delivery-notes-overview"><h1>Delivery Notes endpoints</h1><div class="row pre-post">' +
      '<div class="col-md-7 no-float"><div class="endpoint-desc"><p>API v3 exposes the complete Delivery Notes workflow: CRUD, filtering, status transitions, email delivery, PDF generation, copying and document conversions.</p>' +
      '<p>The module must be installed and active. Authenticate with <code>authtoken</code> and grant the matching Delivery Notes method capability.</p>' +
      '<p>Creation uses the module sales-document structure, including <code>clientid</code>, <code>currency</code>, <code>date</code>, address fields and <code>newitems</code>.</p></div>' +
      '<div class="table-responsive-wrapper"><table class="table table-hover"><thead><tr><th>Method</th><th>Endpoint</th><th>Operation</th></tr></thead><tbody>' +
      endpoints.map(function (row) { return '<tr><td>' + row[0] + '</td><td><code>' + escapeHtml(row[2]) + '</code></td><td>' + escapeHtml(row[1]) + '</td></tr>'; }).join('') +
      '</tbody></table></div></div><div class="col-md-4 section-example no-float"><pre class="astro-code catppuccin-mocha" style="background:#1e1e2e;color:#cdd6f4;overflow-x:auto"><code>' + escapeHtml(example('GET', '/api/delivery_notes')) + '</code></pre></div></div></article>' + endpoints.map(endpointHtml).join('');

    var customers = document.getElementById('api-customers');
    content.insertBefore(section, customers || document.getElementById('api-custom-fields') || null);

    var nav = document.querySelector('#sidenav ul.sidenav');
    if (nav) {
      var header = document.createElement('li');
      header.className = 'nav-header nav-list-item';
      header.innerHTML = '<a href="#api-delivery-notes">Delivery Notes</a>';
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
