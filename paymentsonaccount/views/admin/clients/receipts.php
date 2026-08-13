<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s">
  <div class="panel-body">
    <h4 class="mbot20"><?php echo _l('Receipts'); ?></h4>

    <div class="table-responsive">
      <table class="table dt-table">
        <thead>
          <tr>
            <th>#</th>
            <th><?php echo _l('date'); ?></th>
            <th><?php echo _l('amount'); ?></th>
            <th><?php echo _l('payment_mode'); ?></th>
            <th><?php echo _l('note'); ?></th>
            <th></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($receipts as $r): ?>
          <tr>
            <td><?php echo html_escape($r->receipt_number); ?></td>
            <td><?php echo _d($r->payment_date); ?></td>
            <td><?php echo app_format_money($r->total_amount, get_base_currency()); ?></td>
            <td><?php echo html_escape($r->payment_mode); ?></td>
            <td><?php echo html_escape($r->note); ?></td>
            <td>
              <a href="<?php echo admin_url('paymentsonaccount/view_receipt/'.$r->id); ?>" class="btn btn-default btn-sm">
                <?php echo _l('view'); ?>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>
