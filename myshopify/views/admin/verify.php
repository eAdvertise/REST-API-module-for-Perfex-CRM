<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-6 col-md-offset-3">
            <div class="panel_s">
               <div class="panel-body">
                  <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">
                     <?php echo _l('myshopify_verify'); ?>
                  </h4>
                  <p class="text-muted">External purchase-code verification is disabled for this fork.</p>
                  <a href="<?php echo admin_url('myshopify/products'); ?>" class="btn btn-primary">
                     <?php echo _l('continue'); ?>
                  </a>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?php init_tail(); ?>
