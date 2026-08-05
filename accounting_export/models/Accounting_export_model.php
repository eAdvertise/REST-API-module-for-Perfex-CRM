<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Accounting_export_model extends App_Model
{
    protected $setting_keys = [
        'accounting_export_col_type',
        'accounting_export_col_account_reference',
        'accounting_export_col_nominal_ac_ref',
        'accounting_export_col_department_code',
        'accounting_export_col_date',
        'accounting_export_col_reference',
        'accounting_export_col_details',
        'accounting_export_col_net_amount',
        'accounting_export_col_tax_code',
        'accounting_export_col_tax_amount',
        'accounting_export_default_account_reference',
        'accounting_export_default_nominal_ac_ref',
        'accounting_export_default_department_code',
        'accounting_export_invoice_type_code',
        'accounting_export_credit_note_type_code',
        'accounting_export_payment_type_code',
        'accounting_export_invoice_tax_code',
        'accounting_export_credit_note_tax_code',
        'accounting_export_payment_tax_code',
        'accounting_export_payment_source_mode',
        'accounting_export_details_template_invoice',
        'accounting_export_details_template_credit',
        'accounting_export_details_template_payment',
        'accounting_export_date_format',
        'accounting_export_csv_delimiter',
    ];

    public function get_settings()
    {
        $settings = [];
        foreach ($this->setting_keys as $key) {
            $settings[$key] = get_option($key);
        }
        return $settings;
    }

    public function save_settings($data)
    {
        foreach ($this->setting_keys as $key) {
            if (isset($data[$key])) {
                update_option($key, trim((string) $data[$key]));
            }
        }
    }

    public function get_export_headers()
    {
        $s = $this->get_settings();
        return [
            $s['accounting_export_col_type'],
            $s['accounting_export_col_account_reference'],
            $s['accounting_export_col_nominal_ac_ref'],
            $s['accounting_export_col_department_code'],
            $s['accounting_export_col_date'],
            $s['accounting_export_col_reference'],
            $s['accounting_export_col_details'],
            $s['accounting_export_col_net_amount'],
            $s['accounting_export_col_tax_code'],
            $s['accounting_export_col_tax_amount'],
        ];
    }

    public function build_export_rows($filters)
    {
        $settings = $this->get_settings();
        $rows     = [];
        $type     = $filters['document_type'];

        if (in_array($type, ['all', 'invoices'], true)) {
            $rows = array_merge($rows, $this->get_invoice_rows($filters, $settings));
        }

        if (in_array($type, ['all', 'credit_notes'], true)) {
            $rows = array_merge($rows, $this->get_credit_note_rows($filters, $settings));
        }

        if (in_array($type, ['all', 'payments'], true)) {
            $rows = array_merge($rows, $this->get_payment_rows($filters, $settings));
        }

        usort($rows, function ($a, $b) {
            return strcmp((string) $a[4], (string) $b[4]);
        });

        return $rows;
    }

    public function download_csv($filename, $headers, $rows)
    {
        $delimiter = get_option('accounting_export_csv_delimiter') ?: ',';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, $headers, $delimiter);

        foreach ($rows as $row) {
            fputcsv($output, $row, $delimiter);
        }

        fclose($output);
        exit;
    }

    public function get_payment_source_status()
    {
        return [
            'payments_on_account_installed' => $this->is_payments_on_account_available(),
            'effective_source'              => $this->get_effective_payment_source(),
        ];
    }


    protected function get_invoice_rows($filters, $settings)
	{
		$this->db->select('i.id, i.clientid, i.formatted_number, i.number, i.prefix, i.date, i.status, i.subtotal, i.total_tax, i.total, c.company');
		$this->db->from(db_prefix() . 'invoices i');
		$this->db->join(db_prefix() . 'clients c', 'c.userid = i.clientid', 'left');

		// Exclude Cancelled (5) and Draft (6)
		$this->db->where_not_in('i.status', [5, 6]);

		$this->apply_date_range('i.date', $filters);
		$result = $this->db->get()->result_array();

		$rows = [];
		foreach ($result as $item) {
			$reference = $this->normalize_reference($item['formatted_number'], $item['prefix'], $item['number']);

			$rows[] = $this->build_row([
				'type'              => $settings['accounting_export_invoice_type_code'],
				'account_reference' => $settings['accounting_export_default_account_reference'],
				'nominal_ac_ref'    => $settings['accounting_export_default_nominal_ac_ref'],
				'department_code'   => $settings['accounting_export_default_department_code'],
				'date'              => $item['date'],
				'reference'         => $reference,
				'details'           => $this->render_details(
					$settings['accounting_export_details_template_invoice'],
					$reference,
					$item['company'],
					$item
				),
				'net_amount'        => $item['total'],
				'tax_code'          => $settings['accounting_export_invoice_tax_code'],
				'tax_amount'        => $item['total_tax'],
			], $settings);
		}

		return $rows;
	}

	protected function get_credit_note_rows($filters, $settings)
	{
		$this->db->select('cn.id, cn.clientid, cn.formatted_number, cn.number, cn.prefix, cn.date, cn.status, cn.subtotal, cn.total_tax, cn.total, c.company');
		$this->db->from(db_prefix() . 'creditnotes cn');
		$this->db->join(db_prefix() . 'clients c', 'c.userid = cn.clientid', 'left');

		// Exclude Cancelled (5) and Draft (6)
		$this->db->where_not_in('cn.status', [5, 6]);

		$this->apply_date_range('cn.date', $filters);
		$result = $this->db->get()->result_array();

		$rows = [];
		foreach ($result as $item) {
			$reference = $this->normalize_reference($item['formatted_number'], $item['prefix'], $item['number']);

			$rows[] = $this->build_row([
				'type'              => $settings['accounting_export_credit_note_type_code'],
				'account_reference' => $settings['accounting_export_default_account_reference'],
				'nominal_ac_ref'    => $settings['accounting_export_default_nominal_ac_ref'],
				'department_code'   => $settings['accounting_export_default_department_code'],
				'date'              => $item['date'],
				'reference'         => $reference,
				'details'           => $this->render_details(
					$settings['accounting_export_details_template_credit'],
					$reference,
					$item['company'],
					$item
				),
				'net_amount'        => $item['total'],
				'tax_code'          => $settings['accounting_export_credit_note_tax_code'],
				'tax_amount'        => $item['total_tax'],
			], $settings);
		}

		return $rows;
	}

    protected function get_payment_rows($filters, $settings)
    {
        $source = $this->get_effective_payment_source();
        return $source === 'payments_on_account'
            ? $this->get_receipt_payment_rows($filters, $settings)
            : $this->get_core_payment_rows($filters, $settings);
    }

    protected function get_core_payment_rows($filters, $settings)
	{
		$this->db->select('
			p.id,
			p.invoiceid,
			p.amount AS payment_amount,
			p.paymentmode,
			p.paymentmethod,
			p.date,
			p.transactionid,
			i.formatted_number,
			i.number,
			i.prefix,
			c.company
		');
		$this->db->from(db_prefix() . 'invoicepaymentrecords p');
		$this->db->join(db_prefix() . 'invoices i', 'i.id = p.invoiceid', 'left');
		$this->db->join(db_prefix() . 'clients c', 'c.userid = i.clientid', 'left');
		$this->apply_date_range('p.date', $filters);
		$result = $this->db->get()->result_array();

		$rows = [];
		foreach ($result as $item) {
			$invoiceRef = $this->normalize_reference($item['formatted_number'], $item['prefix'], $item['number']);
			$reference  = trim((string) $item['transactionid']) !== '' ? trim((string) $item['transactionid']) : 'PAY-' . $item['id'];

			$rows[] = $this->build_row([
				'type'              => $settings['accounting_export_payment_type_code'],
				'account_reference' => $settings['accounting_export_default_account_reference'],
				'nominal_ac_ref'    => $settings['accounting_export_default_nominal_ac_ref'],
				'department_code'   => $settings['accounting_export_default_department_code'],
				'date'              => $item['date'],
				'reference'         => $reference,
				'details'           => $this->render_details(
					$settings['accounting_export_details_template_payment'],
					$reference,
					$item['company'],
					array_merge($item, ['invoice_reference' => $invoiceRef])
				),
				'net_amount'        => $this->normalize_numeric_amount($item['payment_amount']),
				'tax_code'          => $settings['accounting_export_payment_tax_code'],
				'tax_amount'        => 0,
			], $settings);
		}

		return $rows;
	}

    protected function get_receipt_payment_rows($filters, $settings)
	{
		$this->db->select('
			r.id,
			r.receipt_number,
			r.client_id,
			r.total_amount AS payment_amount,
			r.payment_date,
			r.payment_mode,
			r.payment_method,
			r.transaction_id,
			c.company
		');
		$this->db->from(db_prefix() . 'receipts r');
		$this->db->join(db_prefix() . 'clients c', 'c.userid = r.client_id', 'left');
		$this->apply_date_range('r.payment_date', $filters);
		$result = $this->db->get()->result_array();

		$rows = [];
		foreach ($result as $item) {
			$reference = trim((string) $item['receipt_number']) !== '' ? trim((string) $item['receipt_number']) : 'RCPT-' . $item['id'];

			$rows[] = $this->build_row([
				'type'              => $settings['accounting_export_payment_type_code'],
				'account_reference' => $settings['accounting_export_default_account_reference'],
				'nominal_ac_ref'    => $settings['accounting_export_default_nominal_ac_ref'],
				'department_code'   => $settings['accounting_export_default_department_code'],
				'date'              => $item['payment_date'],
				'reference'         => $reference,
				'details'           => $this->render_details(
					$settings['accounting_export_details_template_payment'],
					$reference,
					$item['company'],
					$item
				),
				'net_amount'        => $this->normalize_numeric_amount($item['payment_amount']),
				'tax_code'          => $settings['accounting_export_payment_tax_code'],
				'tax_amount'        => 0,
			], $settings);
		}

		return $rows;
	}
	protected function normalize_numeric_amount($amount)
	{
		if ($amount === null || $amount === '') {
			return 0;
		}

		if (is_string($amount)) {
			$amount = trim($amount);
			$amount = str_replace([' ', ','], ['', '.'], $amount);
		}

		return (float) $amount;
	}
    protected function build_row($data, $settings)
    {
        return [
            (string) $data['type'],
            (string) $data['account_reference'],
            (string) $data['nominal_ac_ref'],
            (string) $data['department_code'],
            $this->format_date($data['date'], $settings['accounting_export_date_format']),
            (string) $data['reference'],
            (string) $data['details'],
            $this->format_amount($data['net_amount']),
            (string) $data['tax_code'],
            $this->format_amount($data['tax_amount']),
        ];
    }

    protected function apply_date_range($column, $filters)
	{
		$from = isset($filters['date_from']) ? $this->normalize_input_date($filters['date_from']) : null;
		$to   = isset($filters['date_to']) ? $this->normalize_input_date($filters['date_to']) : null;

		if (!empty($from)) {
			$this->db->where($column . ' >=', $from);
		}

		if (!empty($to)) {
			$this->db->where($column . ' <=', $to);
		}
	}

	protected function normalize_input_date($date)
	{
		$date = trim((string) $date);

		if ($date === '') {
			return null;
		}

		// Already SQL format
		if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
			return $date;
		}

		// dd/mm/YYYY
		if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date)) {
			$dt = DateTime::createFromFormat('d/m/Y', $date);
			return $dt ? $dt->format('Y-m-d') : null;
		}

		// dd-mm-YYYY
		if (preg_match('/^\d{2}\-\d{2}\-\d{4}$/', $date)) {
			$dt = DateTime::createFromFormat('d-m-Y', $date);
			return $dt ? $dt->format('Y-m-d') : null;
		}

		$ts = strtotime($date);
		if ($ts) {
			return date('Y-m-d', $ts);
		}

		return null;
	}

    protected function normalize_reference($formatted, $prefix, $number)
    {
        $formatted = trim((string) $formatted);
        if ($formatted !== '') {
            return $formatted;
        }
        return trim((string) $prefix) . $number;
    }

    protected function render_details($template, $reference, $company, $row)
    {
        $replacements = [
            '{reference}' => (string) $reference,
            '{company}'   => (string) $company,
            '{id}'        => isset($row['id']) ? (string) $row['id'] : '',
            '{invoice_reference}' => isset($row['invoice_reference']) ? (string) $row['invoice_reference'] : '',
        ];

        $details = strtr((string) $template, $replacements);
        return trim(preg_replace('/\s+/', ' ', $details));
    }

    protected function format_date($date, $format)
    {
        if (!$date) {
            return '';
        }
        $ts = strtotime((string) $date);
        if (!$ts) {
            return (string) $date;
        }
        return date($format ?: 'Y-m-d', $ts);
    }

    protected function format_amount($amount)
    {
        return number_format((float) $amount, 2, '.', '');
    }

    protected function signed_amount($amount, $sign)
    {
        return ((float) $amount) * ((int) $sign);
    }

    protected function get_effective_payment_source()
    {
        $mode = get_option('accounting_export_payment_source_mode');
        $poa  = $this->is_payments_on_account_available();

        if ($mode === 'payments_on_account' && $poa) {
            return 'payments_on_account';
        }
        if ($mode === 'core') {
            return 'core';
        }
        return $poa ? 'payments_on_account' : 'core';
    }

    protected function is_payments_on_account_available()
    {
        if (!$this->db->table_exists(db_prefix() . 'receipts')) {
            return false;
        }

        if (!$this->db->table_exists(db_prefix() . 'modules')) {
            return true;
        }

        $module = $this->db->where('module_name', 'paymentsonaccount')
            ->where('active', 1)
            ->get(db_prefix() . 'modules')
            ->row();

        return (bool) $module;
    }
}
