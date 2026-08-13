<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once(APPPATH . 'libraries/pdf/App_pdf.php');

class Receipt_pdf extends App_pdf
{
    /** @var object $receipt */
    protected $receipt;

    /** @var object|null $client */
    protected $client = null;

    /** @var object|null $currency */
    protected $currency = null;

    /** @var string */
    protected $payment_mode_name = '';

    /** @var string */
    protected $ref = '';

    public function __construct($receipt, $tag = '')
    {
        parent::__construct();

        // Input guard
        if (!is_object($receipt)) {
            throw new \InvalidArgumentException('Receipt_pdf requires receipt object.');
        }
        $this->receipt = $receipt;
        $this->tag     = $tag;

        // Load needed models only if not already loaded
        if (!class_exists('Invoices_model', false)) {
            $this->ci->load->model('invoices_model');
        }
        if (!class_exists('Clients_model', false)) {
            $this->ci->load->model('clients_model');
        }
        if (!class_exists('Payment_modes_model', false)) {
            $this->ci->load->model('payment_modes_model');
        }
        if (!class_exists('Currencies_model', false)) {
            $this->ci->load->model('currencies_model');
        }

        // Client
        $cid = (int)($this->receipt->client_id ?? 0);
        $this->client = $cid ? $this->ci->clients_model->get($cid) : null;

        // Currency object (όχι id)
        if ($this->client && !empty($this->client->default_currency)) {
            $this->currency = $this->ci->currencies_model->get($this->client->default_currency);
        } else {
            $this->currency = $this->ci->currencies_model->get_base_currency();
        }

        // Payment mode name. Ensure the receipt object also carries the
        // resolved label because the PDF theme reads from $receipt first.
        $pmId = (int)($this->receipt->payment_mode ?? 0);
        $pm   = $pmId ? $this->ci->payment_modes_model->get($pmId) : null;
        if ($pm) {
            $this->payment_mode_name = is_array($pm) ? (string)($pm['name'] ?? '') : (string)($pm->name ?? '');
        }
        if ($this->payment_mode_name !== '') {
            $this->receipt->payment_mode_name = $this->payment_mode_name;
        }
        if (empty($this->receipt->paymentmethod) && !empty($this->receipt->payment_method)) {
            $this->receipt->paymentmethod = $this->receipt->payment_method;
        }

        // Human ref (e.g. zero-pad)
        $num = (int)($this->receipt->receipt_number ?? 0);
        $this->ref = $num ? str_pad($num, 6, '0', STR_PAD_LEFT) : ($this->receipt->ref ?? '');

        // Title
        $this->SetTitle('Receipt #' . $this->ref);
    }

    protected function type()
    {
        return 'receipt';
    }

    protected function file_path()
    {
        // View του module
        // modules/paymentsonaccount/views/themes/receiptpdf.php
        return APP_MODULES_PATH . 'paymentsonaccount/views/themes/receiptpdf.php';
    }

    protected function file_name()
    {
        // Όνομα αρχείου για download/attach
        return 'Receipt-' . $this->ref . '.pdf';
    }

    public function prepare()
    {
        // Πέρνα ΟΛΑ στο view (ώστε να μην «ζητάει» μόνο του πράγματα)
        $this->set_view_vars([
            'receipt'            => $this->receipt,
            'client'             => $this->client,
            'currency'           => $this->currency,          // OBJECT (όχι id)
            'payment_mode_name'  => $this->payment_mode_name, // string
            'ref'                => $this->ref,               // string
        ]);

        return $this->build(); // App_pdf θα συμπεριλάβει το view και θα σου δώσει το TCPDF instance
    }
}
