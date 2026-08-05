<?php
//controllers/Api.php
defined('BASEPATH') or exit('No direct script access allowed');

class Api extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('contactsplus/pmc_contacts_model');
        $this->load->model('contactsplus/pmc_contact_company_model');
    }

    private function json($data, int $status = 200)
    {
        return $this->output
            ->set_status_header($status)
            ->set_content_type('application/json')
            ->set_output(is_string($data) ? $data : json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    private function require_post()
    {
        if (strtoupper($this->input->method(true)) !== 'POST') {
            $this->json(['error' => 'Method Not Allowed'], 405);
            exit;
        }
    }

    /**
     * POST /admin/contactsplus/api/core_contacts/{core_contact_id}/link
     */
    public function link_core_contact($core_contact_id)
    {
        $this->require_post();

        $payload   = $this->input->post();
        $client_id = (int)($payload['client_id'] ?? 0);
        if ($client_id <= 0) {
            return $this->json(['ok'=>false,'error' => 'client_id required'], 400);
        }

        // 1) Core contact
        $core = $this->db->where('id', (int)$core_contact_id)
                         ->get(db_prefix().'contacts')
                         ->row_array();
        if (!$core) {
            return $this->json(['ok'=>false,'error' => 'Core contact not found'], 404);
        }

        // 2) Find or create pmc_contact (by email else firstname/lastname/phone)
        $pmc = null;
        if (!empty($core['email'])) {
            $pmc = $this->db->where('email', $core['email'])
                            ->get(db_prefix().'pmc_contacts')->row_array();
        }
        if (!$pmc) {
            $this->db->from(db_prefix().'pmc_contacts');
            $this->db->where('firstname', $core['firstname'] ?? '');
            $this->db->where('lastname',  $core['lastname']  ?? null);
            if (!empty($core['phonenumber'])) {
                $this->db->where('phone', $core['phonenumber']);
            }
            $pmc = $this->db->get()->row_array();
        }

        if ($pmc) {
            $pmc_id = (int)$pmc['id'];
        } else {
            $pmc_id = $this->pmc_contacts_model->create([
                'firstname'         => $core['firstname'] ?? '',
                'lastname'          => $core['lastname']  ?? null,
                'email'             => $core['email']     ?? null,
                'phone'             => $core['phonenumber'] ?? null,
                'position'          => $payload['position'] ?? null,
                'has_portal_access' => 1,
                'status'            => 'active',
            ]);
        }

        // 3) Update position (optional)
        if (!empty($payload['position'])) {
            $this->db->where('id', $pmc_id)
                     ->update(db_prefix().'pmc_contacts', ['position' => $payload['position']]);
        }

        // 4) Build link attributes
        $perms       = isset($payload['perm'])        && is_array($payload['perm'])        ? $payload['perm']        : [];
        $email_notif = isset($payload['email_notif']) && is_array($payload['email_notif']) ? $payload['email_notif'] : [];
        $flags       = isset($payload['flags'])       && is_array($payload['flags'])       ? $payload['flags']       : [];

        if (function_exists('get_contact_permissions')) {
            $allowed = array_column(get_contact_permissions(), 'id');
            $perms   = array_intersect_key($perms, array_flip($allowed));
        }

        $attrs = [
            'role'          => null,
            'is_primary'    => !empty($flags['is_primary']) ? 1 : 0,
            'billing'       => !empty($flags['billing']) ? 1 : 0,
            'notifications' => array_key_exists('notifications',$flags) ? (int)!empty($flags['notifications']) : 1,
            'perms'         => $perms,
            'email_notif'   => $email_notif,
        ];

        // 5) Link (UPSERT)
        $link_id = $this->pmc_contact_company_model->link($pmc_id, $client_id, $attrs);

        // 6) BRIDGE: mapping core<->pmc για αυτόν τον client
        $pref = db_prefix();
        $bridge = $this->db->where('tblcontact_id', (int)$core_contact_id)
                           ->where('client_id', (int)$client_id)
                           ->get($pref.'pmc_contacts_bridge')->row_array();

        $bridgeData = [
            'tblcontact_id'   => (int)$core_contact_id,
            'client_id'       => (int)$client_id,
            'pmc_contact_id'  => (int)$pmc_id,
        ];

        if ($bridge) {
            $this->db->where('id', (int)$bridge['id'])->update($pref.'pmc_contacts_bridge', $bridgeData);
        } else {
            $this->db->insert($pref.'pmc_contacts_bridge', $bridgeData);
        }

        return $this->json([
            'ok'             => true,
            'pmc_contact_id' => $pmc_id,
            'link_id'        => $link_id,
        ], 200);
    }

    /**
     * POST /admin/contactsplus/api/unlink/{link_id}
     * Αφαιρεί ΜΟΝΟ τη σύνδεση contact<->client (δεν σβήνει τον pmc_contact).
     */
    public function unlink($link_id)
    {
        $this->require_post();
        $link_id = (int)$link_id;
        if ($link_id <= 0) {
            return $this->json(['ok'=>false,'error'=>'Invalid link_id'], 400);
        }
        $ok = $this->pmc_contact_company_model->unlink($link_id);
        return $this->json(['ok'=>(bool)$ok], 200);
    }

    /**
     * POST /admin/contactsplus/api/delete_contact/{pmc_contact_id}
     * Σβήνει ΟΡΙΣΤΙΚΑ τον pmc_contact και όλες τις συνδέσεις του.
     */
    public function delete_contact($pmc_contact_id)
    {
        $this->require_post();
        $pmc_contact_id = (int)$pmc_contact_id;
        if ($pmc_contact_id <= 0) {
            return $this->json(['ok'=>false,'error'=>'Invalid contact id'], 400);
        }

        $pref = db_prefix();
        // delete links
        $this->db->where('contact_id', $pmc_contact_id)->delete($pref.'pmc_contact_company');
        // delete pmc record
        $this->db->where('id', $pmc_contact_id)->delete($pref.'pmc_contacts');

        return $this->json(['ok'=>true], 200);
    }

    /**
     * POST /admin/contactsplus/api/unlink_core/{core_contact_id}
     * Αφαιρεί τη “γέφυρα” και το pmc link για τον δοσμένο πελάτη (ΔΕΝ σβήνει τον core contact).
     * Body: client_id (required)
     */
    public function unlink_core($core_contact_id)
    {
        $this->require_post();

        $client_id = (int)$this->input->post('client_id');
        if ($client_id <= 0) {
            return $this->json(['ok'=>false,'error'=>'client_id required'], 400);
        }

        $doDelete = (int)$this->input->post('delete') === 1;

        $pref = db_prefix();

        if ($doDelete) {
            // --- DELETE πραγματικού core contact + cleanup bridge/link ---
            $core = $this->db->where('id', (int)$core_contact_id)->get($pref.'contacts')->row_array();
            if (!$core) { return $this->json(['ok'=>false,'error'=>'Core contact not found'], 404); }
            if ((int)$core['userid'] !== $client_id) {
                return $this->json(['ok'=>false,'error'=>'Contact does not belong to this client'], 400);
            }

            // σβήσε μέσω core model για σωστό housekeeping
            $this->load->model('clients_model');
            $ok = $this->clients_model->delete_contact((int)$core_contact_id, (int)$client_id);

            // καθάρισε τυχόν bridges για αυτόν τον client
            if ($this->db->table_exists($pref.'pmc_contacts_bridge')) {
                $this->db->where('tblcontact_id', (int)$core_contact_id)
                         ->where('client_id', $client_id)
                         ->delete($pref.'pmc_contacts_bridge');
            }

            return $this->json(['ok'=>(bool)$ok], $ok ? 200 : 500);
        }

        // --- ΜΟΝΟ unlink (bridge + pmc link) ---
        $bridge = $this->db->where('tblcontact_id', (int)$core_contact_id)
                           ->where('client_id', $client_id)
                           ->get($pref.'pmc_contacts_bridge')
                           ->row_array();

        if ($bridge) {
            // drop pmc link (αν υπάρχει)
            $this->db->where('contact_id', (int)$bridge['pmc_contact_id'])
                     ->where('client_id', $client_id)
                     ->delete($pref.'pmc_contact_company');
            // drop bridge
            $this->db->where('id', (int)$bridge['id'])->delete($pref.'pmc_contacts_bridge');
        }

        return $this->json(['ok'=>true], 200);
    }

    /**
     * POST /admin/contactsplus/api/move_core/{core_contact_id}
     * Μεταφορά core contact σε άλλον πελάτη (αλλάζει το userid στο tblcontacts)
     * Body: from_client_id (required), to_client_id (required)
     */
    public function move_core($core_contact_id)
    {
        $this->require_post();

        $from = (int)$this->input->post('from_client_id');
        $to   = (int)$this->input->post('to_client_id');

        if ($from<=0 || $to<=0) return $this->json(['ok'=>false,'error'=>'from_client_id & to_client_id required'], 400);
        if ($from === $to)      return $this->json(['ok'=>false,'error'=>'Target client must be different'], 400);

        $pref = db_prefix();

        // Υπάρχει ο core contact;
        $core = $this->db->where('id', (int)$core_contact_id)->get($pref.'contacts')->row_array();
        if (!$core) return $this->json(['ok'=>false,'error'=>'Core contact not found'], 404);

        // Επιβεβαίωση ότι ανήκει στον from
        if ((int)$core['userid'] !== $from) return $this->json(['ok'=>false,'error'=>'Contact does not belong to the source client'], 400);

        // Υπάρχει ο target client;
        $target = $this->db->where('userid', $to)->get($pref.'clients')->row_array();
        if (!$target) return $this->json(['ok'=>false,'error'=>'Target client not found'], 404);

        // Ενημέρωση userid (μεταφορά)
        $this->db->where('id', (int)$core_contact_id)->update($pref.'contacts', ['userid'=>$to]);

        // Καθάρισε bridge για τον παλιό πελάτη
        if ($this->db->table_exists($pref.'pmc_contacts_bridge')) {
            $this->db->where('tblcontact_id', (int)$core_contact_id)->where('client_id', $from)->delete($pref.'pmc_contacts_bridge');
        }

        return $this->json(['ok'=>true], 200);
    }

    /**
     * GET /admin/contactsplus/api/get_pmc_contact/{id}?client_id=...
     * Επιστρέφει στοιχεία pmc contact + link flags για client_id (αν δοθεί)
     */
    public function get_pmc_contact($id)
    {
        $id   = (int)$id;
        $pref = db_prefix();

        $pmc = $this->db->where('id',$id)->get($pref.'pmc_contacts')->row_array();
        if (!$pmc) return $this->json(['ok'=>false,'error'=>'Not found'], 404);

        $client_id = (int)($this->input->get('client_id') ?? 0);
        $link = null;
        $perms_ids = [];
        $email_notif_keys = [];

        if ($client_id>0) {
            $link = $this->db->where('contact_id',$id)
                             ->where('client_id',$client_id)
                             ->get($pref.'pmc_contact_company')->row_array();

            // ---- Permissions: normalize σε numeric IDs για το core modal ----
            if (!empty($link['perms_json'])) {
                $raw = json_decode($link['perms_json'], true);
                if (is_array($raw)) {
                    // Φέρε core permissions (id + short_name)
                    $mapShortToId = [];
                    if (function_exists('get_contact_permissions')) {
                        foreach (get_contact_permissions() as $p) {
                            if (isset($p['short_name'])) {
                                $mapShortToId[(string)$p['short_name']] = (string)$p['id'];
                            }
                            $mapShortToId[(string)$p['id']] = (string)$p['id'];
                        }
                    }
                    foreach ($raw as $k) {
                        $k = (string)$k;
                        if (isset($mapShortToId[$k])) {
                            $perms_ids[] = (int)$mapShortToId[$k];
                        } elseif (ctype_digit($k)) {
                            $perms_ids[] = (int)$k; // ήδη numeric
                        }
                    }
                    $perms_ids = array_values(array_unique($perms_ids));
                }
            }

            // ---- Email notifications: επέστρεψε τα keys σαν array ----
            if (!empty($link['email_notif_json'])) {
                $rawNotif = json_decode($link['email_notif_json'], true);
                if (is_array($rawNotif)) {
                    $email_notif_keys = array_values(array_unique(array_map('strval', $rawNotif)));
                }
            }
        }

        return $this->json([
            'ok'                => true,
            'contact'           => $pmc,
            'link'              => $link,
            'perms_ids'         => $perms_ids,
            'email_notif_keys'  => $email_notif_keys,
        ], 200);
    }

    /**
     * POST /admin/contactsplus/api/update_pmc_contact/{id}
     * Ενημέρωση pmc contact (firstname, lastname, email, phone, position)
     * + προαιρετικά ενημέρωση link (role, is_primary, billing, notifications, perms/email_notif)
     * + [ΝΕΟ] sync και στο tblcontacts αν υπάρχει bridge με τον client
     */
    public function update_pmc_contact($id)
    {
        $this->require_post();

        $id   = (int)$id;
        $pref = db_prefix();

        $pmc = $this->db->where('id',$id)->get($pref.'pmc_contacts')->row_array();
        if (!$pmc) return $this->json(['ok'=>false,'error'=>'Not found'], 404);

        $payload = $this->input->post();

        $upd = [
            'firstname' => trim((string)($payload['firstname'] ?? $pmc['firstname'])),
            'lastname'  => trim((string)($payload['lastname']  ?? $pmc['lastname'])),
            'email'     => trim((string)($payload['email']     ?? $pmc['email'])),
            'phone'     => trim((string)($payload['phone']     ?? $pmc['phone'])),
            'position'  => trim((string)($payload['position']  ?? $pmc['position'])),
        ];
        $this->db->where('id',$id)->update($pref.'pmc_contacts', $upd);

        // Προαιρετικά ενημέρωσε link αν δόθηκε client_id
        $client_id = (int)($payload['client_id'] ?? 0);
        if ($client_id>0) {
            $attrs = [
                'role'          => $payload['role'] ?? null,
                'is_primary'    => !empty($payload['is_primary']) ? 1 : 0,
                'billing'       => !empty($payload['billing']) ? 1 : 0,
                'notifications' => isset($payload['notifications']) ? (int)!empty($payload['notifications']) : 1,
            ];
            if (isset($payload['perm']) && is_array($payload['perm'])) {
                $attrs['perms'] = $payload['perm'];
            }
            if (isset($payload['email_notif']) && is_array($payload['email_notif'])) {
                $attrs['email_notif'] = $payload['email_notif'];
            }
            // map core modal permissions[] (numeric ids) -> attrs['perms']
            if (isset($payload['permissions']) && is_array($payload['permissions'])) {
                $attrs['perms'] = array_fill_keys(array_map('strval', $payload['permissions']), 1);
            }

            // συλλογή email notifications από core-like checkboxes
            $emailKeys = [
                'invoice_emails','estimate_emails','credit_note_emails',
                'project_emails','ticket_emails','task_emails','contract_emails'
            ];
            $em = [];
            foreach ($emailKeys as $ek) {
                if (!empty($payload[$ek])) { $em[$ek] = 1; }
            }
            if ($em) {
                $attrs['email_notif'] = $em;
            }

            $this->pmc_contact_company_model->link($id, $client_id, $attrs);
        }

        // --- Συγχρονισμός και στο core contact αν υπάρχει bridge για τον πελάτη ---
        if ($client_id > 0 && $this->db->table_exists(db_prefix().'pmc_contacts_bridge')) {
            $bridge = $this->db->where('pmc_contact_id', $id)
                               ->where('client_id', $client_id)
                               ->get(db_prefix().'pmc_contacts_bridge')
                               ->row_array();
            if ($bridge) {
                $coreUpd = [
                    'firstname'   => $upd['firstname'],
                    'lastname'    => $upd['lastname'],
                    'email'       => $upd['email'],
                    'phonenumber' => $upd['phone'],
                    'title'       => $upd['position'],
                ];
                $this->db->where('id', (int)$bridge['tblcontact_id'])
                         ->update(db_prefix().'contacts', $coreUpd);
            }
        }

        return $this->json(['ok'=>true], 200);
    }

    /**
     * GET /admin/contactsplus/api/emails_for_client?client_id=123&context=invoice
     *
     * Επιστρέφει emails από:
     *  - CORE contacts (tblcontacts) με ενεργό το αντίστοιχο notification flag
     *  - Contacts+ links (pmc_contact_company + pmc_contacts) με notifications=1
     *    και το αντίστοιχο key στο email_notif_json.
     *
     * context -> flag:
     *   invoice/receipt/payment => invoice_emails
     *   estimate                => estimate_emails
     *   credit_note             => credit_note_emails
     *   project                 => project_emails
     *   ticket                  => ticket_emails
     *   task                    => task_emails
     *   contract                => contract_emails
     *
     * Αν δεν δοθεί context, επιστρέφει όσους έχουν οποιοδήποτε σχετικό flag.
     */
	public function emails_for_client()
	{
		// Returns Contacts+ emails for a given client (or inferred from a core contact id)
		// GET params:
		//   - client_id (int) OR contact_id (int, core tblcontacts.id)
		//   - context (string) optional: invoice|estimate|credit_note|proposal|delivery_note|payment|poa_statement|generic
		$client_id = (int)$this->input->get('client_id');
		$contact_id = (int)$this->input->get('contact_id'); // core contact id (tblcontacts.id)
		$context = trim((string)$this->input->get('context'));

		$pref = db_prefix();

		if ($client_id <= 0 && $contact_id > 0) {
			// infer client_id from core contact
			$row = $this->db->select('userid')->where('id', $contact_id)->get($pref.'contacts')->row_array();
			if ($row && !empty($row['userid'])) {
				$client_id = (int)$row['userid'];
			}
		}

		if ($client_id <= 0) {
			return $this->json(['ok'=>false,'error'=>'client_id or contact_id required'], 400);
		}

		// Optional: context-based filtering key mapping for Contacts+
		// Τα keys στο email_notif_json του link είναι σε στυλ core:
		// invoice_emails, estimate_emails, credit_note_emails, project_emails, ticket_emails, task_emails, contract_emails
		// Δεν υπάρχει ειδικό "payment", οπότε για payments/receipts θα χρησιμοποιήσουμε invoice_emails σαν proxy.
		$ctxKey = null;
		switch ($context) {
			case 'invoice':        $ctxKey = 'invoice_emails'; break;
			case 'estimate':       $ctxKey = 'estimate_emails'; break;
			case 'credit_note':    $ctxKey = 'credit_note_emails'; break;
			case 'proposal':       $ctxKey = null; break; // δεν υπάρχει ειδικό flag -> μη φιλτράρεις
			case 'delivery_note':  $ctxKey = 'invoice_emails'; break; // κοντινότερο proxy
			case 'payment':        $ctxKey = 'invoice_emails'; break; // κοντινότερο proxy
			case 'poa_statement':  $ctxKey = 'invoice_emails'; break; // κοντινότερο proxy
			default:               $ctxKey = null;
		}

		// Φέρε Contacts+ emails για τον πελάτη
		// κριτήρια:
		//  - link.notifications = 1
		//  - pmc.email IS NOT NULL & valid
		//  - αν υπάρχει email_notif_json -> πρέπει να περιέχει το ctxKey (αν δόθηκε)
		$this->db->from($pref.'pmc_contact_company as l');
		$this->db->join($pref.'pmc_contacts as c', 'c.id = l.contact_id', 'inner');
		$this->db->where('l.client_id', $client_id);
		$this->db->where('l.notifications', 1);
		$this->db->where('c.email IS NOT NULL', null, false);
		$this->db->where("c.email != ''", null, false);
		$links = $this->db->get()->result_array();

		$out = [];
		foreach ($links as $r) {
			// context filter
			if ($ctxKey) {
				if (!empty($r['email_notif_json'])) {
					$jn = json_decode($r['email_notif_json'], true);
					if (is_array($jn)) {
						// μπορεί να είναι associative {invoice_emails:1,...} ή array
						$has = false;
						if (array_keys($jn) !== range(0, count($jn)-1)) {
							// assoc
							$has = !empty($jn[$ctxKey]);
						} else {
							// indexed
							$has = in_array($ctxKey, array_map('strval', $jn), true);
						}
						if (!$has) continue;
					}
				}
				// αν δεν έχει email_notif_json καθόλου, τον αφήνουμε να περάσει (χαλαρό default)
			}

			$email = trim((string)$r['email']);
			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) continue;

			$labelParts = [];
			if (!empty($r['firstname'])) $labelParts[] = $r['firstname'];
			if (!empty($r['lastname']))  $labelParts[] = $r['lastname'];
			$label = trim(implode(' ', $labelParts));
			if ($label !== '') $label .= ' — ';
			$label .= $email;

			$out[] = [
				'email'  => $email,
				'label'  => $label,
				'source' => 'contactsplus',
			];
		}

		// Deduplicate by email (case-insensitive)
		$seen = [];
		$uniq = [];
		foreach ($out as $row) {
			$k = strtolower($row['email']);
			if (isset($seen[$k])) continue;
			$seen[$k] = 1;
			$uniq[] = $row;
		}

		return $this->json(['ok'=>true, 'emails'=>$uniq, 'client_id'=>$client_id], 200);
	}

}
