<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>

<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body">
                  <h4 class="no-margin"><?php echo _l('webhook_logs'); ?> - <?php echo htmlspecialchars($webhook->name); ?></h4>
                  <a href="<?php echo admin_url('api/event_webhooks'); ?>" class="btn btn-default pull-right">
                     <i class="fa fa-arrow-left"></i> <?php echo _l('back'); ?>
                  </a>
                  <hr class="hr-panel-heading" />
                  
                  <?php if (empty($logs)): ?>
                     <div class="alert alert-info">
                        <?php echo _l('no_logs_found'); ?>
                     </div>
                  <?php else: ?>
                     <table class="table dt-table">
                        <thead>
                           <tr>
                              <th><?php echo _l('event'); ?></th>
                              <th><?php echo _l('status'); ?></th>
                              <th><?php echo _l('response_code'); ?></th>
                              <th><?php echo _l('attempt_number'); ?></th>
                              <th><?php echo _l('triggered_at'); ?></th>
                              <th><?php echo _l('options'); ?></th>
                           </tr>
                        </thead>
                        <tbody>
                           <?php foreach ($logs as $log): ?>
                              <tr>
                                 <td><?php echo htmlspecialchars($log['event']); ?></td>
                                 <td>
                                    <span class="label label-<?php 
                                       echo $log['status'] === 'success' ? 'success' : 
                                          ($log['status'] === 'failed' ? 'danger' : 'warning'); 
                                    ?>">
                                       <?php echo ucfirst($log['status']); ?>
                                    </span>
                                 </td>
                                 <td><?php echo $log['response_code'] ?: '-'; ?></td>
                                 <td><?php echo $log['attempt_number']; ?></td>
                                 <td><?php echo date('Y-m-d H:i:s', strtotime($log['triggered_at'])); ?></td>
                                 <td>
                                    <button type="button" class="btn btn-info btn-xs view-payload" 
                                            data-payload='<?php echo htmlspecialchars($log['payload']); ?>'
                                            data-response='<?php echo htmlspecialchars($log['response_body'] ?: ''); ?>'
                                            data-error='<?php echo htmlspecialchars($log['error_message'] ?: ''); ?>'>
                                       <i class="fa fa-eye"></i> <?php echo _l('view_details'); ?>
                                    </button>
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

<!-- Modal for viewing log details -->
<div class="modal fade" id="logDetailsModal" tabindex="-1" role="dialog">
   <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title"><?php echo _l('webhook_log_details'); ?></h4>
         </div>
         <div class="modal-body">
            <h5><?php echo _l('payload'); ?></h5>
            <pre id="payload-content" style="max-height: 300px; overflow-y: auto; background: #f5f5f5; padding: 10px; border-radius: 4px;"></pre>
            
            <h5><?php echo _l('response'); ?></h5>
            <pre id="response-content" style="max-height: 200px; overflow-y: auto; background: #f5f5f5; padding: 10px; border-radius: 4px;"></pre>
            
            <h5><?php echo _l('error_message'); ?></h5>
            <pre id="error-content" style="max-height: 200px; overflow-y: auto; background: #f5f5f5; padding: 10px; border-radius: 4px;"></pre>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
         </div>
      </div>
   </div>
</div>

<?php init_tail(); ?>

<script>
$(document).ready(function() {
   $('.view-payload').on('click', function() {
      var payload = $(this).data('payload');
      var response = $(this).data('response');
      var error = $(this).data('error');
      
      try {
         var payloadObj = JSON.parse(payload);
         $('#payload-content').text(JSON.stringify(payloadObj, null, 2));
      } catch(e) {
         $('#payload-content').text(payload);
      }
      
      $('#response-content').text(response || 'N/A');
      $('#error-content').text(error || 'N/A');
      
      $('#logDetailsModal').modal('show');
   });
});
</script>
