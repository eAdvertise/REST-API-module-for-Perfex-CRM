<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
  h1,h2,h3,h4 { margin:0; padding:0; }
  .muted { color:#777; }
  .mt10 { margin-top:10px; }
  .mb10 { margin-bottom:10px; }
  table { width:100%; border-collapse: collapse; }
  th, td { border:1px solid #ddd; padding:6px; }
  th { background:#f5f5f5; }
  .text-right { text-align:right; }
  .no-border td, .no-border th { border:0; }
</style>

<h3><?php echo _l('account_summary'); ?></h3>
<p class="muted">
  <?php echo _l('customer_statement_for', get_company_name($client->userid)); ?><br/>
  <?php echo _l('statement_from_to', [$from, $to]); ?>
</p>

<table class="no-border mt10">
  <tr>
    <td width="60%">
      <strong><?php echo _l('statement_bill_to'); ?>:</strong><br/>
      <?php echo format_customer_info($client, 'statement', 'billing'); ?>
    </td>
    <td width="40%" class="text-right">
      <?php echo format_organization_info(); ?>
    </td>
  </tr>
</table>

<table class="mt10">
  <tbody>
    <tr>
      <td><?php echo _l('statement_beginning_balance'); ?></td>
      <td class="text-right"><?php echo app_format_money($statement['beginning_balance'], $statement['currency']); ?></td>
    </tr>
    <tr>
      <td><?php echo _l('invoiced_amount'); ?></td>
      <td class="text-right"><?php echo app_format_money($statement['invoiced_amount'], $statement['currency']); ?></td>
    </tr>
    <tr>
      <td><?php echo _l('amount_received'); ?></td>
      <td class="text-right"><?php echo app_format_money($statement['amount_received'], $statement['currency']); ?></td>
    </tr>
    <tr>
      <th><?php echo _l('balance_due'); ?></th>
      <th class="text-right"><?php echo app_format_money($statement['balance_due'], $statement['currency']); ?></th>
    </tr>
  </tbody>
</table>

<h4 class="mt10"><?php echo _l('customer_statement_info', [$from, $to]); ?></h4>

<table class="mt10">
  <thead>
    <tr>
      <th><?php echo _l('statement_heading_date'); ?></th>
      <th><?php echo _l('statement_heading_details'); ?></th>
      <th class="text-right"><?php echo _l('statement_heading_amount'); ?></th>
      <th class="text-right"><?php echo _l('statement_heading_payments'); ?></th>
      <th class="text-right"><?php echo _l('statement_heading_balance'); ?></th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><?php echo $from; ?></td>
      <td><?php echo _l('statement_beginning_balance'); ?></td>
      <td class="text-right"><?php echo app_format_money($statement['beginning_balance'], $statement['currency'], true); ?></td>
      <td></td>
      <td class="text-right"><?php echo app_format_money($statement['beginning_balance'], $statement['currency'], true); ?></td>
    </tr>
    <?php
      $running = $statement['beginning_balance'];
      foreach ($statement['result'] as $row):
        $isInvoice = isset($row['invoice_id']);
        $isReceipt = isset($row['receipt_id']);
    ?>
    <tr>
      <td><?php echo _d($row['date']); ?></td>
      <td>
        <?php
          if ($isInvoice) {
            echo _l('statement_invoice_details', [
              format_invoice_number($row['invoice_id']),
              _d($row['duedate'])
            ]);
          } elseif ($isReceipt) {
            echo _l('receipt').' #'.html_escape($row['receipt_number']);
          }
        ?>
      </td>
      <td class="text-right">
        <?php
          if ($isInvoice) {
            echo app_format_money($row['invoice_amount'], $statement['currency'], true);
          }
        ?>
      </td>
      <td class="text-right">
        <?php
          if ($isReceipt) {
            echo app_format_money($row['receipt_total'], $statement['currency'], true);
          }
        ?>
      </td>
      <td class="text-right">
        <?php
          if ($isInvoice)  $running += (float)$row['invoice_amount'];
          if ($isReceipt)  $running -= (float)$row['receipt_total'];
          echo app_format_money($running, $statement['currency'], true);
        ?>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
  <tfoot>
    <tr>
      <th colspan="4" class="text-right"><?php echo _l('balance'); ?></th>
      <th class="text-right"><?php echo app_format_money($running, $statement['currency']); ?></th>
    </tr>
  </tfoot>
</table>
