<?php defined('BASEPATH') or exit('No direct script access allowed');

/** @var TCPDF $pdf */
/** @var array $statement */
/** @var mixed $client */
/** @var string $from */
/** @var string $to */

$dimensions = $pdf->getPageDimensions();

/* ---- SAFE CURRENCY GUARD ---- */
$currency = (isset($statement['currency']) && is_object($statement['currency']))
    ? $statement['currency']
    : get_base_currency();

/* ---- HEADER (logo + org info) ---- */
$info_right_column = '<div style="color:#424242;">' . format_organization_info() . '</div>';
$info_left_column  = pdf_logo_url();

pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);
$pdf->ln(10);

$y = $pdf->getY();

/* ---- SAFE CLIENT BLOCK ---- */
$client_details  = '<b>' . _l('statement_bill_to') . '</b>';
$client_details .= '<div style="color:#424242;">';

if (isset($client) && is_object($client) && isset($client->userid)) {
    $client_details .= format_customer_info($client, 'statement', 'billing');
} else {
    $client_details .= _l('customer') . ': ' . e(($statement['client_name'] ?? ''));
}
$client_details .= '</div>';

$pdf->writeHTMLCell(($dimensions['wk'] / 2) - $dimensions['lm'] + 15, '', '', $y, $client_details, 0, 0, false, true, 'J', true);

/* ---- SUMMARY ---- */
$from_safe = e($from);
$to_safe   = e($to);

$summary  = '<h2>' . _l('account_summary') . '</h2>';
$summary .= '<div style="color:#676767;">' . _l('statement_from_to', [$from_safe, $to_safe]) . '</div>';
$summary .= '<hr />';
$summary .= '
<table cellpadding="4" border="0" style="color:#424242;" width="100%">
   <tbody>
      <tr>
          <td align="left"><br /><br />' . _l('statement_beginning_balance') . ':</td>
          <td><br /><br />' . app_format_money((float)$statement['beginning_balance'], $currency) . '</td>
      </tr>
      <tr>
          <td align="left">' . _l('invoiced_amount') . ':</td>
          <td>' . app_format_money((float)$statement['invoiced_amount'], $currency) . '</td>
      </tr>
      <tr>
          <td align="left">' . (_l('amount_paid') ?: _l('amount_received')) . ':</td>
          <td>' . app_format_money((float)($statement['amount_received'] ?? 0), $currency) . '</td>
      </tr>
      <tr>
          <td align="left">' . (_l('credit_notes') ?: 'Credit notes') . ':</td>
          <td>' . app_format_money((float)($statement['credit_notes_amount'] ?? 0), $currency) . '</td>
      </tr>
  </tbody>
  <tfoot>
      <tr>
        <td align="left"><b>' . _l('balance_due') . '</b>:</td>
        <td>' . app_format_money((float)$statement['balance_due'], $currency) . '</td>
    </tr>
  </tfoot>
</table>';

$pdf->writeHTMLCell(($dimensions['wk'] / 2) - $dimensions['rm'] - 15, '', '', '', $summary, 0, 1, false, true, 'R', true);

$summary_info = '<div style="text-align: center;">' . _l('customer_statement_info', [$from_safe, $to_safe]) . '</div>';

$pdf->ln(9);
$pdf->writeHTMLCell($dimensions['wk'] - ($dimensions['rm'] + $dimensions['lm']), '', '', $pdf->getY(), $summary_info, 0, 1, false, true, 'C', false);
$pdf->ln(9);

/* ---- TABLE ---- */
$tmpBeginningBalance = (float)$statement['beginning_balance'];

$tblhtml = '<table width="100%" cellspacing="0" cellpadding="8" border="0">
<thead>
 <tr height="10" bgcolor="#e8e8e8" style="color:#424242;">
     <th width="13%"><b>' . _l('statement_heading_date') . '</b></th>
     <th width="30%"><b>' . _l('statement_heading_details') . '</b></th>
     <th align="right"><b>' . _l('statement_heading_amount') . '</b></th>
     <th align="right"><b>' . _l('statement_heading_payments') . '</b></th>
     <th align="right"><b>' . _l('statement_heading_balance') . '</b></th>
 </tr>
</thead>
<tbody>
 <tr>
     <td width="13%">' . $from_safe . '</td>
     <td width="30%">' . _l('statement_beginning_balance') . '</td>
     <td align="right">' . app_format_money((float)$statement['beginning_balance'], $currency, true) . '</td>
     <td></td>
     <td align="right">' . app_format_money((float)$statement['beginning_balance'], $currency, true) . '</td>
 </tr>';

$count = 0;

foreach ((array)$statement['result'] as $row) {
    $isInvoice    = isset($row['invoice_id']);
    $isReceipt    = isset($row['receipt_id']);
    $isCreditNote = isset($row['credit_note_id']);

    $tblhtml .= '<tr' . ((++$count % 2) ? ' bgcolor="#f6f5f5"' : '') . '>
        <td width="13%">' . _d($row['date']) . '</td>
        <td width="30%" style="font-size:80%;">';

    if ($isInvoice) {
        $tblhtml .= _l('statement_invoice_details', [
            format_invoice_number((int)$row['invoice_id']),
            _d($row['duedate']),
        ]);
    } elseif ($isReceipt) {
        $label   = ((_l('receipt') ?: 'Receipt') . ' #' . html_escape($row['receipt_number']));
        $applied = (isset($row['applied_invoices']) && is_array($row['applied_invoices'])) ? $row['applied_invoices'] : [];

        if (!empty($applied)) {
            $maxShow = 5;
            $parts   = [];
            for ($i = 0; $i < min(count($applied), $maxShow); $i++) {
                $parts[] = format_invoice_number((int)$applied[$i]);
            }
            $more  = count($applied) - $maxShow;
            $extra = $more > 0 ? ' +' . $more . ' more' : '';
            $tblhtml .= $label . ' (' . (_l('invoices') ?: 'Invoices') . ': ' . implode(', ', $parts) . $extra . ')';
        } else {
            $tblhtml .= $label . ' (' . (_l('on_account') ?: 'On Account') . ')';
        }
    } elseif ($isCreditNote) {
        $cnNo = function_exists('format_credit_note_number')
            ? format_credit_note_number((int)$row['credit_note_id'])
            : ('CN-' . (int)$row['credit_note_id']);
        $tblhtml .= ((_l('credit_note') ?: 'Credit Note') . ' #' . html_escape($cnNo));
    }

    $tblhtml .= '</td>
        <td align="right">';

    if ($isInvoice) {
        $tblhtml .= app_format_money((float)$row['invoice_amount'], $currency, true);
    }

    $tblhtml .= '</td>
        <td align="right">';

    if ($isReceipt) {
        $tblhtml .= app_format_money((float)$row['receipt_total'], $currency, true);
    } elseif ($isCreditNote) {
        $tblhtml .= app_format_money((float)$row['credit_note_total'], $currency, true);
    }

    $tblhtml .= '</td>
        <td align="right">';

    if ($isInvoice) { $tmpBeginningBalance += (float)$row['invoice_amount']; }
    if ($isReceipt) { $tmpBeginningBalance -= (float)$row['receipt_total']; }
    if ($isCreditNote) { $tmpBeginningBalance -= (float)$row['credit_note_total']; }

    $tblhtml .= app_format_money($tmpBeginningBalance, $currency, true);

    $tblhtml .= '</td></tr>';
}

$tblhtml .= '</tbody>
    <tfoot>
     <tr style="color:#424242;">
         <td></td>
         <td></td>
         <td align="right"><b>' . _l('balance_due') . '</b></td>
         <td></td>
         <td align="right"><b>' . app_format_money((float)$statement['balance_due'], $currency) . '</b></td>
     </tr>
   </tfoot>
</table>';

$pdf->writeHTML($tblhtml, true, false, false, false, '');
