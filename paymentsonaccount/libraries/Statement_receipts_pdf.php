<?php

defined('BASEPATH') or exit('No direct script access allowed');
require_once(APPPATH . 'libraries/pdf/App_pdf.php');

class Statement_receipts_pdf extends App_pdf
{
    protected $data = [];

    public function __construct(array $data)
    {
        $this->data = $data;

        // Ασφαλή defaults
        $this->data += [
            'client'    => null,
            'from'      => '',
            'to'        => '',
            'statement' => [],
        ];
        $this->data['statement'] += [
            'currency'            => null,
            'beginning_balance'   => 0,
            'invoiced_amount'     => 0,
            'amount_received'     => 0,
            'credit_notes_amount' => 0, // <-- ΝΕΟ
            'balance_due'         => 0,
            'result'              => [],
        ];

        parent::__construct();

        $client  = $this->data['client'];
        $company = $client && isset($client->userid) ? get_company_name($client->userid) : 'client';
        $this->SetTitle('Statement - ' . $company);
    }

    protected function type()
    {
        return 'statement_receipts';
    }

    protected function file_path()
    {
        // modules/paymentsonaccount/views/admin/clients/partials/statement_receipts_pdf.php
        if (function_exists('module_views_path')) {
            return module_views_path(PAYMENTS_ON_ACCOUNT_MODULE_NAME, 'admin/clients/partials/statement_receipts_pdf.php');
        }
        return module_dir_path(PAYMENTS_ON_ACCOUNT_MODULE_NAME) . 'views/admin/clients/partials/statement_receipts_pdf.php';
    }

    protected function file_name()
    {
        $client  = $this->data['client'];
        $company = $client && isset($client->userid) ? get_company_name($client->userid) : 'client';
        return slug_it('statement-' . $company) . '.pdf';
    }

    public function prepare()
    {
        $this->set_view_vars($this->data);
        return $this->build();
    }
}
