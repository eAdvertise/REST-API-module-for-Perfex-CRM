(function () {
  'use strict';

  var resources = [
    ['warehouses', 'Locations and staff assignments', 'GET, POST, PUT, DELETE'],
    ['items', 'Products, variants, tags and custom values', 'GET, POST, PUT, DELETE'],
    ['inventory', 'Computed stock balances', 'GET only'],
    ['receipts / deliveries / transfers / adjustments', 'Operational stock documents and lines', 'GET, POST, PUT, DELETE'],
    ['commodity_types / commodity_groups / sub_groups', 'Item classification', 'GET, POST, PUT, DELETE'],
    ['units / sizes / styles / bodies / colors', 'Item attributes', 'GET, POST, PUT, DELETE'],
    ['brands / models / series', 'Product catalogue metadata', 'GET, POST, PUT, DELETE'],
    ['inventory_minimums / serial_numbers', 'Stock controls and traceability', 'GET, POST, PUT, DELETE'],
    ['stock_takes / packing_lists / order_returns', 'Stock control documents and returns', 'GET, POST, PUT, DELETE'],
    ['approval_settings / approval_details', 'Approval configuration and state', 'GET, POST, PUT, DELETE'],
    ['warehouse_custom_fields / staff_warehouses', 'Configuration and access', 'GET, POST, PUT, DELETE'],
    ['activity_logs / transaction_details / relations', 'Audit, transaction and relation data', 'GET, POST, PUT, DELETE']
  ];

  var operations = [
    ['GET', '/api/warehouse/:resource/:id?', 'List or retrieve records', 'List with page/per_page and exact database-field filters, or append an ID.'],
    ['POST', '/api/warehouse/:resource', 'Create a record', 'Send JSON or form data. Operational documents accept native Warehouse newitems lines.'],
    ['PUT', '/api/warehouse/:resource/:id', 'Update a record', 'Update a writable resource using real fields from its Warehouse table.'],
    ['DELETE', '/api/warehouse/:resource/:id', 'Delete a record', 'Delete a writable resource using native handlers where relationship checks exist.']
  ];

  function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, function (character) {
      return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[character];
    });
  }

  function install() {
    var content = document.getElementById('content');
    if (!content || document.getElementById('api-warehouse')) return false;

    var section = document.createElement('section');
    section.id = 'api-warehouse';
    section.innerHTML = '<h2>Warehouse</h2>' +
      '<article id="api-warehouse-resources"><h1>Warehouse resources</h1>' +
      '<p>API v3 exposes Warehouse master data, documents, configuration and supporting records. Inventory balances are read-only; stock changes use receipts, deliveries, transfers or adjustments.</p>' +
      '<div class="table-responsive-wrapper"><table class="table table-hover"><thead><tr><th>Resources</th><th>Description</th><th>Methods</th></tr></thead><tbody>' +
      resources.map(function (row) { return '<tr><td><code>' + escapeHtml(row[0]) + '</code></td><td>' + escapeHtml(row[1]) + '</td><td>' + escapeHtml(row[2]) + '</td></tr>'; }).join('') +
      '</tbody></table></div></article>' +
      operations.map(function (operation) {
        var method = operation[0];
        var example = method === 'GET'
          ? 'curl -H "authtoken: YOUR_API_TOKEN" "https://yoursite.com/api/warehouse/items?page=1&per_page=25"'
          : 'curl -X ' + method + ' -H "authtoken: YOUR_API_TOKEN" -H "Content-Type: application/json" "https://yoursite.com' + operation[1].replace(':resource', 'items').replace('/:id?', '/42').replace(':id', '42') + '"';
        return '<article id="api-warehouse-' + method.toLowerCase() + '"><h1>' + escapeHtml(operation[2]) + '</h1><div class="row pre-post"><div class="col-md-7 no-float"><pre class="full-pre"><span class="typ typ-' + method.toLowerCase() + '">' + method + '</span><span class="url">' + escapeHtml(operation[1]) + '</span></pre><div class="endpoint-desc"><p>' + escapeHtml(operation[3]) + '</p></div></div><div class="col-md-4 section-example no-float"><pre class="astro-code catppuccin-mocha" style="background:#1e1e2e;color:#cdd6f4;overflow-x:auto"><code>' + escapeHtml(example) + '</code></pre></div></div></article>';
      }).join('');

    var customFields = document.getElementById('api-custom-fields');
    content.insertBefore(section, customFields || null);

    var nav = document.querySelector('#sidenav ul.sidenav');
    if (nav) {
      var item = document.createElement('li');
      item.className = 'nav-header nav-list-item';
      item.innerHTML = '<a href="#api-warehouse">Warehouse</a>';
      nav.appendChild(item);
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
