<?php defined('BASEPATH') or exit('No direct script access allowed'); 

/**
 * modules/contactsplus/views/contacts/core_contact_modal.php
 * Standalone αντίγραφο του core contact modal, προσαρμοσμένο για χρήση στο Contacts+ tab.
 *
 * ΑΠΑΙΤΕΙ παραμέτρους όταν γίνεται load:
 *  - $customer_id (int)  : ο πελάτης για τον οποίο δημιουργούμε επαφή
 * προαιρετικά:
 *  - $calling_code (string|null)
 */

$contactid = 0;          // add mode
$title      = _l('add_new') . ' ' . _l('contact');
$contact    = null;

// permissions όπως στο core
$customer_permissions = [];
if (function_exists('get_contact_permissions')) {
    // Επιστρέφει πίνακα με id, name, short_name
    $customer_permissions = array_map(function($p){
        // ορισμένα builds θέλουν το 'name' μεταφρασμένο ήδη
        if (!empty($p['name'])) $p['name'] = _l($p['name']);
        return $p;
    }, get_contact_permissions());
}
// default checked permissions (core option)
$default_contact_permissions = @unserialize(get_option('default_contact_permissions'));

// email notifications – ίδια keys με core
$notify_defaults = [
    'invoice_emails'      => 0,
    'estimate_emails'     => 0,
    'credit_note_emails'  => 0,
    'project_emails'      => 0,
    'ticket_emails'       => 0,
    'task_emails'         => 0,
    'contract_emails'     => 0,
];

// calling code (optional)
if (!isset($calling_code)) { $calling_code = ''; }
?>

<!-- Contacts+ • Core-like Contact Modal -->
<div class="modal fade" id="contactsplus_core_contact" tabindex="-1" role="dialog" aria-labelledby="cpContactLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <?php echo form_open(admin_url('clients/form_contact/' . (int)$customer_id), [
          'id' => 'contactsplus-core-contact-form',
          'autocomplete' => 'off'
      ]); ?>
		<?php echo form_hidden('userid', (int)$customer_id); ?>
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <div class="tw-flex">
          <div class="tw-mr-4 tw-flex-shrink-0 tw-relative"></div>
          <div>
            <h4 class="modal-title tw-mb-0"><?php echo $title; ?></h4>
            <p class="tw-mb-0"><?php echo get_company_name($customer_id, true); ?></p>
          </div>
        </div>
      </div>

      <div class="modal-body">
        <div class="row"><div class="col-md-12">
          <?php echo form_hidden('contactid', $contactid); ?>

          <?php echo render_input('firstname', 'client_firstname', ''); ?>
          <?php echo render_input('lastname', 'client_lastname', ''); ?>
          <?php echo render_input('title', 'contact_position', ''); ?>
          <?php echo render_input('email', 'client_email', '', 'email'); ?>

          <?php
            $phValue = $calling_code ?: '';
          ?>
          <?php echo render_input('phonenumber', 'client_phonenumber', $phValue, 'text', ['autocomplete' => 'off']); ?>

          <div class="form-group contact-direction-option">
            <label for="direction"><?php echo _l('document_direction'); ?></label>
            <select class="selectpicker" data-none-selected-text="<?php echo _l('system_default_string'); ?>"
                    data-width="100%" name="direction" id="direction">
              <option value=""></option>
              <option value="ltr">LTR</option>
              <option value="rtl">RTL</option>
            </select>
          </div>

          <?php echo render_custom_fields('contacts', false); ?>

          <!-- fake fields to avoid chrome autofill -->
          <input type="text" class="fake-autofill-field" name="fakeusernameremembered" value="" tabindex="-1"/>
          <input type="password" class="fake-autofill-field" name="fakepasswordremembered" value="" tabindex="-1"/>

          <div class="client_password_set_wrapper">
            <label for="password" class="control-label"><?php echo _l('client_password'); ?></label>
            <div class="input-group">
              <input type="password" class="form-control password" name="password" autocomplete="false">
              <span class="input-group-addon tw-border-l-0">
                <a href="#password" class="show_password" onclick="showPassword('password'); return false;"><i class="fa fa-eye"></i></a>
              </span>
              <span class="input-group-addon">
                <a href="#" class="generate_password" onclick="generatePassword(this);return false;"><i class="fa fa-refresh"></i></a>
              </span>
            </div>
          </div>

          <hr />
          <div class="checkbox checkbox-primary">
            <input type="checkbox" name="is_primary" id="contact_primary">
            <label for="contact_primary"><?php echo _l('contact_primary'); ?></label>
          </div>

          <?php if (is_email_template_active('new-client-created')) { ?>
          <div class="checkbox checkbox-primary">
            <input type="checkbox" name="donotsendwelcomeemail" id="donotsendwelcomeemail">
            <label for="donotsendwelcomeemail"><?php echo _l('client_do_not_send_welcome_email'); ?></label>
          </div>
          <?php } ?>

          <?php if (is_email_template_active('contact-set-password')) { ?>
          <div class="checkbox checkbox-primary">
            <input type="checkbox" name="send_set_password_email" id="send_set_password_email">
            <label for="send_set_password_email"><?php echo _l('client_send_set_password_email'); ?></label>
          </div>
          <?php } ?>

          <hr />
          <p class="bold"><?php echo _l('customer_permissions'); ?></p>
          <p class="text-danger"><?php echo _l('contact_permissions_info'); ?></p>

          <?php foreach ($customer_permissions as $permission) { ?>
            <div class="col-md-6 row">
              <div class="row">
                <div class="col-md-6 mtop10 border-right">
                  <span><?php echo html_escape($permission['name']); ?></span>
                </div>
                <div class="col-md-6 mtop10">
                  <div class="onoffswitch">
                    <input type="checkbox"
                           id="<?php echo $permission['id']; ?>"
                           class="onoffswitch-checkbox"
                           value="<?php echo $permission['id']; ?>"
                           name="permissions[]"
                           <?php echo (is_array($default_contact_permissions) && in_array($permission['id'], $default_contact_permissions)) ? 'checked' : ''; ?>>
                    <label class="onoffswitch-label" for="<?php echo $permission['id']; ?>"></label>
                  </div>
                </div>
              </div>
            </div>
            <div class="clearfix"></div>
          <?php } ?>

          <hr />
          <p class="bold"><?php echo _l('email_notifications'); ?><?php if (is_sms_trigger_active()) { echo '/SMS'; } ?></p>
          <div id="contact_email_notifications">
            <?php
              // helper για render μιας γραμμής on/off
              $renderNotify = function($key, $label, $permId = null) use ($notify_defaults){
                $checked = !empty($notify_defaults[$key]) ? 'checked' : '';
                $pidAttr = $permId ? ' data-perm-id="'.(int)$permId.'"' : '';
                echo '
                <div class="col-md-6 row">
                  <div class="row">
                    <div class="col-md-6 mtop10 border-right">
                      <span>'.html_escape($label).'</span>
                    </div>
                    <div class="col-md-6 mtop10">
                      <div class="onoffswitch">
                        <input type="checkbox" id="'.$key.'"'.$pidAttr.' class="onoffswitch-checkbox" '.$checked.' value="'.$key.'" name="'.$key.'">
                        <label class="onoffswitch-label" for="'.$key.'"></label>
                      </div>
                    </div>
                  </div>
                </div>';
              };
              $renderNotify('invoice_emails',     _l('invoice'),      1);
              $renderNotify('estimate_emails',    _l('estimate'),     2);
              $renderNotify('credit_note_emails', _l('credit_note'),  1);
              $renderNotify('project_emails',     _l('project'),      6);
              $renderNotify('ticket_emails',      _l('tickets'),      5);
            ?>
            <div class="col-md-6 row">
              <div class="row">
                <div class="col-md-6 mtop10 border-right">
                  <span><i class="fa-regular fa-circle-question" data-toggle="tooltip" data-title="<?php echo _l('only_project_tasks'); ?>"></i> <?php echo _l('task'); ?></span>
                </div>
                <div class="col-md-6 mtop10">
                  <div class="onoffswitch">
                    <input type="checkbox" id="task_emails" data-perm-id="6" class="onoffswitch-checkbox" value="task_emails" name="task_emails">
                    <label class="onoffswitch-label" for="task_emails"></label>
                  </div>
                </div>
              </div>
            </div>
            <?php
              $renderNotify('contract_emails',    _l('contract'),     3);
            ?>
          </div>

        </div></div>
        <?php hooks()->do_action('after_contact_modal_content_loaded'); ?>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
        <button type="submit" class="btn btn-primary" data-loading-text="<?php echo _l('wait_text'); ?>" autocomplete="off">
          <?php echo _l('submit'); ?>
        </button>
      </div>

      <?php echo form_close(); ?>
    </div>
  </div>
</div>

<script>
(function($){
  "use strict";
  // Αντιστοίχιση default email notifications με βάση τα permissions (όπως στο core)
  $('#contactsplus_core_contact').on('shown.bs.modal', function(){
    var $form = $('#contactsplus-core-contact-form');
    // χαλάρωσε το required στο email
    var $email = $form.find('input[name="email"]');
    $email.prop('required', false).attr('required', false);
    $form.find('label[for="email"] .text-danger').remove();

    // Εφάρμοσε defaults από checked permissions
    $('#contact_email_notifications [data-perm-id]').each(function(){
      var permId = $(this).data('perm-id');
      var on = $form.find('input[name="permissions[]"][value="'+permId+'"]').prop('checked');
      if (on) { $(this).prop('checked', true); }
    });
  });

  // ΠΡΟΣΟΧΗ: Δεν υπάρχει πλέον submit handler εδώ.
})(jQuery);
</script>
