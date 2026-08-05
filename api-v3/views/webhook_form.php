<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>

<link href="<?php echo base_url('modules/api/assets/main.css'); ?>" rel="stylesheet" type="text/css" />

<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body">
                  <?php echo form_open(admin_url('api/webhook/' . ($webhook ? $webhook->id : ''))); ?>
                  
                  <div class="row">
                     <div class="col-md-12">
                        <h4 class="no-margin"><?php echo isset($title) ? $title : _l('new_webhook'); ?></h4>
                        <hr class="hr-panel-heading" />
                     </div>
                  </div>
      
                  <div class="row">
                     <div class="col-md-6">
                        <?php echo render_input('name', 'webhook_name', $webhook ? $webhook->name : ''); ?>
                     </div>
                     <div class="col-md-6">
                        <?php echo render_input('url', 'webhook_url', $webhook ? $webhook->url : '', 'url'); ?>
                     </div>
                  </div>
                  
                  <div class="row">
                     <div class="col-md-12">
                        <h4><?php echo _l('events_to_trigger'); ?></h4>
                        <div class="checkbox checkbox-primary">
                           <input type="checkbox" name="events[]" value="*" id="all_events" 
                                  <?php echo ($webhook && ($webhook->events === '*' || strpos($webhook->events, '*') !== false)) ? 'checked' : ''; ?>>
                           <label for="all_events"><?php echo _l('all_events'); ?></label>
                        </div>
                        <hr />
                        <?php foreach ($available_events as $event => $label): ?>
                           <div class="checkbox checkbox-primary">
                              <input type="checkbox" name="events[]" value="<?php echo $event; ?>" id="event_<?php echo str_replace('.', '_', $event); ?>"
                                     <?php echo ($webhook && (strpos($webhook->events, '*') !== false || strpos($webhook->events, $event) !== false)) ? 'checked' : ''; ?>>
                              <label for="event_<?php echo str_replace('.', '_', $event); ?>"><?php echo $label; ?></label>
                           </div>
                        <?php endforeach; ?>
                     </div>
                  </div>
                  
                  <div class="row">
                     <div class="col-md-6">
                        <?php echo render_input('secret', 'webhook_secret', $webhook ? $webhook->secret : '', 'text', [
                           'placeholder' => _l('optional_secret_for_signature'),
                           'help' => _l('webhook_secret_help')
                        ]); ?>
                     </div>
                     <div class="col-md-6">
                        <div class="form-group">
                           <label for="active"><?php echo _l('status'); ?></label>
                           <div class="checkbox checkbox-primary">
                              <input type="checkbox" name="active" id="active" value="1" 
                                     <?php echo (!$webhook || $webhook->active) ? 'checked' : ''; ?>>
                              <label for="active"><?php echo _l('active'); ?></label>
                           </div>
                        </div>
                     </div>
                  </div>
                  
                  <div class="row">
                     <div class="col-md-6">
                        <?php echo render_input('timeout', 'webhook_timeout', $webhook ? $webhook->timeout : 30, 'number', [
                           'min' => 1,
                           'max' => 300,
                           'help' => _l('timeout_in_seconds')
                        ]); ?>
                     </div>
                     <div class="col-md-6">
                        <?php echo render_input('retry_count', 'webhook_retry_count', $webhook ? $webhook->retry_count : 3, 'number', [
                           'min' => 0,
                           'max' => 10,
                           'help' => _l('number_of_retries_on_failure')
                        ]); ?>
                     </div>
                  </div>
                  
                  <div class="row">
                     <div class="col-md-12">
                        <div class="form-group">
                           <label for="headers"><?php echo _l('custom_headers'); ?> (JSON)</label>
                           <textarea name="headers" id="headers" class="form-control" rows="4" placeholder='{"Authorization": "Bearer token", "X-Custom-Header": "value"}'><?php echo $webhook && $webhook->headers ? htmlspecialchars(trim($webhook->headers)) : ''; ?></textarea>
                           <small class="help-block"><?php echo _l('custom_headers_help'); ?></small>
                        </div>
                     </div>
                  </div>
      
                  <div class="row">
                     <div class="col-md-12">
                        <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                        <a href="<?php echo admin_url('api/event_webhooks'); ?>" class="btn btn-default"><?php echo _l('cancel'); ?></a>
                     </div>
                  </div>
                  
                  <?php echo form_close(); ?>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

<?php init_tail(); ?>

<script>
$(document).ready(function() {
   $('#all_events').on('change', function() {
      if ($(this).is(':checked')) {
         $('input[name="events[]"]').not(this).prop('checked', false).prop('disabled', true);
      } else {
         $('input[name="events[]"]').not(this).prop('disabled', false);
      }
   });
   
   $('input[name="events[]"]').not('#all_events').on('change', function() {
      if ($(this).is(':checked')) {
         $('#all_events').prop('checked', false);
      }
   });
});
</script>
