<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
    body {
        font-family: <?php echo get_option('pdf_font'); ?>;
        font-size: <?php echo get_option('pdf_font_size'); ?>px;
        color: #333;
    }
    .table th {
        background-color: <?php echo get_option('pdf_table_heading_color'); ?>;
        color: <?php echo get_option('pdf_table_heading_text_color'); ?>;
        font-weight: bold;
    }
    .total-amount {
        background-color: #70B743;
        color: #fff;
        padding: 10px;
        font-weight: bold;
        text-align: center;
        font-size: 14px;
        margin-top: 15px;
    }
    .receipt-header {
        font-size: 20px;
        font-weight: bold;
        color: #70B743;
    }
</style>

<table width="100%">
    <tr>
        <td align="left" width="50%">
            <?php if(get_option('invoice_company_logo')) { ?>
                <img src="<?php echo base_url('uploads/company/' . get_option('invoice_company_logo')); ?>" style="width:<?php echo get_option('pdf_logo_width'); ?>px;">
                <br><br>
            <?php } ?>
            <strong><?php echo get_option('invoice_company_name'); ?></strong><br>
            <?php echo get_option('invoice_company_address'); ?><br>
            <?php echo get_option('invoice_company_city'); ?>, <?php echo get_option('invoice_company_country_code'); ?><br>
            VAT Number: <?php echo get_option('company_vat'); ?><br>
            <?php echo get_option('invoice_company_phonenumber'); ?><br>
            <?php echo get_option('company_email'); ?>
        </td>

        <td align="right" width="50%">
            <span class="receipt-header"><?php echo _l('Payment Receipt'); ?></span><br>
            <strong>#<?php echo $receipt->receipt_number; ?></strong><br><br>

            <strong>Customer</strong><br>
            <?php echo $client->company; ?><br>
            <?php echo $client->address; ?><br>
            <?php echo $client->city; ?><br>
            <?php echo $client->phonenumber; ?>
        </td>
    </tr>
</table>

<br><br>

<table cellpadding="5" cellspacing="0" width="100%">
    <tr>
        <td><strong>REF:</strong> #<?php echo $receipt->id; ?></td>
    </tr>
    <tr>
        <td><strong>Payment Date:</strong> <?php echo _d($receipt->payment_date); ?></td>
    </tr>
    <tr>
        <td><strong>Payment Mode:</strong> <?php echo $payment_mode_name; ?></td>
    </tr>
    <tr>
        <td><strong>Transaction ID:</strong> <?php echo $receipt->transaction_id ?: '-'; ?></td>
    </tr>
</table>

<div class="total-amount">
    <?php echo _l('Total Amount'); ?><br>
    <?php echo app_format_money($receipt->total_amount, $receipt->currency); ?>
</div>

<br><br>

<h4><?php echo _l('Payment For'); ?></h4>

<table class="table" width="100%" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>Invoice Number</th>
            <th>Invoice Date</th>
            <th>Invoice Amount</th>
            <th>Payment Amount</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($receipt->invoices_applied)) {
            $invoices = json_decode($receipt->invoices_applied, true);
            foreach ($invoices as $invoice_id) {
                $invoice = $this->invoices_model->get($invoice_id);
                if ($invoice) { ?>
                    <tr>
                        <td><?php echo format_invoice_number($invoice->id); ?></td>
                        <td><?php echo _d($invoice->date); ?></td>
                        <td><?php echo app_format_money($invoice->total, $invoice->currency); ?></td>
                        <td><?php echo app_format_money($receipt->total_amount, $invoice->currency); ?></td>
                    </tr>
        <?php } } } ?>
    </tbody>
</table>

<?php if(get_option('document_signature_image')) { ?>
    <br><br>
    <strong>Authorized Signature:</strong><br>
    <img src="<?php echo base_url('uploads/company/' . get_option('document_signature_image')); ?>" height="80">
<?php } ?>
