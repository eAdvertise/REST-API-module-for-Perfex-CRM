<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <h4 class="tw-mt-0 tw-mb-3"><?php echo _l('poa_client_credit_balances_report'); ?></h4>
            <?php
              $period = isset($period) ? $period : 'all_time';
              $from_ui = isset($from_ui) ? $from_ui : '';
              $to_ui = isset($to_ui) ? $to_ui : '';
            ?>
            <?php echo form_open(admin_url('paymentsonaccount/reports/credits'), ['method' => 'get', 'id' => 'poa-credits-period-form']); ?>
              <div class="row mtop10 mbot15">
                <div class="col-md-3">
                  <label for="period"><?php echo _l('period_datepicker'); ?></label>
                  <select id="period" name="period" class="selectpicker" data-width="100%">
                    <option value="all_time" <?php echo $period === 'all_time' ? 'selected' : ''; ?>><?php echo _l('poa_all_time') ?: 'All time'; ?></option>
                    <option value="last_month" <?php echo $period === 'last_month' ? 'selected' : ''; ?>><?php echo _l('poa_last_month') ?: 'Last Month'; ?></option>
                    <option value="this_year" <?php echo $period === 'this_year' ? 'selected' : ''; ?>><?php echo _l('poa_this_year') ?: 'This Year'; ?></option>
                    <option value="last_year" <?php echo $period === 'last_year' ? 'selected' : ''; ?>><?php echo _l('poa_last_year') ?: 'Last Year'; ?></option>
                    <option value="last_3_months" <?php echo $period === 'last_3_months' ? 'selected' : ''; ?>><?php echo _l('poa_last_3_months') ?: 'Last 3 Months'; ?></option>
                    <option value="last_6_months" <?php echo $period === 'last_6_months' ? 'selected' : ''; ?>><?php echo _l('poa_last_6_months') ?: 'Last 6 Months'; ?></option>
                    <option value="last_12_months" <?php echo $period === 'last_12_months' ? 'selected' : ''; ?>><?php echo _l('poa_last_12_months') ?: 'Last 12 Months'; ?></option>
                    <option value="period" <?php echo $period === 'period' ? 'selected' : ''; ?>><?php echo _l('period_datepicker') ?: 'Period'; ?></option>
                  </select>
                </div>
                <div class="col-md-3 poa-credits-period-dates <?php echo $period === 'period' ? '' : 'hide'; ?>">
                  <?php echo render_date_input('from', 'From', $from_ui); ?>
                </div>
                <div class="col-md-3 poa-credits-period-dates <?php echo $period === 'period' ? '' : 'hide'; ?>">
                  <?php echo render_date_input('to', 'To', $to_ui); ?>
                </div>
                <div class="col-md-2 poa-credits-period-dates <?php echo $period === 'period' ? '' : 'hide'; ?>" style="padding-top:25px;">
                  <button type="submit" class="btn btn-primary"><?php echo _l('apply'); ?></button>
                </div>
              </div>
            <?php echo form_close(); ?>

            <div class="tw-flex tw-items-center tw-mb-2">
              <label class="tw-inline-flex tw-items-center" style="padding-left: 20px;">
                <input type="checkbox" id="hide-zero-balance" class="tw-mr-2" style="margin-right: 15px;margin-top: -1px;">
                  Hide customers with zero balance
              </label>
            </div>
            <div class="table-responsive">
              <table class="table table-striped table-bordered" id="credits-table" style="width:100%">
                <thead>
                  <tr>
                    <th><?php echo _l('client'); ?></th>
                    <th><?php echo _l('invoices_total'); ?></th>
                    <th><?php echo _l('receipts_total'); ?></th>
                    <th><?php echo _l('credit_notes_total'); ?></th>
                    <th><?php echo _l('balance'); ?></th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($rows)) : ?>
                    <?php foreach ($rows as $r): ?>
                      <?php
                        $client   = $r['company'] ?? '';
                        $invTotal = (float)($r['invoices_total'] ?? 0);
                        $recTotal = (float)($r['receipts_total'] ?? 0);
                        $cnTotal  = (float)($r['credit_notes_total'] ?? 0);
                        $balance  = (float)($r['balance'] ?? ($invTotal - ($recTotal + $cnTotal)));

                        $cls = $balance < 0 ? 'text-danger' : ($balance > 0 ? 'text-success' : 'text-muted');
                      ?>
                      <tr>
                        <td><a href="<?php echo admin_url('clients/client/'.$r['client_id'].'?group=poa_statement');?>" target="_blank"><?php echo html_escape($client); ?></a></td>
                        <td data-order="<?php echo $invTotal; ?>"><?php echo number_format($invTotal,2); ?></td>
                        <td data-order="<?php echo $recTotal; ?>"><?php echo number_format($recTotal,2); ?></td>
                        <td data-order="<?php echo $cnTotal; ?>"><?php echo number_format($cnTotal,2); ?></td>
                        <td class="<?php echo $cls; ?>" data-order="<?php echo $balance; ?>">
                          <?php echo number_format($balance,2); ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
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
  /* Μην δείχνεις ποτέ processing/loader στο συγκεκριμένο table */
  #credits-table_wrapper .dataTables_processing,
  #credits-table_wrapper .dt-loader { display:none !important; }
  /* Ακόμα κι αν μείνει η κλάση, μη δείχνεις τίποτα οπτικά */
  #credits-table_wrapper.table-loading { background:none !important; }
</style>

<script>
(function($){
  $(function(){
    var $tbl = $('#credits-table');
    var $period = $('#period');
    var $periodDates = $('.poa-credits-period-dates');

    function toggleCreditsPeriodDates(){
      $periodDates.toggleClass('hide', $period.val() !== 'period');
    }

    toggleCreditsPeriodDates();

    $period.on('change', function(){
      toggleCreditsPeriodDates();
      if ($period.val() !== 'period') {
        $('#poa-credits-period-form').submit();
      }
    });

    $('#from, #to').on('change', function(){
      if ($period.val() === 'period' && $('#from').val() && $('#to').val()) {
        $('#poa-credits-period-form').submit();
      }
    });

    if ($.fn.DataTable.isDataTable($tbl)) {
      try { $tbl.DataTable().clear().destroy(); } catch(e){}
    }

    var dt = $tbl.DataTable({
      processing: false,
      serverSide: false,
      deferRender: true,
      retrieve: true,
      destroy: true,

      /* === NEW: length control & "view all" === */
      stateSave: true,
      pageLength: 25, // default
      lengthMenu: [[25, 50, 100, -1], [25, 50, 100, 'View all']],
      dom: 'Bfrtip', // add the 'l' for length menu (dom: 'Blfrtip')

      order: [[4,'desc']],
      buttons: [
        { extend: 'pageLength' }, // shows 10/25/50/100/All in a button menu (if available)
        { extend: 'copy',  title: 'Client Credits' },
        { extend: 'csv',   title: 'Client Credits' },
        { extend: 'excel', title: 'Client Credits' },
        { extend: 'pdf',   title: 'Client Credits' },
        { extend: 'print', title: 'Client Credits' },
      ],
      language: (typeof app !== 'undefined' && app.lang && app.lang.datatables) ? app.lang.datatables : {}
    });

    // --- Hide zero balance filter (χωρίς να χαλάμε sorting/export) ---
    var $toggle = $('#hide-zero-balance');

    if (!window._creditsZeroFilterAdded) {
      window._creditsZeroFilterAdded = true;

      $.fn.dataTable.ext.search.push(function(settings, data, dataIndex){
        if (settings.nTable !== $tbl[0]) return true;
        if (!$toggle.prop('checked')) return true;

        var node = dt.row(dataIndex).node();
        if (!node) return true;
        var td = node.querySelector('td:last-child');
        if (!td) return true;

        var raw = td.getAttribute('data-order') || td.textContent || '';
        var val = parseFloat(raw.replace(/[^0-9.\-]/g,''));
        if (isNaN(val)) return true;

        return val !== 0;
      });
    }

    $toggle.on('change', function(){ dt.draw(); });

    function nukeLoader(){
      var $w = $('#credits-table_wrapper');
      $w.removeClass('table-loading dt-table-loading');
      $w.find('.dataTables_processing,.dt-loader,.loader,.loading').remove();
    }
    nukeLoader(); setTimeout(nukeLoader, 50); setTimeout(nukeLoader, 200);
    $tbl.on('init.dt draw.dt', nukeLoader);

    var $wrap = document.getElementById('credits-table_wrapper');
    if ($wrap) {
      new MutationObserver(function(){ nukeLoader(); })
        .observe($wrap, { attributes:true, childList:true, subtree:true });
    }

    try {
      var oldInit = window.initDataTable;
      if (typeof oldInit === 'function') {
        window.initDataTable = function(table){
          try {
            if ($(table).is('#credits-table') || $(table).closest('table').is('#credits-table')) {
              return;
            }
          } catch(e){}
          return oldInit.apply(this, arguments);
        };
      }
    } catch(e){}
  });
})(jQuery);
</script>
