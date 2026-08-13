<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Paymentsonaccount extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('payments_on_account_model');
        $this->load->model('clients_model');
        $this->load->model('payment_modes_model');
        if (!function_exists('send_mail_template_custom')) {
            require_once(module_dir_path(PAYMENTS_ON_ACCOUNT_MODULE_NAME, 'helpers/paymentsonaccount_mail_helper.php'));
        }
		$this->load->model('emails_model');
    }

    /** Λίστα */
    public function index()
    {
        if (!has_permission('payments_on_account', '', 'view')) {
            access_denied('Receipts');
        }

        $receipts = $this->payments_on_account_model->get_all_receipts();

        // Προφορτώσεις για company & currency OBJECT ανά client (για app_format_money).
        $client_ids  = array_unique(array_column($receipts, 'client_id'));
        $clients_map = [];
        $baseCur     = get_base_currency(); // OBJECT

        if (!empty($client_ids)) {
            $this->db->where_in('userid', $client_ids);
            $rows = $this->db->get(db_prefix().'clients')->result();
            foreach ($rows as $c) {
                $clients_map[$c->userid] = [
                    'company'  => $c->company,
                    'currency' => !empty($c->default_currency) ? get_currency($c->default_currency) : $baseCur,
                ];
            }
        }

        foreach ($receipts as &$r) {
            $info = $clients_map[$r['client_id']] ?? null;
            $r['company_name'] = $info['company']  ?? '[Unknown Client]';
            $r['currency']     = $info['currency'] ?? $baseCur; // OBJECT, όχι id
        }
        unset($r);

        $data['title']    = _l('Receipts');
        $data['receipts'] = $receipts;
        $this->load->view('manage', $data);
    }

    /** Create */
    public function create_receipt()
    {
        if (!has_permission('payments_on_account', '', 'create')) {
            access_denied('Payments On Account');
        }

        if ($this->input->post()) {
            $p               = $this->input->post();
            $client_id       = (int)($p['client_id'] ?? 0);
            $amount          = (float)($p['amount'] ?? 0);
            $payment_mode    = (string)($p['payment_mode'] ?? '');
            $payment_method  = (string)($p['payment_method'] ?? '');
            $payment_date    = to_sql_date($p['payment_date'] ?? date('Y-m-d'));
            $transaction_id  = (string)($p['transaction_id'] ?? '');
            $note            = (string)($p['note'] ?? '');
            $on_account      = !empty($p['on_account']);
            $manual_digits   = $this->input->post('manual_receipt_digits', true); // μόνο ψηφία
            $invoice_ids     = isset($p['invoice_ids']) ? (array)$p['invoice_ids'] : [];

            // sanitize ids
            $invoice_ids = array_values(array_unique(array_map('intval', $invoice_ids)));
            $invoice_ids = array_filter($invoice_ids, fn($v)=>$v>0);

            try {
                $receipt_id = $this->payments_on_account_model->create_receipt(
                    $client_id,
                    $amount,
                    $payment_mode,
                    $invoice_ids,      // μπορεί να είναι κενό -> auto-FIFO αν not on_account
                    $note,
                    $payment_date,
                    $payment_method,
                    $transaction_id,
                    $on_account,
                    null,              // source_payment_id
                    $manual_digits
                );

                if (empty($p['do_not_send_email'])) {
                    $email_sent = $this->payments_on_account_model->send_receipt_email($receipt_id);
                    set_alert(
                        $email_sent ? 'success' : 'warning',
                        $email_sent ? 'Receipt created and email sent.' : 'Receipt created, but the email was not sent. Check Activity Log for details.'
                    );
                } else {
                    $email_sent = false;
                    set_alert('success', 'Receipt created.');
                }

                paymentsonaccount_emit_event('receipt_created', $receipt_id, ['email_sent' => (bool) $email_sent]);
                if ($email_sent) {
                    paymentsonaccount_emit_event('receipt_email', $receipt_id, ['email_sent' => true]);
                }

                redirect(admin_url('paymentsonaccount/view_receipt/'.$receipt_id));
            } catch (Exception $e) {
                set_alert('danger', $e->getMessage());
            }
			/* Core primary contacts
			$data['contacts'] = $this->clients_model->get_contacts($client_id, ['active'=>1]) ?: [];

			// Contact+ (αν υπάρχει)
			$data['contacts_plus'] = [];
			if ($this->payments_on_account_model->is_contact_plus_installed()) {
				$data['contacts_plus'] = $this->payments_on_account_model->get_contact_plus_emails_for_client($client_id);
			}*/
        }
		
        $data['title']          = 'Create Receipt';
        $data['clients']        = $this->clients_model->get();
        $data['paymentmodes']   = $this->payment_modes_model->get();
        $data['receipt_prefix'] = get_option('receipt_number_prefix');
        $data['number_padding'] = (int)(get_option('number_padding') ?: 4);
        $this->load->view('form', $data);
    }

    /** Προβολή + inline update βασικών πεδίων */
    public function view_receipt($id)
    {
        if (!has_permission('payments_on_account', '', 'view')) {
            access_denied('Receipts');
        }

        $receipt = $this->payments_on_account_model->get_receipt((int)$id);
        if (!$receipt) {
            set_alert('danger', 'Receipt not found.');
            redirect(admin_url('paymentsonaccount'));
            return;
        }

        // Inline update βασικών πεδίων (όχι allocations εδώ).
        if ($this->input->post()) {
            $p = $this->input->post();

            $update = [
                'total_amount'   => (float)($p['amount'] ?? $receipt->total_amount),
                'payment_date'   => to_sql_date($p['payment_date'] ?? $receipt->payment_date),
                'payment_mode'   => (string)($p['payment_mode'] ?? $receipt->payment_mode),
                'payment_method' => (string)($p['payment_method'] ?? $receipt->payment_method),
                'transaction_id' => (string)($p['transaction_id'] ?? $receipt->transaction_id),
                'note'           => (string)($p['note'] ?? $receipt->note),
            ];

            // Προαιρετική αλλαγή του receipt_number με suffix (digits)
            if (isset($p['receipt_number_suffix']) && $p['receipt_number_suffix'] !== '') {
                $prefix        = (string)get_option('receipt_number_prefix');
                $padding       = (int)(get_option('number_padding') ?: 4);
                $digits        = preg_replace('/\D+/', '', (string)$p['receipt_number_suffix']);
                if ($digits === '' || !ctype_digit($digits)) {
                    set_alert('danger', _l('problem_with_input').' Invalid receipt number.');
                    redirect(admin_url('paymentsonaccount/view_receipt/'.$id)); return;
                }
                $new_no = $prefix . str_pad($digits, max(1, $padding), '0', STR_PAD_LEFT);
                if ($this->payments_on_account_model->is_receipt_number_taken($new_no, (int)$id)) {
                    set_alert('danger', _l('problem_with_input').' Receipt number already exists.');
                    redirect(admin_url('paymentsonaccount/view_receipt/'.$id)); return;
                }
                $update['receipt_number'] = $new_no;
            }

            $this->payments_on_account_model->update_receipt_fields((int)$id, $update);
            paymentsonaccount_emit_event('receipt_updated', (int) $id, ['changed_fields' => array_keys($update)]);
            set_alert('success', _l('updated_successfully'));
            redirect(admin_url('paymentsonaccount/view_receipt/'.$id));
            return;
        }

        // Data για view
        $this->load->model('currencies_model');
        $client = $this->clients_model->get($receipt->client_id);

        $data['title']              = 'Receipt '.$receipt->receipt_number;
        $data['receipt']            = $receipt;
        $data['client']             = $client;
        $data['client_id']          = $receipt->client_id;
        $data['client_name']        = $client ? $client->company : '[Unknown Client]';
        $data['client_currency']    = ($client && $client->default_currency)
                                        ? $this->currencies_model->get($client->default_currency)
                                        : get_base_currency();

		// Φόρτωση του template με βάση το slug
		$slug = 'receipt-sent-to-customer';
		$data['template_name'] = $slug;
		$email_template = $this->emails_model->get(['slug' => $slug, 'language' => 'english'], 'row');
		$data['email_template'] = $email_template;
		$data['template_system_name'] = $email_template->name;
		$data['template_id']          = $email_template->emailtemplateid;
		
		
        $mode = $this->payment_modes_model->get($receipt->payment_mode);
        $data['payment_mode_name']  = $mode ? $mode->name : '[Unknown Mode]';
        $data['paymentmodes']       = $this->payment_modes_model->get();

        // Primary contact για modal emailing statements
        $data['contacts'] = $this->clients_model->get_contacts($receipt->client_id, ['active'=>1]) ?: [];

		// Contact+ (μόνο αν είναι εγκατεστημένο)
		$data['contacts_plus'] = [];
		if (
			isset($this->payments_on_account_model) &&
			method_exists($this->payments_on_account_model, 'is_contact_plus_installed') &&
			$this->payments_on_account_model->is_contact_plus_installed()
		) {
			$data['contacts_plus'] = $this->payments_on_account_model->get_contact_plus_emails_for_client($receipt->client_id);
		}

        $this->load->view('view', $data);
    }

    /** PDF */
    public function receipt_pdf($id)
    {
        $this->load->model('Invoices_model');
        $this->load->model('payments_model');
        $this->load->model('Clients_model');

        $receipt = $this->payments_on_account_model->get_receipt((int)$id);
        if (!$receipt) {
            set_alert('danger', _l('sonting_going_wrong_pdf'));
            redirect(admin_url('paymentsonaccount')); return;
        }

        $pm = $this->payment_modes_model->get($receipt->payment_mode);
        $receipt->payment_mode_name = $pm ? $pm->name : '';
        $receipt->ref               = str_pad($receipt->receipt_number, 6, '0', STR_PAD_LEFT);

        $client               = $this->clients_model->get($receipt->client_id);
        $receipt->client      = $client;
        $receipt->client_name = $client ? $client->company : '[Unknown Client]';
        $receipt->invoices    = json_decode($receipt->invoices_applied, true);

        try {
            $pdf = $this->payments_on_account_model->receipt_pdf($receipt);
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Unable to get the size of the image') !== false) {
                show_pdf_unable_to_get_image_size_error();
            }
            set_alert('danger', $e->getMessage());
            redirect(admin_url('paymentsonaccount')); return;
        }

        $type = 'D';
        if ($this->input->get('output_type')) $type = $this->input->get('output_type');
        if ($this->input->get('print'))       $type = 'I';

        ob_end_clean();
        $pdf->Output('payment_receipt_'.$id.'.pdf', $type);
    }
	/**
     * Αποστολή Receipt Email (με PDF επισύναψη)
     */
    public function send_receipt_email($id)
    {
        $success = $this->payments_on_account_model->send_receipt_email($id);
        paymentsonaccount_emit_event('receipt_email', (int) $id, ['email_sent' => (bool) $success]);

        if ($success) {
            set_alert('success', 'Receipt email sent successfully.');
        } else {
            set_alert('danger', 'Failed to send receipt email.');
        }

        redirect(admin_url('paymentsonaccount/view_receipt/' . $id));
    }

    /** Αποστολή email με PDF (manual modal) */
    public function send_receipt_email_manual($id)
	{
		if (!is_staff_logged_in() || !$this->input->is_ajax_request()) {
			ajax_access_denied();
		}

		$this->load->model('Invoices_model');
		$this->load->model('payments_model');
		$this->load->model('Clients_model');

		$receipt = $this->payments_on_account_model->get_receipt((int)$id);
		if (!$receipt) { echo json_encode(['success'=>false]); return; }

		$pm = $this->payment_modes_model->get($receipt->payment_mode);
		$receipt->payment_mode_name = $pm ? $pm->name : '';
		$receipt->ref = str_pad($receipt->receipt_number, 6, '0', STR_PAD_LEFT);

		$client               = $this->clients_model->get($receipt->client_id);
		$receipt->client      = $client;
		$receipt->client_name = $client ? $client->company : '[Unknown Client]';
		$receipt->invoices    = json_decode($receipt->invoices_applied, true);

		$recipients   = (array)$this->input->post('recipients');
		$cc_emails    = (string)$this->input->post('cc_emails');
		$email_body   = (string)$this->input->post('email_body');

		// ---------- SERVER-SIDE SAFETY FILTER ----------
		// επιτρέπεται να σταλούν ΜΟΝΟ:
		// - core contacts του client
		// - Contact+ contacts που είναι συνδεδεμένα με τον client (bridge/company)
		$allowed = [];

		// core contacts
		$coreContacts = $this->clients_model->get_contacts((int)$receipt->client_id, ['active'=>1]) ?: [];
		foreach ($coreContacts as $c) {
			if (!empty($c['email'])) {
				$allowed[strtolower(trim($c['email']))] = true;
			}
		}
		// Contact+ συνδεδεμένοι με τον πελάτη
		if (method_exists($this->payments_on_account_model, 'get_contact_plus_emails_for_client')
			&& $this->payments_on_account_model->is_contact_plus_installed()) {
			$plus = $this->payments_on_account_model->get_contact_plus_emails_for_client((int)$receipt->client_id);
			foreach ($plus as $p) {
				if (!empty($p['email'])) {
					$allowed[strtolower(trim($p['email']))] = true;
				}
			}
		}

		// φιλτράρισμα recipients με βάση allowed whitelist
		$recipients = array_values(array_filter(array_map(function($e){ return strtolower(trim((string)$e)); }, $recipients), function($e) use (&$allowed){
			return $e !== '' && isset($allowed[$e]);
		}));

		if (empty($recipients)) { echo json_encode(['success'=>false,'message'=>'No valid recipients for this customer.']); return; }
		// -----------------------------------------------

		$client_currency_id = ($client && $client->default_currency) ? $client->default_currency : get_base_currency()->id;

		$merge = [
			'{client_name}'    => $receipt->client_name,
			'{receipt_number}' => $receipt->ref,
			'{payment_mode}'   => $receipt->payment_mode_name,
			'{transaction_id}' => $receipt->transaction_id,
			'{total_amount}'   => app_format_money($receipt->total_amount, $client_currency_id),
			'{amount}'         => app_format_money($receipt->total_amount, $client_currency_id),
			'{receipt_date}'   => _d($receipt->payment_date),
			'{payment_date}'   => _d($receipt->payment_date),
			'{companyname}'    => get_option('invoice_company_name'),
		];
		$body = strtr($email_body, $merge);
		$subject = 'Payment Receipt #'.$receipt->ref;
		$filename  = mb_strtoupper('Receipt-'.$receipt->ref, 'UTF-8').'.pdf';
		$attachRaw = $this->payments_on_account_model->attach_receipt_pdf($receipt, $filename);
		$attachments = [[
			'attachment' => $attachRaw,
			'filename'   => $filename,
			'type'       => 'application/pdf',
		]];

		$cc = [];
		if ($cc_emails !== '') { $cc = array_map('trim', explode(',', $cc_emails)); }

		$sent = false;
		foreach ($recipients as $email) {
			$sent = send_mail_template_custom($email, $subject, $body, $cc, $attachments);
		}

		echo json_encode(['success' => (bool)$sent]);
	}


    /** AJAX: ανοιχτά τιμολόγια πελάτη για το modal (όπως στο create) */
    public function get_unpaid_invoices_ajax()
    {
        if (!$this->input->is_ajax_request()) { show_404(); }
        $client_id = (int)$this->input->post('client_id');

        $this->load->model('invoices_model');
        $invoices  = $this->invoices_model->get_unpaid_invoices($client_id);

        echo json_encode($invoices);
    }

    /**
     * AJAX: Apply ένα receipt σε επιλεγμένα τιμολόγια.
     * ΚΑΘΟΡΙΣΤΙΚΟ: το URL πρέπει να είναι .../apply_receipt_to_invoices/{receipt_id}
     * (έτσι το καλεί και το view JS).
     */
    public function apply_receipt_to_invoices($receipt_id)
    {
        if (!is_staff_logged_in() || !$this->input->is_ajax_request()) {
            ajax_access_denied();
        }

        $receipt_id = (int)$receipt_id;
        $ids        = $this->input->post('invoice_ids');

        if ($receipt_id <= 0 || empty($ids) || !is_array($ids)) {
            echo json_encode(['success'=>false,'message'=>'Missing data.']); return;
        }

        // sanitize
        $ids = array_values(array_unique(array_map('intval', $ids)));
        $ids = array_filter($ids, fn($v)=>$v>0);

        // Θα αφήσουμε το μοντέλο να περιορίσει ποσά (due/remaining) και να κάνει:
        // 1) core payments
        // 2) ενημέρωση status
        // 3) insert στο tblreceipt_invoice_applications
        $allocations = [];
        foreach ($ids as $iid) {
            $allocations[] = ['invoice_id' => (int)$iid, 'amount' => PHP_FLOAT_MAX];
        }

        $applied = $this->payments_on_account_model->apply_receipt_to_invoices($receipt_id, $allocations);

        if ($applied > 0) {
            paymentsonaccount_emit_event('receipt_applied', $receipt_id, ['applied_amount' => (float) $applied]);
            echo json_encode(['success'=>true,'applied'=>$applied]); return;
        }
        echo json_encode(['success'=>false,'message'=>'Nothing applied.']);
    }

    /** Διαγραφή receipt (μαζί με core payments μέσω του model) */
    public function delete_receipt($id)
    {
        if (!has_permission('payments_on_account', '', 'delete')) {
            access_denied('Payments On Account');
        }

        $id     = (int)$id;
        $isAjax = $this->input->is_ajax_request();

        $snapshot = $this->payments_on_account_model->get_receipt($id);
        $ok = $this->payments_on_account_model->delete_receipt($id);
        if ($ok) {
            paymentsonaccount_emit_event('receipt_deleted', $id, [], $snapshot);
        }

        if ($isAjax) { echo json_encode(['success'=>(bool)$ok]); return; }

        set_alert($ok ? 'success' : 'danger', $ok ? _l('deleted') : _l('problem_deleting'));
        redirect(admin_url('paymentsonaccount'));
    }
	/**
     * APPLY από το modal — AJAX
     * Προσοχή: το view καλεί αυτό το endpoint:
     *   admin_url + 'paymentsonaccount/apply_receipt_invoices/' + receiptId
     */
    public function apply_receipt_invoices($receipt_id)
	{
		if (!is_staff_logged_in() || !$this->input->is_ajax_request()) {
			ajax_access_denied();
		}

		$receipt_id = (int)$receipt_id;
		$ids        = $this->input->post('invoice_ids');

		if ($receipt_id <= 0 || empty($ids) || !is_array($ids)) {
			echo json_encode(['success'=>false,'message'=>'Missing data.']); 
			return;
		}

		// Πιάσε το receipt (μόνο για γρήγορους ελέγχους/μηδενικό ποσό)
		$r = $this->db->where('id',$receipt_id)->get(db_prefix().'receipts')->row();
		if (!$r) {
			echo json_encode(['success'=>false,'message'=>'Receipt not found']); 
			return;
		}
		if ((float)$r->total_amount <= 0) {
			echo json_encode(['success'=>false,'message'=>'Receipt amount is zero.']); 
			return;
		}

		// Καθάρισε/μοναδικοποίησε τα ids
		$ids = array_values(array_unique(array_filter(array_map('intval',$ids), fn($v)=>$v>0)));
		if (empty($ids)) {
			echo json_encode(['success'=>false,'message'=>'No invoice ids']); 
			return;
		}

		// Φτιάξε allocations: για κάθε επιλεγμένο τιμολόγιο ζήτα “ό,τι μπορεί”
		$allocations = [];
		foreach ($ids as $iid) {
			// μεγάλο ποσό ώστε το μοντέλο να το «κόψει» σε due/remaining
			$allocations[] = ['invoice_id' => (int)$iid, 'amount' => PHP_FLOAT_MAX];
		}

		// Χρησιμοποίησε το model που μιλάει με το core (batch/process/add)
		$appliedTotal = $this->payments_on_account_model->apply_receipt_to_invoices($receipt_id, $allocations);

        if ($appliedTotal > 0) {
            paymentsonaccount_emit_event('receipt_applied', $receipt_id, ['applied_amount' => (float) $appliedTotal]);
			echo json_encode([
				'success'      => true,
				'applied'      => (float)$appliedTotal,
				'message'      => 'Applied successfully.',
			]);
		} else {
			echo json_encode([
				'success' => false,
				'message' => 'Nothing applied (maybe selected invoices are already fully paid or no due found).'
			]);
		}
	}

	public function delete_applied_payment()
	{
		if (!is_staff_logged_in() || !$this->input->is_ajax_request()) {
			ajax_access_denied();
		}
		if (!has_permission('payments_on_account', '', 'edit')) {
			echo json_encode(['success' => false, 'message' => _l('access_denied')]); return;
		}

		$receipt_id = (int)$this->input->post('receipt_id');
		$payment_id = (int)$this->input->post('payment_id');
		$invoice_id = (int)$this->input->post('invoice_id'); // optional

		if ($receipt_id <= 0 || $payment_id <= 0) {
			echo json_encode(['success' => false, 'message' => 'Missing or invalid IDs.']); return;
		}

		// Βεβαιώσου ότι ο payment ανήκει στη συγκεκριμένη απόδειξη (γέφυρα)
		$bridgeTbl = db_prefix() . 'receipt_invoice_applications';
		$this->db->where('receipt_id', $receipt_id)
				 ->where('payment_record_id', $payment_id);
		if ($invoice_id > 0) {
			$this->db->where('invoice_id', $invoice_id);
		}
		$bridgeRow = $this->db->get($bridgeTbl)->row_array();

		if (!$bridgeRow) {
			echo json_encode(['success' => false, 'message' => 'Mapping not found for this receipt/payment.']); return;
		}
		if ($invoice_id <= 0) {
			$invoice_id = (int)$bridgeRow['invoice_id'];
		}

		// 1) Διέγραψε το core payment μέσω core model ώστε να “τρέξουν” hooks & status updates
		$this->load->model('payments_model');
		$deleted = false;
		try {
			$deleted = (bool)$this->payments_model->delete($payment_id);
		} catch (\Throwable $e) {
			$deleted = false;
		}
		if (!$deleted) {
			echo json_encode(['success' => false, 'message' => 'Failed to delete core payment.']); return;
		}

		// 2) Διέγραψε τη γραμμή από τη γέφυρα
		$this->db->where('receipt_id', $receipt_id)
				 ->where('payment_record_id', $payment_id)
				 ->delete($bridgeTbl);

		// 3) Ανασύνθεση του receipts.invoices_applied από ό,τι έχει μείνει στη γέφυρα
		$left = $this->db->select('invoice_id, amount')
						 ->from($bridgeTbl)
						 ->where('receipt_id', $receipt_id)
						 ->get()->result_array();

		// Μπορούμε να κρατήσουμε [{invoice_id, amount}] ή ids – επιλέγουμε αντικείμενα για ακρίβεια
		$newApplied = [];
		foreach ($left as $r) {
			$iid = (int)$r['invoice_id'];
			$amt = (float)$r['amount'];
			if ($iid > 0) {
				$newApplied[] = ['invoice_id' => $iid, 'amount' => $amt];
			}
		}

		$this->db->where('id', $receipt_id)
				 ->update(db_prefix().'receipts', [
					 'invoices_applied' => json_encode($newApplied, JSON_UNESCAPED_UNICODE),
				 ]);

		paymentsonaccount_emit_event('application_deleted', $receipt_id, [
			'payment_id' => $payment_id,
			'invoice_id' => $invoice_id,
		]);

		echo json_encode(['success' => true]);
	}


    /**
     * Statement (HTML για modal/tab) – receipts-based
     */
    public function statement_receipts()
	{
		if (!is_staff_logged_in()) { show_404(); }

		$customer_id = (int)$this->input->get('customer_id');
		if (!$customer_id) { $customer_id = (int)$this->input->get('client_id'); }
		if (!$customer_id) { $customer_id = (int)$this->uri->segment(4); }
		if (!$customer_id) { show_404(); }
		$beginning = 0;
		$from_human = $this->input->get('from');
		$to_human   = $this->input->get('to');
		$from = to_sql_date($from_human ?: date('Y-m-01'));
		$to   = to_sql_date($to_human   ?: date('Y-m-t'));

		$this->load->model('clients_model');
		$this->load->model('currencies_model');
		$this->load->model('invoices_model');

		$draft     = defined('Invoices_model::STATUS_DRAFT')     ? Invoices_model::STATUS_DRAFT     : 6;
		$cancelled = defined('Invoices_model::STATUS_CANCELLED') ? Invoices_model::STATUS_CANCELLED : 5;

		$client = $this->clients_model->get($customer_id);
		$currency = !empty($client->default_currency)
			? $this->currencies_model->get($client->default_currency)
			: $this->currencies_model->get_base_currency();

		// === Beginning (invoices - receipts - credits_before)
		$invoices_before = (float)($this->db->select_sum('total')
			->where('clientid', $customer_id)
			->where('date <', $from)
			->where_not_in('status', [$draft, $cancelled])
			->get(db_prefix().'invoices')->row()->total ?? 0);

		$receipts_before = (float)($this->db->select_sum('total_amount')
			->where('client_id', $customer_id)
			->where('payment_date <', $from)
			->get(db_prefix().'receipts')->row()->total_amount ?? 0);

		list($credit_before, $credit_rows, $credit_notes_amount) =
			$this->_fetch_credit_notes_data($customer_id, $from, $to);

		$beginning = $invoices_before - $receipts_before - $credit_before;

		// === Period: invoices & receipts (όπως ήταν)
		$invoices = $this->db->select('id,date as txn_date,duedate,total')
			->where('clientid', $customer_id)
			->where('date >=', $from)->where('date <=', $to)
			->where_not_in('status', [$draft, $cancelled])
			->order_by('date','ASC')
			->get(db_prefix().'invoices')->result_array();

		$invoiced_amount = 0.0;
		foreach ($invoices as $i) { $invoiced_amount += (float)$i['total']; }

		$receipts = $this->db->select('id,payment_date as txn_date,total_amount as total,receipt_number,invoices_applied')
			->where('client_id', $customer_id)
			->where('payment_date >=', $from)->where('payment_date <=', $to)
			->order_by('payment_date','ASC')
			->get(db_prefix().'receipts')->result_array();

		$amount_received = 0.0;
		foreach ($receipts as $r) { $amount_received += (float)$r['total']; }

		// === Build rows
		$rows = [];
		foreach ($invoices as $i) {
			$rows[] = [
				'date'           => $i['txn_date'],
				'invoice_id'     => (int)$i['id'],
				'invoice_amount' => (float)$i['total'],
				'duedate'        => $i['duedate'],
			];
		}
		foreach ($receipts as $r) {
			$applied = [];
			if (!empty($r['invoices_applied'])) {
				$tmp = json_decode($r['invoices_applied'], true);
				if (is_array($tmp)) {
					foreach ($tmp as $it) {
						$applied[] = (int)(is_array($it) && isset($it['invoice_id']) ? $it['invoice_id'] : $it);
					}
				}
			}
			$rows[] = [
				'date'             => $r['txn_date'],
				'receipt_id'       => (int)$r['id'],
				'receipt_total'    => (float)$r['total'],
				'receipt_number'   => $r['receipt_number'],
				'applied_invoices' => array_values(array_unique(array_filter($applied))),
				'on_account'       => empty($applied),
			];
		}
		foreach ($credit_rows as $cn) {
			$rows[] = [
				'date'              => $cn['txn_date'],
				'credit_note_id'    => (int)$cn['id'],
				'credit_note_total' => (float)$cn['total'],
			];
		}

		usort($rows, function($a,$b){ return strcmp($a['date'], $b['date']); });

		$balance_due = ($beginning + $invoiced_amount) - $amount_received - $credit_notes_amount;

		$data = [
			'from'      => _d($from),
			'to'        => _d($to),
			'statement' => [
				'currency'            => $currency,
				'beginning_balance'   => $beginning,
				'invoiced_amount'     => $invoiced_amount,
				'amount_received'     => $amount_received,
				'credit_notes_amount' => $credit_notes_amount,
				'balance_due'         => $balance_due,
				'result'              => $rows,
			],
		];

		$modulePath = module_dir_path('paymentsonaccount');
		$this->load->add_package_path($modulePath);
		$html = $this->load->view('admin/clients/partials/statement_receipts_html', $data, true);
		$this->load->remove_package_path($modulePath);

		echo json_encode(['html' => $html]);
		die();
	}



    /**
     * Statement PDF
     */
    public function statement_receipts_pdf()
	{
		log_activity('POA send_statement_receipts HIT | URL: '.current_url().' | POST: '.json_encode($this->input->post()));

		if (!is_staff_logged_in()) { show_404(); }

		$customer_id = (int)$this->input->get('customer_id');
		if (!$customer_id) { $customer_id = (int)$this->input->get('client_id'); }
		if (!$customer_id) { $customer_id = (int)$this->uri->segment(4); }
		if (!$customer_id) { show_404(); }
		$beginning = 0;
		$from_human = $this->input->get('from');
		$to_human   = $this->input->get('to');
		$from = to_sql_date($from_human ?: date('Y-m-01'));
		$to   = to_sql_date($to_human   ?: date('Y-m-t'));

		$this->load->model('clients_model');
		$this->load->model('currencies_model');
		$this->load->model('invoices_model');

		$draft     = defined('Invoices_model::STATUS_DRAFT')     ? Invoices_model::STATUS_DRAFT     : 6;
		$cancelled = defined('Invoices_model::STATUS_CANCELLED') ? Invoices_model::STATUS_CANCELLED : 5;

		$client = $this->clients_model->get($customer_id);
		$currency = !empty($client->default_currency)
			? $this->currencies_model->get($client->default_currency)
			: $this->currencies_model->get_base_currency();

		$invoices_before = (float)($this->db->select_sum('total')
			->where('clientid', $customer_id)
			->where('date <', $from)
			->where_not_in('status', [$draft, $cancelled])
			->get(db_prefix().'invoices')->row()->total ?? 0);

		$receipts_before = (float)($this->db->select_sum('total_amount')
			->where('client_id', $customer_id)
			->where('payment_date <', $from)
			->get(db_prefix().'receipts')->row()->total_amount ?? 0);

		list($credit_before, $credit_rows, $credit_notes_amount) =
			$this->_fetch_credit_notes_data($customer_id, $from, $to);

		$beginning = $invoices_before - $receipts_before - $credit_before;

		$invoices = $this->db->select('id,date as txn_date,duedate,total')
			->where('clientid', $customer_id)
			->where('date >=', $from)->where('date <=', $to)
			->where_not_in('status', [$draft, $cancelled])
			->order_by('date','ASC')
			->get(db_prefix().'invoices')->result_array();

		$invoiced_amount = 0.0;
		foreach ($invoices as $i) { $invoiced_amount += (float)$i['total']; }

		$receipts = $this->db->select('id,payment_date as txn_date,total_amount as total,receipt_number,invoices_applied')
			->where('client_id', $customer_id)
			->where('payment_date >=', $from)->where('payment_date <=', $to)
			->order_by('payment_date','ASC')
			->get(db_prefix().'receipts')->result_array();

		$amount_received = 0.0;
		foreach ($receipts as $r) { $amount_received += (float)$r['total']; }

		$rows = [];
		foreach ($invoices as $i) {
			$rows[] = [
				'date'           => $i['txn_date'],
				'invoice_id'     => (int)$i['id'],
				'invoice_amount' => (float)$i['total'],
				'duedate'        => $i['duedate'],
			];
		}
		foreach ($receipts as $r) {
			$applied = [];
			if (!empty($r['invoices_applied'])) {
				$tmp = json_decode($r['invoices_applied'], true);
				if (is_array($tmp)) {
					foreach ($tmp as $it) {
						$applied[] = (int)(is_array($it) && isset($it['invoice_id']) ? $it['invoice_id'] : $it);
					}
				}
			}
			$rows[] = [
				'date'             => $r['txn_date'],
				'receipt_id'       => (int)$r['id'],
				'receipt_total'    => (float)$r['total'],
				'receipt_number'   => $r['receipt_number'],
				'applied_invoices' => array_values(array_unique(array_filter($applied))),
				'on_account'       => empty($applied),
			];
		}
		foreach ($credit_rows as $cn) {
			$rows[] = [
				'date'              => $cn['txn_date'],
				'credit_note_id'    => (int)$cn['id'],
				'credit_note_total' => (float)$cn['total'],
			];
		}

		usort($rows, function($a,$b){ return strcmp($a['date'], $b['date']); });

		$balance_due = ($beginning + $invoiced_amount) - $amount_received - $credit_notes_amount;

		$data = [
			'client' => $client,
			'from'   => _d($from),
			'to'     => _d($to),
			'statement' => [
				'currency'            => $currency,
				'beginning_balance'   => $beginning,
				'invoiced_amount'     => $invoiced_amount,
				'amount_received'     => $amount_received,
				'credit_notes_amount' => $credit_notes_amount,
				'balance_due'         => $balance_due,
				'result'              => $rows,
			],
		];

		$pdf = app_pdf(
			'statement_receipts',
			module_dir_path(PAYMENTS_ON_ACCOUNT_MODULE_NAME, 'libraries/Statement_receipts_pdf.php'),
			$data
		);

		$file_name = slug_it(_l('account_summary')).'-'.slug_it(get_company_name($client->userid)).'.pdf';
		$type = $this->input->get('download') == '1' ? 'D' : 'I';
		$pdf->Output($file_name, $type);
	}



    /**
	 * Αποστολή Statement (Receipts) με email – AJAX
	 * POST: customer_id, from, to, send_to[] (contact ids), cc, email_template_custom (optional)
	 */
	public function send_statement_receipts()
	{
		if (!is_staff_logged_in() || !$this->input->is_ajax_request()) {
			ajax_access_denied();
		}
		$this->output->set_content_type('application/json');

		$customer_id = (int)$this->input->post('customer_id');
		$from_human  = trim((string)$this->input->post('from', true));
		$to_human    = trim((string)$this->input->post('to', true));
		$send_to_ids = (array)$this->input->post('send_to'); // contact IDs
		$cc          = trim((string)$this->input->post('cc', true));
		$custom_body = $this->input->post('email_template_custom', false); // μπορεί να έχει HTML

		if (!$customer_id || !$from_human || !$to_human || empty($send_to_ids)) {
			echo json_encode(['success'=>false,'message'=>'Missing required data.']); return;
		}

		$from_sql = to_sql_date($from_human);
		$to_sql   = to_sql_date($to_human);

		$this->load->model('clients_model');
		$this->load->model('paymentsonaccount/payments_on_account_model', 'poa');
		$this->load->model('currencies_model');

		$client = $this->clients_model->get($customer_id);
		if (!$client) { echo json_encode(['success'=>false,'message'=>'Client not found']); return; }

		// === Χτίσε το PDF (RAW BYTES) με τα δικά μας receipts ===
		list($ok, $pdfBytes, $filename, $balance_due, $currencyObj) =
			$this->poa->build_statement_receipts_pdf_bytes($customer_id, $from_sql, $to_sql);

		if (!$ok) {
			echo json_encode(['success'=>false,'message'=>$pdfBytes]); return; // $pdfBytes έχει το error msg
		}

		// === Subject & Body από template (DB) με fallback ===
		$subject = 'Account Statement from ' . _d($from_sql) . ' to ' . _d($to_sql);

		// Προσπάθησε να βρεις custom template (π.χ. poa-statement) ή core client-statement για BODY
		$tpl_body = '';
		$tpl      = $this->db->where('slug', 'poa-statement')
							 ->or_where('slug', 'client-statement')
							 ->limit(1)
							 ->get(db_prefix().'emailtemplates')
							 ->row();
		if ($tpl && !empty($tpl->message)) {
			$tpl_body = $tpl->message;
		}
		if ($custom_body && trim(strip_tags($custom_body)) !== '') {
			$tpl_body = $custom_body; // αν ο χρήστης έγραψε κάτι στο modal, υπερισχύει
		}
		if ($tpl_body === '') {
			$tpl_body = "Dear {contact_firstname} {contact_lastname},<br><br>"
					  . "Attached is your statement for {statement_from} to {statement_to}.<br><br>"
					  . "Balance due: {statement_balance_due}<br><br>"
					  . "Kind Regards,<br>{email_signature}";
		}

		$org_name        = get_option('invoice_company_name') ?: get_option('companyname');
		$email_signature = get_option('email_signature');
		$balance_str     = app_format_money((float)$balance_due, $currencyObj);

		$attachments = [[
			'attachment' => $pdfBytes,            // RAW bytes
			'filename'   => $filename,            // π.χ. statement_client_xxx.pdf
			'type'       => 'application/pdf',
		]];

		// === Στείλε ανά contact ===
		$emails_sent = [];
		$errors      = [];

		foreach ($send_to_ids as $contact_id) {
			$contact = $this->clients_model->get_contact((int)$contact_id);
			if (!$contact || empty($contact->email)) { continue; }

			$placeholders = [
				'{contact_firstname}'     => $contact->firstname ?? '',
				'{contact_lastname}'      => $contact->lastname ?? '',
				'{client_company}'        => $client->company ?? '',
				'{statement_from}'        => _d($from_sql),
				'{statement_to}'          => _d($to_sql),
				'{statement_balance_due}' => $balance_str,
				'{companyname}'           => $org_name,
				'{email_signature}'       => $email_signature,
			];
			$body = strtr($tpl_body, $placeholders);

			// CC μόνο στο πρώτο send (όπως κάνει το core)
			$cc_list = $cc && empty($emails_sent) ? array_map('trim', explode(',', $cc)) : [];

			$okSend = send_mail_template_custom($contact->email, $subject, $body, $cc_list, $attachments);
			if ($okSend) { $emails_sent[] = $contact->email; } else { $errors[] = $contact->email; }

			log_activity('[POA] Statement email try to '.$contact->email.' | attach='.(is_string($pdfBytes)?strlen($pdfBytes):0).' bytes | result=' . ($okSend?'OK':'ERR'));
		}

		log_activity('[POA] Statement (Receipts) emailed | ClientID: '.$customer_id.' | From: '._d($from_sql).' | To: '._d($to_sql).' | Sent: '.implode(',',$emails_sent).' | Errors: '.implode(',',$errors));

		echo json_encode(['success' => !empty($emails_sent), 'sent'=>$emails_sent, 'errors'=>$errors]);
	}
	
	/**
	 * Router για reports
	 * URL: /admin/paymentsonaccount/reports/credits
	 */
	public function reports($slug = '')
	{
		if (!has_permission('reports', '', 'view')) {
			access_denied();
		}
		switch ($slug) {
			case 'credits':
				return $this->report_credits();
			default:
				show_404();
		}
	}

	private function report_credits()
	{
		$period      = $this->input->get('period', true) ?: 'all_time';
		$fromUi      = $this->input->get('from', true) ?: '';
		$toUi        = $this->input->get('to', true) ?: '';
		$customer_id = (int)$this->input->get('customer_id');

		list($fromSql, $toSql) = $this->_poa_resolve_period($period, $fromUi, $toUi);

		$this->load->model('clients_model');
		$this->load->model('paymentsonaccount/payments_on_account_model', 'poa');

		$rows    = $this->poa->report_credit_balances_period($customer_id ?: null, $fromSql, $toSql);
		$clients = $this->clients_model->get();

		$data = [
			'title'       => _l('poa_client_credit_balances_report') ?: 'Client Credit Balances',
			'period'      => $period,
			'from_ui'     => $fromUi,
			'to_ui'       => $toUi,
			'from_sql'    => $fromSql,
			'to_sql'      => $toSql,
			'customer_id' => $customer_id,
			'rows'        => $rows,
			'clients'     => is_array($clients) ? $clients : [],
		];

		$this->load->view('paymentsonaccount/reports/credits', $data);
	}
	
	public function ping()
	{
		header('Content-Type: text/plain; charset=utf-8');
		echo 'paymentsonaccount::ping OK';
	}
	public function migrate_diag()
	{
		if (!is_staff_logged_in()) { exit('no staff'); }
		header('Content-Type: text/plain; charset=utf-8');

		$db = $this->db;
		$tables = [
			'tbl_receipts',
			'tbl_receiptsInvPayments',
			db_prefix().'invoicepaymentrecords',
			db_prefix().'invoices',
			db_prefix().'receipts', // NEW
		];

		foreach ($tables as $t) {
			$exists = $db->table_exists($t);
			$count  = $exists ? (int)$db->query("SELECT COUNT(*) c FROM `$t`")->row()->c : -1;
			echo str_pad($t, 35).' exists=' . ($exists?'YES':'NO ')
				.'  count=' . ($count>=0?$count:'-') . PHP_EOL;
		}
	}

	public function migrate_old_receipts_to_new()
	{
		if (!is_staff_logged_in() || !has_permission('payments_on_account', '', 'view')) {
			access_denied('Receipts Migration');
		}
		$this->output->set_content_type('application/json');

		$dry = ($this->input->get('dry') === '1' || (int)$this->input->get('dry') === 1);

		// Πίνακες σύμφωνα με το σχήμα που μου έστειλες
		$T_OLD_RECEIPTS = 'tbl_receipts';
		$T_OLD_LINKS    = 'tbl_receiptsInvPayments';
		$T_PAYMENTS     = db_prefix().'invoicepaymentrecords';
		$T_INVOICES     = db_prefix().'invoices';
		$T_NEW_RECEIPTS = db_prefix().'receipts';

		$migrated_old = 0;
		$skipped_ref_conflicts = 0;
		$created_orphans = 0;
		$errors = [];

		// STEP 1: παλιά receipts -> νέα
		if ($this->db->table_exists($T_OLD_RECEIPTS)) {
			$old_rows = $this->db->order_by('id','ASC')->get($T_OLD_RECEIPTS)->result_array();
			foreach ($old_rows as $r) {
				$old_id     = (int)$r['id'];
				$client_id  = (int)$r['customer_id'];
				$ref        = trim((string)$r['ref']);
				$amount     = (float)$r['amount'];
				$mode       = (string)$r['paymentmode'];
				$method     = (string)$r['paymentmethod'];
				$date       = $r['date'] ?: date('Y-m-d');
				$recorded   = $r['daterecorded'] ?: date('Y-m-d H:i:s');
				$note       = (string)($r['note'] ?? '');
				$txn        = (string)($r['transactionid'] ?? '');

				// invoice ids από links (αν υπάρχει ο πίνακας)
				$invIds = [];
				if ($this->db->table_exists($T_OLD_LINKS)) {
					$links = $this->db->where('receiptid',$old_id)->get($T_OLD_LINKS)->result_array();
					foreach ($links as $ln) {
						$invPaymentId = is_numeric($ln['invpaymentid']) ? (int)$ln['invpaymentid'] : 0;
						if ($invPaymentId <= 0) { continue; }
						$pr = $this->db->select('invoiceid')->where('id',$invPaymentId)->get($T_PAYMENTS)->row();
						if ($pr && (int)$pr->invoiceid > 0) { $invIds[] = (int)$pr->invoiceid; }
					}
					$invIds = array_values(array_unique($invIds));
				}

				// conflict στο νέο;
				$exists = $this->db->select('id')->where('receipt_number',$ref)->get($T_NEW_RECEIPTS)->row();
				if ($exists) { $skipped_ref_conflicts++; continue; }

				if ($dry) { $migrated_old++; continue; }

				$payload = [
					'receipt_number'   => $ref,
					'client_id'        => $client_id,
					'total_amount'     => $amount,
					'payment_date'     => $date,
					'payment_mode'     => $mode,
					'payment_method'   => $method ?: null,
					'transaction_id'   => $txn ?: null,
					'date_created'     => $recorded,
					'invoices_applied' => json_encode($invIds),
					'staff_id'         => is_staff_logged_in() ? (int)get_staff_user_id() : null,
					'note'             => $note ?: null,
				];
				$ok = $this->db->insert($T_NEW_RECEIPTS, $payload);
				if ($ok && $this->db->affected_rows() > 0) { $migrated_old++; }
				else { $errors[] = "Failed to insert OLD#{$old_id} (ref={$ref})"; }
			}
		}

		// ========= STEP 2: Ορφανά payments → νέα receipts (με ξεχωριστή αρίθμηση) =========

		// Elastic join: το invpaymentid στο παλιό mapping είναι TEXT, γι’ αυτό κάνουμε CAST και REGEXP
		$orphans = $this->db->query("
			SELECT 
				p.*,
				i.clientid AS client_from_invoice
			FROM {$T_PAYMENTS} p
			" . ($this->db->table_exists($T_OLD_LINKS) ? "
				LEFT JOIN {$T_OLD_LINKS} l
				  ON (
					   l.invpaymentid = CAST(p.id AS CHAR)
					   OR
					   (CASE WHEN l.invpaymentid REGEXP '^[0-9]+$' THEN CAST(l.invpaymentid AS UNSIGNED) ELSE NULL END) = p.id
					 )
			" : "LEFT JOIN (SELECT NULL) l ON 1=0") . "
			LEFT JOIN {$T_INVOICES} i ON i.id = p.invoiceid
			WHERE l.id IS NULL
			ORDER BY p.id ASC
		")->result_array();

		// Μοντέλο (αν δεν έχει ήδη φορτωθεί πιο πάνω)
		if (!isset($this->payments_on_account_model)) {
			$this->load->model('paymentsonaccount/payments_on_account_model');
		}

		// Ξεχωριστή αρίθμηση ΜΟΝΟ για ορφανά: ξεκίνα από ?orphan_start=XXXX (default 10001)
		$orphanStart = (int)$this->input->get('orphan_start') ?: 10001;
		$seq     = $orphanStart;
		$prefix  = (string)get_option('receipt_number_prefix');        // π.χ. RCPT-
		$padding = (int)(get_option('number_padding') ?: 4);           // π.χ. 4 → RCPT-0001

		foreach ($orphans as $p) {
			$pid        = (int)$p['id'];
			$invoice_id = (int)$p['invoiceid'];

			// Idempotency: αν υπάρχει ήδη νέο receipt με source_payment_id = $pid, κάνε skip
			$already = $this->db->select('id')
				->where('source_payment_id', $pid)
				->get($T_NEW_RECEIPTS)->row();
			if ($already) { continue; }

			// Βρες client: προτίμηση από payments.client_id, αλλιώς από invoice
			$client_id = isset($p['client_id']) ? (int)$p['client_id'] : 0;
			if ($client_id <= 0 && $invoice_id > 0) {
				$inv = $this->db->select('clientid')->where('id', $invoice_id)->get($T_INVOICES)->row();
				if ($inv && (int)$inv->clientid > 0) { $client_id = (int)$inv->clientid; }
			}
			if ($client_id <= 0) {
				$errors[] = "ORPHAN payment#{$pid} skipped (no client found)";
				continue;
			}

			$amount = (float)$p['amount'];
			$mode   = (string)$p['paymentmode'];
			$method = (string)$p['paymentmethod'];
			$date   = $p['date'] ?: date('Y-m-d');
			$note   = (string)($p['note'] ?? '');
			$txn    = (string)($p['transactionid'] ?? '');

			// Αν έχει invoice → applied, αλλιώς on_account
			$applied    = [];
			$on_account = true;
			if ($invoice_id > 0) { $applied = [$invoice_id]; $on_account = false; }

			if ($dry) {
				$created_orphans++;
				continue;
			}

			// === ΧΕΙΡΟΚΙΝΗΤΗ ΑΡΙΘΜΗΣΗ ΓΙΑ ΟΡΦΑΝΑ: βρες το επόμενο διαθέσιμο digits ===
			$digits = $seq;
			while ($this->payments_on_account_model->is_receipt_number_taken(
				$prefix . str_pad($digits, max(1, $padding), '0', STR_PAD_LEFT)
			)) {
				$digits++;
			}

			try {
				// Στα ορφανά περνάμε το $manual_digits για custom αρίθμηση
				$this->payments_on_account_model->create_receipt(
					$client_id,
					$amount,
					$mode,
					$applied,
					$note,
					$date,
					$method,
					$txn,
					$on_account,
					$pid,        // source_payment_id (idempotency)
					$digits      // manual_digits: μόνο για ΟΡΦΑΝΑ
				);

				$created_orphans++;
				$seq = $digits + 1; // προχώρησε την αρίθμηση για το επόμενο ορφανό
			} catch (\Throwable $e) {
				$errors[] = "ORPHAN payment#{$pid} failed: " . $e->getMessage();
			}
		}


		echo json_encode([
		  'dry_run'               => $dry,
		  'migrated_old'          => (int)$migrated_old,
		  'skipped_ref_conflicts' => (int)$skipped_ref_conflicts,  // <- ίδιο όνομα
		  'created_orphans'       => (int)$created_orphans,
		  'errors'                => $errors,
		]);

	}

	/** Resolve + fetch Credit Notes cross-version (returns [$beforeSum, $rows, $periodSum]) */
	private function _fetch_credit_notes_data($customer_id, $from, $to)
	{
		$db = $this->db;

		// 1) Βρες πίνακα
		$table = db_prefix().'creditnotes';
		if (!$db->table_exists($table)) {
			$table = 'tblcreditnotes';
			if (!$db->table_exists($table)) {
				return [0.0, [], 0.0]; // δεν υπάρχει πίνακας
			}
		}

		// 2) Διάβασε διαθέσιμες στήλες
		$cols      = array_map('strtolower', (array)$db->list_fields($table));
		$totalCol  = in_array('total', $cols) ? 'total' : (in_array('amount',$cols) ? 'amount' : (in_array('subtotal',$cols) ? 'subtotal' : 'total'));
		$dateCol   = in_array('date', $cols) ? 'date' : (in_array('datecreated',$cols) ? 'datecreated' : 'date');
		$clientCol = in_array('clientid', $cols) ? 'clientid' : (in_array('customer_id',$cols) ? 'customer_id' : 'clientid');
		$statusCol = in_array('status', $cols) ? 'status' : null;

		// 3) Sum πριν την περίοδο
		$db->select_sum($totalCol, 's')->from($table)
		   ->where($clientCol, $customer_id)
		   ->where("$dateCol <", $from);
		// Αν θες, μπορείς να εξαιρέσεις draft/void αν ξέρεις τους κωδικούς σου
		$before = $db->get()->row();
		$beforeSum = (float)($before->s ?? 0);

		// 4) Γραμμές περιόδου
		$db->select("id, $dateCol as txn_date, $totalCol as total")
		   ->from($table)
		   ->where($clientCol, $customer_id)
		   ->where("$dateCol >=", $from)->where("$dateCol <=", $to)
		   ->order_by($dateCol,'ASC');
		$rows = $db->get()->result_array();

		$periodSum = 0.0;
		foreach ($rows as $r) { $periodSum += (float)$r['total']; }

		return [$beforeSum, $rows, $periodSum];
	}

	public function reports_receipts()
	{
		if (!is_admin() && !has_permission('reports', '', 'view')) {
			access_denied('Reports');
		}

		$data = [
			'title'  => _l('poa_receipts_report') ?: 'Receipts',
			'period' => $this->input->get('period') ?: 'all_time',
			'from_ui' => $this->input->get('from') ?: '',
			'to_ui'   => $this->input->get('to') ?: '',
		];

		$this->load->view('paymentsonaccount/reports/receipts_received', $data);
	}
	// Helpers: period -> [from,to] (Y-m-d) / null για all time
	private function _poa_resolve_period(?string $period, ?string $from = null, ?string $to = null): array
	{
		$tz = date_default_timezone_get();
		$today = new DateTime('now', new DateTimeZone($tz));

		$firstDayThisMonth = (clone $today)->modify('first day of this month')->format('Y-m-d');
		$lastDayThisMonth  = (clone $today)->modify('last day of this month')->format('Y-m-d');

		$map = function($start, $end) { return [$start, $end]; };

		switch ($period) {
			case 'this_month':   return $map($firstDayThisMonth, $lastDayThisMonth);
			case 'last_month':   return $map(
									(clone $today)->modify('first day of last month')->format('Y-m-d'),
									(clone $today)->modify('last day of last month')->format('Y-m-d'));
			case 'this_year':    return $map(date('Y-01-01'), date('Y-12-31'));
			case 'last_year':    $y = (int)date('Y')-1; return $map("$y-01-01", "$y-12-31");
			case 'last_3_months':{
				$start = (clone $today)->modify('first day of -2 months')->format('Y-m-d');
				return $map($start, $lastDayThisMonth);
			}
			case 'last_6_months':{
				$start = (clone $today)->modify('first day of -5 months')->format('Y-m-d');
				return $map($start, $lastDayThisMonth);
			}
			case 'last_12_months':{
				$start = (clone $today)->modify('first day of -11 months')->format('Y-m-d');
				return $map($start, $lastDayThisMonth);
			}
			case 'period':
			case 'custom':
				$f = $from ? to_sql_date($from) : null;
				$t = $to   ? to_sql_date($to)   : null;
				return [$f, $t];
			case 'all_time':
			default:
				return [null, null];
		}
	}

	/**
	 * AJAX: /admin/paymentsonaccount/reports_receipts_data
	 * Body: period, from, to
	 * Return: { rows:[...]} συμβατό με DataTables (client-side)
	 */
	public function reports_receipts_data()
	{
		if (!$this->input->is_ajax_request()) { show_404(); }

		if (!is_admin() && !has_permission('reports', '', 'view')) { access_denied('Reports'); }

		$period = $this->input->post('period') ?? 'all_time';
		$from   = $this->input->post('from') ?? null;
		$to     = $this->input->post('to') ?? null;

		list($fromSql, $toSql) = $this->_poa_resolve_period($period, $from, $to);

		$pref = db_prefix();

		// Base query: receipts (+ optional date window)
		$this->db->select("r.id, r.receipt_number, r.client_id, r.payment_date, r.total_amount, r.payment_mode, r.transaction_id,
						   pm.name AS pm_name, c.company");
		$this->db->from($pref.'receipts AS r');
		// JOIN payment modes: αν payment_mode (στο receipts) είναι numeric id -> ταιριάζει, αλλιώς απλώς pm_name null
		if ($this->db->table_exists($pref.'payment_modes')) {
			// cast-as-int join για installations που κρατούν string/number
			$this->db->join($pref.'payment_modes AS pm', 'pm.id = CAST(r.payment_mode AS UNSIGNED)', 'left');
		}
		$this->db->join($pref.'clients AS c', 'c.userid = r.client_id', 'left');

		if ($fromSql) $this->db->where('r.payment_date >=', $fromSql);
		if ($toSql)   $this->db->where('r.payment_date <=', $toSql);

		$this->db->order_by('r.payment_date', 'DESC');
		$receipts = $this->db->get()->result_array();

		// Φέρε invoices για κάθε receipt από bridge
		$ids = array_map(fn($r) => (int)$r['id'], $receipts);
		$invoicesByReceipt = [];
		if ($ids) {
			$rows = $this->db->select('ria.receipt_id, ria.invoice_id, i.prefix, i.number, i.id AS iid')
				->from($pref.'receipt_invoice_applications AS ria')
				->join($pref.'invoices AS i', 'i.id = ria.invoice_id', 'left')
				->where_in('ria.receipt_id', $ids)
				->order_by('ria.id', 'ASC')
				->get()->result_array();

			foreach ($rows as $r) {
				$rid = (int)$r['receipt_id'];
				if (!isset($invoicesByReceipt[$rid])) $invoicesByReceipt[$rid] = [];
				$invoicesByReceipt[$rid][] = $r;
			}
		}

		// Build rows for DT
		$rows = [];
		foreach ($receipts as $r) {
			$rid = (int)$r['id'];

			// Receipt link
			$receiptLink = '<a href="'.admin_url('paymentsonaccount/view_receipt/'.$rid).'" target="_blank">'
						 . htmlspecialchars($r['receipt_number'] ?: ('#'.$rid)) . '</a>';

			// Customer link
			$custLink = '<a href="'.admin_url('clients/client/'.$r['client_id']).'" target="_blank">'
					  . htmlspecialchars($r['company'] ?: ('#'.$r['client_id'])) . '</a>';

			// Invoices formatted list
			$invParts = [];
			if (!empty($invoicesByReceipt[$rid])) {
				foreach ($invoicesByReceipt[$rid] as $inv) {
					$invNo = function_exists('format_invoice_number')
						? format_invoice_number((int)$inv['iid'])
						: (($inv['prefix'] ?? '').($inv['number'] ?? $inv['iid']));
					$invParts[] = '<a href="'.admin_url('invoices#'.(int)$inv['iid']).'" target="_blank">'.$invNo.'</a>';
				}
			}
			$invoicesHtml = $invParts ? implode(', ', $invParts) : '<span class="text-muted">—</span>';

			// Payment mode name
			$pmName = trim((string)($r['pm_name'] ?: $r['payment_mode'] ?: ''));
			if ($pmName === '' && $r['payment_mode'] !== null) {
				$pmName = (string)$r['payment_mode']; // fallback
			}

			$rows[] = [
				'receipt'        => $receiptLink,
				'date'           => _d($r['payment_date']),
				'customer'       => $custLink,
				'invoices'       => $invoicesHtml,
				'payment_mode'   => htmlspecialchars($pmName) ?: '—',
				'transaction_id' => htmlspecialchars((string)($r['transaction_id'] ?? '')), // αν υπάρχει πεδίο
				'amount'         => app_format_money((float)$r['total_amount'], get_base_currency()),
				// raw για sort
				'_date_raw'      => $r['payment_date'],
				'_amount_raw'    => (float)$r['total_amount'],
			];
		}

		$totalAmount = 0.0;
		foreach ($receipts as $receipt) {
			$totalAmount += (float)$receipt['total_amount'];
		}

		echo json_encode([
			'rows' => $rows,
			'totals' => [
				'count' => count($rows),
				'amount_raw' => $totalAmount,
				'amount' => app_format_money($totalAmount, get_base_currency()),
			],
		], JSON_UNESCAPED_UNICODE);
		exit;
	}



	public function client_payment_modes_save($client_id)
	{
		if (!is_staff_logged_in() || !has_permission('payments_on_account', '', 'edit')) {
			access_denied('payments_on_account');
		}
		$client_id = (int)$client_id;
		$mode_ids = $this->input->post('payment_modes');
		$mode_ids = is_array($mode_ids) ? array_values(array_unique(array_map('intval', $mode_ids))) : [];

		$this->db->where('client_id', $client_id)->delete(db_prefix().'poa_client_payment_modes');
		foreach ($mode_ids as $mid) {
			if ($mid <= 0) { continue; }
			$this->db->insert(db_prefix().'poa_client_payment_modes', [
				'client_id' => $client_id,
				'payment_mode_id' => $mid,
				'created_at' => date('Y-m-d H:i:s'),
			]);
		}

		set_alert('success', _l('settings_updated'));
		redirect(admin_url('clients/client/'.$client_id.'?group=poa_payment_modes'));
	}
}
