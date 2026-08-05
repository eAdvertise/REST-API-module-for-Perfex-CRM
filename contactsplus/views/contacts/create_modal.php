<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade" id="contactsplus_create_modal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal">&times;</button>
      <h4 class="modal-title"><?= _l('contactsplus_modal_new_contact'); ?></h4>
    </div>
    <div class="modal-body">
      <form method="post" action="<?= admin_url('contactsplus/api/contacts'); ?>">
        <input type="hidden" name="client_id" value="<?= (int)($client_id ?? 0) ?>">
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label><?= _l('contactsplus_firstname'); ?> *</label>
              <input type="text" name="firstname" class="form-control" required>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label><?= _l('contactsplus_lastname'); ?></label>
              <input type="text" name="lastname" class="form-control">
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label><?= _l('contactsplus_phone'); ?></label>
              <input type="text" name="phone" class="form-control">
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label><?= _l('contactsplus_position'); ?></label>
              <input type="text" name="position" class="form-control">
            </div>
          </div>
        </div>
        <div class="form-group">
          <label><?= _l('contactsplus_notes'); ?></label>
          <textarea name="notes" class="form-control" rows="3"></textarea>
        </div>

        <!-- Προαιρετικά quick flags για το link -->
        <div class="row">
          <div class="col-md-4">
            <input type="text" class="form-control" name="link_role" placeholder="<?= _l('contactsplus_role'); ?>">
          </div>
          <div class="col-md-8">
            <div class="checkbox-inline"><label><input type="checkbox" name="link_is_primary" value="1"> <?= _l('contactsplus_flag_primary'); ?></label></div>
            <div class="checkbox-inline"><label><input type="checkbox" name="link_billing" value="1"> <?= _l('contactsplus_flag_billing'); ?></label></div>
            <div class="checkbox-inline"><label><input type="checkbox" name="link_notifications" value="1" checked> <?= _l('contactsplus_flag_notifications'); ?></label></div>
          </div>
        </div>

        <button type="submit" class="btn btn-primary"><?= _l('contactsplus_save'); ?></button>
      </form>
    </div>
  </div></div>
</div>
