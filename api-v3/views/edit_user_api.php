<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>

<link href="<?php echo base_url('modules/api/assets/main.css'); ?>" rel="stylesheet" type="text/css" />

<div id="wrapper">
   <div class="content">
      <?php echo form_open('admin/api/user/'); ?>

      <input type="hidden" name="id" value="<?php echo $user_api['id'] ?? ''?>" />
      <div class="row">
         <div class="col-md-4">
            <?php echo render_input('user', 'user_api', $user_api['user'] ?? ''); ?>
            <?php echo render_input('staff_id', 'api_staff_id_optional', $user_api['staff_id'] ?? '', 'number'); ?>
         </div>
         <div class="col-md-4">
            <?php echo render_input('name', 'name_api', $user_api['name'] ?? ''); ?>
         </div>
         <div class="col-md-4">
            <?php echo render_datetime_input('expiration_date', 'expiration_date', $user_api['expiration_date'] ?? ''); ?>
         </div>
      </div>
      <div class="row">
         <div class="col-md-12">
            <?php echo render_input('token', 'token_api', $user_api['token'] ?? '', 'text', ['readonly' => true]); ?>
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
               <input type="number" class="form-control" name="request_limit" id="request_limit" 
                      value="<?php echo $user_api['request_limit'] ?? 1000; ?>" min="1" required>
            </div>
         </div>
         <div class="col-md-4">
            <div class="form-group">
               <label for="time_window" class="control-label"><?php echo _l('time_window'); ?> <span class="text-danger">*</span></label>
               <select class="form-control" name="time_window" id="time_window" required>
                  <option value="3600" <?php echo ($user_api['time_window'] ?? 3600) == 3600 ? 'selected' : ''; ?>>1 Hour</option>
                  <option value="86400" <?php echo ($user_api['time_window'] ?? 3600) == 86400 ? 'selected' : ''; ?>>24 Hours</option>
                  <option value="604800" <?php echo ($user_api['time_window'] ?? 3600) == 604800 ? 'selected' : ''; ?>>7 Days</option>
                  <option value="2592000" <?php echo ($user_api['time_window'] ?? 3600) == 2592000 ? 'selected' : ''; ?>>30 Days</option>
               </select>
            </div>
         </div>
         <div class="col-md-4">
            <div class="form-group">
               <label for="burst_limit" class="control-label"><?php echo _l('burst_limit'); ?> <span class="text-danger">*</span></label>
               <input type="number" class="form-control" name="burst_limit" id="burst_limit" 
                      value="<?php echo $user_api['burst_limit'] ?? 100; ?>" min="1" required>
            </div>
         </div>
      </div>

      <div class="row">
         <div class="col-md-6">
            <div class="form-group">
               <div class="checkbox">
                  <input type="checkbox" name="quota_active" id="quota_active" value="1" 
                           <?php echo ($user_api['quota_active'] ?? 1) ? 'checked' : ''; ?>/>
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