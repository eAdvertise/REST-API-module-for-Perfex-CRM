<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
  h2 { margin: 0 0 10px; }
  table { width:100%; border-collapse: collapse; }
  th, td { border:1px solid #ddd; padding:6px; }
  th { background:#f6f7f9; text-align:left; }
  .text-right { text-align:right; }
  .text-center { text-align:center; }
</style>

<h2><?= _l('customer_statement') ?: 'Customer Statement'; ?></h2>
<p><strong><?= _l('client') ?: 'Client'; ?>:</strong> <?= html_escape($client->company); ?></p>
<?php if (!empty($period['from']) || !empty($period['to'])): ?>
<p><strong>Period:</strong> <?= html_escape($period['from'] ?? '…'); ?> — <?= html_escape($period['to'] ?? '…'); ?></p>
<?php endif; ?>

<table>
  <thead>
    <tr>
      <th style="width:12%">Date</th>
      <th style="width:18%">Reference</th>
      <th>Description</th>
      <th style="width:12%">Debit</th>
      <th style="width:12%">Credit</th>
      <th style="width:12%">Balance</th>
    </tr>
  </thead>
  <tbody>
  <?php if (!empty($rows)): foreach ($rows as $r): ?>
    <tr>
      <td><?= _d($r['date']); ?></td>
      <td><?= html_escape($r['ref']); ?></td>
      <td><?= html_escape($r['desc']); ?></td>
      <td class="text-right"><?= app_format_money($r['debit'] ?? 0, $client->default_currency); ?></td>
      <td class="text-right"><?= app_format_money($r['credit'] ?? 0, $client->default_currency); ?></td>
      <td class="text-right"><?= app_format_money($r['balance'] ?? 0, $client->default_currency); ?></td>
    </tr>
  <?php endforeach; else: ?>
    <tr><td colspan="6" class="text-center">No data</td></tr>
  <?php endif; ?>
  </tbody>
</table>

<p style="margin-top:10px">
  <strong>Totals:</strong>
  Invoices: <?= app_format_money($totals['invoices'] ?? 0, $client->default_currency); ?> —
  Receipts: <?= app_format_money($totals['receipts'] ?? 0, $client->default_currency); ?> —
  Balance: <?= app_format_money($totals['balance'] ?? 0, $client->default_currency); ?>
</p>

<p><em>Printed at: <?= html_escape($printed_at); ?></em></p>
