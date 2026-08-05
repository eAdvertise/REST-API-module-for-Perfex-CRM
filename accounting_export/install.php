<?php
defined('BASEPATH') or exit('No direct script access allowed');

$defaults = [
    'accounting_export_module_version'             => ACCOUNTING_EXPORT_MODULE_VERSION,
    'accounting_export_col_type'                   => 'Type',
    'accounting_export_col_account_reference'      => 'Account Reference',
    'accounting_export_col_nominal_ac_ref'         => 'Nominal A/C Ref',
    'accounting_export_col_department_code'        => 'Department Code',
    'accounting_export_col_date'                   => 'Date',
    'accounting_export_col_reference'              => 'Reference',
    'accounting_export_col_details'                => 'Details',
    'accounting_export_col_net_amount'             => 'Net Amount',
    'accounting_export_col_tax_code'               => 'Tax Code',
    'accounting_export_col_tax_amount'             => 'Tax Amount',
    'accounting_export_default_account_reference'  => '',
    'accounting_export_default_nominal_ac_ref'     => '',
    'accounting_export_default_department_code'    => '',
    'accounting_export_invoice_type_code'          => 'SI',
    'accounting_export_credit_note_type_code'      => 'SC',
    'accounting_export_payment_type_code'          => 'SA',
    'accounting_export_invoice_tax_code'           => 'T1',
    'accounting_export_credit_note_tax_code'       => 'T1',
    'accounting_export_payment_tax_code'           => 'T9',
    'accounting_export_payment_source_mode'        => 'auto',
    'accounting_export_details_template_invoice'   => 'Invoice {reference} - {company}',
    'accounting_export_details_template_credit'    => 'Credit Note {reference} - {company}',
    'accounting_export_details_template_payment'   => 'Receipt {reference} - {company}',
    'accounting_export_date_format'                => 'Y-m-d',
    'accounting_export_csv_delimiter'              => ',',
];

foreach ($defaults as $name => $value) {
    if (get_option($name) === '') {
        add_option($name, $value);
    }
}
