<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="panel_s">
            <div class="panel-body">
                <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                    <h4 class="no-margin"><?php echo _l('accounting_export'); ?></h4>
                    <a href="<?php echo admin_url('accounting_export/settings'); ?>" class="btn btn-default">
                        <i class="fa fa-cog"></i> <?php echo _l('settings'); ?>
                    </a>
                </div>

                <p class="text-muted"><?php echo _l('accounting_export_description'); ?></p>

                <div class="alert alert-info">
                    <strong><?php echo _l('accounting_export_payment_source'); ?>:</strong>
                    <?php echo $sources['effective_source'] === 'payments_on_account' ? 'PaymentsOnAccount / tblreceipts' : 'Core Payments / tblinvoicepaymentrecords'; ?>
                </div>

                <?php echo form_open(admin_url('accounting_export/export')); ?>
                <div class="row">
                    <div class="col-md-3">
                        <?php echo render_select('document_type', [
                            ['id' => 'all', 'name' => _l('accounting_export_all')],
                            ['id' => 'invoices', 'name' => _l('invoices')],
                            ['id' => 'payments', 'name' => _l('payments')],
                            ['id' => 'credit_notes', 'name' => _l('credit_notes')],
                        ], ['id', 'name'], 'accounting_export_document_type'); ?>
                    </div>
                    <div class="col-md-3">
                        <?php echo render_date_input('date_from', 'accounting_export_date_from'); ?>
                    </div>
                    <div class="col-md-3">
                        <?php echo render_date_input('date_to', 'accounting_export_date_to'); ?>
                    </div>
                    <div class="col-md-3">
                        <?php echo render_select('format', [
                            ['id' => 'csv', 'name' => 'CSV'],
                            ['id' => 'xlsx', 'name' => 'Excel (.xlsx)'],
                        ], ['id', 'name'], 'accounting_export_format'); ?>
                    </div>
                </div>

                <hr>

                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-download"></i> <?php echo _l('accounting_export_generate'); ?>
                </button>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
