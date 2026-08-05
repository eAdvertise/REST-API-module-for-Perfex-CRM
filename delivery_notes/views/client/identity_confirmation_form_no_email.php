<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="modal fade" id="identityConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="identityConfirmationModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <?php echo form_open($formAction, ['id' => 'identityConfirmationForm', 'autocomplete' => 'off']); ?>
      <div class="modal-header">
        <h4 class="modal-title" id="identityConfirmationModalLabel"><?php echo _l('confirm_delivery'); ?></h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo _l('close'); ?>">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="col-md-6">
            <?php echo render_input('acceptance_firstname', 'client_firstname', '', 'text', ['required' => true]); ?>
          </div>
          <div class="col-md-6">
            <?php echo render_input('acceptance_lastname', 'client_lastname', '', 'text', ['required' => true]); ?>
          </div>
        </div>

        <!-- Email removed as requested. Kept as hidden field for backwards compatibility -->
        <input type="hidden" name="acceptance_email" value="" />

        <div class="m-top-15">
          <label class="control-label"><?php echo _l('signature'); ?></label>
          <div class="signature-pad--body">
            <canvas id="signature" height="130"></canvas>
            <input type="hidden" name="signature" id="signatureInput" value="" />
          </div>
          <a href="#" class="btn btn-default btn-sm m-top-10" id="clearSignature"><?php echo _l('clear'); ?></a>
          <p class="text-muted m-top-10"><?php echo _l('sign_document_validation'); ?></p>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
        <button type="submit" class="btn btn-primary"><?php echo _l('confirm'); ?></button>
      </div>
      <?php echo form_close(); ?>
    </div>
  </div>
</div>

<!-- Signature pad library (required for drawing) -->
<script src="<?php echo base_url('assets/plugins/signature-pad/signature_pad.min.js'); ?>"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // Initialize signature pad (Perfex already loads SignaturePad globally on client pages when enabled)
  var canvas = document.getElementById('signature');
  if (!canvas || typeof SignaturePad === 'undefined') { return; }

  var signaturePad = new SignaturePad(canvas);

  var clearBtn = document.getElementById('clearSignature');
  if (clearBtn) {
    clearBtn.addEventListener('click', function(e){
      e.preventDefault();
      signaturePad.clear();
      var inp = document.getElementById('signatureInput');
      if (inp) inp.value = '';
    });
  }

  // Before submit, store base64 in hidden input
  var form = document.getElementById('identityConfirmationForm');
  if (form) {
    form.addEventListener('submit', function(){
      var inp = document.getElementById('signatureInput');
      if (inp) {
        inp.value = signaturePad.isEmpty() ? '' : signaturePad.toDataURL('image/png');
      }
    });
  }

  // Basic jQuery validation if available (Perfex uses it)
  if (typeof $ !== 'undefined' && $.fn && $.fn.validate) {
    $('#identityConfirmationForm').validate({
      rules: {
        acceptance_firstname: { required: true },
        acceptance_lastname:  { required: true },
        signature:           { required: true }
      }
    });
  }
});
</script>
