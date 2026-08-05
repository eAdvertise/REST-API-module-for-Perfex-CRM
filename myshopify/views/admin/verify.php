<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-6 col-md-offset-3">
            <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">
               <?php echo _l('myshopify_verify') ?>
            </h4>
            <?php echo form_open($this->uri->uri_string()); ?>
            <div class="panel_s">
               <div class="panel-body">
                 
                  <label for="show_subscriptions_in_customers_area" class="control-label clearfix">
       </label><p class="text-muted">Purchase-code verification is disabled for this fork.</p>
               </div>
               <div class="panel-footer text-right">
                  <button class="btn btn-primary"
                     type="submit"><?php echo _l('Save'); ?></button>
               </div>
            </div>
            <?php echo form_close(); ?>
         </div>
      </div>
   </div>
</div>
<?php init_tail(); ?>
</html>