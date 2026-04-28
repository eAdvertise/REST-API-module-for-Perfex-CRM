<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Guestinvoices extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        // Models που χρησιμοποιούμε
        $this->load->model('clients_model');
        $this->load->model('invoices_model');

    }

    /** AJAX: Δημιουργία/εύρεση "guest" πελάτη και επιστροφή client_id */
	public function ajax_create_guest()
	{
		if (!$this->input->is_ajax_request()) { show_404(); }

		if (!has_permission('invoices', '', 'create') && !has_permission('customers', '', 'create')) {
			echo json_encode(['success'=>false, 'message'=>_l('access_denied')]); die;
		}

		// Λαμβάνουμε input
		$firstname = trim((string)$this->input->post('firstname'));
		$lastname  = trim((string)$this->input->post('lastname'));
		$company   = trim((string)$this->input->post('company'));
		$email     = trim((string)$this->input->post('email'));

		if ($email === '') {
			echo json_encode(['success'=>false, 'message'=>_l('email_is_required') ?: 'Email is required']); die;
		}

		// Αν υπάρχει ήδη contact με αυτό το email → επέστρεψε τον πελάτη
		$this->db->select('userid')->where('email', $email);
		$existing = $this->db->get(db_prefix().'contacts')->row();
		if ($existing && !empty($existing->userid)) {
			$cid = (int)$existing->userid;
			echo json_encode([
				'success'   => true,
				'client_id' => $cid,
				'company'   => $company ?: trim($firstname.' '.$lastname) ?: 'Existing Client',
				'message'   => _l('client_exists'),
			]); die;
		}

		// Fallback κανόνες:
		// 1) Αν δεν δοθεί company → company = "First Last" (trim, μπορεί και μόνο First ή μόνο Last)
		if ($company === '') {
			$company = trim($firstname.' '.$lastname);
		}

		// 2) Αν δεν δοθούν ΟΛΑ (first, last, company) → θα χρησιμοποιήσουμε Guest+ID ΜΕΤΑ τη δημιουργία
		$useGuestIdName = ($company === '' && $firstname === '' && $lastname === '');

		// ---------------- 1) Δημιουργία Client ----------------
		$billing_country = $this->input->post('country');
		$billing_country = ($billing_country === '' || $billing_country === null) ? null : (int)$billing_country;

		$clientData = [
			'company'         => $useGuestIdName ? 'Guest' : $company,  // προσωρινό "Guest", θα ενημερωθεί μετά
			'website'         => (string)$this->input->post('website') ?: '',
			'billing_street'  => (string)$this->input->post('address') ?: '',
			'billing_city'    => (string)$this->input->post('city') ?: '',
			'billing_state'   => (string)$this->input->post('state') ?: '',
			'billing_zip'     => (string)$this->input->post('zip') ?: '',
			'billing_country' => $billing_country,
			'active'          => 1,
		];

		$client_id = $this->clients_model->add($clientData);
		if (!$client_id) {
			echo json_encode(['success'=>false, 'message'=>_l('something_went_wrong')]); die;
		}

		// Αν λείπουν όλα → μετονόμασε client σε Guest+ID
		if ($useGuestIdName) {
			$guestName = 'Guest'.$client_id;
			$this->clients_model->update(['company' => $guestName], $client_id);
			$company   = $guestName;        // από εδώ και πέρα αυτό είναι το οριστικό company
			$firstname = $guestName;        // και ο contact θα πάρει αυτό
			$lastname  = '';
		} else {
			// Αν έχουμε company (είτε δόθηκε είτε προέκυψε από First/Last) αλλά δεν έχουμε First/Last → φτιάξε contact name από company
			if ($firstname === '' && $lastname === '') {
				$firstname = $company;
				$lastname  = '';
			}
		}

		// ---------------- 2) Δημιουργία Primary Contact ----------------
		$contactData = [
			'userid'      => $client_id,
			'firstname'   => $firstname,
			'lastname'    => $lastname,
			'email'       => $email,
			'phonenumber' => (string)$this->input->post('phonenumber') ?: '',
			'is_primary'  => 1,
			'donotsendwelcomeemail' => 1,
			'estimate_emails' => 1,
			'credit_note_emails' => 1,
			'contract_emails' => 1,
			'invoice_emails' => 1,
			'project_emails' => 1,
			// Θες να παραμένει το welcome/setup email; ΑΦΗΣΕ τα ως έχουν (στέλνονται)
			// Αν κάποτε θέλεις να μην στέλνεται, βάλε: 'donotsendwelcomeemail' => 1, 'send_set_password_email' => 0,
			// Εδώ τα αφήνουμε default (να φύγει welcome) όπως είπες ότι σε καλύπτει.
		];

		$contact_id = $this->clients_model->add_contact($contactData, $client_id);
		if (!$contact_id) {
			log_activity('GI WARNING: Contact NOT created for client ID '.$client_id.' (email '.$email.')');
		} else {
			log_activity('GI: Guest contact created (ID '.$contact_id.') for client '.$client_id);
		}

		echo json_encode([
			'success'   => true,
			'client_id' => $client_id,
			'company'   => $company,
			'message'   => _l('added_successfully', _l('client')),
		]); die;
	}




    /** AJAX: Set session flags για combo flow (Save & Record Payment) */
    public function flag_combo_flow()
    {
        if (!$this->input->is_ajax_request()) { show_404(); }

        if (!has_permission('invoices', '', 'create')) {
            echo json_encode(['success' => false, 'message' => _l('access_denied')]);
            die;
        }

        // γράψε flags στη session
        $this->session->set_userdata('gi_combo_flow', 1);
        $this->session->set_userdata('gi_combo_amount', 'full'); // αν θες partial, αλλάζει αργότερα

        echo json_encode(['success' => true]);
        die;
    }
	
	public function ajax_clients_primary_emails()
	{
		if (!$this->input->is_ajax_request()) { show_404(); }
		if (!has_permission('invoices', '', 'create') && !has_permission('customers', '', 'view')) {
			echo json_encode(['success'=>false, 'message'=>_l('access_denied')]); die;
		}

		$idsStr = trim((string)$this->input->get('ids'));
		if ($idsStr === '') {
			echo json_encode(['success'=>true, 'items'=>[]]); die;
		}

		// Κανονικοποίηση IDs
		$ids = array_filter(array_map('intval', explode(',', $idsStr)));
		$ids = array_values(array_unique($ids));
		if (empty($ids)) {
			echo json_encode(['success'=>true, 'items'=>[]]); die;
		}

		// Φέρε εταιρικές επωνυμίες
		$this->db->select('userid, company')->from(db_prefix().'clients');
		$this->db->where_in('userid', $ids);
		$clients = $this->db->get()->result_array();
		$companies = [];
		foreach ($clients as $c) { $companies[(int)$c['userid']] = (string)$c['company']; }

		// Φέρε primary contacts σε ένα query
		$this->db->select('userid, email')->from(db_prefix().'contacts');
		$this->db->where_in('userid', $ids);
		$this->db->where('is_primary', 1);
		$contacts = $this->db->get()->result_array();

		$emails = [];
		foreach ($contacts as $ct) { $emails[(int)$ct['userid']] = (string)$ct['email']; }

		// Σύνθεση απάντησης
		$items = [];
		foreach ($ids as $id) {
			$items[] = [
				'id'     => $id,
				'company'=> isset($companies[$id]) ? $companies[$id] : '',
				'email'  => isset($emails[$id]) ? $emails[$id] : '',
			];
		}

		echo json_encode(['success'=>true, 'items'=>$items]); die;
	}

	public function rel_customers_with_email()
	{
		if (!is_staff_logged_in()) { show_404(); }
		if (!has_permission('invoices', '', 'create') && !has_permission('customers', '', 'view')) {
			echo json_encode(['results'=>[], 'pagination'=>['more'=>false]]); die;
		}

		$q    = trim($this->input->get('q') ?? '');
		$page = max(1, (int)($this->input->get('page') ?? 1));
		$per  = 50;
		$off  = ($page-1)*$per;

		$this->db->start_cache();
		$this->db->select('c.userid as id, c.company, ct.email');
		$this->db->from(db_prefix().'clients as c');
		$this->db->join(db_prefix().'contacts as ct', 'ct.userid = c.userid AND ct.is_primary = 1', 'left');
		if ($q !== '') {
			$this->db->group_start();
			$this->db->like('c.company', $q);
			$this->db->or_like('ct.email', $q);
			$this->db->group_end();
		}
		$this->db->stop_cache();

		// count
		$count_q = clone $this->db->get_compiled_select('', false);
		$total   = $this->db->count_all_results();

		// page of results
		$this->db->limit($per, $off);
		$rows = $this->db->get()->result_array();
		$this->db->flush_cache();

		$items = [];
		foreach ($rows as $r) {
			$id    = (int)$r['id'];
			$comp  = trim($r['company'] ?? '');
			$email = trim($r['email'] ?? '');
			$text  = $comp . ($email ? ' — '.$email : '');
			$items[] = ['id' => $id, 'text' => $text, 'company' => $comp, 'email' => $email];
		}

		echo json_encode([
			'results'    => $items,
			'pagination' => ['more' => ($off + $per) < $total],
		]);
		die;
	}

}
