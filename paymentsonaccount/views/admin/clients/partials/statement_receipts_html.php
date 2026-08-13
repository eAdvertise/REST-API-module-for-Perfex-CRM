<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="col-md-5">
  <div class="text-right">
    <h4 class="tw-my-0 tw-font-semibold"><?php echo _l('account_summary'); ?></h4>
    <p class="text-muted"><?php echo e(_l('statement_from_to', [$from, $to])); ?></p>
    <hr />
    <table class="table statement-account-summary">
      <tbody>
        <tr>
          <td class="text-left"><?php echo _l('statement_beginning_balance'); ?>:</td>
          <td><?php echo e(app_format_money($statement['beginning_balance'], $statement['currency'])); ?></td>
        </tr>
        <tr>
          <td class="text-left"><?php echo _l('invoiced_amount'); ?>:</td>
          <td><?php echo e(app_format_money($statement['invoiced_amount'], $statement['currency'])); ?></td>
        </tr>
        <tr>
          <td class="text-left"><?php echo _l('amount_received'); ?>:</td>
          <td><?php echo e(app_format_money($statement['amount_received'], $statement['currency'])); ?></td>
        </tr>
        <tr>
          <td class="text-left"><?php echo (_l('credit_notes') ?: 'Credit notes'); ?>:</td>
          <td><?php echo e(app_format_money($statement['credit_notes_amount'] ?? 0, $statement['currency'])); ?></td>
        </tr>
      </tbody>
      <tfoot>
        <tr>
          <td class="text-left"><b><?php echo _l('balance_due'); ?></b>:</td>
          <td><?php echo e(app_format_money($statement['balance_due'], $statement['currency'])); ?></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<div class="col-md-12">
  <div class="text-center bold padding-10">
    <?php echo _l('customer_statement_info', [$from, $to]); ?>
  </div>
  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th><b><?php echo _l('statement_heading_date'); ?></b></th>
          <th><b><?php echo _l('statement_heading_details'); ?></b></th>
          <th class="text-right"><b><?php echo _l('statement_heading_amount'); ?></b></th>
          <th class="text-right"><b><?php echo _l('statement_heading_payments'); ?></b></th>
          <th class="text-right"><b><?php echo _l('statement_heading_balance'); ?></b></th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><?php echo e($from); ?></td>
          <td><?php echo _l('statement_beginning_balance'); ?></td>
          <td class="text-right">
            <?php echo e(app_format_money($statement['beginning_balance'], $statement['currency'], true)); ?>
          </td>
          <td></td>
          <td class="text-right">
            <?php echo e(app_format_money($statement['beginning_balance'], $statement['currency'], true)); ?>
          </td>
        </tr>
        <?php
          $running = $statement['beginning_balance'];
          foreach ($statement['result'] as $row):
            $isInvoice     = isset($row['invoice_id']);
            $isReceipt     = isset($row['receipt_id']);
            $isCreditNote  = isset($row['credit_note_id']);
        ?>
        <tr>
          <td><?php echo e(_d($row['date'])); ?></td>
          <td>
            <?php
              if ($isInvoice) {
                echo _l('statement_invoice_details', [
                  '<a href="'.admin_url('invoices/list_invoices/'.$row['invoice_id']).'" target="_blank">'.e(format_invoice_number($row['invoice_id'])).'</a>',
                  e(_d($row['duedate']))
                ]);
              } elseif ($isReceipt) {
                $label = (_l('receipt') ?: 'Receipt') . ' #' . e($row['receipt_number']);
                if (!empty($row['applied_invoices'])) {
                  $ids = $row['applied_invoices'];
                  $links = [];
                  $maxShow = 3;
                  for ($i=0; $i < min(count($ids), $maxShow); $i++) {
                    $invId = (int)$ids[$i];
                    $links[] = '<a href="'.admin_url('invoices/list_invoices/'.$invId).'" target="_blank">'.e(format_invoice_number($invId)).'</a>';
                  }
                  $more = count($ids) - $maxShow;
                  $extra = $more > 0 ? ' +' . $more . ' more' : '';
                  echo $label . ' (' . (_l('invoices') ?: 'Invoices') . ': ' . implode(', ', $links) . $extra . ')';
                } else {
                  echo $label . ' (' . (_l('on_account') ?: 'On Account') . ')';
                }
              } elseif ($isCreditNote) {
                $cnUrl = admin_url('credit_notes/credit_note/'.$row['credit_note_id']);
                $cnNo  = function_exists('format_credit_note_number') ? format_credit_note_number($row['credit_note_id']) : ('CN-' . (int)$row['credit_note_id']);
                echo (_l('credit_note') ?: 'Credit Note') . ' #' .
                     '<a href="'.$cnUrl.'" target="_blank">'.e($cnNo).'</a>';
              }
            ?>
          </td>
          <td class="text-right">
            <?php
              if ($isInvoice) {
                echo e(app_format_money($row['invoice_amount'], $statement['currency'], true));
              }
            ?>
          </td>
          <td class="text-right">
            <?php
              if ($isReceipt) {
                echo e(app_format_money($row['receipt_total'], $statement['currency'], true));
              } elseif ($isCreditNote) {
                echo e(app_format_money($row['credit_note_total'], $statement['currency'], true));
              }
            ?>
          </td>
          <td class="text-right">
            <?php
              if ($isInvoice) {
                $running += (float)$row['invoice_amount'];
              } elseif ($isReceipt) {
                $running -= (float)$row['receipt_total'];
              } elseif ($isCreditNote) {
                $running -= (float)$row['credit_note_total'];
              }
              echo e(app_format_money($running, $statement['currency'], true));
            ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot class="statement_tfoot">
        <tr>
          <td colspan="3" class="text-right"><b><?php echo _l('balance'); ?></b></td>
          <td class="text-right" colspan="2">
            <b><?php echo e(app_format_money($running, $statement['currency'])); ?></b>
          </td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>
