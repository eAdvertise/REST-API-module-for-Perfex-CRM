<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if (!isset($client) || empty($client)) { return; } ?>
<?php
$CI = &get_instance();
$CI->load->model('payment_modes_model');
$paymentModes = $CI->payment_modes_model->get();
$selectedRows = $CI->db->select('payment_mode_id')->where('client_id', (int)$client->userid)->get(db_prefix().'poa_client_payment_modes')->result_array();
$selected = array_map('intval', array_column($selectedRows, 'payment_mode_id'));

$modeMap = [];
foreach ($paymentModes as $mode) {
    $modeId = (int)(is_array($mode) ? ($mode['id'] ?? 0) : ($mode->id ?? 0));
    $modeName = is_array($mode) ? ($mode['name'] ?? '') : ($mode->name ?? '');
    if ($modeId > 0) { $modeMap[$modeId] = $modeName; }
}
?>

<h4 class="customer-profile-group-heading"><?php echo _l('poa_payment_modes_tab') ?: 'Payment Modes'; ?></h4>

<?php echo form_open(admin_url('paymentsonaccount/client_payment_modes_save/' . (int)$client->userid), ['id' => 'poa-payment-modes-form']); ?>

<p class="text-muted"><?php echo _l('poa_payment_modes_help') ?: 'If you select payment modes here, only these will be available for this customer invoices. If none are selected, system defaults are used.'; ?></p>

<div class="row mtop10">
  <div class="col-md-6">
    <label for="poa_payment_mode_select"><?php echo _l('payment_mode') ?: 'Payment Mode'; ?></label>
    <select id="poa_payment_mode_select" class="selectpicker" data-live-search="true" data-width="100%" multiple data-actions-box="true" data-selected-text-format="count > 2">
      <?php foreach ($modeMap as $id => $name) { ?>
        <option value="<?php echo (int)$id; ?>"><?php echo html_escape($name); ?></option>
      <?php } ?>
    </select>
  </div>
  <div class="col-md-2 mtop25">
    <button type="button" class="btn btn-primary" id="poa-add-mode-btn"><i class="fa fa-plus"></i> <?php echo _l('add'); ?></button>
  </div>
</div>

<div class="table-responsive mtop20">
  <table class="table table-striped table-bordered" id="poa-payment-modes-table">
    <thead>
      <tr>
        <th><?php echo _l('payment_mode') ?: 'Payment Mode'; ?></th>
        <th style="width:90px;"><?php echo _l('options'); ?></th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<div id="poa-payment-modes-hidden-inputs"></div>

<hr>
<button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>

<?php echo form_close(); ?>

<script>
(function(){
  'use strict';

  function boot(){
    var $ = window.jQuery || window.$;
    if (!$) { return false; }

    var allModes = <?php echo json_encode($modeMap, JSON_UNESCAPED_UNICODE); ?>;
    var selectedModes = <?php echo json_encode(array_values($selected)); ?>.map(function(v){ return parseInt(v,10)||0; }).filter(Boolean);
    var dt = null;

    function esc(str){ return $('<div>').text(str).html(); }


    function clearLoadingState(){
      var $w = $('#poa-payment-modes-table_wrapper');
      if ($w.length) { $w.removeClass('table-loading'); }
      $w.find('.dataTables_processing,.dt-loader').hide();
    }

    function ensureDt(){
      if (!dt && $.fn.DataTable) {
        dt = $('#poa-payment-modes-table').DataTable({
          paging: false,
          searching: true,
          info: false,
          ordering: true,
          order: [[0, 'asc']],
          columnDefs: [{ orderable: false, targets: 1 }],
          language: { emptyTable: '<?php echo e(_l('no_data_found') ?: 'No data found'); ?>' }
        });
        clearLoadingState();
      }
    }

    function syncHidden(){
      var $hidden = $('#poa-payment-modes-hidden-inputs');
      $hidden.empty();
      selectedModes.forEach(function(id){
        $hidden.append('<input type="hidden" name="payment_modes[]" value="'+id+'">');
      });
    }

    function render(){
      selectedModes = selectedModes.filter(function(id){ return !!allModes[id]; });
      selectedModes = selectedModes.filter(function(v, i, arr){ return arr.indexOf(v) === i; });
      selectedModes.sort(function(a,b){ return a-b; });
      syncHidden();
      ensureDt();

      clearLoadingState();

      if (!dt) {
        var $tbody = $('#poa-payment-modes-table tbody');
        $tbody.empty();
        selectedModes.forEach(function(id){
          $tbody.append('<tr><td>'+esc(allModes[id])+'</td><td><button type="button" class="btn btn-danger btn-icon poa-remove-mode" data-id="'+id+'"><i class="fa fa-times"></i></button></td></tr>');
        });
        clearLoadingState();
        return;
      }

      dt.clear();
      selectedModes.forEach(function(id){
        dt.row.add([
          esc(allModes[id]),
          '<button type="button" class="btn btn-danger btn-icon poa-remove-mode" data-id="'+id+'"><i class="fa fa-times"></i></button>'
        ]);
      });
      dt.draw(false);
      clearLoadingState();
    }

    $(function(){
      if ($.fn.selectpicker) { $('#poa_payment_mode_select').selectpicker(); }
      render();
      clearLoadingState();

      $('#poa-add-mode-btn').off('click.poa').on('click.poa', function(e){
        e.preventDefault();
        var vals = $('#poa_payment_mode_select').val() || [];
        vals.forEach(function(raw){
          var id = parseInt(raw, 10) || 0;
          if (id && selectedModes.indexOf(id) === -1 && allModes[id]) { selectedModes.push(id); }
        });
        clearLoadingState();
        render();
      clearLoadingState();
        if ($.fn.selectpicker) { $('#poa_payment_mode_select').val([]).selectpicker('refresh'); }
      });

      $(document).off('click.poaRemove').on('click.poaRemove', '.poa-remove-mode', function(){
        var id = parseInt($(this).data('id'), 10) || 0;
        selectedModes = selectedModes.filter(function(v){ return v !== id; });
        render();
      clearLoadingState();
      });
    });

    return true;
  }

  if (!boot()) {
    var tries = 0;
    var t = setInterval(function(){ tries++; if (boot() || tries > 100) { clearInterval(t); } }, 100);
  }
})();
</script>
