<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="modal fade" id="sendReceiptEmailModal" tabindex="-1" role="dialog" aria-labelledby="sendReceiptEmailModalLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Send Receipt to Customer</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo _l('close'); ?>"></button>
      </div>

      <div class="modal-body">
        <form id="sendReceiptEmailForm" action="<?= admin_url('paymentsonaccount/send_receipt_email_manual/'.$receipt->id); ?>" method="post">
          <!-- CSRF -->
          <input type="hidden"
                 name="<?php echo $this->security->get_csrf_token_name(); ?>"
                 value="<?php echo $this->security->get_csrf_hash(); ?>">

          <div class="form-group">
            <label for="recipients">Recipients</label>
            <select id="recipients" class="form-control selectpicker"
                    data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>"
                    data-live-search="true" multiple name="recipients[]">
              <?php
                $coreContacts  = is_iterable($contacts) ? $contacts : [];
                $pmcContacts   = isset($contacts_plus) && is_array($contacts_plus) ? $contacts_plus : [];

                // Core contacts (Perfex)
                if (!empty($coreContacts)) {
                  echo '<optgroup label="Core">';
                  foreach ($coreContacts as $c) {
                    if (empty($c['email'])) { continue; }
                    $label = trim(($c['firstname'] ?? '').' '.($c['lastname'] ?? ''));
                    $label = trim($label) !== '' ? $label : ($c['email']);
                    $optionText = $label.' <'.$c['email'].'>';
                    echo '<option value="'.htmlspecialchars($c['email'], ENT_QUOTES, 'UTF-8').'" selected>'
                         . htmlspecialchars($optionText, ENT_QUOTES, 'UTF-8')
                         . '</option>';
                  }
                  echo '</optgroup>';
                }

                // Contact+ contacts ΜΟΝΟ για τον συγκεκριμένο client (ή άδειο αν δεν υπάρχουν)
                if (!empty($pmcContacts)) {
                  echo '<optgroup label="Contact+">';
                  foreach ($pmcContacts as $p) {
                    $email = trim((string)($p['email'] ?? ''));
                    if ($email === '') { continue; }
                    $fname = (string)($p['firstname'] ?? '');
                    $lname = (string)($p['lastname'] ?? '');
                    $label = trim(trim($fname.' '.$lname));
                    if ($label === '') { $label = $email; }
                    $isPrimary = !empty($p['is_primary']) ? 1 : 0;

                    $optionText = $label.' <'.$email.'>'.($isPrimary ? ' (primary)' : '');
                    echo '<option value="'.htmlspecialchars($email, ENT_QUOTES, 'UTF-8').'" data-primary="'.$isPrimary.'">'
                        . htmlspecialchars($optionText, ENT_QUOTES, 'UTF-8')
                        . '</option>';
                  }
                  echo '</optgroup>';
                }
              ?>
            </select>
          </div>

          <div class="form-group">
            <label for="cc_emails">CC emails</label>
            <input type="text" name="cc_emails" id="cc_emails" class="form-control" placeholder="separate emails by comma">
          </div>

          <div class="form-group">
            <label for="email_body"><?php echo _l('email_template'); ?></label>
            <?php if (!empty($email_template)) { ?>
              <textarea name="email_body" id="email_body" class="form-control" rows="8"><?php
                 echo htmlspecialchars($email_template ? $email_template->message : '', ENT_QUOTES, 'UTF-8');
              ?></textarea>
            <?php } else { ?>
              <div class="alert alert-warning">No email template found.</div>
            <?php } ?>
          </div>

        </form>
      </div>

      <div class="modal-footer">
        <button type="button" id="btn-send-receipt" class="btn btn-primary">
          <?php echo _l('send'); ?>
        </button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
          <?php echo _l('close'); ?>
        </button>
      </div>

    </div>
  </div>
</div>
