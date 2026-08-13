<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>
<div id="wrapper" style="min-height: 902px; height: 100%;">
  <div class="content">
    <div class="tw-max-w-4xl tw-mx-auto">
		<div class="row">
			<div class="col-md-6 col-sm-6">
				<h4 class="tw-mt-0 tw-font-bold tw-text-lg tw-text-neutral-700">
					<?php echo _l('Payment Receipt ' . $receipt->receipt_number); ?>
				</h4>
			</div>
			<div class="col-sm-6 text-right pull-right">
				<?php if(isset($receipt)){?>
					<div class="_buttons tw-mb-2 tw-flex tw-items-center tw-gap-1 pull-right">
						<a href="<?php echo admin_url('paymentsonaccount/create_receipt'); ?>" class="btn btn-primary">
							Create New Receipt
						</a>
					</div>
				<?php } ?>
			</div>
		</div>
      <div class="horizontal-scrollable-tabs">
        <div class="scroller arrow-left" style="display:none;"><i class="fa fa-angle-left"></i></div>
        <div class="scroller arrow-right" style="display:none;"><i class="fa fa-angle-right"></i></div>
        <div class="horizontal-tabs">
          <ul class="nav nav-tabs nav-tabs-horizontal nav-tabs-segmented tw-mb-3" role="tablist">
            <li role="presentation" class="active">
              <a href="#receipt_tab" aria-controls="receipt_tab" role="tab" data-toggle="tab" aria-expanded="true">Payment Receipt</a>
            </li>
            <li role="presentation">
              <a href="#payment_tab" aria-controls="payment_tab" role="tab" data-toggle="tab">Payment</a>
            </li>
          </ul>
        </div>
      </div>

      <div class="tab-content">
        <!-- TAB: RECEIPT -->
        <div class="tab-pane fade in active" id="receipt_tab">
          <div class="panel_s">
            <div class="panel-body">
              <div class="tw-flex tw-justify-end tw-mb-6">
                <div class="tw-self-start">
                  <div class="btn-group">
                    <!-- Open Apply modal -->
                    <a href="#" id="open-apply-modal" class="btn btn-default" title="<?= _l('apply_to_invoices'); ?>">
                      <i class="fa fa-link"></i>
                    </a>

                    <a href="#" onclick="$('#sendReceiptEmailModal').modal('show'); return false;" class="payment-send-to-client btn-with-tooltip btn btn-default">
                      <i class="fa-regular fa-envelope"></i>
                    </a>

                    <a href="#" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="fa-regular fa-file-pdf"></i>
                      <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-right">
                      <li class="hidden-xs">
                        <a href="<?php echo admin_url('paymentsonaccount/receipt_pdf/' . $receipt->id . '?output_type=I'); ?>">View PDF</a>
                      </li>
                      <li class="hidden-xs">
                        <a href="<?php echo admin_url('paymentsonaccount/receipt_pdf/' . $receipt->id . '?output=I'); ?>" target="_blank">View PDF in New Tab</a>
                      </li>
                      <li><a href="<?php echo admin_url('paymentsonaccount/receipt_pdf/' . $receipt->id); ?>">Download</a></li>
                      <li><a href="<?php echo admin_url('paymentsonaccount/receipt_pdf/' . $receipt->id . '?print=true'); ?>" target="_blank">Print</a></li>
                    </ul>
                  </div>

                  <a href="<?= admin_url('paymentsonaccount/delete_receipt/'.$receipt->id); ?>"
                     class="btn btn-danger _delete" title="<?= _l('delete'); ?>">
                    <i class="fa-regular fa-trash-can"></i>
                  </a>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 col-sm-6">
                  <address>
                    <b style="color:black" class="company-name-formatted"><?php echo get_option('invoice_company_name'); ?></b><br>
                    <?php echo get_option('invoice_company_address'); ?><br>
                    <?php echo get_option('company_vat'); ?>
                  </address>
                </div>
                <div class="col-sm-6 text-right">
                  <address>
                    <a href="<?php echo admin_url('clients/client/'.$client_id);?>" target="_blank"><b><?php echo $client_name; ?></b></a><br>
                    <?php echo $client->address; ?><br>
                    <?php echo $client->city; ?><br>
                    <?php echo $client->phonenumber; ?>
                  </address>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12"><h3 class="text-uppercase tw-font-bold tw-text-neutral-700">Payment Receipt</h3></div>
                <div class="col-md-12 tw-mt-4">
                  <div class="row">
                    <div class="col-md-6">
                      <p class="tw-text-neutral-600 tw-font-medium">
                        Payment Date:<span class="pull-right"><?php echo _d($receipt->payment_date); ?></span>
                      </p>
                      <hr class="tw-my-2">
                      <p class="tw-text-neutral-600 tw-font-medium">
                        Payment Mode:<span class="pull-right"><?php echo $payment_mode_name; ?></span>
                      </p>
                      <hr class="tw-my-2">
                      <p class="tw-text-neutral-600 tw-font-medium">
                        Transaction ID:<span class="pull-right"><?php echo $receipt->transaction_id ?: '-'; ?></span>
                      </p>
                    </div>
                    <div class="clearfix"></div>
                    <div class="col-md-6">
                      <div class="tw-py-3 tw-px-4 tw-rounded-lg tw-bg-neutral-100 tw-flex tw-flex-col tw-my-4">
                        <span class="tw-font-medium">Total Amount</span>
                        <span class="tw-font-bold"><?php echo app_format_money($receipt->total_amount, $client_currency); ?></span>
                      </div>
                    </div>
                  </div>
                </div>

                <?php
                // ===== Payment For table (links + delete action) =====
                $bridge_rows = $this->db->where('receipt_id', $receipt->id)
                                         ->get(db_prefix().'receipt_invoice_applications')->result_array();
                $bridge_map = []; // invoice_id => ['payment_id'=>X,'amount'=>Y]
                foreach ($bridge_rows as $br) {
                  $iid = (int)$br['invoice_id'];
                  $bridge_map[$iid] = [
                    'payment_id' => isset($br['payment_record_id']) ? (int)$br['payment_record_id'] : null,
                    'amount'     => isset($br['amount']) ? (float)$br['amount'] : null,
                  ];
                }

                if (!empty($receipt->invoices_applied)) {
                  $raw = json_decode($receipt->invoices_applied, true);
                  $applied_items = [];
                  if (is_array($raw)) {
                    foreach ($raw as $item) {
                      if (is_numeric($item)) {
                        $applied_items[] = ['invoice_id' => (int)$item, 'amount' => null];
                      } elseif (is_array($item)) {
                        $iid = isset($item['invoice_id']) ? (int)$item['invoice_id'] : ( (isset($item['id']) ? (int)$item['id'] : 0) );
                        $amt = isset($item['amount']) ? (float)$item['amount'] : null;
                        if ($iid > 0) { $applied_items[] = ['invoice_id'=>$iid, 'amount'=>$amt]; }
                      }
                    }
                  }

                  if (!empty($applied_items)) { ?>
                    <div class="col-md-12 tw-mt-4">
                      <h4 class="tw-font-semibold tw-text-base tw-text-neutral-800">Payment For</h4>
                      <div class="table-responsive">
                        <table class="table table-bordered !tw-mt-0" id="receipt-applied-table">
                          <thead>
                            <tr>
                              <th>Invoice Number</th>
                              <th>Invoice Date</th>
                              <th>Invoice Amount</th>
                              <th>Payment Amount</th>
                              <th style="width:90px;" class="text-center">Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                          <?php
                          foreach ($applied_items as $rowi) {
                            $invoice = $this->invoices_model->get($rowi['invoice_id']);
                            if ($invoice) {
                              $invUrl = admin_url('invoices#'.$invoice->id);
                              $bridge = $bridge_map[$invoice->id] ?? ['payment_id'=>null,'amount'=>null];
                              $paymentAmt = $bridge['amount'] ?? ($rowi['amount'] ?? $receipt->total_amount);
                              $paymentId  = $bridge['payment_id']; ?>
                              <tr data-invoice-id="<?= (int)$invoice->id; ?>">
                                <td>
                                  <a href="<?= $invUrl; ?>" target="_blank" rel="noopener">
                                    <?= format_invoice_number($invoice->id); ?>
                                  </a>
                                </td>
                                <td><?php echo _d($invoice->date); ?></td>
                                <td><?php echo app_format_money($invoice->total, $invoice->currency); ?></td>
                                <td><?php echo app_format_money($paymentAmt, $client_currency); ?></td>
                                <td class="text-center">
                                  <?php if (!empty($paymentId)) { ?>
                                    <button
                                      type="button"
                                      class="btn btn-danger btn-xs js-delete-applied-payment"
                                      data-payment-id="<?= (int)$paymentId; ?>"
                                      data-invoice-id="<?= (int)$invoice->id; ?>"
                                      title="<?= _l('delete'); ?>">
                                      <i class="fa fa-trash"></i>
                                    </button>
                                  <?php } else { ?>
                                    <span class="text-muted">—</span>
                                  <?php } ?>
                                </td>
                              </tr>
                            <?php }
                          } ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  <?php }
                } ?>
                <!-- ================================================ -->
              </div> <!-- /row -->
            </div>
          </div>
        </div>

        <!-- TAB: PAYMENT -->
        <div class="tab-pane fade" id="payment_tab">
          <div class="panel_s">
            <div class="panel-body">
              <?php echo form_open(); ?>
              <?php echo render_input('amount', 'Amount Received', $receipt->total_amount, 'number', ['step' => '0.01']); ?>
              <?php echo render_date_input('payment_date', 'Payment Date', $receipt->payment_date); ?>
              <?php echo render_select('payment_mode', $paymentmodes, ['id', 'name'], 'Payment Mode', $receipt->payment_mode); ?>
              <?php echo render_input('payment_method', 'Payment Method', $receipt->payment_method); ?>
              <?php echo render_input('transaction_id', 'Transaction ID', $receipt->transaction_id); ?>
              <?php echo render_textarea('note', 'Note', $receipt->note); ?>
              <button type="submit" class="btn btn-primary"><?php echo _l('Save'); ?></button>
              <?php echo form_close(); ?>
            </div>
          </div>
        </div>
      </div> <!-- /tab-content -->
    </div>
  </div>
</div>

<?php $this->load->view('send_receipt_email_modal'); ?>

<!-- APPLY TO INVOICES MODAL -->
<div class="modal fade" id="applyToInvoicesModal" tabindex="-1" role="dialog" aria-labelledby="applyToInvoicesModalLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <?= form_open('', ['id' => 'apply-invoices-form', 'onsubmit' => 'return false;']); ?>
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="<?= _l('close'); ?>"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="applyToInvoicesModalLabel"><?= _l('apply_to_invoices'); ?></h4>
      </div>
      <div class="modal-body">
        <div class="tw-flex tw-justify-between tw-mb-3">
          <div class="checkbox">
            <input type="checkbox" id="on_account_apply">
            <label for="on_account_apply"><?= _l('on_account'); ?></label>
          </div>
          <div>
            <span class="tw-mr-3"><?= _l('total_amount'); ?>: <b><?= app_format_money($receipt->total_amount, $client_currency); ?></b></span>
            <span><?= _l('remaining'); ?>: <b id="apply-remaining"><?= app_format_money($receipt->total_amount, $client_currency); ?></b></span>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-bordered" id="apply-invoices-table">
            <thead>
              <tr>
                <th style="width:36px"></th>
                <th>Invoice</th>
                <th>Date</th>
                <th>Due</th>
                <th class="text-right">Total</th>
              </tr>
            </thead>
            <tbody id="apply-invoices-body">
              <tr><td colspan="5" class="text-center text-muted">Loading…</td></tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close'); ?></button>
        <button type="button" id="btn-apply-save" class="btn btn-primary"><?= _l('apply'); ?></button>
      </div>
      <?= form_close(); ?>
    </div>
  </div>
</div>

<?php init_tail(); ?>

<!-- CSRF bootstrap: ensures both POST body and header carry token -->
<script>
(function(w){
  function bootCsrf(){
    if (!w.jQuery) return setTimeout(bootCsrf, 50);

    var name = '<?php echo $this->security->get_csrf_token_name(); ?>';
    var hash = '<?php echo $this->security->get_csrf_hash(); ?>';

    w.csrfData = {
      formatted: (function(o){ o[name]=hash; return o; })({}),
      token_name: name,
      hash: hash
    };
    // Compat with older scripts
    w.csrf_token_name   = w.csrfData.token_name;
    w.csrf_jquery_token = w.csrfData.formatted;

    $.ajaxSetup({
      data: w.csrfData.formatted,
      beforeSend: function(xhr){
        xhr.setRequestHeader('X-CSRF-TOKEN', w.csrfData.hash);
      }
    });

    $(document).ajaxError(function(_e, xhr){
      if (xhr && xhr.status === 419) {
        alert_float('warning', 'Page expired, refresh the page and try again.');
      }
    });
  }
  bootCsrf();
})(window);
</script>

<script>
(function($){
  /* =========================
     SEND RECEIPT (Modal) — Contact+ aware
     ========================= */
  var contactPlusPrimed = false; // run primary preselect once

  // helper: returns unique, case-insensitive list
  function uniqueEmails(list) {
    var seen = {};
    var out = [];
    for (var i=0;i<list.length;i++){
      var v = (list[i] || '').trim();
      if (!v) continue;
      var k = v.toLowerCase();
      if (!seen[k]) { seen[k]=1; out.push(v); }
    }
    return out;
  }

  // When modal opens:
  $(document).on('shown.bs.modal', '#sendReceiptEmailModal', function(){
    var $sel = $('#recipients');

    // refresh selectpicker (in case modal partial loaded after page)
    if ($.fn.selectpicker) { $sel.selectpicker('refresh'); }

    // Auto-select Contact+ primaries if visible and not already selected (once)
    if (!contactPlusPrimed) {
      contactPlusPrimed = true;

      var values = $sel.val() || [];
      var current = {};
      (values || []).forEach(function(v){ current[(v||'').toLowerCase()] = true; });

      // Heuristic: option text contains "(primary)" OR attribute data-primary="1" (if present)
      var toAdd = [];
      $sel.find('optgroup[label="Contact+"] option').each(function(){
        var $opt = $(this);
        var txt = ($opt.text() || '').toLowerCase();
        var isPrimary = ($opt.data('primary') === 1) || txt.indexOf('(primary)') !== -1;
        if (!isPrimary) return;

        var email = ($opt.val() || '').trim();
        if (!email) return;

        if (!current[email.toLowerCase()]) {
          toAdd.push(email);
          current[email.toLowerCase()] = true;
        }
      });

      if (toAdd.length) {
        var next = (values || []).concat(toAdd);
        if ($.fn.selectpicker) {
          $sel.selectpicker('val', uniqueEmails(next));
        } else {
          $sel.val(uniqueEmails(next));
        }
      }
    }
  });

  // SEND (AJAX) with dedupe before serialize
  $('#btn-send-receipt').on('click', function(e){
    e.preventDefault();
    var $btn  = $(this);
    var $form = $('#sendReceiptEmailForm');
    var $sel  = $('#recipients');

    // Make TinyMCE push HTML back into the <textarea>
    if (window.tinymce && typeof tinymce.triggerSave === 'function') {
      tinymce.triggerSave();
    }

    // Dedupe recipients (avoid double sends if same email in Core & Contact+)
    var selected = ($sel.val() || []);
    var cleaned  = uniqueEmails(selected);
    if ($.fn.selectpicker) { $sel.selectpicker('val', cleaned); } else { $sel.val(cleaned); }

    // Optional quick email validation (comment out if not desired)
    // var invalid = cleaned.filter(function(x){ return !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(x); });
    // if (invalid.length) { alert_float('warning', 'Invalid emails: ' + invalid.join(', ')); return; }

    var oldHtml = $btn.html();
    $btn.prop('disabled', true).html('<?= _l('wait_text'); ?>');

    $.ajax({
      url: $form.attr('action'),
      method: 'POST',
      data: $form.serialize(),
      dataType: 'json',
      beforeSend: function(xhr){
        if (window.csrfData && csrfData.hash) xhr.setRequestHeader('X-CSRF-TOKEN', csrfData.hash);
      }
    }).done(function(res){
      if (res && res.success) {
        alert_float('success', '<?= _l('email_sent_successfully'); ?>');
        $('#sendReceiptEmailModal').modal('hide');
      } else {
        alert_float('danger', (res && res.message) ? res.message : 'Failed to send email');
      }
    }).fail(function(xhr){
      alert_float('danger', xhr.responseText || 'Failed to send email');
    }).always(function(){
      $btn.prop('disabled', false).html(oldHtml);
    });
  });

  /* =========================
     APPLY TO INVOICES
     ========================= */
  var adminUrl   = typeof admin_url !== 'undefined' ? admin_url : '<?= admin_url(); ?>';
  var receiptId  = <?= (int)$receipt->id; ?>;
  var clientId   = <?= (int)$client_id; ?>;

  // PRE_APPLIED IDs for pre-check
  var PRE_APPLIED = <?php
    $pre = [];
    if (!empty($receipt->invoices_applied)) {
      $tmp = json_decode($receipt->invoices_applied, true);
      if (is_array($tmp)) {
        foreach ($tmp as $item) {
          if (is_numeric($item)) {
            $pre[] = (int)$item;
          } elseif (is_array($item)) {
            if (isset($item['invoice_id']) && is_numeric($item['invoice_id'])) $pre[] = (int)$item['invoice_id'];
            elseif (isset($item['id']) && is_numeric($item['id'])) $pre[] = (int)$item['id'];
          }
        }
        $pre = array_values(array_unique(array_filter($pre, function($v){return $v>0;})));
      }
    }
    echo json_encode($pre);
  ?>;

  // Open modal
  $(document).on('click', '#open-apply-modal', function(e){
    e.preventDefault();
    $('#applyToInvoicesModal').modal('show');
  });

  // Load unpaid invoices on modal open
  $('#applyToInvoicesModal').on('shown.bs.modal', function(){
    var $tbody = $('#apply-invoices-body');
    $tbody.html('<tr><td colspan="5" class="text-center text-muted">Loading…</td></tr>');

    $.ajax({
      url: adminUrl + 'paymentsonaccount/get_unpaid_invoices_ajax',
      type: 'POST',
      dataType: 'json',
      data: { client_id: clientId },
      success: function(rows){
        if (!Array.isArray(rows)) {
          try { rows = JSON.parse(rows) || []; } catch(e){ rows = []; }
        }

        if (!rows.length){
          $tbody.html('<tr><td colspan="5" class="text-center text-muted">No open invoices.</td></tr>');
          return;
        }

        var html = '';
        rows.forEach(function(inv){
          if (String(inv.clientid) !== String(clientId)) return;

          var invNo = (typeof format_invoice_number === 'function')
            ? format_invoice_number(inv.id)
            : ('#' + (inv.formatted_number || (inv.prefix||'') + (inv.number||inv.id)));

          var date   = inv.date    || '';
          var dued   = inv.duedate || '';
          var totalF = (typeof app !== 'undefined' && app.format && app.format.money)
            ? app.format.money(inv.total_left_to_pay || inv.total, inv.currency)
            : (Number(inv.total_left_to_pay || inv.total).toFixed(2));

          var checked = PRE_APPLIED.indexOf(parseInt(inv.id,10)) !== -1 ? ' checked' : '';

          html += '<tr>' +
                    '<td><input type="checkbox" class="apply-invoice" value="'+inv.id+'"'+checked+'></td>' +
                    '<td>'+invNo+'</td>' +
                    '<td>'+date+'</td>' +
                    '<td>'+dued+'</td>' +
                    '<td class="text-right">'+totalF+'</td>' +
                  '</tr>';
        });

        if (!html) {
          $tbody.html('<tr><td colspan="5" class="text-center text-muted">No open invoices.</td></tr>');
        } else {
          $tbody.html(html);
        }
      },
      error: function(){
        $tbody.html('<tr><td colspan="5" class="text-danger text-center">Error loading invoices.</td></tr>');
      }
    });
  });

  // APPLY selected invoices
  $(document).on('click', '#btn-apply-save', function(){
    var ids = [];
    $('#apply-invoices-body .apply-invoice:checked').each(function(){
      ids.push(parseInt(this.value, 10));
    });

    if (!ids.length) {
      alert_float('warning', 'Select at least one invoice.');
      return;
    }

    $.ajax({
      url: adminUrl + 'paymentsonaccount/apply_receipt_invoices/' + receiptId,
      method: 'POST',
      data: { invoice_ids: ids },
      success: function(resp){
        try { resp = JSON.parse(resp); } catch(e){}
        if (resp && resp.success) {
          alert_float('success', 'Applied successfully.');
          $('#applyToInvoicesModal').modal('hide');
          setTimeout(function(){ window.location.reload(); }, 500);
        } else {
          alert_float('danger', (resp && resp.message) ? resp.message : 'Apply failed.');
        }
      },
      error: function(){
        alert_float('danger', 'Server error while applying.');
      }
    });
  });

  // DELETE single applied payment
  $(document).on('click', '.js-delete-applied-payment', function(){
    var $btn = $(this);
    var paymentId = $btn.data('payment-id');
    var invoiceId = $btn.data('invoice-id');

    if (!paymentId) { return; }
    if (!confirm('Delete this payment from core and unlink from this receipt?')) { return; }

    $.ajax({
      url: adminUrl + 'paymentsonaccount/delete_applied_payment',
      method: 'POST',
      dataType: 'json',
      data: {
        receipt_id: <?= (int)$receipt->id; ?>,
        payment_id: paymentId,
        invoice_id: invoiceId
      },
      success: function(resp){
        if (resp && resp.success) {
          alert_float('success', 'Payment deleted.');
          $btn.closest('tr').fadeOut(200, function(){ $(this).remove(); });
        } else {
          alert_float('danger', (resp && resp.message) ? resp.message : 'Failed to delete payment.');
        }
      },
      error: function(){
        alert_float('danger', 'Server error while deleting payment.');
      }
    });
  });

})(jQuery);
</script>

<script>
  // Init TinyMCE only once for this modal textarea
  (function(){
    if (window.tinymce && tinymce.editors && tinymce.editors['email_body']) return;
    if (window.tinymce) {
      tinymce.init({
        selector: '#email_body',
        height: 300,
        menubar: true,
        plugins: 'lists link image table code autoresize',
        toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image | code',
        branding: false
      });
    }
  })();
</script>

<script>
(function($){
  // --- Build whitelist από PHP: core + Contact+ (linked μόνο) ---
  var POA_ALLOWED_RECIPIENTS = (function(){
    var list = [];

    // core contacts που ήρθαν ήδη στο view ως $contacts
    <?php
      $coreEmails = [];
      if (!empty($contacts) && is_iterable($contacts)) {
        foreach ($contacts as $c) {
          if (!empty($c['email'])) { $coreEmails[] = strtolower(trim($c['email'])); }
        }
      }
      echo 'var _core = '.json_encode(array_values(array_unique($coreEmails))).";\n";
    ?>
    list = list.concat(_core);

    // Contact+ που πέρασε ο controller ως $contacts_plus (ΜΟΝΟ συνδεδεμένοι)
    <?php
      $plusEmails = [];
      if (!empty($contacts_plus) && is_iterable($contacts_plus)) {
        foreach ($contacts_plus as $p) {
          if (!empty($p['email'])) { $plusEmails[] = strtolower(trim($p['email'])); }
        }
      }
      echo 'var _plus = '.json_encode(array_values(array_unique($plusEmails))).";\n";
    ?>
    list = list.concat(_plus);

    // de-dup
    var seen = {}; var out = [];
    list.forEach(function(e){ e = (e||'').toLowerCase().trim(); if (e && !seen[e]) { seen[e]=1; out.push(e); } });
    return out;
  })();

  function sanitizeRecipientsSelect(){
    var $sel = $('#sendReceiptEmailForm select[name="recipients[]"]');
    if (!$sel.length) return;

    var allowed = {};
    POA_ALLOWED_RECIPIENTS.forEach(function(e){ allowed[e]=true; });

    // 1) αφαίρεσε options που δεν είναι στη whitelist
    $sel.find('option').each(function(){
      var val = (this.value||'').toLowerCase().trim();
      if (!allowed[val]) { $(this).remove(); }
    });

    // 2) de-dup (ασφάλεια)
    var seen = {};
    $sel.find('option').each(function(){
      var v = (this.value||'').toLowerCase().trim();
      if (seen[v]) { $(this).remove(); }
      else { seen[v]=1; }
    });

    // 3) αν δεν υπάρχει τίποτα, πρόσθεσε ένα disabled hint
    if ($sel.find('option').length === 0) {
      $sel.append($('<option/>', {disabled:true, text:'No linked contacts for this customer'}));
    }

    // refresh bootstrap-select
    if (typeof $sel.selectpicker === 'function') { $sel.selectpicker('refresh'); }
  }

  // Τρέξε κάθε φορά που ανοίγει το modal (και μία φορά στο load)
  $(document).on('shown.bs.modal', '#sendReceiptEmailModal', sanitizeRecipientsSelect);
  $(function(){ sanitizeRecipientsSelect(); });

})(jQuery);
</script>
