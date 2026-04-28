<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-6">
            <div class="panel_s">
               <div class="panel-body">
                  <h4>Module Activation 🔑</h4>
                  <hr class="hr-panel-heading">
                  <p>👉 Enter your license purchase key below (<a target="_blank" href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-">Where do I get this from?</a>)</p>
                  <br>
                  <?php echo form_open($submit_url, ['autocomplete' => 'off', 'id' => 'verify-form']); ?>
                  <?php echo form_hidden('original_url', $original_url); ?>
                  <?php echo form_hidden('module_name', $module_name); ?>
                  <?php echo render_input('purchase_key', 'purchase_key', '', 'text', ['required' => true]); ?>
                  <div class="checkbox">
                     <input type="checkbox" id="confirmation" name="confirmation" required value="">
                     <label for="confirmation">I confirm that I adhere to the <a href="https://codecanyon.net/licenses/standard" target="_blank">Envato Licensing Terms</a></label>
                  </div>
                  <div class="row mbot20">
                     <div class="col-md-12">
					 <br>
                        <button id="submit" type="submit" class="btn btn-primary">Click to activate ✔️</button>
                     </div>
                  </div><br>
                  <?php echo form_close(); ?>
               </div>
               <div class="panel-footer">
                  {} <?php echo 'Version ' . $this->app_modules->get($module_name)['headers']['version'] ?? ''; ?>
               </div>
            </div>
         </div>
         <div class="col-md-6">
            <div class="panel_s">
               <div class="panel-body">
                  <center>
                  <i class="fa fa-gavel fa-fw fa-lg tw-mr-0.5 group-hover:tw-text-neutral-800 tw-text-neutral-300"></i><h4>License Compliance & Terms</h4>
                  <ul class="list-unstyled">
                     <li><i class="fa fa-check-circle"></i> Your license is valid for one instance only.</li>
                     <li><i class="fa fa-check-circle"></i> A Regular License allows you to create one end product for yourself/a client.</li>
                     <li><i class="fa fa-exclamation-circle"></i> Violations of these terms may result in license termination.</li>
                  </ul>
                  <br>
                  <i class="fa fa-thumbs-down fa-fw fa-lg tw-mr-0.5 group-hover:tw-text-neutral-800 tw-text-neutral-300"></i><h4>Prohibited Usage</h4>
                  <ul class="list-unstyled">
                     <li><i class="fa fa-ban"></i> Resell product/end product to multiple clients without an Extended License.</li>
                     <li><i class="fa fa-ban"></i> Redistribute the product as-is or with minor modifications.</li>
                     <li><i class="fa fa-ban"></i> Use the product in third party applications as a bundled product.</li>
                     <li><i class="fa fa-ban"></i> Extract code components from the product for separate use.</li>
                     <li><i class="fa fa-ban"></i> Edit the copyrights of the code (name, author etc).</li>
                  </ul>
                  </center>
               </div>
               <div class="panel-footer">
                  <center>📋 <a style="color:#000" href="https://codecanyon.net/licenses/terms/regular">Envato License Aggrement »</center>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

<?php init_tail(); ?>
<script type="text/javascript">
   "use strict";
   appValidateForm($('#verify-form'), {
      purchase_key: 'required',
      confirmation: 'required'
   }, manage_verify_form, {
      confirmation: {
         required: "You didnt accept the terms - Please reload the page to activate your license."
      }
   });

function manage_verify_form(form) {
    // Get and trim the value of the purchase_key input field
    var purchaseKey = $('#purchase_key').val().trim();
    
    // Log the value to check if trimming is happening correctly
    console.log("Trimmed Purchase Key: '" + purchaseKey + "'");
    
    // Set the trimmed value back to the input field
    $('#purchase_key').val(purchaseKey);  

    // Disable the submit button and show loading icon
    $("#submit").prop('disabled', true).prepend('<i class="fa fa-spinner fa-pulse"></i> ');

    // Send the form data using AJAX
    $.post(form.action, $(form).serialize()).done(function(response) {
        var response = $.parseJSON(response);
        if (!response.status) {
            alert_float("danger", response.message);
        }
        if (response.status) {
            alert_float("success", "Activating your license..");
            window.location.href = response.original_url;
        }
        // Enable the submit button and remove the loading icon
        $("#submit").prop('disabled', false).find('i').remove();
    });
}

</script>