<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>

<link href="<?php echo base_url('modules/api/assets/main.css'); ?>" rel="stylesheet" type="text/css" />

<div id="wrapper">
   <div class="content">
      <?php echo form_open('admin/api/user/'); ?>

      <div class="row">
         <div class="col-md-4">
            <?php echo render_input('user', 'user_api'); ?>
            <?php echo render_input('staff_id', 'api_staff_id_optional', '', 'number'); ?>
         </div>
         <div class="col-md-4">
            <?php echo render_input('name', 'name_api'); ?>
         </div>
         <div class="col-md-4">
            <?php echo render_datetime_input('expiration_date', 'expiration_date'); ?>
         </div>
      </div>

      <div class="row">
         <div class="col-md-12">
            <h4 class="no-margin"><?php echo _l('quota_settings'); ?></h4>
            <hr class="hr-panel-heading" />
         </div>
      </div>

      <div class="row">
         <div class="col-md-4">
            <div class="form-group">
               <label for="request_limit" class="control-label"><?php echo _l('request_limit'); ?> <span class="text-danger">*</span></label>
               <input type="number" class="form-control" name="request_limit" id="request_limit" value="1000" min="1" required>
            </div>
         </div>
         <div class="col-md-4">
            <div class="form-group">
               <label for="time_window" class="control-label"><?php echo _l('time_window'); ?> <span class="text-danger">*</span></label>
               <select class="form-control" name="time_window" id="time_window" required>
                  <option value="3600">1 Hour</option>
                  <option value="86400" selected>24 Hours</option>
                  <option value="604800">7 Days</option>
                  <option value="2592000">30 Days</option>
               </select>
            </div>
         </div>
         <div class="col-md-4">
            <div class="form-group">
               <label for="burst_limit" class="control-label"><?php echo _l('burst_limit'); ?> <span class="text-danger">*</span></label>
               <input type="number" class="form-control" name="burst_limit" id="burst_limit" value="100" min="1" required>
            </div>
         </div>
      </div>

      <div class="row">
         <div class="col-md-6">
            <div class="form-group">
               <div class="checkbox">
                  <input type="checkbox" name="quota_active" id="quota_active" value="1" checked>
                  <label><?php echo _l('quota_active'); ?></label>
               </div>
            </div>
         </div>
      </div>

      <?php $this->load->view('permissions'); ?>

      <div class="row">
         <div class="col-md-12">
            <button type="submit" class="btn btn-primary pull-right permission-save-btn" id="permission-form-submit">
               <?php echo _l('submit'); ?>
            </button>
         </div>
      </div>

      <?php echo form_close(); ?>
   </div>
</div>

<?php init_tail(); ?>

<script src="<?php echo base_url('modules/api/assets/main.js'); ?>"></script>