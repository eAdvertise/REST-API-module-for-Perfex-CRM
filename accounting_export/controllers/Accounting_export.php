<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Accounting_export extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('accounting_export/accounting_export_model');
        $this->load->library('accounting_export/accounting_export_xlsx');
    }

    public function index()
    {
        if (!staff_can('view', 'accounting_export') && !is_admin()) {
            access_denied('accounting_export');
        }

        $data['title']    = _l('accounting_export');
        $data['settings'] = $this->accounting_export_model->get_settings();
        $data['sources']  = $this->accounting_export_model->get_payment_source_status();
        $this->load->view('accounting_export/manage', $data);
    }

    public function settings()
    {
        if (!staff_can('view', 'accounting_export') && !is_admin()) {
            access_denied('accounting_export');
        }

        $data['title']    = _l('accounting_export_settings');
        $data['settings'] = $this->accounting_export_model->get_settings();
        $data['sources']  = $this->accounting_export_model->get_payment_source_status();
        $this->load->view('accounting_export/settings', $data);
    }

    public function save_settings()
    {
        if (!is_admin()) {
            access_denied('accounting_export');
        }

        if ($this->input->post()) {
            $this->accounting_export_model->save_settings($this->input->post());
            set_alert('success', _l('settings_updated'));
        }

        redirect(admin_url('accounting_export/settings'));
    }

    public function export()
    {
        if (!staff_can('view', 'accounting_export') && !is_admin()) {
            access_denied('accounting_export');
        }

        $filters = [
            'document_type' => $this->input->post('document_type') ?: 'all',
            'date_from'     => $this->input->post('date_from') ?: null,
            'date_to'       => $this->input->post('date_to') ?: null,
            'format'        => $this->input->post('format') ?: 'csv',
        ];

        $rows      = $this->accounting_export_model->build_export_rows($filters);
        $headers   = $this->accounting_export_model->get_export_headers();
        $filename  = 'accounting-export-' . date('Y-m-d-His');

        if ($filters['format'] === 'xlsx') {
            $this->accounting_export_xlsx->download($filename . '.xlsx', $headers, $rows);
            return;
        }

        $this->accounting_export_model->download_csv($filename . '.csv', $headers, $rows);
    }
}
