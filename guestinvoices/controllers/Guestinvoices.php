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

			$payload = [
				'email'       => $email,
				'firstname'   => $firstname,
				'lastname'    => $lastname,
				'company'     => $company,
			'phonenumber' => (string)$this->input->post('phonenumber') ?: '',
			'website'     => (string)$this->input->post('website') ?: '',
			'address'     => (string)$this->input->post('address') ?: '',
			'city'        => (string)$this->input->post('city') ?: '',
				'state'       => (string)$this->input->post('state') ?: '',
				'zip'         => (string)$this->input->post('zip') ?: '',
				'country'     => (string)$this->input->post('country') ?: '',
				'estimate_emails'    => 1,
				'credit_note_emails' => 1,
				'contract_emails'    => 1,
				'invoice_emails'     => 1,
				'project_emails'     => 1,
			];

		$this->load->library('api/Guest_checkout_service');
		[$client_id, $contact_id, $err] = $this->guest_checkout_service->findOrCreateGuest($payload, [
			'update_existing_name' => false,
		]);

		if ((int)$client_id <= 0) {
			echo json_encode(['success'=>false, 'message'=>$err ?: (_l('something_went_wrong') ?: 'Something went wrong')]); die;
		}

		if ((int)$contact_id <= 0) {
			log_activity('GI WARNING: Contact NOT created for client ID '.$client_id.' (email '.$email.')');
		} else {
			log_activity('GI: Guest contact created/found (ID '.$contact_id.') for client '.$client_id);
		}

		$dbCompany = '';
		$this->db->select('company')->where('userid', (int)$client_id);
		$cRow = $this->db->get(db_prefix().'clients')->row();
		if ($cRow && isset($cRow->company)) {
			$dbCompany = (string)$cRow->company;
		}
		if ($dbCompany === '') {
			$dbCompany = $company ?: trim($firstname.' '.$lastname) ?: ('Guest'.$client_id);
		}

		echo json_encode([
			'success'   => true,
			'client_id' => $client_id,
			'company'   => $dbCompany,
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
