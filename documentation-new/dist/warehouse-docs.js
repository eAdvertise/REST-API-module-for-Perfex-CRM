(function () {
  'use strict';

  var resources = [
    ['warehouses', 'Warehouse locations and staff assignments', 'CRUD'],
    ['items', 'Products and variants', 'CRUD'],
    ['inventory', 'Computed stock balances', 'GET only'],
    ['receipts', 'Goods receipt documents', 'CRUD'],
    ['receipt_details', 'Goods receipt lines', 'CRUD'],
    ['deliveries', 'Goods delivery documents', 'CRUD'],
    ['delivery_details', 'Goods delivery lines', 'CRUD'],
    ['transfers', 'Internal transfer documents', 'CRUD'],
    ['adjustments', 'Loss adjustment documents', 'CRUD'],
    ['adjustment_details', 'Loss adjustment lines', 'CRUD'],
    ['commodity_types', 'Commodity types', 'CRUD'],
    ['commodity_groups', 'Commodity groups', 'CRUD'],
    ['sub_groups', 'Commodity subgroups', 'CRUD'],
    ['units', 'Measurement units', 'CRUD'],
    ['sizes', 'Item sizes', 'CRUD'],
    ['styles', 'Item styles', 'CRUD'],
    ['bodies', 'Item bodies', 'CRUD'],
    ['colors', 'Item colors', 'CRUD'],
    ['brands', 'Product brands', 'CRUD'],
    ['models', 'Product models', 'CRUD'],
    ['series', 'Product series', 'CRUD'],
    ['inventory_minimums', 'Minimum and maximum stock rules', 'CRUD'],
    ['serial_numbers', 'Inventory serial numbers', 'CRUD'],
    ['stock_takes', 'Stock take documents', 'CRUD'],
    ['stock_take_details', 'Stock take lines', 'CRUD'],
    ['packing_lists', 'Packing list documents', 'CRUD'],
    ['packing_list_details', 'Packing list lines', 'CRUD'],
    ['order_returns', 'Order return documents', 'CRUD'],
    ['return_details', 'Order return lines', 'CRUD'],
    ['approval_settings', 'Approval workflows', 'CRUD'],
    ['approval_details', 'Approval workflow state', 'CRUD'],
    ['warehouse_custom_fields', 'Warehouse custom-field configuration', 'CRUD'],
    ['staff_warehouses', 'Staff-to-warehouse assignments', 'CRUD'],
    ['activity_logs', 'Warehouse activity log', 'CRUD'],
    ['delivery_activity_logs', 'Delivery activity log', 'CRUD'],
    ['transaction_details', 'Inventory transaction details', 'CRUD'],
    ['delivery_order_links', 'Delivery-to-order relations', 'CRUD'],
    ['item_relations', 'Warehouse item relations', 'CRUD'],
    ['omni_shipments', 'Omnichannel shipment records', 'CRUD']
  ];

  var operations = [
    ['GET', '/api/warehouse/:resource/:id?', 'List or retrieve records', 'List with page/per_page, from/to, and any real resource field as an exact-match filter, or append an ID.'],
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
      '<h2 class="sub">Endpoints</h2><div class="table-responsive-wrapper"><table class="table table-hover"><thead><tr><th>Resource</th><th>Collection endpoint</th><th>Record endpoint</th><th>Description</th><th>Methods</th></tr></thead><tbody>' +
      '<tr><td><code>catalog</code></td><td><code>/api/warehouse</code></td><td>&mdash;</td><td>Discover resources, methods and database fields</td><td>GET only</td></tr>' +
      resources.map(function (row) { return '<tr><td><code>' + escapeHtml(row[0]) + '</code></td><td><code>/api/warehouse/' + escapeHtml(row[0]) + '</code></td><td><code>/api/warehouse/' + escapeHtml(row[0]) + '/:id</code></td><td>' + escapeHtml(row[1]) + '</td><td>' + escapeHtml(row[2]) + '</td></tr>'; }).join('') +
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

  // Surface asset/runtime failures instead of leaving a blank page.
  window.addEventListener('error', function (event) {
    var fallback = document.getElementById('documentation-loading');
    if (fallback) {
      fallback.querySelector('p').textContent = 'The interactive documentation could not be loaded: ' + (event.message || 'JavaScript asset error');
    }
  });

  if (!install()) {
    var observer = new MutationObserver(function () {
      if (install()) observer.disconnect();
    });
    observer.observe(document.documentElement, {childList: true, subtree: true});
  }
}());
