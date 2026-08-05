<?php

defined('BASEPATH') or exit('No direct script access allowed');

class PoA_reports extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        if (!is_staff_logged_in()) { access_denied('Reports'); }
        $this->load->model('poa_reports_model');
        $this->load->model('currencies_model');
    }

    public function credits()
    {
        // φίλτρα
        $only_positive = (int)$this->input->get('only_positive', true);
        $min_credit    = (float)$this->input->get('min_credit', true);

        $rows = $this->poa_reports_model->get_client_credit_balances([
            'only_positive' => ($only_positive !== 0),
            'min_credit'    => $min_credit,
        ]);

        $base_currency = $this->currencies_model->get_base_currency();

        $data = [
            'title'         => _l('client_credit_balances'),
            'rows'          => $rows,
            'base_currency' => $base_currency,
            'only_positive' => $only_positive ? 1 : 0,
            'min_credit'    => $min_credit,
        ];

        $this->load->view('paymentsonaccount/reports/credits', $data);
    }

    public function credits_csv()
    {
        if (!has_permission('reports', '', 'view')) { access_denied('reports'); }

        $only_positive = (int)$this->input->get('only_positive', true);
        $min_credit    = (float)$this->input->get('min_credit', true);

        $rows = $this->poa_reports_model->get_client_credit_balances([
            'only_positive' => ($only_positive !== 0),
            'min_credit'    => $min_credit,
        ]);

        $filename = 'client_credit_balances_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename='.$filename);

        $out = fopen('php://output', 'w');
        fputcsv($out, ['ClientID','Customer','CurrencyID','Credit','Receipts','Last Receipt Date']);

        foreach ($rows as $r) {
            fputcsv($out, [
                $r['clientid'],
                $r['company'],
                $r['currency_id'],
                $r['credit'],
                $r['receipts'],
                $r['last_receipt_date'],
            ]);
        }
        fclose($out);
        exit;
    }
}
