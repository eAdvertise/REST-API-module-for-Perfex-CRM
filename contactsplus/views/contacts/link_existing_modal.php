<?php 
//modules/contactsplus/views/contacts/link_existing_modal.php
defined('BASEPATH') or exit('No direct script access allowed'); 

// ------- Φόρτωση permissions & email notifications με ασφαλή fallbacks -------

// Permissions από core (αν υπάρχει), αλλιώς safe defaults (7 όπως στο core)
if (function_exists('get_contact_permissions')) {
    $contact_permissions = get_contact_permissions();
} else {
    $contact_permissions = [
        ['id' => 'invoices',   'name' => 'customer_permission_invoice',   'default' => 0],
        ['id' => 'estimates',  'name' => 'customer_permission_estimate',  'default' => 0],
        ['id' => 'contracts',  'name' => 'customer_permission_contract',  'default' => 0],
        ['id' => 'proposals',  'name' => 'customer_permission_proposal',  'default' => 0],
        ['id' => 'support',    'name' => 'customer_permission_support',   'default' => 0],
        ['id' => 'projects',   'name' => 'customer_permission_projects',  'default' => 0],
        ['id' => 'waybills',   'name' => 'customer_permission_waybills',  'default' => 0], // custom στο instance σου
    ];
}

// Email notifications από core (αν υπάρχει), αλλιώς safe defaults (8 όπως στο core)
if (function_exists('get_client_contact_email_templates')) {
    $email_templates = get_client_contact_email_templates();
} else {
    // id => internal key που θα υποβληθεί, name => label, selected_by_default => 0/1
    $email_templates = [
        ['id' => 'invoice',      'name' => _l('invoice'),      'selected_by_default' => 0],
        ['id' => 'estimate',     'name' => _l('estimate'),     'selected_by_default' => 0],
        ['id' => 'credit_note',  'name' => _l('credit_note'),  'selected_by_default' => 0],
        ['id' => 'project',      'name' => _l('project'),      'selected_by_default' => 0],
        ['id' => 'tickets',      'name' => _l('tickets'),      'selected_by_default' => 0],
        ['id' => 'task',         'name' => _l('task'),         'selected_by_default' => 0],
        ['id' => 'contract',     'name' => _l('contract'),     'selected_by_default' => 0],
        ['id' => 'waybills',     'name' => 'Waybills',         'selected_by_default' => 0], // custom
    ];
}
?>

<div class="modal fade" id="contactsplus_link_modal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg">
	<div class="modal-content">

		<div class="modal-header">
		  <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
		  <h4 class="modal-title"><?= _l('contactsplus_modal_link_existing'); ?></h4>
		  <small class="text-muted"><?= isset($client_id) && $client_id ? '#'.(int)$client_id : '' ?></small>
		</div>

		<div class="modal-body">
			<form id="contactsplus_link_form" method="post" action="#">
				<input type="hidden" name="client_id" value="<?= (int)($client_id ?? 0) ?>">
				<input type="hidden"
				   name="<?= $this->security->get_csrf_token_name(); ?>"
				   value="<?= $this->security->get_csrf_hash(); ?>">

				<!-- CONTACT PICKER -->
				<div class="form-group">
					<label class="control-label"><i class="fa fa-user"></i> Contact</label>
					<select id="contactsplus_contact_select"
							class="selectpicker"
							data-live-search="true"
							data-size="10"
							data-width="100%"
							title="Search system contacts (name / phone / email)">
					</select>
				</div>

				<!-- POSITION -->
				<div class="form-group">
					<label class="control-label"><i class="fa fa-id-badge"></i> <?= _l('contactsplus_position'); ?></label>
					<input type="text" name="position" class="form-control" placeholder="<?= _l('contactsplus_position'); ?>">
				</div>

				<hr class="hr-panel-heading"/>

				<!-- PERMISSIONS (δυναμικά όπως στο core) -->
				<hr />
				<p class="bold"><?php echo _l('customer_permissions'); ?></p>
				<p class="text-danger"><?php echo _l('contact_permissions_info'); ?></p>

				<?php foreach ($contact_permissions as $permission) { ?>
					<div class="col-md-6 row">
						<div class="row">
							<div class="col-md-6 mtop10 border-right">
								<span><?= isset($permission['name']) ? _l($permission['name']) : ucfirst(html_escape($permission['id'])); ?></span>
							</div>
							<div class="col-md-6 mtop10">
								<div class="onoffswitch">
									<input type="checkbox" id="perm_<?= html_escape($permission['id']); ?>" class="onoffswitch-checkbox" value="1" <?= !empty($permission['default']) ? 'checked' : ''; ?> name="perm[<?= html_escape($permission['id']); ?>]">
									<label class="onoffswitch-label" for="perm_<?= html_escape($permission['id']); ?>"></label>
								</div>
							</div>
						</div>
					</div>
					<div class="clearfix"></div>
				<?php } ?>
				<hr />
				<p class="bold"><?php echo _l('email_notifications'); ?><?php if (is_sms_trigger_active()) {echo '/SMS';} ?></p>
				<!-- EMAIL NOTIFICATIONS (δυναμικά όπως στο core) -->
				<div id="contactsplus_email_notif_wrap">
			   
					<div class="row">
						<?php foreach ($email_templates as $et) { ?>
							<div class="col-md-6 mtop10">
								<div class="col-md-6 border-right">
									<span><?= html_escape($et['name']); ?></span>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<div class="onoffswitch">
											<input type="checkbox"
												id="en_<?= html_escape($et['id']); ?>"
												class="onoffswitch-checkbox"
												name="email_notif[<?= html_escape($et['id']); ?>]"
												value="1"
												<?= !empty($et['selected_by_default']) ? 'checked' : ''; ?>>
											<label class="onoffswitch-label" for="en_<?= html_escape($et['id']); ?>"></label>
										</div>
									</div>
								</div>
							</div>
						<?php } ?>
					</div>
				</div>
				<div class="clearfix"></div>

				<div class="text-right">
					<button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close'); ?></button>
					<button type="submit" class="btn btn-primary"><?= _l('contactsplus_link'); ?></button>
				</div>
			</form>
		</div>

	</div>
	</div>
</div>

<!-- μόνο show/hide panel αν ποτέ θες master toggle (προαιρετικό, μπορείς να το αφαιρέσεις) -->
<script>
(function(){
  var master = document.getElementById('perm_email_notifications'); // αν έχεις master toggle στο UI
  var panel  = document.getElementById('contactsplus_email_notif_wrap');
  if (master && panel) {
    var sync = function(){ panel.style.display = master.checked ? 'block' : 'none'; };
    master.addEventListener('change', sync);
    sync();
  }
})();
</script>
