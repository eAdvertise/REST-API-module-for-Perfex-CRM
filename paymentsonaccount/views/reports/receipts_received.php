<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <h4 class="tw-mt-0 tw-mb-3"><?php echo _l('poa_receipts_report') ?: 'Receipts'; ?></h4>

            <div class="row mtop10 mbot15">
              <div class="col-md-3">
                <label for="period"><?php echo _l('period_datepicker'); ?></label>
                <select id="period" class="selectpicker" data-width="100%">
                  <option value="all_time" selected><?php echo _l('poa_all_time') ?: 'All time'; ?></option>
                  <option value="last_month"><?php echo _l('poa_last_month') ?: 'Last Month'; ?></option>
                  <option value="this_year"><?php echo _l('poa_this_year') ?: 'This Year'; ?></option>
                  <option value="last_year"><?php echo _l('poa_last_year') ?: 'Last Year'; ?></option>
                  <option value="last_3_months"><?php echo _l('poa_last_3_months') ?: 'Last 3 Months'; ?></option>
                  <option value="last_6_months"><?php echo _l('poa_last_6_months') ?: 'Last 6 Months'; ?></option>
                  <option value="last_12_months"><?php echo _l('poa_last_12_months') ?: 'Last 12 Months'; ?></option>
                  <option value="period"><?php echo _l('period_datepicker') ?: 'Period'; ?></option>
                </select>
              </div>
              <div class="col-md-3 poa-period-dates hide">
                <?php echo render_date_input('from_date', 'From'); ?>
              </div>
              <div class="col-md-3 poa-period-dates hide">
                <?php echo render_date_input('to_date', 'To'); ?>
              </div>
            </div>

            <div class="row mbot15">
              <div class="col-md-3">
                <div class="alert alert-info mbot0">
                  <strong><?php echo _l('total'); ?>:</strong>
                  <span id="poa-receipts-total-amount">0.00</span>
                </div>
              </div>
              <div class="col-md-3">
                <div class="alert alert-default mbot0">
                  <strong><?php echo _l('poa_receipts_total_count') ?: 'Receipts'; ?>:</strong>
                  <span id="poa-receipts-total-count">0</span>
                </div>
              </div>
            </div>

            <div class="table-responsive">
              <table class="table table-striped table-bordered" id="receipts-table" style="width:100%">
                <thead>
                  <tr>
                    <th><?php echo _l('poa_receipt_number') ?: 'Receipt Number'; ?></th>
                    <th><?php echo _l('invoice_pdf_date') ?: 'Date'; ?></th>
                    <th><?php echo _l('customer'); ?></th>
                    <th><?php echo _l('invoices'); ?></th>
                    <th><?php echo _l('payment_mode') ?: 'Payment Mode'; ?></th>
                    <th><?php echo _l('poa_transaction_id') ?: 'Transaction ID'; ?></th>
                    <th class="text-right"><?php echo _l('invoice_pdf_total') ?: 'Total'; ?></th>
                  </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                  <tr>
                    <th colspan="6" class="text-right"><?php echo _l('total'); ?></th>
                    <th class="text-right" id="poa-receipts-footer-total">0.00</th>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>

<style>
  #receipts-table_wrapper .dataTables_processing,
  #receipts-table_wrapper .dt-loader { display:none!important; }
  #receipts-table_wrapper.table-loading,
  #receipts-table_wrapper.dt-table-loading { background:none!important; }
  .dt-button-collection .dt-button{display:block;width:100%;text-align:left}
</style>
<script>
(function(){
  'use strict';

  function boot(){
    var $ = window.jQuery || window.$;
    if (!$ || !$.fn.DataTable) { return false; }

    var adminUrl = typeof admin_url !== 'undefined' ? admin_url : '<?php echo admin_url(); ?>';
    var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
    var csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';
    var $tbl = $('#receipts-table');

    function nukeLoader(){
      var $wrap = $('#receipts-table_wrapper');
      $wrap.removeClass('table-loading dt-table-loading');
      $wrap.find('.dataTables_processing,.dt-loader,.loader,.loading').remove();
    }

    function togglePeriodDates(){
      $('.poa-period-dates').toggleClass('hide', $('#period').val() !== 'period');
    }

    function payload(){
      var data = { period: $('#period').val() || 'all_time' };
      if (data.period === 'period') {
        data.from = $('#from_date').val() || '';
        data.to = $('#to_date').val() || '';
      }
      data[csrfName] = csrfHash;
      return data;
    }

    if ($.fn.DataTable.isDataTable($tbl)) {
      try { $tbl.DataTable().clear().destroy(); } catch(e) {}
    }

    var dt = $tbl.DataTable({
      processing: false,
      serverSide: false,
      deferRender: true,
      destroy: true,
      pageLength: 25,
      lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, '<?php echo e(_l('dt_length_menu_all') ?: 'All'); ?>']],
      order: [[1, 'desc']],
      ajax: {
        url: adminUrl + 'paymentsonaccount/reports_receipts_data',
        type: 'POST',
        data: function(d){ $.extend(d, payload()); },
        dataSrc: function(res){
          var totals = (res && res.totals) ? res.totals : {count: 0, amount: '0.00'};
          $('#poa-receipts-total-count').text(totals.count || 0);
          $('#poa-receipts-total-amount').text(totals.amount || '0.00');
          return (res && $.isArray(res.rows)) ? res.rows : [];
        }
      },
      columns: [
        { data: 'receipt' },
        { data: '_date_raw', render:function(d,t,row){ return row.date; } },
        { data: 'customer' },
        { data: 'invoices', orderable:false },
        { data: 'payment_mode' },
        { data: 'transaction_id', orderable:false, render:function(d){ return d || '—'; } },
        { data: '_amount_raw', className:'text-right', render:function(d,t,row){ return row.amount; } }
      ],
      dom: "<'row'<'col-md-7'lB><'col-md-5'f>>" +
           "<'row'<'col-sm-12'tr>>" +
           "<'row'<'col-sm-5'i><'col-sm-7'p>>",
      buttons: [
        { extend: 'copy',  title: 'Receipts' },
        { extend: 'csv',   title: 'Receipts' },
        { extend: 'excel', title: 'Receipts' },
        { extend: 'pdf',   title: 'Receipts' },
        { extend: 'print', title: 'Receipts' }
      ],
      footerCallback: function(){
        var api = this.api();
        var total = api.column(6, {search:'applied'}).data().reduce(function(sum, value){
          var n = typeof value === 'number' ? value : parseFloat(String(value).replace(/[^0-9.\-]/g, ''));
          return sum + (isNaN(n) ? 0 : n);
        }, 0);
        $('#poa-receipts-footer-total').text(total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        nukeLoader();
      },
      language: (typeof app !== 'undefined' && app.lang && app.lang.datatables) ? app.lang.datatables : {}
    });

    togglePeriodDates();
    nukeLoader();
    setTimeout(nukeLoader, 100);
    setTimeout(nukeLoader, 400);

    $tbl.on('init.dt draw.dt xhr.dt', nukeLoader);

    $('#period').on('change', function(){
      togglePeriodDates();
      dt.ajax.reload(null, true);
    });

    $('#from_date, #to_date').on('change', function(){
      if ($('#period').val() === 'period') {
        dt.ajax.reload(null, true);
      }
    });

    return true;
  }

  if (!boot()) {
    var tries = 0;
    var timer = setInterval(function(){
      tries++;
      if (boot() || tries > 100) { clearInterval(timer); }
    }, 100);
  }
})();
</script>
