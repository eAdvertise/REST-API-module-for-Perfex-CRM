<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <?php
        echo form_open($this->uri->uri_string(), ['id' => 'delivery_note-form', 'class' => '_transaction_form delivery_note-form']);
        if (isset($delivery_note)) { echo form_hidden('isedit'); }
      ?>
      <div class="col-md-12">
        <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700 tw-flex tw-items-center tw-space-x-2">
          <span><?php echo isset($delivery_note) ? format_delivery_note_number($delivery_note) : _l('create_new_delivery_note'); ?></span>
          <?php echo isset($delivery_note) ? format_delivery_note_status($delivery_note->status) : ''; ?>
        </h4>
        <?php $this->load->view('admin/delivery_notes/delivery_note_template'); ?>
      </div>
      <?php echo form_close(); ?>
      <?php $this->load->view('admin/invoice_items/item'); ?>
    </div>
  </div>
</div>
<?php init_tail(); ?>

<script>
  window.addEventListener('DOMContentLoaded', function () {
    if (typeof window.jQuery === 'undefined') { console.error('jQuery not loaded yet – skipping form init'); return; }
    var $ = window.jQuery;

    if (typeof window.csrf_jquery_ajax_setup === 'function') {
      try { window.csrf_jquery_ajax_setup(); } catch(e){ console.warn(e); }
    }

    //if (typeof validate_delivery_note_form === 'function') { validate_delivery_note_form(); }
    if (typeof init_currency === 'function') { init_currency(); }
    if (typeof init_ajax_project_search_by_customer_id === 'function') { init_ajax_project_search_by_customer_id(); }
    if (typeof init_ajax_search === 'function') { init_ajax_search('items', '#item_select.ajax-search', undefined, admin_url + 'items/search'); }
  });
</script>

<script>
(function($){
  // --- Helpers για recurring UI ---
  function syncCyclesHidden(){
    var $vis = $('#cycles_input');
    var v = $vis.val();
    $('#cycles_hidden').val(v === '' ? 0 : v);
  }

  function setInfinityUI(){
    var $in  = $('#cycles_input');
    var $chk = $('#cycles_infinity');
    if (!$chk.length || !$in.length) return;

    if ($chk.is(':checked')) {
      $in.val(0).prop('disabled', true);
    } else {
      if ($in.val() === '' || $in.val() === '0') $in.val('1');
      $in.prop('disabled', false);
    }
    syncCyclesHidden();
  }

  function applyChooser(){
    var $chooser = $('#recurring_chooser');
    if (!$chooser.length) return;
    var v = $chooser.val();

    if (v === '0' || v === null || v === '') {
      $('#recurring_custom_wrap').addClass('hide');
      $('#recurring_cycles_wrap').addClass('hide');
      $('input[name="custom_recurring"]').val(0);
      $('input[name="recurring"]').val(0);
      $('select[name="recurring_type"]').val('month');
      $('#cycles_input').val(0);
      $('#cycles_infinity').prop('checked', true);
      setInfinityUI();
      return;
    }

    if (v === 'custom') {
      $('#recurring_custom_wrap').removeClass('hide');
      $('#recurring_cycles_wrap').removeClass('hide');
      $('input[name="custom_recurring"]').val(1);
      if (!$('input[name="recurring"]').val() || $('input[name="recurring"]').val()==='0') {
        $('input[name="recurring"]').val(1);
      }
    } else {
      // preset μήνες 1..12
      $('#recurring_custom_wrap').addClass('hide');
      $('#recurring_cycles_wrap').removeClass('hide');
      $('input[name="custom_recurring"]').val(0);
      $('input[name="recurring"]').val(v);
      $('select[name="recurring_type"]').val('month');
    }
    setInfinityUI();
  }

  $(function(){
    var $chooser = $('#recurring_chooser');
    var isEdit = <?php echo isset($delivery_note) ? 'true' : 'false'; ?>;

    // Επιβολή "No" ΜΕΤΑ την αρχικοποίηση του selectpicker και fallback
    function forceNo(){
      if (!$chooser.length || isEdit) return;
      if (typeof $chooser.selectpicker === 'function') {
        $chooser.selectpicker('val', '0');
        $chooser.trigger('changed.bs.select');
      } else {
        $chooser.val('0').trigger('change');
      }
    }

    if ($chooser.length) {
      // αν υπάρχει selectpicker, συγχρονισμός γεγονότων
      if (typeof $chooser.selectpicker === 'function') {
        $chooser.on('changed.bs.select', applyChooser);
        $chooser.on('loaded.bs.select', forceNo);
      }
      // πάντα και native change
      $chooser.on('change', applyChooser);
      // αρχικοποίηση UI
      setTimeout(forceNo, 0); // ασφαλιστικό για νέα DN
      applyChooser();
    }

    // Cycles – sync & infinity toggle
    $(document).on('change', '#cycles_infinity', setInfinityUI);
    $(document).on('input change', '#cycles_input', syncCyclesHidden);
    setInfinityUI();

    // Server-side safety πριν submit
    $('form#delivery_note-form').on('submit', function(){
      // Αν "No", καθάρισε/κλείδωσε σωστά
      if ($('#recurring_chooser').val() === '0') {
        $('input[name="recurring"]').val(0);
        $('input[name="custom_recurring"]').val(0);
        $('select[name="recurring_type"]').val('month');
        $('#cycles_input').val(0);
        $('#cycles_infinity').prop('checked', true);
      }
      // Σιγουρέψου ότι θα σταλεί η τιμή κύκλων (0) ακόμη κι αν ήταν disabled
      $('#cycles_input').prop('disabled', false);
      syncCyclesHidden();
    });
  });
})(jQuery);
</script>
