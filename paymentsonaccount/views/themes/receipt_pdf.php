<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** @var TCPDF $pdf */
/** @var object $receipt */

// Core models/helpers
$CI = &get_instance();
$CI->load->model('invoices_model');
$CI->load->model('clients_model');
$CI->load->model('currencies_model');

$dimensions = $pdf->getPageDimensions();

/* -------- Resolve Client -------- */
if (!isset($client) || empty($client)) {
    $clientId = isset($receipt->client_id) ? (int)$receipt->client_id : 0;
    $client   = $clientId ? $CI->clients_model->get($clientId) : null;
}

/* -------- Resolve Currency (OBJECT), fallback safe -------- */
$currency = null;
if ($client && !empty($client->default_currency)) {
    $currency = $CI->currencies_model->get($client->default_currency);
}
if (!$currency) {
    $currency = $CI->currencies_model->get_base_currency();
}
if (!$currency) { // ultimate fallback to avoid null deref
    $currency = (object)[
        'id'                 => null,
        'symbol'             => '',
        'decimal_separator'  => '.',
        'thousand_separator' => ',',
        'decimal_places'     => 2,
        'name'               => '',
    ];
}

/* -------- Header (company / bill-to) -------- */
$y = $pdf->getY();

$company_info = '<div style="color:#424242;">'.format_organization_info().'</div>';

$billToLines = [];
if ($client) {
    $billToLines[] = '<b>'.html_escape(get_company_name($client->userid)).'</b>';
    if (!empty($client->address))     $billToLines[] = html_escape($client->address);
    $cityLine = [];
    if (!empty($client->city))        $cityLine[] = html_escape($client->city);
    if (!empty($client->country))     $cityLine[] = html_escape($client->country);
    if ($cityLine) $billToLines[] = implode(', ', $cityLine);
    if (!empty($client->phonenumber)) $billToLines[] = html_escape($client->phonenumber);
}
$right_info = '<div style="color:#424242;">'.implode('<br>', $billToLines).'</div>';

pdf_multi_row($company_info, $right_info, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

/* -------- Title -------- */
$pdf->SetFontSize(15);
$heading = '<div style="text-align:center">'.mb_strtoupper(_l('payment_receipt'), 'UTF-8').'</div>';
$pdf->Ln(20);
$pdf->writeHTMLCell(0, '', '', '', $heading, 0, 1, false, true, 'L', true);

/* -------- Receipt Meta -------- */
$pdf->SetFontSize($font_size);

$receiptNumber = '';
// προσάρμοσε ανάλογα με το πεδίο που έχεις διαθέσιμο
if (!empty($receipt->receipt_number)) {
    $receiptNumber = $receipt->receipt_number;
} elseif (!empty($receipt->ref)) {
    $receiptNumber = $receipt->ref;
} else {
    $receiptNumber = '#'.$receipt->id;
}
$pdf->Ln(20);
$pdf->Cell(0, 0, 'Receipt Number: '.$receiptNumber, 0, 1, 'L', 0, '', 0);
$pdf->Ln(3);
$pdf->Line($pdf->getX(), $pdf->getY(), 90, $pdf->getY());
$pdf->Ln(3);

$paymentDate = !empty($receipt->payment_date) ? _d($receipt->payment_date) : (_d($receipt->date ?? ''));
$pdf->Cell(0, 0, _l('payment_date').' '.$paymentDate, 0, 1, 'L', 0, '', 0);
$pdf->Ln(3);
$pdf->Line($pdf->getX(), $pdf->getY(), 90, $pdf->getY());
$pdf->Ln(3);

$paymentMode = '';
if (!empty($payment_mode_name)) $paymentMode = $payment_mode_name;
elseif (!empty($receipt->payment_mode_name)) $paymentMode = $receipt->payment_mode_name;
elseif (!empty($receipt->payment_mode))  $paymentMode = $receipt->payment_mode;
elseif (!empty($receipt->paymentmode))   $paymentMode = $receipt->paymentmode;
if (!empty($receipt->paymentmethod))     $paymentMode .= ' - '.$receipt->paymentmethod;
elseif (!empty($receipt->payment_method)) $paymentMode .= ' - '.$receipt->payment_method;

$pdf->Cell(0, 0, _l('payment_view_mode').' '.$paymentMode, 0, 1, 'L', 0, '', 0);

if (!empty($receipt->transactionid) || !empty($receipt->transaction_id)) {
    $tx = !empty($receipt->transactionid) ? $receipt->transactionid : $receipt->transaction_id;
    $pdf->Ln(3);
    $pdf->Line($pdf->getX(), $pdf->getY(), 90, $pdf->getY());
    $pdf->Ln(3);
    $pdf->Cell(0, 0, _l('payment_transaction_id').': '.$tx, 0, 1, 'L', 0, '', 0);
}

/* -------- Total Amount box -------- */
$pdf->Ln(3);
$pdf->Line($pdf->getX(), $pdf->getY(), 90, $pdf->getY());
$pdf->Ln(3);
$pdf->SetFillColor(132, 197, 41);
$pdf->SetTextColor(255);
$pdf->SetFontSize(12);
$pdf->Ln(3);
$pdf->Cell(80, 10, _l('payment_total_amount'), 0, 1, 'C', 1);
$pdf->SetFontSize(11);

$totalAmount = (float)($receipt->total_amount ?? $receipt->amount ?? 0);
$pdf->Cell(80, 10, app_format_money($totalAmount, $currency), 0, 1, 'C', 1);

/* -------- Applied Invoices Table -------- */
$pdf->Ln(10);
$pdf->SetTextColor(0);

$itemsRaw = [];
if (!empty($receipt->invoices_applied)) {
    $decoded = json_decode($receipt->invoices_applied, true);
    if (is_array($decoded)) $itemsRaw = $decoded;
}

$appliedItems = []; // [['invoice_id'=>int,'amount'=>float|null], ...]
foreach ($itemsRaw as $row) {
    if (is_numeric($row)) {
        $appliedItems[] = ['invoice_id' => (int)$row, 'amount' => null];
    } elseif (is_array($row)) {
        $iid = null; $amt = null;
        if (isset($row['invoice_id'])) $iid = (int)$row['invoice_id'];
        elseif (isset($row['id']))     $iid = (int)$row['id'];
        if (isset($row['amount']))     $amt = (float)$row['amount'];
        if ($iid) $appliedItems[] = ['invoice_id'=>$iid, 'amount'=>$amt];
    }
}

if (!empty($appliedItems)) {
    $pdf->SetFont($font_name, 'B', 14);
    $pdf->Cell(0, 0, _l('payment_for_string'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(5);

    $tblhtml  = '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="5" border="0">';
    $tblhtml .= '<tr height="30" style="color:#fff;" bgcolor="#3A4656">';
    $tblhtml .= '<th width="25%">Invoice Number</th>';
    $tblhtml .= '<th width="25%">Invoice Date</th>';
    $tblhtml .= '<th width="25%">Invoice Amount</th>';
    $tblhtml .= '<th width="25%">Payment Amount</th>';
    $tblhtml .= '</tr><tbody>';

    $sumApplied = 0.0;

    foreach ($appliedItems as $item) {
        $inv = $CI->invoices_model->get($item['invoice_id']);
        if (!$inv) continue;

        $invNo   = format_invoice_number($inv->id);
        $invDate = _d($inv->date);
        // μπορείς να εμφανίσεις ποσό τιμολογίου στο νόμισμα πελάτη για συνέπεια
        $invoiceAmount  = (float)$inv->total;
        $appliedAmount  = isset($item['amount']) && $item['amount'] !== null ? (float)$item['amount'] : $totalAmount;

        $sumApplied += $appliedAmount;

        $tblhtml .= '<tr>';
        $tblhtml .= '<td>'.$invNo.'</td>';
        $tblhtml .= '<td>'.$invDate.'</td>';
        $tblhtml .= '<td>'.app_format_money($invoiceAmount, $currency).'</td>';
        $tblhtml .= '<td>'.app_format_money($appliedAmount, $currency).'</td>';
        $tblhtml .= '</tr>';
    }

    // Αν μέρος του ποσού έμεινε “On Account”
    if ($totalAmount > $sumApplied + 0.00001) {
        $onAcc = $totalAmount - $sumApplied;
        $tblhtml .= '<tr>';
        $tblhtml .= '<td>Payment On Account</td>';
        $tblhtml .= '<td>'.html_escape($paymentDate).'</td>';
        $tblhtml .= '<td>-</td>';
        $tblhtml .= '<td>'.app_format_money($onAcc, $currency).'</td>';
        $tblhtml .= '</tr>';
    }

    $tblhtml .= '</tbody></table>';
    $pdf->writeHTML($tblhtml, true, false, false, false, '');
}
