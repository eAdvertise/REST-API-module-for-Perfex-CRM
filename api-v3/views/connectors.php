<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>

<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <h4 class="no-margin"><?php echo _l('automation_connectors'); ?></h4>
            <hr class="hr-panel-heading" />
         </div>
      </div>
      
      <div class="row connectors-page" style="display: flex; flex-wrap: wrap;">
         <div class="col-md-3" style="display: flex;">
            <div class="panel_s" style="width: 100%; display: flex; flex-direction: column;">
               <div class="panel-body text-center" style="flex: 1; display: flex; flex-direction: column;">
                  <h3>
                     <i class="fa fa-bolt" style="color: #FF4A00;"></i> Zapier
                     <a href="http://1.envato.market/zapier-module-for-perfex" target="_blank" rel="noopener noreferrer" 
                        data-toggle="tooltip" 
                        data-placement="top" 
                        data-html="true"
                        title="<strong><i class=&quot;fa fa-star&quot;></i> Looking for a ready-made solution?</strong><br />Get our official Zapier Module for Perfex CRM - a fully approved, pre-configured Zapier app that works out of the box!"
                        style="color: #f39c12; cursor: pointer; margin-left: 5px; font-size: 0.9em; text-decoration: none;">
                        <i class="fa fa-info-circle"></i>
                     </a>
                  </h3>
                  <p>Connect Perfex CRM with 5000+ apps via Zapier</p>
                  
                  <a href="<?php echo site_url('modules/api/download_manifest.php?platform=zapier'); ?>" class="btn btn-warning btn-sm" target="_blank" download>
                     <i class="fa fa-download"></i> Download Manifest
                  </a>
                  <hr />
                  <h5>Setup Instructions (Manual Integration):</h5>
                  <ol class="text-left" style="padding-left: 20px; font-size: 13px; margin-top: auto;">
                     <li>Download the manifest file</li>
                     <li>Go to <a href="https://developer.zapier.com/" target="_blank" rel="noopener noreferrer">Zapier Developer Platform</a></li>
                     <li>Create a new integration</li>
                     <li>Upload the manifest file</li>
                     <li>Configure authentication</li>
                  </ol>
               </div>
            </div>
         </div>
         
         <div class="col-md-3" style="display: flex;">
            <div class="panel_s" style="width: 100%; display: flex; flex-direction: column;">
               <div class="panel-body text-center" style="flex: 1; display: flex; flex-direction: column;">
                  <h3><i class="fa fa-puzzle-piece" style="color: #00C7B1;"></i> Make.com</h3>
                  <p>Automate workflows with Make.com integration</p>
                  <a href="<?php echo site_url('modules/api/download_manifest.php?platform=make'); ?>" class="btn btn-success btn-sm" target="_blank" download>
                     <i class="fa fa-download"></i> Download Manifest
                  </a>
                  <hr />
                  <h5>Setup Instructions:</h5>
                  <ol class="text-left" style="padding-left: 20px; margin-top: auto;">
                     <li>Download the manifest file</li>
                     <li>Go to Make.com Custom App</li>
                     <li>Import the manifest</li>
                     <li>Configure API connection</li>
                     <li>Start building scenarios</li>
                  </ol>
               </div>
            </div>
         </div>
         
         <div class="col-md-3" style="display: flex;">
            <div class="panel_s" style="width: 100%; display: flex; flex-direction: column;">
               <div class="panel-body text-center" style="flex: 1; display: flex; flex-direction: column;">
                  <h3><i class="fa fa-code" style="color: #FF6D5A;"></i> n8n</h3>
                  <p>Self-hosted workflow automation with n8n</p>
                  <a href="<?php echo site_url('modules/api/download_manifest.php?platform=n8n'); ?>" class="btn btn-danger btn-sm" target="_blank" download>
                     <i class="fa fa-download"></i> Download Manifest
                  </a>
                  <hr />
                  <h5>Setup Instructions:</h5>
                  <ol class="text-left" style="padding-left: 20px; margin-top: auto;">
                     <li>Download the manifest file</li>
                     <li>Open n8n workflow editor</li>
                     <li>Add HTTP Request node</li>
                     <li>Configure using manifest</li>
                     <li>Set up credentials</li>
                  </ol>
               </div>
            </div>
         </div>
         
         <div class="col-md-3" style="display: flex;">
            <div class="panel_s" style="width: 100%; display: flex; flex-direction: column;">
               <div class="panel-body text-center" style="flex: 1; display: flex; flex-direction: column;">
                  <h3><i class="fa fa-rocket" style="color: #FF6C37;"></i> Postman</h3>
                  <p>Test and explore API endpoints with Postman</p>
                  
                  <a href="<?php echo base_url('modules/api/download_postman.php'); ?>" class="btn btn-info btn-sm" target="_blank">
                     <i class="fa fa-download"></i> Download Collection
                  </a>
                  <hr />
                  <h5>Setup Instructions:</h5>
                  <ol class="text-left" style="padding-left: 20px; margin-top: auto;">
                     <li>Download the Postman collection</li>
                     <li>Open Postman application</li>
                     <li>Import the collection file</li>
                     <li>Configure API key in variables</li>
                     <li>Start testing endpoints</li>
                  </ol>
               </div>
            </div>
         </div>
      </div>
      
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body">
                  <h4>API Endpoints for Polling</h4>
                  <div class="alert alert-info" style="margin-bottom: 20px;">
                     <strong><i class="fa fa-info-circle"></i> What is Polling?</strong><br />
                     <p style="margin: 10px 0 0 0; font-size: 13px;">
                        Polling is a method used by automation platforms (like Zapier, Make.com, n8n) to check for new or updated data periodically. 
                        Instead of your CRM pushing data to these platforms (webhooks), the automation platform <strong>pulls</strong> data from your API 
                        at regular intervals (e.g., every 15 minutes). When a platform polls your API, it asks: "Give me any customers/invoices/leads/etc. 
                        that were created or modified since the last time I checked." This allows these platforms to trigger workflows when new data appears.
                     </p>
                     <p style="margin: 10px 0 0 0; font-size: 13px;">
                        The <code>since</code> parameter is a timestamp that tells the API "only return records modified after this time", ensuring you only get new/changed data.
                     </p>
                  </div>
                  <p>Use these endpoints for polling triggers:</p>
                  <table class="table dt-table table-connectors">
                     <thead>
                        <tr>
                           <th>Resource</th>
                           <th>Polling URL</th>
                           <th>Test URL</th>
                        </tr>
                     </thead>
                     <tbody>
                        <tr>
                           <td>Customers</td>
                           <td><code><?php echo $base_url; ?>zapier/poll/customers?since={timestamp}</code></td>
                           <td><code><?php echo $base_url; ?>zapier/test/customers</code></td>
                        </tr>
                        <tr>
                           <td>Invoices</td>
                           <td><code><?php echo $base_url; ?>zapier/poll/invoices?since={timestamp}</code></td>
                           <td><code><?php echo $base_url; ?>zapier/test/invoices</code></td>
                        </tr>
                        <tr>
                           <td>Leads</td>
                           <td><code><?php echo $base_url; ?>zapier/poll/leads?since={timestamp}</code></td>
                           <td><code><?php echo $base_url; ?>zapier/test/leads</code></td>
                        </tr>
                        <tr>
                           <td>Tasks</td>
                           <td><code><?php echo $base_url; ?>zapier/poll/tasks?since={timestamp}</code></td>
                           <td><code><?php echo $base_url; ?>zapier/test/tasks</code></td>
                        </tr>
                        <tr>
                           <td>Tickets</td>
                           <td><code><?php echo $base_url; ?>zapier/poll/tickets?since={timestamp}</code></td>
                           <td><code><?php echo $base_url; ?>zapier/test/tickets</code></td>
                        </tr>
                     </tbody>
                  </table>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

<?php init_tail(); ?>

<script>
$(document).ready(function() {
    // Initialize tooltip for Zapier info icon
    $('[data-toggle="tooltip"]').tooltip();
    
    // Make tooltip clickable - clicking the icon will open the link
    $('[data-toggle="tooltip"]').on('click', function(e) {
        e.preventDefault();
        var href = $(this).attr('href');
        if (href) {
            window.open(href, '_blank', 'noopener,noreferrer');
        }
    });
});
</script>
