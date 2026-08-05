<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>

<link href="<?php echo base_url('modules/api/assets/main.css'); ?>" rel="stylesheet" type="text/css" />

<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body">
                  <div class="_buttons">
                     <a href="<?php echo admin_url('api/webhook/') ?>" class="btn btn-info pull-left display-block">
                        <i class="fa fa-plus"></i> <?php echo _l('new_webhook'); ?>
                     </a>
                  </div>
                  <div class="clearfix"></div>
                  <hr class="hr-panel-heading" />
                  <div class="clearfix"></div>
                  
                  <?php if (empty($webhooks)): ?>
                     <div class="alert alert-info">
                        <?php echo _l('no_webhooks_configured'); ?>
                     </div>
                  <?php else: ?>
                     <table class="table dt-table">
                        <thead>
                           <tr>
                              <th><?php echo _l('name'); ?></th>
                              <th><?php echo _l('url'); ?></th>
                              <th><?php echo _l('events'); ?></th>
                              <th><?php echo _l('status'); ?></th>
                              <th><?php echo _l('success_count'); ?></th>
                              <th><?php echo _l('failure_count'); ?></th>
                              <th><?php echo _l('last_triggered'); ?></th>
                              <th><?php echo _l('options'); ?></th>
                           </tr>
                        </thead>
                        <tbody>
                           <?php foreach ($webhooks as $webhook): ?>
                              <tr>
                                 <td><?php echo htmlspecialchars($webhook['name']); ?></td>
                                 <td>
                                    <code style="font-size: 11px;">
                                       <?php echo htmlspecialchars(substr($webhook['url'], 0, 50)); ?>
                                       <?php echo strlen($webhook['url']) > 50 ? '...' : ''; ?>
                                    </code>
                                 </td>
                                 <td>
                                    <?php 
                                    $events = explode(',', $webhook['events']);
                                    $eventLabels = [];
                                    foreach ($events as $event) {
                                       $event = trim($event);
                                       if ($event === '*') {
                                          $eventLabels[] = '<span class="label label-default">All Events</span>';
                                       } else {
                                          $eventLabels[] = '<span class="label label-info">' . htmlspecialchars($event) . '</span>';
                                       }
                                    }
                                    echo implode(' ', $eventLabels);
                                    ?>
                                 </td>
                                 <td>
                                    <span class="label label-<?php echo $webhook['active'] ? 'success' : 'danger'; ?>">
                                       <?php echo $webhook['active'] ? _l('active') : _l('inactive'); ?>
                                    </span>
                                 </td>
                                 <td><?php echo $webhook['success_count']; ?></td>
                                 <td><?php echo $webhook['failure_count']; ?></td>
                                 <td>
                                    <?php echo $webhook['last_triggered'] ? date('Y-m-d H:i:s', strtotime($webhook['last_triggered'])) : _l('never'); ?>
                                 </td>
                                 <td class="options">
                                    <div class="row-options">
                                       <a href="<?php echo admin_url('api/webhook/' . $webhook['id']) ?>" 
                                          class="btn btn-default btn-icon" 
                                          data-toggle="tooltip" 
                                          data-placement="top"
                                          title="<?php echo _l('edit'); ?>">
                                          <i class="fa fa-pencil"></i>
                                       </a>
                                       <a href="<?php echo admin_url('api/webhook_logs/' . $webhook['id']) ?>" 
                                          class="btn btn-info btn-icon" 
                                          data-toggle="tooltip" 
                                          data-placement="top"
                                          title="<?php echo _l('view_logs'); ?>">
                                          <i class="fa fa-list"></i>
                                       </a>
                                       <button type="button" 
                                               class="btn btn-success btn-icon test-webhook" 
                                               data-webhook-id="<?php echo $webhook['id']; ?>"
                                               data-toggle="tooltip" 
                                               data-placement="top"
                                               title="<?php echo _l('test_webhook'); ?>">
                                          <i class="fa fa-paper-plane"></i>
                                       </button>
                                       <a href="<?php echo admin_url('api/delete_webhook/' . $webhook['id']); ?>" 
                                          class="btn btn-danger btn-icon _delete" 
                                          data-toggle="tooltip" 
                                          data-placement="top"
                                          title="<?php echo _l('delete'); ?>">
                                          <i class="fa fa-remove"></i>
                                       </a>
                                    </div>
                                 </td>
                              </tr>
                           <?php endforeach; ?>
                        </tbody>
                     </table>
                  <?php endif; ?>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

<?php init_tail(); ?>

<script>
$(document).ready(function() {
   // Initialize tooltips
   $('[data-toggle="tooltip"]').tooltip();
   
   $('.test-webhook').on('click', function() {
      var webhookId = $(this).data('webhook-id');
      var $btn = $(this);
      
      $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
      
      $.ajax({
         url: '<?php echo admin_url('api/test_webhook/'); ?>' + webhookId,
         type: 'POST',
         dataType: 'json',
         success: function(response) {
            if (response && response.success) {
               alert_float('success', response.message || '<?php echo _l('webhook_test_success'); ?>');
            } else {
               alert_float('danger', (response && response.message) || '<?php echo _l('webhook_test_failed'); ?>');
            }
         },
         error: function(xhr, status, error) {
            var errorMsg = '<?php echo _l('webhook_test_error'); ?>';
            if (xhr.responseText) {
               try {
                  var response = JSON.parse(xhr.responseText);
                  if (response.message) {
                     errorMsg = response.message;
                  }
               } catch(e) {
                  errorMsg = 'Error: ' + xhr.status + ' - ' + (xhr.responseText.substring(0, 100) || error);
               }
            }
            alert_float('danger', errorMsg);
         },
         complete: function() {
            $btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i>');
         }
      });
   });
});
</script>
