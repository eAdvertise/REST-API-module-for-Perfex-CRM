<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="panel_s">
            <div class="panel-body">
                <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                    <h4 class="no-margin"><?php echo _l('accounting_export_settings'); ?></h4>
                    <a href="<?php echo admin_url('accounting_export'); ?>" class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> <?php echo _l('back'); ?>
                    </a>
                </div>

                <?php echo form_open(admin_url('accounting_export/save_settings')); ?>

                <h5><?php echo _l('accounting_export_columns'); ?></h5>
                <div class="row">
                    <div class="col-md-4"><?php echo render_input('accounting_export_col_type', 'accounting_export_col_type_label', $settings['accounting_export_col_type']); ?></div>
                    <div class="col-md-4"><?php echo render_input('accounting_export_col_account_reference', 'accounting_export_col_account_reference_label', $settings['accounting_export_col_account_reference']); ?></div>
                    <div class="col-md-4"><?php echo render_input('accounting_export_col_nominal_ac_ref', 'accounting_export_col_nominal_ac_ref_label', $settings['accounting_export_col_nominal_ac_ref']); ?></div>
                    <div class="col-md-4"><?php echo render_input('accounting_export_col_department_code', 'accounting_export_col_department_code_label', $settings['accounting_export_col_department_code']); ?></div>
                    <div class="col-md-4"><?php echo render_input('accounting_export_col_date', 'accounting_export_col_date_label', $settings['accounting_export_col_date']); ?></div>
                    <div class="col-md-4"><?php echo render_input('accounting_export_col_reference', 'accounting_export_col_reference_label', $settings['accounting_export_col_reference']); ?></div>
                    <div class="col-md-4"><?php echo render_input('accounting_export_col_details', 'accounting_export_col_details_label', $settings['accounting_export_col_details']); ?></div>
                    <div class="col-md-4"><?php echo render_input('accounting_export_col_net_amount', 'accounting_export_col_net_amount_label', $settings['accounting_export_col_net_amount']); ?></div>
                    <div class="col-md-4"><?php echo render_input('accounting_export_col_tax_code', 'accounting_export_col_tax_code_label', $settings['accounting_export_col_tax_code']); ?></div>
                    <div class="col-md-4"><?php echo render_input('accounting_export_col_tax_amount', 'accounting_export_col_tax_amount_label', $settings['accounting_export_col_tax_amount']); ?></div>
                </div>

                <hr>
                <h5><?php echo _l('accounting_export_defaults'); ?></h5>
                <div class="row">
                    <div class="col-md-4"><?php echo render_input('accounting_export_default_account_reference', 'accounting_export_default_account_reference_label', $settings['accounting_export_default_account_reference']); ?></div>
                    <div class="col-md-4"><?php echo render_input('accounting_export_default_nominal_ac_ref', 'accounting_export_default_nominal_ac_ref_label', $settings['accounting_export_default_nominal_ac_ref']); ?></div>
                    <div class="col-md-4"><?php echo render_input('accounting_export_default_department_code', 'accounting_export_default_department_code_label', $settings['accounting_export_default_department_code']); ?></div>
                    <div class="col-md-4"><?php echo render_input('accounting_export_invoice_type_code', 'accounting_export_invoice_type_code_label', $settings['accounting_export_invoice_type_code']); ?></div>
                    <div class="col-md-4"><?php echo render_input('accounting_export_credit_note_type_code', 'accounting_export_credit_note_type_code_label', $settings['accounting_export_credit_note_type_code']); ?></div>
                    <div class="col-md-4"><?php echo render_input('accounting_export_payment_type_code', 'accounting_export_payment_type_code_label', $settings['accounting_export_payment_type_code']); ?></div>
                    <div class="col-md-4"><?php echo render_input('accounting_export_invoice_tax_code', 'accounting_export_invoice_tax_code_label', $settings['accounting_export_invoice_tax_code']); ?></div>
                    <div class="col-md-4"><?php echo render_input('accounting_export_credit_note_tax_code', 'accounting_export_credit_note_tax_code_label', $settings['accounting_export_credit_note_tax_code']); ?></div>
                    <div class="col-md-4"><?php echo render_input('accounting_export_payment_tax_code', 'accounting_export_payment_tax_code_label', $settings['accounting_export_payment_tax_code']); ?></div>
                </div>

                <hr>
                <h5><?php echo _l('accounting_export_behavior'); ?></h5>
                <div class="row">
                    <div class="col-md-4"><?php echo render_select('accounting_export_payment_source_mode', [
                        ['id' => 'auto', 'name' => _l('accounting_export_payment_source_auto')],
                        ['id' => 'payments_on_account', 'name' => _l('accounting_export_payment_source_poa')],
                        ['id' => 'core', 'name' => _l('accounting_export_payment_source_core')],
                    ], ['id', 'name'], 'accounting_export_payment_source_mode_label', $settings['accounting_export_payment_source_mode']); ?></div>
                    <div class="col-md-4"><?php echo render_input('accounting_export_date_format', 'accounting_export_date_format_label', $settings['accounting_export_date_format']); ?></div>
                    <div class="col-md-4"><?php echo render_input('accounting_export_csv_delimiter', 'accounting_export_csv_delimiter_label', $settings['accounting_export_csv_delimiter']); ?></div>
                </div>

                <div class="row">
                    <div class="col-md-4"><?php echo render_input('accounting_export_details_template_invoice', 'accounting_export_details_template_invoice_label', $settings['accounting_export_details_template_invoice']); ?></div>
                    <div class="col-md-4"><?php echo render_input('accounting_export_details_template_credit', 'accounting_export_details_template_credit_label', $settings['accounting_export_details_template_credit']); ?></div>
                    <div class="col-md-4"><?php echo render_input('accounting_export_details_template_payment', 'accounting_export_details_template_payment_label', $settings['accounting_export_details_template_payment']); ?></div>
                </div>

                <div class="alert alert-info mtop20">
                    <strong><?php echo _l('accounting_export_tokens'); ?>:</strong> {reference}, {company}, {id}, {invoice_reference}
                    <br>
                    <strong><?php echo _l('accounting_export_payment_source'); ?>:</strong>
                    <?php echo $sources['effective_source'] === 'payments_on_account' ? 'PaymentsOnAccount / tblreceipts' : 'Core Payments / tblinvoicepaymentrecords'; ?>
                </div>

                <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
