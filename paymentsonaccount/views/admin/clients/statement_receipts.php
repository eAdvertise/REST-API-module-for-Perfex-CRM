<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if (!isset($client) || empty($client)) { return; } ?>

<?php
// Φέρε active contacts με δικαίωμα invoices για προεπιλογή
$CI = &get_instance();
$CI->load->model('clients_model');
$contacts = $CI->clients_model->get_contacts($client->userid, ['active' => 1]) ?: [];
$selectedContacts = [];
foreach ($contacts as $c) {
    if (has_contact_permission('invoices', $c['id'])) {
        $selectedContacts[] = $c['id'];
    }
}

// Φέρε το core email template "client-statement"
$CI->load->model('emails_model');
$tpl = $CI->emails_model->get(['slug' => 'client-statement', 'language' => 'english'], 'row');
$tpl_subject = '';
$tpl_body    = '';
$tpl_disabled = false;
$tpl_id = null; $tpl_name = '';

if (is_object($tpl)) {
    $tpl_subject  = (string) ($tpl->subject ?? '');
    $tpl_body     = (string) ($tpl->message ?? '');
    $tpl_disabled = (int)($tpl->active ?? 1) === 0;
    $tpl_id       = $tpl->emailtemplateid ?? null;
    $tpl_name     = $tpl->name ?? 'Client Statement';
}

// Fallbacks
if ($tpl_subject === '') {
    $tpl_subject = 'Account Statement {statement_from} - {statement_to}';
}
if ($tpl_body === '') {
    $tpl_body = "Dear {contact_firstname} {contact_lastname},<br><br>"
              . "Attached is your statement for {statement_from} to {statement_to}.<br><br>"
              . "Balance due: {statement_balance_due}<br><br>"
              . "Kind Regards,<br>{email_signature}";
}
?>

<h4 class="customer-profile-group-heading">
  <?= e(_l('customer_statement_for', get_company_name($client->userid))); ?>
</h4>

<div id="poa-wrap" data-client-id="<?= (int)$client->userid; ?>">
  <div class="row">
    <div class="col-md-4">
      <?php
        // Core period select (χωρίς inline onchange – το χειριζόμαστε από JS)
        $this->load->view('admin/clients/groups/_statement_period_select');
      ?>
    </div>

    <div class="col-md-8 col-xs-12">
      <div class="text-right _buttons pull-right tw-space-x-1">
        <a href="#" id="poa_statement_print"
           class="btn btn-default btn-with-tooltip sm:!tw-px-3"
           data-toggle="tooltip" title="<?= _l('print'); ?>" data-placement="bottom">
          <i class="fa fa-print"></i>
        </a>

        <a href="#" id="poa_statement_pdf"
           class="btn btn-default btn-with-tooltip sm:!tw-px-3"
           data-toggle="tooltip" title="<?= _l('view_pdf'); ?>" data-placement="bottom">
          <i class="fa-regular fa-file-pdf"></i>
        </a>

        <a href="#" class="btn btn-default btn-with-tooltip sm:!tw-px-3"
           data-toggle="modal" data-target="#poa_statement_send_to_client"
           title="<?= _l('send_to_email'); ?>" data-placement="bottom">
          <i class="fa-regular fa-envelope"></i>
        </a>
      </div>
    </div>

    <div class="clearfix"></div>

    <div class="col-md-12 mtop15">
      <div class="row">
        <div class="col-md-12">
          <address class="text-right">
            <?= format_organization_info(); ?>
          </address>
        </div>
        <div class="col-md-12"><hr /></div>

        <div class="col-md-7">
          <address>
            <p class="tw-font-bold"><?= _l('statement_bill_to'); ?>:</p>
            <?= format_customer_info($client, 'statement', 'billing'); ?>
          </address>
        </div>

        <div id="poa-statement-html"></div>
      </div>
	  
    </div>
  </div>
</div>

<!-- Modal: Send Statement -->
<div class="modal fade email-template"
     data-editor-id=".<?= 'tinymce-poa-' . $client->userid; ?>"
     id="poa_statement_send_to_client" tabindex="-1" role="dialog" aria-labelledby="poaLabel">
  <div class="modal-dialog" role="document">
    <?= form_open(admin_url('paymentsonaccount/send_statement_receipts'), ['id' => 'poa_send_statement_form', 'method' => 'post']); ?>
      <input type="hidden" name="customer_id" value="<?= (int)$client->userid; ?>">
      <input type="hidden" name="from" value="">
      <input type="hidden" name="to" value="">

      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
          <h4 class="modal-title" id="poaLabel"><?= _l('account_summary'); ?></h4>
        </div>
        <div class="modal-body">
          <?php
            if ($tpl_disabled && $tpl_id) {
                echo '<div class="alert alert-danger">';
                echo 'The email template <b><a href="' . admin_url('emails/email_template/' . $tpl_id) . '" target="_blank" class="alert-link">' . htmlspecialchars($tpl_name) . '</a></b> is disabled.';
                echo ' Click <a href="' . admin_url('emails/email_template/' . $tpl_id) . '" class="alert-link" target="_blank">here</a> to enable it.';
                echo '</div>';
            }

            if (count($selectedContacts) == 0) {
                echo '<p class="text-danger">' . _l('sending_email_contact_permissions_warning', _l('customer_permission_invoice')) . '</p><hr />';
            }

            echo render_select(
                'send_to[]',
                $contacts,
                ['id', 'email', 'firstname,lastname'],
                'invoice_estimate_sent_to_email',
                $selectedContacts,
                ['multiple' => true],
                [],
                '',
                '',
                false
            );

            echo render_input('cc', 'CC');
          ?>
          <hr />
          <h5 class="bold"><?= _l('invoice_send_to_client_preview_template'); ?></h5>
          <hr />
          <?php
            // ΣΗΜ.: μόνο message περνάμε από εδώ (subject το φτιάχνει ο controller με merge fields)
            echo render_textarea(
                'email_template_custom',
                '',
                $tpl_body,
                [],
                [],
                '',
                'tinymce-' . $client->userid
            );
          ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close'); ?></button>
          <button type="button" class="btn btn-primary" id="poa_send_btn" data-loading-text="<?= _l('wait_text'); ?>">
            <?= _l('send'); ?>
          </button>
        </div>
      </div>
    <?= form_close(); ?>
  </div>
</div>

<?php
// Footers scripts – ΜΕΤΑ το init_tail, για να υπάρχει jQuery/Perfex JS
hooks()->add_action('app_admin_footer', function(){ ?>
<script>
(function(){
  function getPeriod() {
    var val = $('#range').selectpicker('val');
    var p = [];
    if (val != 'period') {
      try { p = JSON.parse(val); } catch(e){ p = []; }
    } else {
      p[0] = $('input[name="period-from"]').val();
      p[1] = $('input[name="period-to"]').val();
    }
    return p;
  }

  window.render_poa_statement = function(){
    var $wrap = $('#poa-wrap');
    var customer_id = parseInt($wrap.data('client-id'), 10) || 0;
    if (!customer_id) { return; }

    var period = getPeriod();
    if (!period[0] || !period[1]) { return; }

    var params = { customer_id: customer_id, from: period[0], to: period[1] };
    var url = buildUrl(admin_url + 'paymentsonaccount/statement_receipts', params);

    $.get(url, function(resp){
      if (resp && resp.html !== undefined) {
        $('#poa-statement-html').html(resp.html);

        var pdfUrl = buildUrl(admin_url + 'paymentsonaccount/statement_receipts_pdf', params);
        $('#poa_statement_pdf').attr('href', pdfUrl).attr('target','_blank');
        $('#poa_statement_print').attr('href', pdfUrl + (pdfUrl.indexOf('?')>-1 ? '&' : '?') + 'print=1')
                                 .attr('target','_blank');
      } else {
        alert_float('danger', 'Invalid response while loading statement.');
      }
    }, 'json').fail(function(xhr){
      alert_float('danger', xhr.responseText || 'Request failed while loading statement.');
    });
  };

  $(function(){
    // 1) αρχικό render
    window.render_poa_statement();

    // 2) αλλαγές period -> re-render
    $(document).on('changed.bs.select', '#range', function(){ window.render_poa_statement(); });
    $(document).on('change', 'input[name="period-from"], input[name="period-to"]', function(){
      window.render_poa_statement();
    });

    // 3) Όταν ανοίγει το modal, γέμισε from/to
    $('#poa_statement_send_to_client').on('show.bs.modal', function(){
      var p = getPeriod();
      $('#poa_send_statement_form input[name="from"]').val(p[0] || '');
      $('#poa_send_statement_form input[name="to"]').val(p[1] || '');
    });

    // 4) Αποστολή με AJAX
    $('#poa_send_btn').on('click', function(e){
      e.preventDefault();
      var $btn  = $(this);
      var $form = $('#poa_send_statement_form');

      var oldHtml = $btn.html();
      $btn.prop('disabled', true).html('<?= _l('wait_text'); ?>');

      $.ajax({
        url: $form.attr('action'),
        method: 'POST',
        data: $form.serialize(),
        dataType: 'json'
      }).done(function(res){
        if (res && res.success) {
          alert_float('success', '<?= _l('email_sent_successfully'); ?>');
          $('#poa_statement_send_to_client').modal('hide');
        } else {
          alert_float('danger', (res && res.message) ? res.message : 'Failed to send email');
        }
      }).fail(function(xhr){
        alert_float('danger', xhr.responseText || 'Failed to send email');
      }).always(function(){
        $btn.prop('disabled', false).html(oldHtml);
      });
    });
  });
})();
</script>
<script>
	tinymce.init({
		selector: '#email_template_custom',
		height: 300,
		menubar: true,
		plugins: 'lists link image table code autoresize',
		toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image | code',
		branding: false
	});
</script>
<?php }); ?>
