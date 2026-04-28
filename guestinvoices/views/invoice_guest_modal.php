<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade" id="giGuestModal" tabindex="-1" role="dialog" aria-labelledby="giGuestLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <?php echo form_open(admin_url('guestinvoices/ajax_create_guest'), ['id'=>'gi-guest-form']); ?>
      <div class="modal-header">
        <h4 class="modal-title" id="giGuestLabel">
          <i class="fa fa-user-plus"></i> <?php echo _l('guestinvoices_modal_title') ?: 'Guest Customer'; ?>
        </h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo _l('close'); ?>">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <div class="row">
          <!-- ΠΡΩΤΑ First / Last Name -->
          <div class="col-md-6">
            <div class="form-group">
              <label for="gi_firstname" class="control-label"><?php echo _l('client_firstname') ?: 'First Name'; ?></label>
              <input type="text" id="gi_firstname" name="firstname" class="form-control" autocomplete="off">
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="gi_lastname" class="control-label"><?php echo _l('client_lastname') ?: 'Last Name'; ?></label>
              <input type="text" id="gi_lastname" name="lastname" class="form-control" autocomplete="off">
            </div>
          </div>

          <!-- Email (υποχρεωτικό) -->
          <div class="col-md-12">
            <div class="form-group">
              <label for="gi_email" class="control-label">Email <span class="text-danger">*</span></label>
              <input type="email" id="gi_email" name="email" class="form-control" required autocomplete="off">
            </div>
          </div>

          <!-- Company πιο κάτω (προαιρετικό) -->
          <div class="col-md-12">
            <div class="form-group">
              <label for="gi_company" class="control-label"><?php echo _l('client_company') ?: 'Company'; ?></label>
              <input type="text" id="gi_company" name="company" class="form-control" autocomplete="off">
              <small class="text-muted">
				If left blank, <b>First Name + Last Name</b> will be used. 
				If all (First/Last/Company) are missing, it will be set to <b>Guest+ID</b>.
              </small>
            </div>
          </div>

          <div class="col-md-12"><hr/></div>

          <!-- Billing / λοιπά προαιρετικά -->
          <div class="col-md-12">
            <div class="form-group">
              <label for="gi_address" class="control-label"><?php echo _l('client_billing_street'); ?></label>
              <textarea id="gi_address" name="address" class="form-control" rows="2"></textarea>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label for="gi_city" class="control-label"><?php echo _l('client_billing_city'); ?></label>
              <input type="text" id="gi_city" name="city" class="form-control">
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="gi_state" class="control-label"><?php echo _l('client_billing_state'); ?></label>
              <input type="text" id="gi_state" name="state" class="form-control">
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label for="gi_zip" class="control-label"><?php echo _l('client_billing_zip'); ?></label>
              <input type="text" id="gi_zip" name="zip" class="form-control">
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="gi_country" class="control-label"><?php echo _l('client_billing_country'); ?></label>
              <select id="gi_country" name="country" class="form-control selectpicker" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" data-live-search="true">
                <option value=""></option>
                <?php if (!empty($countries)) { foreach ($countries as $c) { ?>
                  <option value="<?php echo html_escape($c['country_id']); ?>"><?php echo html_escape($c['short_name']); ?></option>
                <?php } } ?>
              </select>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label for="gi_phone" class="control-label"><?php echo _l('client_phonenumber'); ?></label>
              <input type="text" id="gi_phone" name="phonenumber" class="form-control">
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="gi_website" class="control-label">Website</label>
              <input type="text" id="gi_website" name="website" class="form-control">
            </div>
          </div>

        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
        <button type="submit" id="gi-guest-submit" class="btn btn-primary">
          <i class="fa fa-check"></i> <?php echo _l('submit'); ?>
        </button>
      </div>
      <?php echo form_close(); ?>
    </div>
  </div>
</div>
