<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>

<!-- EARLY SHIM: προσπάθησε να ξεκλειδώσεις το window.$ πριν τρέξουν άλλα scripts του view -->
<script>
// 🔓 Προσπάθεια "ξεκλειδώματος" του window.$ αν κάποιο script το έκανε read-only ή getter
(function() {
  try {
    var d = Object.getOwnPropertyDescriptor(window, '$');
    if (d && d.configurable && (d.writable === false || typeof d.get === 'function')) {
      delete window.$; // αφήνουμε το core να το ξαναορίσει
    }
  } catch (e) {}
  try {
    var desc = Object.getOwnPropertyDescriptor(window, '$');
    console.log('[pre-view] window.$ descriptor:', desc);
  } catch (e) {}
})();
</script>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel_s">
                    <div class="panel-body">

                        <h4 class="no-margin">
                            <?php echo isset($receipt) ? 'Edit Receipt' : 'New Receipt'; ?>
                        </h4>
						<?php if(isset($receipt)){?>
						<div class="_buttons tw-mb-2 tw-flex tw-items-center tw-gap-1">
							<a href="<?php echo admin_url('paymentsonaccount/create_receipt'); ?>" class="btn btn-primary">
								Create New Receipt
							</a>
						</div>
						<?php } ?>
                        <hr class="hr-panel-heading" />

                        <?php echo form_open(); ?>

                        <?php
                        echo render_select(
                            'client_id',
                            $clients,
                            ['userid', ['company']],
                            'Client',
                            isset($receipt) ? $receipt->client_id : '',
                            ['data-live-search' => 'true', 'required' => true, 'class' => 'selectpicker'] // βεβαιώσου ότι έχει selectpicker
                        );
                        ?>
                        <div class="form-group">
                            <label>Unpaid Invoices</label>
                            <div id="unpaid-invoices-wrapper">
                                <p class="text-muted">Select a client to load unpaid invoices.</p>
                            </div>
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="on_account" id="on_account" value="1">
                                <label for="on_account">On Account (do not allocate to specific invoices)</label>
                            </div>
                        </div>
						<?php
						  // από το controller: $receipt_prefix, $number_padding
						  $prefix  = isset($receipt_prefix) ? $receipt_prefix : get_option('receipt_number_prefix');
						  $padHint = isset($number_padding) ? (int)$number_padding : (int)(get_option('number_padding') ?: 4);
						?>
						<div class="form-group">
						  <label for="manual_receipt_digits">
							Manual Receipt No. (optional)
						  </label>
						  <div class="input-group">
							<span class="input-group-addon" style="min-width:90px">
							  <?php echo html_escape($prefix); ?>
							</span>
							<input
							  type="text"
							  id="manual_receipt_digits"
							  name="manual_receipt_digits"
							  class="form-control"
							  pattern="^\d+$"
							  inputmode="numeric"
							  placeholder="<?php echo str_pad(' ', max(1,$padHint), '0'); ?>  (digits only)"
							  title="Enter only the numeric part. The prefix will be added automatically."
							  value="<?php echo html_escape(set_value('manual_receipt_digits','')); ?>"
							/>
						  </div>
						  <small class="text-muted">
							Leave empty for auto-number. Padding: <?php echo (int)$padHint; ?> digits (e.g. <?php echo html_escape($prefix); echo str_pad('123', max(1,$padHint), '0', STR_PAD_LEFT); ?>).
						  </small>
						</div>

                        <?php echo render_input('amount', 'Amount', isset($receipt) ? $receipt->total_amount : '', 'number', ['required' => true, 'step' => '0.01']); ?>

						<?php echo render_date_input('payment_date', 'Payment Date', isset($receipt) ? _d($receipt->payment_date) : _d(date('Y-m-d'))); ?>

                        <div class="form-group">
                            <label for="payment_mode">Payment Mode</label>
                            <select name="payment_mode" class="form-control selectpicker" data-live-search="true" required>
                                <?php foreach ($paymentmodes as $mode) { ?>
                                    <option value="<?php echo $mode['id']; ?>"
                                        <?php echo (isset($receipt) && $receipt->payment_mode == $mode['id']) ? 'selected' : ''; ?>>
                                        <?php echo $mode['name']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <?php echo render_input('payment_method', 'Payment Method (optional)', isset($receipt) ? $receipt->payment_method : ''); ?>

                        <?php echo render_input('transaction_id', 'Transaction ID (optional)', isset($receipt) ? $receipt->transaction_id : ''); ?>

                        <?php echo render_textarea('note', 'Note', isset($receipt) ? $receipt->note : ''); ?>

                        <div class="form-group">
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="do_not_send_email" id="do_not_send_email" value="1">
                                <label for="do_not_send_email">
                                    Do not send Receipt to Customer
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <?php echo isset($receipt) ? 'Save Changes' : 'Create Receipt'; ?>
                        </button>

                        <?php echo form_close(); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<!-- STABILIZER: τρέχει μετά το core για να φτιάξει $/jQuery, bootstrap-select, Dropzone guard κ.λπ. -->
<script>
(function stabilizeAfterCore() {
  try {
    // 1) Βεβαιώσου ότι $ δείχνει στο jQuery
    if (window.jQuery && !window.$) {
      window.$ = window.jQuery;
    }

    // 2) Δώσε χειροκίνητα BootstrapVersion στο bootstrap-select αν δεν έχει πιαστεί
    if (window.jQuery && window.jQuery.fn && window.jQuery.fn.selectpicker) {
      var SP = window.jQuery.fn.selectpicker;
      if (!SP.Constructor || !SP.Constructor.BootstrapVersion) {
        SP.Constructor = SP.Constructor || {};
        SP.Constructor.BootstrapVersion = '4'; // Perfex 3.3.x ok
      }
    }

    // 3) Απόφυγε σπασίματα αν λείπει Dropzone (το main.min.js μπορεί να καλεί Dropzone)
    if (!window.Dropzone) {
      console.warn('[guard] Dropzone is not defined, skipping any custom Dropzone inits.');
    }
  } catch (e) {
    console.warn('[stabilizeAfterCore] Error:', e);
  }
})();
</script>

<script>
  // Το δικό σου JS – δουλεύει μετά τον stabilizer, με ασφαλή init για selectpicker.
  (function() {
    // Αν υπάρχει selectpicker, κάνε re-init για να φτιαχτούν τα selects
    if (window.jQuery && window.jQuery.fn && window.jQuery.fn.selectpicker) {
      try {
        $('.selectpicker').selectpicker('render');
      } catch (e) {
        try { $('.selectpicker').selectpicker(); } catch (e2) {}
      }
    }

    // ======= Το υπάρχον σου logic για invoices =======
    $(function () {
        var invoiceData = {}; // Για αποθήκευση των ποσών
        function updateAmountField() {
            var total = 0.00;
            $('input[name="invoice_ids[]"]:checked').each(function () {
                var invoiceId = $(this).val();
                if (!isNaN(invoiceData[invoiceId])) {
                    total += invoiceData[invoiceId];
                }
            });

            if (total > 0) {
                $('input[name="amount"]').val(parseFloat(total).toFixed(2));
            }
        }

        $('select[name="client_id"]').on('change', function () {
            var clientId = $(this).val();

            $('#unpaid-invoices-wrapper').html('<p>Loading...</p>');
            invoiceData = {}; // reset

            $.post(admin_url + 'paymentsonaccount/get_unpaid_invoices_ajax', {client_id: clientId}, function (response) {
                console.log(response);
                var html = '';
                var open_balance = 0.00;
				var inv_formatted_number = "";
                if (response.length > 0) {
                    html += '<ul class="list-unstyled">';
                    response.forEach(function (inv) {
                        if(inv.clientid == clientId){
							if(inv.formatted_number == null){
								inv_formatted_number = inv.prefix+inv.number;
							}
							else{
								inv_formatted_number=inv.formatted_number;
							}
                            html += '<li>';
                            html += '<label>';
                            html += '<input type="checkbox" name="invoice_ids[]" value="' + inv.id + '"> ';
                            html += 'Invoice #' + inv_formatted_number + ' - ' + inv.total_left_to_pay + ' EUR';
                            html += '</label>';
                            html += '</li>';
                            var amount = parseFloat(inv.total_left_to_pay);
                            if (!isNaN(amount)) {
                                invoiceData[inv.id] = amount;
                                open_balance += amount;
                            }
                        }
                    });
                    html += '</ul>';
                } else {
                    html = '<p>No unpaid invoices found.</p>';
                }
                $('input[name="amount"]').val(parseFloat(open_balance).toFixed(2));

                $('#unpaid-invoices-wrapper').html(html);
                $('input[name="invoice_ids[]"]').on('change', updateAmountField);
            }, 'json');
        });
    });
  })();
</script>

<!-- Διαγνωστικά (προαιρετικά, κράτα τα όσο κάνεις debug) -->
<script>
console.log('[post-view] descriptor window.$', Object.getOwnPropertyDescriptor(window,'$'));
console.log('[post-view] scripts order:', [].map.call(document.scripts, s => s.src).filter(Boolean));
</script>
