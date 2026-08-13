<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Payments_on_account_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /* =========================
       Receipt CRUD
       ========================= */

    /**
     * Δημιουργία νέου Receipt
     */
    public function create_receipt(
        $client_id,
        $total_amount,
        $payment_mode,
        $invoices_applied = [],
        $note = '',
        $payment_date = null,
        $payment_method = null,
        $transaction_id = null,
        $on_account = false,
        $source_payment_id = null,
        $manual_digits = null
    ) {
        if (!$payment_date) { $payment_date = date('Y-m-d'); }

        // Χειροκίνητη αρίθμηση: δέχεται ΜΟΝΟ ψηφία για το suffix
        if ($manual_digits !== null && $manual_digits !== '') {
            $digits  = trim((string)$manual_digits);
            if (!ctype_digit($digits)) {
                throw new Exception('Only digits are allowed for the receipt number.');
            }
            $prefix  = (string)get_option('receipt_number_prefix');
            $padding = (int)(get_option('number_padding') ?: 4);
            $receipt_number = $prefix . str_pad((int)$digits, max(1, $padding), '0', STR_PAD_LEFT);

            // Μοναδικότητα
            $exists = $this->db->where('receipt_number', $receipt_number)
                               ->limit(1)->get(db_prefix().'receipts')->row();
            if ($exists) {
                throw new Exception('This receipt number already exists.');
            }
        } else {
            $receipt_number = $this->generate_receipt_number();
        }

        $staff_id = is_staff_logged_in() ? (int)get_staff_user_id() : 0;

        $data = [
            'receipt_number'   => $receipt_number,
            'client_id'        => (int)$client_id,
            'staff_id'         => $staff_id,
            'total_amount'     => (float)$total_amount,
            'payment_mode'     => (string)$payment_mode,
            'payment_date'     => $payment_date,
            'payment_method'   => $payment_method,
            'transaction_id'   => $transaction_id,
            'invoices_applied' => json_encode((array)$invoices_applied),
            'note'             => (string)$note,
            'date_created'     => date('Y-m-d H:i:s'),
        ];
        if ($this->db->field_exists('source_payment_id', db_prefix().'receipts')) {
            $data['source_payment_id'] = $source_payment_id ? (int)$source_payment_id : null;
        }

        $this->db->insert(db_prefix().'receipts', $data);
        $receipt_id = (int)$this->db->insert_id();

        /**
         * Auto-allocation:
         * - Γίνεται ΜΟΝΟ όταν ΔΕΝ είναι on_account ΚΑΙ το receipt ΔΕΝ προήλθε από core payment (hook).
         * - Αν δόθηκαν συγκεκριμένα invoice_ids -> τα τιμάμε.
         * - Αλλιώς -> FIFO auto.
         */
        $came_from_core_payment = !empty($source_payment_id);

        if (!$on_account && !$came_from_core_payment) {
            if (!empty($invoices_applied)) {
                // Χτίσε "αιτήσεις" και άφησε το μοντέλο να περιορίσει στα due/remaining
                $allocs = [];
                foreach ((array)$invoices_applied as $iid) {
                    $iid = (int)$iid;
                    if ($iid > 0) {
                        $allocs[] = ['invoice_id' => $iid, 'amount' => PHP_FLOAT_MAX];
                    }
                }
                if (!empty($allocs)) {
                    $this->apply_receipt_to_invoices($receipt_id, $allocs);
                }
            } else {
                // FIFO σε όλα τα ανοικτά
                $this->apply_receipt_to_invoices($receipt_id, null);
            }
        }

        return $receipt_id;
    }

    public function get_receipt($id)
    {
        return $this->db->where('id', (int)$id)->get(db_prefix().'receipts')->row();
    }

    public function get_all_receipts()
    {
        $this->db->order_by('date_created', 'DESC');
        return $this->db->get(db_prefix().'receipts')->result_array();
    }
	
	/**
	 * BACKWARD-COMPAT: Παλιά υπογραφή που καλούσες π.χ. get_by_client($client->userid)
	 * Επιστρέφει τις αποδείξεις του πελάτη ταξινομημένες νεότερες πρώτα.
	 *
	 * @param int $client_id
	 * @return array
	 */
	public function get_by_client(int $client_id)
	{
		return $this->get_client_receipts_list($client_id, [
			'with_applied' => true,
			'limit'        => 0,   // 0 = χωρίς limit
			'offset'       => 0,
			'order'        => 'DESC', // νεότερες πρώτα
		]);
	}

	/**
	 * Κύρια υλοποίηση: λίστα αποδείξεων πελάτη με optional επιλογές.
	 * options:
	 *  - from (Y-m-d), to (Y-m-d) : φιλτράρισμα με ημερομηνίες πληρωμής
	 *  - with_applied (bool)      : αν θες να επιστρέψει και decoded το invoices_applied
	 *  - limit (int), offset (int)
	 *  - order ('ASC'|'DESC')     : κατά payment_date, έπειτα date_created
	 */
	public function get_client_receipts_list(int $client_id, array $options = []): array
	{
		if ($client_id <= 0) { return []; }

		$opts = array_merge([
			'from'         => null,
			'to'           => null,
			'with_applied' => false,
			'limit'        => 0,
			'offset'       => 0,
			'order'        => 'DESC',
		], $options);

		$this->db->from(db_prefix().'receipts');
		$this->db->where('client_id', $client_id);

		if (!empty($opts['from'])) {
			$this->db->where('payment_date >=', $opts['from']);
		}
		if (!empty($opts['to'])) {
			$this->db->where('payment_date <=', $opts['to']);
		}

		// Ταξινόμηση: πρώτα κατά payment_date, μετά date_created
		$direction = strtoupper($opts['order']) === 'ASC' ? 'ASC' : 'DESC';
		$this->db->order_by('payment_date', $direction);
		$this->db->order_by('date_created', $direction);

		if (!empty($opts['limit'])) {
			$this->db->limit((int)$opts['limit'], (int)$opts['offset']);
		}

		$rows = $this->db->get()->result_array();

		if ($opts['with_applied']) {
			foreach ($rows as &$r) {
				$decoded = [];
				if (!empty($r['invoices_applied'])) {
					$tmp = json_decode($r['invoices_applied'], true);
					if (is_array($tmp)) {
						// ομογενοποίηση: [{invoice_id, amount}] ή [ids]
						foreach ($tmp as $item) {
							if (is_numeric($item)) {
								$decoded[] = ['invoice_id'=>(int)$item, 'amount'=>null];
							} elseif (is_array($item)) {
								$iid = isset($item['invoice_id']) ? (int)$item['invoice_id']
									 : (isset($item['id']) ? (int)$item['id'] : 0);
								$amt = isset($item['amount']) ? (float)$item['amount'] : null;
								if ($iid > 0) { $decoded[] = ['invoice_id'=>$iid, 'amount'=>$amt]; }
							}
						}
					}
				}
				$r['_applied'] = $decoded;
			}
			unset($r);
		}

		return $rows;
	}

    /**
     * Ασφαλής ενημέρωση συγκεκριμένων πεδίων
     */
    public function update_receipt_fields(int $id, array $fields): bool
    {
        if (empty($fields)) return false;
        $this->db->where('id', $id)->update(db_prefix().'receipts', $fields);
        return $this->db->affected_rows() > 0;
    }

    /**
     * Διαγραφή receipt:
     * - Τα email logs μένουν στα core activity logs του Perfex
     * - Σβήνει core payments ΜΕΣΩ core model (payments_model->delete)
     * - Σβήνει bridge
     * - Σβήνει receipt
     */
    public function delete_receipt(int $id): bool
    {
        $receipt = $this->db->where('id', $id)->get(db_prefix().'receipts')->row();
        if (!$receipt) { return false; }

        $this->load->model('payments_model');

        $bridge = db_prefix().'receipt_invoice_applications';

        $this->db->trans_start();

        // 1) Core payments (μέσω core)
        $paymentIds = $this->collect_core_payment_ids_for_receipt($id);
        foreach ($paymentIds as $pid) {
            $this->payments_model->delete($pid);
        }

        // 2) Bridge
        $this->db->where('receipt_id', $id)->delete($bridge);

        // 3) Receipt
        $this->db->where('id', $id)->delete(db_prefix().'receipts');

        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    public function is_receipt_number_taken(string $receipt_number, int $exclude_id = 0): bool
    {
        $this->db->where('receipt_number', $receipt_number);
        if ($exclude_id > 0) { $this->db->where('id !=', $exclude_id); }
        return $this->db->count_all_results(db_prefix().'receipts') > 0;
    }

    /* =========================
       Email / PDF
       ========================= */

    public function send_receipt_email($receipt_id)
    {
        $CI = &get_instance();
        $CI->load->model('emails_model');
        $CI->load->model('clients_model');
        $CI->load->helper('paymentsonaccount_mail');

        $receipt = $this->get_receipt($receipt_id);
        if (!$receipt) {
            return false;
        }

        $client = $CI->clients_model->get($receipt->client_id);
        if (!$client) {
            $this->log_receipt_email($receipt->id, '', 'Failure', 'Receipt client was not found.');
            return false;
        }

        $recipients = $this->get_receipt_email_recipients((int)$receipt->client_id, $client);
        if (empty($recipients)) {
            $this->log_receipt_email($receipt->id, '', 'Failure', 'No active customer contact email found for this receipt.');
            return false;
        }

        $template = $this->get_receipt_email_template($client);
        if (!$template) {
            $this->log_receipt_email($receipt->id, implode(', ', $recipients), 'Failure', 'Receipt email template was not found.');
            return false;
        }

        if (isset($template->active) && (int)$template->active === 0) {
            $this->log_receipt_email($receipt->id, implode(', ', $recipients), 'Failure', 'Receipt email template is disabled.');
            return false;
        }

        // PDF: use the module helper/app_pdf path instead of CI's library loader.
        // CI passes library params as one array argument, which makes Receipt_pdf
        // receive [$receipt] instead of the receipt object and throws
        // "Receipt_pdf requires receipt object.".
        try {
            $pdf = $this->receipt_pdf($receipt);
            $raw = $pdf->Output('', 'S');
        } catch (Throwable $e) {
            $this->log_receipt_email($receipt->id, implode(', ', $recipients), 'Failure', $e->getMessage());
            return false;
        }

        // Merge fields are intentionally simple so the template can be edited
        // from Setup > Email Templates (admin/emails).
        $merge = [
            '{client_name}'    => $client->company,
            '{receipt_number}' => $receipt->receipt_number,
            '{total_amount}'   => app_format_money($receipt->total_amount, $this->get_client_currency_for_formatting($client)),
            '{receipt_date}'   => _d($receipt->payment_date ?: $receipt->date_created),
            '{companyname}'    => get_option('invoice_company_name') ?: get_option('companyname'),
        ];

        $subjectTemplate = (string)$template->subject;
        if (trim($subjectTemplate) === '') {
            $subjectTemplate = 'New Payment Receipt {receipt_number}';
        }

        $messageTemplate = (string)$template->message;
        if (trim(strip_tags($messageTemplate)) === '') {
            $messageTemplate = 'Dear {client_name},<br><br>Your payment receipt is attached.<br><br>Receipt Number: {receipt_number}<br>Total Paid: {total_amount}<br>Date: {receipt_date}<br><br>Thank you,<br>{companyname}';
        }

        $subject = $this->parse_receipt_email_template($subjectTemplate, $merge);
        $message = $this->parse_receipt_email_template($messageTemplate, $merge);

        $attachments = [[
            'attachment' => $raw,
            'filename'   => 'receipt_'.$receipt->receipt_number.'.pdf',
            'type'       => 'application/pdf',
        ]];

        $allSent = true;
        foreach ($recipients as $recipient) {
            $sent = send_mail_template_custom($recipient, $subject, $message, [], $attachments);

            $this->log_receipt_email($receipt->id, $recipient, $sent ? 'Success' : 'Failure', $sent ? null : 'Email sending failed.');
            if (!$sent) {
                $allSent = false;
            }
        }

        return $allSent;
    }

    private function get_receipt_email_template($client)
    {
        $language = !empty($client->default_language) ? $client->default_language : get_option('active_language');
        if (!$language) {
            $language = 'english';
        }

        $template = $this->db->where('slug', 'receipt-sent-to-customer')
                             ->where('language', $language)
                             ->limit(1)
                             ->get(db_prefix().'emailtemplates')
                             ->row();

        if (!$template && $language !== 'english') {
            $template = $this->db->where('slug', 'receipt-sent-to-customer')
                                 ->where('language', 'english')
                                 ->limit(1)
                                 ->get(db_prefix().'emailtemplates')
                                 ->row();
        }

        return $template;
    }

    public function get_client_currency_for_formatting($client)
    {
        if (is_numeric($client)) {
            $CI = &get_instance();
            $CI->load->model('clients_model');
            $client = $CI->clients_model->get((int)$client);
        }

        if (is_object($client) && !empty($client->default_currency)) {
            return $client->default_currency;
        }

        return function_exists('get_base_currency') ? get_base_currency() : '';
    }

    private function get_receipt_email_recipients(int $client_id, $client): array
    {
        $CI = &get_instance();
        $contacts = $CI->clients_model->get_contacts($client_id, ['active' => 1]) ?: [];
        $invoiceContacts = [];
        $primaryContacts = [];
        $allContacts     = [];

        foreach ($contacts as $contact) {
            $email = strtolower(trim((string)($contact['email'] ?? '')));
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $allContacts[$email] = $email;

            if (!empty($contact['invoice_emails'])) {
                $invoiceContacts[$email] = $email;
            }

            if (!empty($contact['is_primary'])) {
                $primaryContacts[$email] = $email;
            }
        }

        if (!empty($invoiceContacts)) {
            return array_values($invoiceContacts);
        }

        if (!empty($primaryContacts)) {
            return array_values($primaryContacts);
        }

        if (!empty($client->email)) {
            $email = strtolower(trim((string)$client->email));
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return [$email];
            }
        }

        return array_values($allContacts);
    }

    private function parse_receipt_email_template(string $content, array $merge): string
    {
        if (function_exists('_parse_template_content')) {
            return _parse_template_content($content, $merge);
        }

        return strtr($content, $merge);
    }

    private function log_receipt_email($receipt_id, $recipient, $status, $message = null)
    {
        $description = 'POA Receipt Email ' . (string)$status
            . ' [Receipt ID: ' . (int)$receipt_id
            . ', Recipient: ' . ((string)$recipient !== '' ? (string)$recipient : '-') . ']';

        if (!empty($message)) {
            $description .= ' - ' . (string)$message;
        }

        log_activity($description);
    }

    public function receipt_email_was_sent(int $receipt_id): bool
    {
        return $this->core_activity_log_contains(
            'POA Receipt Email Success [Receipt ID: ' . (int)$receipt_id
        );
    }

    public function get_receipt_email_logs(int $receipt_id): array
    {
        $table = db_prefix().'activity_log';
        if (!$this->db->table_exists($table)) {
            return [];
        }

        return $this->db->like('description', 'POA Receipt Email', 'after')
                        ->like('description', '[Receipt ID: ' . (int)$receipt_id, 'both')
                        ->order_by('date', 'DESC')
                        ->get($table)
                        ->result_array();
    }

    private function core_activity_log_contains(string $needle): bool
    {
        $table = db_prefix().'activity_log';
        if (!$this->db->table_exists($table)) {
            return false;
        }

        return $this->db->like('description', $needle, 'after')
                        ->limit(1)
                        ->count_all_results($table) > 0;
    }

    public function receipt_pdf($receipt)
    {
        return app_pdf('receipt', module_dir_path(PAYMENTS_ON_ACCOUNT_MODULE_NAME, 'libraries/Receipt_pdf.php'), $receipt);
    }

    public function attach_receipt_pdf($receipt, $filename)
    {
        $pdf = app_pdf('receipt', module_dir_path(PAYMENTS_ON_ACCOUNT_MODULE_NAME, 'libraries/Receipt_pdf.php'), $receipt);
        return $pdf->Output($filename, 'S');
    }

    /* =========================
       Numbering
       ========================= */

    private function generate_receipt_number(): string
    {
        $this->db->trans_start();
        $row = $this->db->query("SELECT * FROM `".db_prefix()."options` WHERE `name`='next_receipt_number' FOR UPDATE")->row();

        if (!$row) {
            $this->db->insert(db_prefix().'options', ['name' => 'next_receipt_number', 'value' => 1]);
            $next = 1;
        } else {
            $next = (int)$row->value ?: 1;
        }

        $prefix    = (string)get_option('receipt_number_prefix');
        $padding   = (int)(get_option('number_padding') ?: 4);
        $formatted = $prefix . str_pad($next, max(1, $padding), '0', STR_PAD_LEFT);

        $this->db->set('value', 'value+1', false)->where('name', 'next_receipt_number')->update(db_prefix().'options');
        $this->db->trans_complete();

        return $formatted;
    }

    /* =========================
       Unpaid invoices (for modal)
       ========================= */

    /**
     * Επιστρέφει ΜΟΝΟ ανοιχτά τιμολόγια πελάτη με υπόλοιπο > 0,
     * ταξινομημένα παλαιότερα πρώτα. Περιλαμβάνει total_left_to_pay.
     */
    public function get_client_open_invoices_for_modal(int $client_id): array
    {
        if ($client_id <= 0) return [];

        $draft     = defined('Invoices_model::STATUS_DRAFT') ? \Invoices_model::STATUS_DRAFT : 6;
        $cancelled = defined('Invoices_model::STATUS_CANCELLED') ? \Invoices_model::STATUS_CANCELLED : 5;
        $paid      = defined('Invoices_model::STATUS_PAID') ? \Invoices_model::STATUS_PAID : 2;

        $sql = "
            SELECT i.id, i.clientid, i.number, i.prefix, i.date, i.duedate, i.currency, i.total,
                   (i.total - COALESCE(p.paid,0)) AS total_left_to_pay
            FROM ".db_prefix()."invoices i
            LEFT JOIN (
                SELECT invoiceid, SUM(amount) AS paid
                FROM ".db_prefix()."invoicepaymentrecords
                GROUP BY invoiceid
            ) p ON p.invoiceid = i.id
            WHERE i.clientid = ?
              AND i.status NOT IN (?, ?, ?)
              AND (i.total - COALESCE(p.paid,0)) > 0
            ORDER BY i.date ASC, i.id ASC
        ";
        return $this->db->query($sql, [$client_id, $paid, $cancelled, $draft])->result_array();
    }

    /* =========================
       Apply logic
       ========================= */

    /** μικρό helper: αν υπάρχει πεδίο στο schema, το βάζει στο insert */
    private function _add_field_if_exists(string $table, string $field, &$arr, $value): void
    {
        if ($this->db->field_exists($field, $table)) {
            $arr[$field] = $value;
        }
    }

    /** helper: staffid/staff_id optional (για installations με διαφορετικό schema) */
    private function add_optional_staff_field(array &$insert): void
    {
        $table = db_prefix().'invoicepaymentrecords';
        if ($this->db->field_exists('staffid', $table)) {
            $insert['staffid'] = is_staff_logged_in() ? (int)get_staff_user_id() : 0;
        } elseif ($this->db->field_exists('staff_id', $table)) {
            $insert['staff_id'] = is_staff_logged_in() ? (int)get_staff_user_id() : 0;
        }
    }

    /**
     * Δημιουργεί core payment με προτεραιότητα:
     * process_payment -> add -> fallback direct insert. Επιστρέφει payment_id.
     */
    private function create_core_invoice_payment(int $invoice_id, float $amount, array $meta): int
	{
		$this->load->model('payments_model');

		// inject marker αν δεν υπάρχει ήδη (safety net)
		if (empty($meta['note']) || stripos($meta['note'], 'via Receipt #') === false) {
			$meta['note'] = trim(($meta['note'] ?? '').' via Receipt #AUTO'); // δεν έχουμε εδώ receipt_no – απλά αποτρέπουμε το hook
		}

		$data = [
			'invoiceid'        => $invoice_id,
			'amount'           => $amount,
			'date'             => $meta['payment_date']   ?? date('Y-m-d'),
			'paymentmode'      => (string)($meta['payment_mode']   ?? ''),
			'paymentmethod'    => (string)($meta['payment_method'] ?? ''),
			'transactionid'    => (string)($meta['transaction_id'] ?? ''),
			'note'             => (string)($meta['note'] ?? ''), // <<<<
			'do_not_send_email'=> 1,
			'send_email'       => 0,
		];

		// ... (τα υπόλοιπα ίδια όπως τα έχεις)
	}


    /**
     * High-level API:
     * - Αν $allocations == null -> AUTO FIFO σε όλα τα open invoices του πελάτη.
     * - Αν $allocations δοθεί -> array από ['invoice_id'=>X,'amount'=>Y], περιορίζει σε due/remaining.
     * - Δημιουργεί core payments (batch όταν γίνεται) και γράφει στη bridge.
     * - Επιστρέφει συνολικό ποσό που εφαρμόστηκε.
     */
    public function apply_receipt_to_invoices(int $receipt_id, ?array $allocations = null): float
	{
		$receipt = $this->get_receipt($receipt_id);
		if (!$receipt) return 0.0;

		// === IMPORTANT: marker note για να αγνοήσει το hook ===
		$marker = 'via Receipt #'.$receipt->receipt_number.' (ID '.$receipt_id.')';
		$baseNote = trim((string)$receipt->note);
		$note = $baseNote !== '' ? ($baseNote.' | '.$marker) : $marker;

		$meta = [
			'payment_mode'   => $receipt->payment_mode,
			'payment_method' => $receipt->payment_method,
			'payment_date'   => $receipt->payment_date,
			'note'           => $note,                  // <<<< ΒΑΖΟΥΜΕ ΤΟ MARKER
			'transaction_id' => '',                     // batch: αφήνουμε κενό (core το δέχεται)
		];

		$this->deallocate_receipt($receipt_id);

		$final     = [];
		$remaining = (float)$receipt->total_amount;

		if (is_array($allocations) && !empty($allocations)) {
			foreach ($allocations as $al) {
				if ($remaining <= 0) break;
				$invoice_id = (int)($al['invoice_id'] ?? 0);
				$want       = (float)($al['amount'] ?? 0);
				if ($invoice_id <= 0 || $want <= 0) continue;

				$dueRow = $this->db->query("
					SELECT (i.total - COALESCE(paid.p,0)) AS due
					FROM ".db_prefix()."invoices i
					LEFT JOIN (
					  SELECT invoiceid, SUM(amount) AS p
					  FROM ".db_prefix()."invoicepaymentrecords
					  WHERE invoiceid = ?
					  GROUP BY invoiceid
					) paid ON paid.invoiceid = i.id
					WHERE i.id = ?
					LIMIT 1
				", [$invoice_id, $invoice_id])->row();

				$due = max(0.0, (float)($dueRow->due ?? 0));
				if ($due <= 0) continue;

				$to_apply = min($due, $want, $remaining);
				if ($to_apply > 0) {
					$final[]   = ['invoice_id' => $invoice_id, 'amount' => $to_apply];
					$remaining -= $to_apply;
				}
			}
		} else {
			$open = $this->get_client_open_invoices_ordered((int)$receipt->client_id);
			foreach ($open as $inv) {
				if ($remaining <= 0) break;
				$due = (float)$inv['due'];
				if ($due <= 0) continue;
				$to_apply = min($due, $remaining);
				if ($to_apply > 0) {
					$final[]   = ['invoice_id' => (int)$inv['id'], 'amount' => $to_apply];
					$remaining -= $to_apply;
				}
			}
		}

		if (empty($final)) {
			$this->db->where('id', $receipt_id)->update(db_prefix().'receipts', [
				'invoices_applied' => json_encode([]),
			]);
			return 0.0;
		}

		// === CORE BATCH (ή fallback) — περνάμε το meta με το marker note ===
		$res = $this->create_core_batch_payments($final, $meta);

		// === Bridge + JSON ===
		$applied_total = 0.0;
		$this->db->trans_start();
		foreach ($final as $i => $row) {
			$applied_total += (float)$row['amount'];
			$this->db->insert(db_prefix().'receipt_invoice_applications', [
				'receipt_id'        => $receipt_id,
				'invoice_id'        => (int)$row['invoice_id'],
				'amount'            => (float)$row['amount'],
				'payment_record_id' => isset($res['created_ids'][$i]) ? (int)$res['created_ids'][$i] : null,
				'created_at'        => date('Y-m-d H:i:s'),
			]);
		}
		$this->db->where('id', $receipt_id)
				 ->update(db_prefix().'receipts', [
					 'invoices_applied' => json_encode($final, JSON_UNESCAPED_UNICODE),
				 ]);
		$this->db->trans_complete();

		return (float)$applied_total;
	}


    /**
     * Συγχρονισμός γέφυρας -> core (idempotent). Καλό γι' αλλαγές/επιδιορθώσεις.
     */
    public function sync_receipt_applications_to_core(int $receipt_id): array
    {
        $out = ['ok'=>false,'created'=>0,'skipped'=>0,'errors'=>[]];

        $r = $this->db->where('id', $receipt_id)->get(db_prefix().'receipts')->row();
        if (!$r) { $out['errors'][] = 'Receipt not found.'; return $out; }

        $apps = $this->db->where('receipt_id', $receipt_id)
                         ->get(db_prefix().'receipt_invoice_applications')->result_array();
        if (empty($apps)) { $out['ok'] = true; return $out; }

        $this->db->trans_start();

        foreach ($apps as $a) {
            $invoice_id = (int)$a['invoice_id'];
            $amount     = (float)$a['amount'];
            if ($invoice_id <= 0 || $amount <= 0) { $out['skipped']++; continue; }

            $tx = 'RCPT-'.$receipt_id.'-INV-'.$invoice_id;

            $exists = $this->db->where('invoiceid', $invoice_id)
                               ->where('transactionid', $tx)
                               ->get(db_prefix().'invoicepaymentrecords')->row();
            if ($exists) { $out['skipped']++; continue; }

            $meta = [
                'payment_mode'   => $r->payment_mode,
                'payment_method' => $r->payment_method,
                'payment_date'   => $r->payment_date ?: date('Y-m-d'),
                'note'           => 'Applied from Receipt #'.$r->receipt_number.' (ID '.$receipt_id.')',
                'transaction_id' => $tx,
            ];

            $pid = $this->create_core_invoice_payment($invoice_id, $amount, $meta);
            if ($pid > 0) {
                $out['created']++;
                $this->_recalc_invoice_status($invoice_id);
            } else {
                $out['errors'][] = 'Failed to create core payment for invoice '.$invoice_id;
            }
        }

        $this->db->trans_complete();
        if (!$this->db->trans_status()) { $out['errors'][]='DB transaction failed.'; return $out; }

        $out['ok'] = empty($out['errors']);
        return $out;
    }

    /** Ενημέρωση status τιμολογίου βάσει πληρωμών */
    private function _recalc_invoice_status(int $invoice_id): void
    {
        $inv = $this->db->select('id,total,status')->where('id', $invoice_id)->get(db_prefix().'invoices')->row();
        if (!$inv) return;

        $paid = (float)$this->db->select_sum('amount')->where('invoiceid', $invoice_id)
                ->get(db_prefix().'invoicepaymentrecords')->row()->amount;

        $eps = 0.01;
        if ($paid + $eps >= (float)$inv->total) {
            $this->db->where('id', $invoice_id)->update(db_prefix().'invoices', ['status' => 2]); // PAID
        } elseif ($paid > 0) {
            $this->db->where('id', $invoice_id)->update(db_prefix().'invoices', ['status' => 3]); // PARTIALLY PAID
        }
    }

    /**
     * Μαζεύει ΟΛΑ τα core payment IDs που σχετίζονται με το συγκεκριμένο receipt:
     * - από τη γέφυρα payment_record_id
     * - και όσα έχουν transactionid σαν "RCPT-{receipt_id}-INV-%" (idempotent sync)
     */
    private function collect_core_payment_ids_for_receipt(int $receipt_id): array
    {
        $bridge = db_prefix().'receipt_invoice_applications';
        $core   = db_prefix().'invoicepaymentrecords';

        // Από bridge
        $apps = $this->db->select('payment_record_id')
                         ->from($bridge)
                         ->where('receipt_id', $receipt_id)
                         ->get()->result_array();

        $idsFromBridge = array_values(array_filter(array_map(function ($r) {
            return (int)($r['payment_record_id'] ?? 0);
        }, $apps)));

        // Από transactionid pattern
        $like = 'RCPT-'.$receipt_id.'-INV-';
        $coreIds = $this->db->select('id')->from($core)
                            ->like('transactionid', $like, 'after')
                            ->get()->result_array();

        $idsFromLike = array_values(array_filter(array_map(fn($r) => (int)$r['id'], $coreIds)));

        $all = array_unique(array_filter(array_merge($idsFromBridge, $idsFromLike), fn($v) => $v > 0));
        return array_values($all);
    }

    /**
     * Καθαρίζει προηγούμενες εφαρμογές του receipt:
     * - σβήνει core payments μέσω core model
     * - καθαρίζει bridge
     */
    private function deallocate_receipt(int $receipt_id): void
    {
        $this->load->model('payments_model');

        // Core payments (με ασφάλεια)
        $paymentIds = $this->collect_core_payment_ids_for_receipt($receipt_id);
        foreach ($paymentIds as $pid) {
            $this->payments_model->delete($pid);
        }

        // Bridge
        $this->db->where('receipt_id', $receipt_id)->delete(db_prefix().'receipt_invoice_applications');
    }

    /** Προτιμά το επίσημο update του core, αλλιώς fallback */
    private function refresh_invoice_status(int $invoice_id): void
    {
        $this->load->model('invoices_model');
        if (method_exists($this->invoices_model, 'update_invoice_status')) {
            $this->invoices_model->update_invoice_status($invoice_id);
        } else {
            $this->_recalc_invoice_status($invoice_id);
        }
    }

    /**
     * Δημιουργεί ΠΟΛΛΑ core payments με 1 κλήση μέσω add_batch_payment (core),
     * διαφορετικά κάνει fallback σε process_payment/add ανά γραμμή.
     *
     * @param array $allocs Array από ['invoice_id'=>int,'amount'=>float]
     * @param array $meta   ['payment_mode','payment_method','payment_date','transaction_id','note']
     * @return array        ['created_ids'=>int[], 'created_count'=>int]
     */
    private function create_core_batch_payments(array $allocs, array $meta): array
	{
		$this->load->model('payments_model');

		// 1) Προσπάθεια με add_batch_payment
		if (method_exists($this->payments_model, 'add_batch_payment')) {
			$post = [
				'date'          => $meta['payment_date'] ?? date('Y-m-d'),
				'paymentmode'   => (string)($meta['payment_mode'] ?? ''),
				'paymentmethod' => (string)($meta['payment_method'] ?? ''),
				'transactionid' => (string)($meta['transaction_id'] ?? ''), // κοινό για όλες (ok)
				'note'              => (string)($meta['note'] ?? ''),           // <<<< MARKER PASSES HERE
				'do_not_send_email' => 1,
				'send_email'        => 0,
				'invoice'           => [],
				'amount'            => [],
			];
			foreach ($allocs as $a) {
				$inv = (int)($a['invoice_id'] ?? 0);
				$amt = (float)($a['amount'] ?? 0);
				if ($inv > 0 && $amt > 0) {
					$post['invoice'][] = $inv;
					$post['amount'][]  = $amt;
				}
			}

			try {
				$total = (int)$this->payments_model->add_batch_payment($post);
				if ($total > 0) {
					foreach ($post['invoice'] as $iid) { $this->refresh_invoice_status((int)$iid); }
					return ['created_ids' => [], 'created_count' => $total];
				}
			} catch (\Throwable $e) { /* fallback */ }
		}

		// 2) Fallback ανά γραμμή
		$created = [];
		foreach ($allocs as $a) {
			$invoice_id = (int)($a['invoice_id'] ?? 0);
			$amount     = (float)($a['amount'] ?? 0);
			if ($invoice_id <= 0 || $amount <= 0) continue;

			$record = [
				'invoiceid'     => $invoice_id,
				'amount'        => $amount,
				'date'          => $meta['payment_date'] ?? date('Y-m-d'),
				'paymentmode'   => (string)($meta['payment_mode'] ?? ''),
				'paymentmethod' => (string)($meta['payment_method'] ?? ''),
				'transactionid' => (string)($meta['transaction_id'] ?? ''), // μπορεί να μείνει κενό
				'note'              => (string)($meta['note'] ?? ''),           // <<<< MARKER PASSES HERE
				'do_not_send_email' => 1,
				'send_email'        => 0,
			];

			$pid = 0;
			if (method_exists($this->payments_model, 'process_payment')) {
				try { $pid = (int)$this->payments_model->process_payment($record); } catch (\Throwable $e) { $pid = 0; }
			}
			if (!$pid && method_exists($this->payments_model, 'add')) {
				try { $pid = (int)$this->payments_model->add($record); } catch (\Throwable $e) { $pid = 0; }
			}
			if ($pid) {
				$created[] = $pid;
				$this->refresh_invoice_status($invoice_id);
			}
		}

		return ['created_ids' => $created, 'created_count' => count($created)];
	}


    /** Επιστρέφει open invoices πελάτη (με υπολοιπόμενο due), FIFO */
    private function get_client_open_invoices_ordered(int $client_id): array
    {
        $draft     = defined('Invoices_model::STATUS_DRAFT') ? \Invoices_model::STATUS_DRAFT : 6;
        $cancelled = defined('Invoices_model::STATUS_CANCELLED') ? \Invoices_model::STATUS_CANCELLED : 5;

        $sql = "
            SELECT i.id, i.date, i.total,
                   (i.total - COALESCE(p.paid,0)) AS due
            FROM ".db_prefix()."invoices i
            LEFT JOIN (
              SELECT invoiceid, SUM(amount) AS paid
              FROM ".db_prefix()."invoicepaymentrecords
              GROUP BY invoiceid
            ) p ON p.invoiceid = i.id
            WHERE i.clientid = ?
              AND i.status NOT IN (?, ?)
              AND (i.total - COALESCE(p.paid,0)) > 0
            ORDER BY i.date ASC, i.id ASC
        ";
        return $this->db->query($sql, [$client_id, $draft, $cancelled])->result_array();
    }
	public function build_statement_receipts_pdf(int $customer_id, string $from_sql, string $to_sql)
	{
		$this->load->model('clients_model');
		$this->load->model('currencies_model');

		$client = $this->clients_model->get($customer_id);
		if (!$client) { return [false, 'Client not found']; }

		$currency = !empty($client->default_currency)
			? $this->currencies_model->get($client->default_currency)
			: $this->currencies_model->get_base_currency();

		// TODO: Γέμισε τις πραγματικές τιμές σου εδώ
		$beginning       = 0.0;
		$invoiced_amount = 0.0;
		$amount_received = 0.0;
		$balance_due     = ($beginning + $invoiced_amount) - $amount_received;
		$rows            = [];

		$from_h = _d($from_sql);
		$to_h   = _d($to_sql);

		$data = [
			'client'    => $client,
			'from'      => $from_h,
			'to'        => $to_h,
			'statement' => [
				'currency'          => $currency,       // OBJECT
				'beginning_balance' => $beginning,
				'invoiced_amount'   => $invoiced_amount,
				'amount_received'   => $amount_received,
				'balance_due'       => $balance_due,
				'result'            => $rows,
			],
		];

		// Παράγουμε PDF με το App_pdf της βιβλιοθήκης σου
		$pdf = app_pdf(
			'statement_receipts',
			module_dir_path(PAYMENTS_ON_ACCOUNT_MODULE_NAME, 'libraries/Statement_receipts_pdf.php'),
			$data
		);

		$company  = slug_it(get_company_name($client->userid));
		$filename = 'statement_' . $company . '_' . $from_h . '_to_' . $to_h . '.pdf';

		// ΕΠΙΣΤΡΕΦΟΥΜΕ RAW BYTES (όχι path)
		$raw = $pdf->Output($filename, 'S'); // RAW BYTES

		return [true, $raw, $filename, $balance_due, $currency];
	}
	/**
	 * Χτίζει το Statement (Receipts) PDF και επιστρέφει RAW BYTES (για attachment)
	 * return: [bool ok, string|bytes payload, string filename, float balance_due, object currency]
	 */
	public function build_statement_receipts_pdf_bytes($customer_id, $from_sql, $to_sql)
	{
		$CI =& get_instance();
		$CI->load->model('clients_model');
		$CI->load->model('currencies_model');
		$CI->load->model('invoices_model');

		$draft     = defined('Invoices_model::STATUS_DRAFT')     ? Invoices_model::STATUS_DRAFT     : 6;
		$cancelled = defined('Invoices_model::STATUS_CANCELLED') ? Invoices_model::STATUS_CANCELLED : 5;

		$client = $CI->clients_model->get($customer_id);
		if (!$client) { return [false, 'Client not found']; }

		$currency = !empty($client->default_currency)
			? $CI->currencies_model->get($client->default_currency)
			: $CI->currencies_model->get_base_currency();

		// === helper inline (ίδια λογική με controller)
		$fetchCredits = function($customer_id, $from, $to) use ($CI) {
			$db = $CI->db;
			$table = db_prefix().'creditnotes';
			if (!$db->table_exists($table)) {
				$table = 'tblcreditnotes';
				if (!$db->table_exists($table)) {
					return [0.0, [], 0.0];
				}
			}
			$cols      = array_map('strtolower', (array)$db->list_fields($table));
			$totalCol  = in_array('total', $cols) ? 'total' : (in_array('amount',$cols) ? 'amount' : (in_array('subtotal',$cols) ? 'subtotal' : 'total'));
			$dateCol   = in_array('date', $cols) ? 'date' : (in_array('datecreated',$cols) ? 'datecreated' : 'date');
			$clientCol = in_array('clientid', $cols) ? 'clientid' : (in_array('customer_id',$cols) ? 'customer_id' : 'clientid');

			$db->select_sum($totalCol,'s')->from($table)
			   ->where($clientCol,$customer_id)->where("$dateCol <", $from);
			$before = $db->get()->row();
			$beforeSum = (float)($before->s ?? 0);

			$db->select("id,$dateCol as txn_date,$totalCol as total")->from($table)
			   ->where($clientCol,$customer_id)->where("$dateCol >=", $from)->where("$dateCol <=", $to)
			   ->order_by($dateCol,'ASC');
			$rows = $db->get()->result_array();

			$periodSum = 0.0; foreach ($rows as $r) { $periodSum += (float)$r['total']; }
			return [$beforeSum, $rows, $periodSum];
		};

		// Beginning parts
		$invoices_before = (float)($CI->db->select_sum('total')
			->where('clientid', $customer_id)->where('date <', $from_sql)
			->where_not_in('status', [$draft, $cancelled])
			->get(db_prefix().'invoices')->row()->total ?? 0);

		$receipts_before = (float)($CI->db->select_sum('total_amount')
			->where('client_id', $customer_id)->where('payment_date <', $from_sql)
			->get(db_prefix().'receipts')->row()->total_amount ?? 0);

		list($credit_before, $credit_rows, $credit_notes_amount) = $fetchCredits($customer_id, $from_sql, $to_sql);

		$beginning = $invoices_before - $receipts_before - $credit_before;

		// Period rows (invoices/receipts)
		$invoices = $CI->db->select('id,date as txn_date,duedate,total')
			->where('clientid',$customer_id)->where('date >=',$from_sql)->where('date <=',$to_sql)
			->where_not_in('status', [$draft, $cancelled])->order_by('date','ASC')
			->get(db_prefix().'invoices')->result_array();

		$invoiced_amount = 0.0; foreach ($invoices as $i) { $invoiced_amount += (float)$i['total']; }

		$receipts = $CI->db->select('id,payment_date as txn_date,total_amount as total,receipt_number,invoices_applied')
			->where('client_id',$customer_id)->where('payment_date >=',$from_sql)->where('payment_date <=',$to_sql)
			->order_by('payment_date','ASC')->get(db_prefix().'receipts')->result_array();

		$amount_received = 0.0; foreach ($receipts as $r) { $amount_received += (float)$r['total']; }

		$rows = [];
		foreach ($invoices as $i) {
			$rows[] = ['date'=>$i['txn_date'],'invoice_id'=>(int)$i['id'],'invoice_amount'=>(float)$i['total'],'duedate'=>$i['duedate']];
		}
		foreach ($receipts as $r) {
			$applied=[]; if (!empty($r['invoices_applied'])) { $tmp=json_decode($r['invoices_applied'],true);
				if (is_array($tmp)) { foreach ($tmp as $it) { $applied[]=(int)(is_array($it)&&isset($it['invoice_id'])?$it['invoice_id']:$it); } } }
			$rows[]=[
				'date'=>$r['txn_date'],'receipt_id'=>(int)$r['id'],'receipt_total'=>(float)$r['total'],
				'receipt_number'=>$r['receipt_number'],'applied_invoices'=>array_values(array_unique(array_filter($applied))),
				'on_account'=>empty($applied),
			];
		}
		foreach ($credit_rows as $cn) {
			$rows[] = ['date'=>$cn['txn_date'],'credit_note_id'=>(int)$cn['id'],'credit_note_total'=>(float)$cn['total']];
		}

		usort($rows, function($a,$b){ return strcmp($a['date'], $b['date']); });

		$balance_due = ($beginning + $invoiced_amount) - $amount_received - $credit_notes_amount;

		$data = [
			'client'=>$client,
			'from'=>_d($from_sql),
			'to'=>_d($to_sql),
			'statement'=>[
				'currency'=>$currency,
				'beginning_balance'=>$beginning,
				'invoiced_amount'=>$invoiced_amount,
				'amount_received'=>$amount_received,
				'credit_notes_amount'=>$credit_notes_amount,
				'balance_due'=>$balance_due,
				'result'=>$rows,
			],
		];

		$pdf = app_pdf('statement_receipts', module_dir_path(PAYMENTS_ON_ACCOUNT_MODULE_NAME,'libraries/Statement_receipts_pdf.php'), $data);
		$filename = 'statement_client_'.$customer_id.'_'.date('Ymd_His').'.pdf';

		return [true, $pdf->output('', 'S'), $filename, $balance_due, $currency];
	}


	/**
	 * Report για το view: επιστρέφει rows με:
	 *  company, invoices_total, receipts_total, credit_notes_total, balance
	 *
	 * invoices_total: SUM(invoices.total) έως as_of (status != DRAFT/CANCELLED)
	 * receipts_total: SUM(receipts.total_amount) έως as_of (για client)
	 * credit_notes_total: SUM(creditnotes.total) έως as_of (status != 3)
	 * balance = invoices_total - (receipts_total + credit_notes_total)
	 */
	public function report_credit_balances_full(?int $customer_id = null, ?string $as_of = null): array
	{
		$as_of = $as_of ?: date('Y-m-d');

		// 1) Φέρε όλους τους clients που έχουν τουλάχιστον ένα από: invoice | receipt | credit note έως as_of
		$clientsSql = "
			SELECT DISTINCT c.userid
			FROM " . db_prefix() . "clients c
			LEFT JOIN " . db_prefix() . "invoices i ON i.clientid = c.userid
			LEFT JOIN " . db_prefix() . "receipts r ON r.client_id = c.userid
			LEFT JOIN " . db_prefix() . "creditnotes cn ON cn.clientid = c.userid
			WHERE 1=1
		";

		$binds = [];
		if ($customer_id) {
			$clientsSql .= " AND c.userid = ? ";
			$binds[] = $customer_id;
		}

		// περιορισμοί ημερομηνίας (σε οποιαδήποτε από τις 3 πηγές)
		$clientsSql .= " AND (
				(i.id IS NOT NULL  AND i.date <= ?)
			 OR (r.id IS NOT NULL  AND r.payment_date <= ?)
			 OR (cn.id IS NOT NULL AND cn.date <= ?)
			)";
		$binds[] = $as_of; $binds[] = $as_of; $binds[] = $as_of;

		$clientIds = array_map(function($r){ return (int)$r['userid']; },
						$this->db->query($clientsSql, $binds)->result_array());

		if (empty($clientIds)) { return []; }

		// Προετοιμασία αποτελεσμάτων
		$rows = [];
		foreach ($clientIds as $cid) {
			$rows[$cid] = [
				'client_id'          => $cid,
				'company'            => '',
				'invoices_total'     => 0.0,
				'receipts_total'     => 0.0,
				'credit_notes_total' => 0.0,
				'balance'            => 0.0,
			];
		}

		// 2) Invoices total (status != DRAFT/CANCELLED)
		if (!class_exists('Invoices_model', false)) {
			$this->load->model('invoices_model');
		}
		$invSql = "SELECT clientid, COALESCE(SUM(total),0) AS total
				   FROM " . db_prefix() . "invoices
				   WHERE date <= ?
					 AND status != " . Invoices_model::STATUS_DRAFT . "
					 AND status != " . Invoices_model::STATUS_CANCELLED . "
					 AND clientid IN (" . implode(',', array_fill(0, count($clientIds), '?')) . ")
				   GROUP BY clientid";
		$invBinds = array_merge([$as_of], $clientIds);
		foreach ($this->db->query($invSql, $invBinds)->result_array() as $r) {
			$cid = (int)$r['clientid'];
			$rows[$cid]['invoices_total'] = (float)$r['total'];
		}

		// 3) Receipts έως as_of (νέα πηγή: tbl_receipts module table)
		$rcpSql = "SELECT client_id, COALESCE(SUM(total_amount),0) AS total
				   FROM " . db_prefix() . "receipts
				   WHERE payment_date <= ?
					 AND client_id IN (" . implode(',', array_fill(0, count($clientIds), '?')) . ")
				   GROUP BY client_id";
		$rcpBinds = array_merge([$as_of], $clientIds);
		foreach ($this->db->query($rcpSql, $rcpBinds)->result_array() as $r) {
			$cid = (int)$r['client_id'];
			$rows[$cid]['receipts_total'] = (float)$r['total'];
		}

		// 4) Credit notes έως as_of (status != 3)  — διατηρούμε όπως ήταν
		$cnSql = "SELECT clientid, COALESCE(SUM(total),0) AS total
				  FROM " . db_prefix() . "creditnotes
				  WHERE date <= ?
					AND status != 3
					AND clientid IN (" . implode(',', array_fill(0, count($clientIds), '?')) . ")
				  GROUP BY clientid";
		$cnBinds = array_merge([$as_of], $clientIds);
		foreach ($this->db->query($cnSql, $cnBinds)->result_array() as $r) {
			$cid = (int)$r['clientid'];
			$rows[$cid]['credit_notes_total'] = (float)$r['total'];
		}

		// 5) Ονόματα εταιρειών + balance
		$this->load->model('clients_model');
		foreach ($rows as $cid => &$row) {
			$client = $this->clients_model->get($cid);
			$row['company'] = $client ? $client->company : ('#' . $cid);
			$row['balance'] = (float)$row['invoices_total'] - ((float)$row['receipts_total'] + (float)$row['credit_notes_total']);
		}
		unset($row);

		// Προαιρετικά: sort με βάση balance desc
		usort($rows, function($a,$b){ return $b['balance'] <=> $a['balance']; });

		return $rows;
	}


	/**
	 * Credit balances report for a selected period.
	 * If both dates are null, totals are calculated for all time.
	 */
	public function report_credit_balances_period(?int $customer_id = null, ?string $from = null, ?string $to = null): array
	{
		$pref = db_prefix();
		$clientIds = [];

		$collectClientIds = function (string $sql, array $queryBinds) use (&$clientIds) {
			foreach ($this->db->query($sql, $queryBinds)->result_array() as $row) {
				$clientIds[(int)$row['client_id']] = (int)$row['client_id'];
			}
		};

		$appendDateWhere = function (string $column, array &$queryBinds) use ($from, $to): string {
			$sql = '';
			if ($from) {
				$sql .= " AND {$column} >= ?";
				$queryBinds[] = $from;
			}
			if ($to) {
				$sql .= " AND {$column} <= ?";
				$queryBinds[] = $to;
			}
			return $sql;
		};

		$binds = [];
		$sql = "SELECT DISTINCT clientid AS client_id FROM {$pref}invoices WHERE 1=1" . $appendDateWhere('date', $binds);
		if ($customer_id) { $sql .= ' AND clientid = ?'; $binds[] = $customer_id; }
		$collectClientIds($sql, $binds);

		$binds = [];
		$sql = "SELECT DISTINCT client_id FROM {$pref}receipts WHERE 1=1" . $appendDateWhere('payment_date', $binds);
		if ($customer_id) { $sql .= ' AND client_id = ?'; $binds[] = $customer_id; }
		$collectClientIds($sql, $binds);

		$binds = [];
		$sql = "SELECT DISTINCT clientid AS client_id FROM {$pref}creditnotes WHERE 1=1" . $appendDateWhere('date', $binds);
		if ($customer_id) { $sql .= ' AND clientid = ?'; $binds[] = $customer_id; }
		$collectClientIds($sql, $binds);

		if (empty($clientIds)) { return []; }

		$clientIds = array_values($clientIds);
		$placeholders = implode(',', array_fill(0, count($clientIds), '?'));

		$rows = [];
		foreach ($clientIds as $cid) {
			$rows[$cid] = [
				'client_id'          => $cid,
				'company'            => '',
				'invoices_total'     => 0.0,
				'receipts_total'     => 0.0,
				'credit_notes_total' => 0.0,
				'balance'            => 0.0,
			];
		}

		if (!class_exists('Invoices_model', false)) {
			$this->load->model('invoices_model');
		}

		$binds = [];
		$sql = "SELECT clientid, COALESCE(SUM(total),0) AS total
				FROM {$pref}invoices
				WHERE status != " . Invoices_model::STATUS_DRAFT . "
				  AND status != " . Invoices_model::STATUS_CANCELLED;
		$sql .= $appendDateWhere('date', $binds);
		$sql .= " AND clientid IN ({$placeholders}) GROUP BY clientid";
		foreach ($this->db->query($sql, array_merge($binds, $clientIds))->result_array() as $r) {
			$rows[(int)$r['clientid']]['invoices_total'] = (float)$r['total'];
		}

		$binds = [];
		$sql = "SELECT client_id, COALESCE(SUM(total_amount),0) AS total
				FROM {$pref}receipts
				WHERE 1=1";
		$sql .= $appendDateWhere('payment_date', $binds);
		$sql .= " AND client_id IN ({$placeholders}) GROUP BY client_id";
		foreach ($this->db->query($sql, array_merge($binds, $clientIds))->result_array() as $r) {
			$rows[(int)$r['client_id']]['receipts_total'] = (float)$r['total'];
		}

		$binds = [];
		$sql = "SELECT clientid, COALESCE(SUM(total),0) AS total
				FROM {$pref}creditnotes
				WHERE status != 3";
		$sql .= $appendDateWhere('date', $binds);
		$sql .= " AND clientid IN ({$placeholders}) GROUP BY clientid";
		foreach ($this->db->query($sql, array_merge($binds, $clientIds))->result_array() as $r) {
			$rows[(int)$r['clientid']]['credit_notes_total'] = (float)$r['total'];
		}

		$this->load->model('clients_model');
		foreach ($rows as $cid => &$row) {
			$client = $this->clients_model->get($cid);
			$row['company'] = $client ? $client->company : ('#' . $cid);
			$row['balance'] = (float)$row['invoices_total'] - ((float)$row['receipts_total'] + (float)$row['credit_notes_total']);
		}
		unset($row);

		usort($rows, function($a, $b){ return $b['balance'] <=> $a['balance']; });

		return $rows;
	}

	/** Επιστρέφει true αν φαίνεται εγκατεστημένο/διαθέσιμο το Contact+ (μέσω πινάκων) */
	public function is_contact_plus_installed(): bool
	{
		$db = $this->db;
		// αν υπάρχει έστω ένας από τους βασικούς πίνακες του Contact+
		return $db->table_exists('tblpmc_contacts')
			|| $db->table_exists('tblpmc_contacts_bridge')
			|| $db->table_exists('tblpmc_contact_company');
	}

	/**
	 * ΕΠΙΣΤΡΕΦΕΙ ΜΟΝΟ επαφές Contact+ που είναι συνδεδεμένες με ΤΟΝ ΣΥΓΚΕΚΡΙΜΕΝΟ client.
	 * Δεν υπάρχει ΠΟΤΕ fallback σε "όλες οι επαφές".
	 * Χρησιμοποιεί ΜΟΝΟ τους πίνακες σύνδεσης:
	 *   - tblpmc_contacts_bridge  (pmc_contact_id <-> client_id)
	 *   - tblpmc_contact_company  (contact_id <-> client_id, is_primary)
	 *
	 * @return array<array{
	 *   id:int, firstname:string, lastname:string, email:string,
	 *   is_primary:int, source:string
	 * }>
	 */
	public function get_contact_plus_emails_for_client(int $client_id): array
	{
		$client_id = (int)$client_id;
		if ($client_id <= 0 || !$this->is_contact_plus_installed()) {
			return [];
		}

		$db = $this->db;
		$out = [];

		// A) Μέσω BRIDGE (ΜΟΝΟ όσες έχουν mapping με τον client)
		if ($db->table_exists('tblpmc_contacts_bridge') && $db->table_exists('tblpmc_contacts')) {
			$rows = $db->select('c.id, c.firstname, c.lastname, c.email, c.status')
					   ->from('tblpmc_contacts c')
					   ->join('tblpmc_contacts_bridge b', 'b.pmc_contact_id = c.id', 'inner')
					   ->where('b.client_id', $client_id)
					   ->get()->result_array();

			foreach ($rows as $r) {
				$email = trim((string)($r['email'] ?? ''));
				if ($email === '' || strtolower((string)$r['status']) !== 'active') { continue; }
				$out[] = [
					'id'         => (int)$r['id'],
					'firstname'  => (string)($r['firstname'] ?? ''),
					'lastname'   => (string)($r['lastname'] ?? ''),
					'email'      => $email,
					'is_primary' => 0,
					'source'     => 'bridge',
				];
			}
		}

		// B) Μέσω COMPANY mapping (ΜΟΝΟ όσες έχουν mapping με τον client)
		if ($db->table_exists('tblpmc_contact_company') && $db->table_exists('tblpmc_contacts')) {
			$rows = $db->select('c.id, c.firstname, c.lastname, c.email, c.status, cc.is_primary')
					   ->from('tblpmc_contacts c')
					   ->join('tblpmc_contact_company cc', 'cc.contact_id = c.id', 'inner')
					   ->where('cc.client_id', $client_id)
					   ->get()->result_array();

			foreach ($rows as $r) {
				$email = trim((string)($r['email'] ?? ''));
				if ($email === '' || strtolower((string)$r['status']) !== 'active') { continue; }
				$out[] = [
					'id'         => (int)$r['id'],
					'firstname'  => (string)($r['firstname'] ?? ''),
					'lastname'   => (string)($r['lastname'] ?? ''),
					'email'      => $email,
					'is_primary' => (int)($r['is_primary'] ?? 0),
					'source'     => 'company',
				];
			}
		}

		// Αν ΔΕΝ υπάρχει κανένας από τους 2 πίνακες σύνδεσης → δεν επιστρέφουμε τίποτα.
		// (Αποφεύγουμε να φέρουμε ΟΛΕΣ τις επαφές Contact+)
		if (empty($out)) {
			return [];
		}

		// De-dup by email (κρατά την 1η εμφάνιση)
		$seen = []; $dedup = [];
		foreach ($out as $r) {
			$k = mb_strtolower(trim($r['email']));
			if ($k === '' || isset($seen[$k])) { continue; }
			$seen[$k] = true;
			$dedup[] = $r;
		}

		// Προτεραιότητα primary, μετά αλφαβητικά
		usort($dedup, function($a,$b){
			if ($a['is_primary'] != $b['is_primary']) return $b['is_primary'] <=> $a['is_primary'];
			return strcasecmp($a['firstname'].' '.$a['lastname'], $b['firstname'].' '.$b['lastname']);
		});

		return $dedup;
	}
	// Payments_on_account_model.php
	public function report_receipts(array $filters = []): array
	{
		$from = $filters['from'] ?? date('Y-m-01');
		$to   = $filters['to']   ?? date('Y-m-t');

		$this->db->select('r.id, r.receipt_number, r.payment_date, r.client_id, c.company,
						   r.payment_mode, r.payment_method, r.total_amount, r.staff_id');
		$this->db->from(db_prefix().'receipts r');
		$this->db->join(db_prefix().'clients c','c.userid = r.client_id','left');
		$this->db->where('r.payment_date >=', $from);
		$this->db->where('r.payment_date <=', $to);

		if (!empty($filters['client_id'])) {
			$this->db->where('r.client_id', (int)$filters['client_id']);
		}
		if (!empty($filters['payment_mode'])) {
			$this->db->where('r.payment_mode', $filters['payment_mode']);
		}
		if (!empty($filters['staff_id'])) {
			$this->db->where('r.staff_id', (int)$filters['staff_id']);
		}

		$this->db->order_by('r.payment_date','ASC');
		$rows = $this->db->get()->result_array();

		// aggregates
		$total = 0.0; $byMode = []; $byClient = [];
		foreach ($rows as $r) {
			$amt = (float)$r['total_amount'];
			$total += $amt;
			$byMode[$r['payment_mode']]  = ($byMode[$r['payment_mode']]  ?? 0) + $amt;
			$byClient[$r['client_id']]   = ($byClient[$r['client_id']]   ?? 0) + $amt;
		}

		return [
			'rows'      => $rows,
			'total'     => $total,
			'by_mode'   => $byMode,
			'by_client' => $byClient,
			'from'      => $from,
			'to'        => $to,
		];
	}
	
	public function report_receipts_detailed(array $filters = []): array
	{
		$from = $filters['from'] ?? null; // Y-m-d or null
		$to   = $filters['to']   ?? null;

		$pref = db_prefix();

		$this->db->select("r.id, r.receipt_number, r.payment_date, r.client_id, r.payment_mode, r.payment_method, r.transaction_id, r.total_amount, c.company, c.default_currency");
		$this->db->from($pref.'receipts r');
		$this->db->join($pref.'clients c', 'c.userid = r.client_id', 'left');

		if ($from) { $this->db->where('r.payment_date >=', $from); }
		if ($to)   { $this->db->where('r.payment_date <=', $to); }

		$this->db->order_by('r.payment_date', 'ASC');
		$receipts = $this->db->get()->result_array();
		if (empty($receipts)) return [];

		// Fetch applied invoices via bridge; fallback to JSON if bridge empty
		$ids = array_map(fn($r) => (int)$r['id'], $receipts);
		$map = []; // receipt_id => [invoice_ids]
		if (!empty($ids)) {
			$apps = $this->db->select('receipt_id, invoice_id')
				->from($pref.'receipt_invoice_applications')
				->where_in('receipt_id', $ids)
				->get()->result_array();
			foreach ($apps as $a) {
				$rid = (int)$a['receipt_id'];
				$iid = (int)$a['invoice_id'];
				if ($rid>0 && $iid>0) { $map[$rid][] = $iid; }
			}
		}

		// enrich rows
		foreach ($receipts as &$r) {
			$invoices = $map[$r['id']] ?? null;

			// Fallback: parse JSON if bridge had no rows
			if ($invoices === null) {
				$raw = $this->db->select('invoices_applied')->where('id', (int)$r['id'])->get($pref.'receipts')->row();
				$idsJson = [];
				if ($raw && !empty($raw->invoices_applied)) {
					$tmp = json_decode($raw->invoices_applied, true);
					if (is_array($tmp)) {
						foreach ($tmp as $it) {
							if (is_numeric($it))                   $idsJson[] = (int)$it;
							elseif (is_array($it) && isset($it['invoice_id'])) $idsJson[] = (int)$it['invoice_id'];
							elseif (is_array($it) && isset($it['id']))         $idsJson[] = (int)$it['id'];
						}
					}
				}
				$invoices = array_values(array_unique(array_filter($idsJson)));
			}

			$r['_invoice_ids'] = $invoices ?: [];
		}
		unset($r);

		return $receipts;
	}



}
