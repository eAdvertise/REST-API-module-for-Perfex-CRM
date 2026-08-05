<?php
//Contactsplus/Api.php
defined('BASEPATH') or exit('No direct script access allowed');

class Contactsplus extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * GET /admin/contactsplus/search_available?client_id=123&q=foo
     * Επιστρέφει core contacts (tblcontacts) που ΔΕΝ είναι συνδεδεμένα (μέσω bridge) με τον δοσμένο πελάτη.
     * Format: { results: [ {id, text, email, phone} ... ] }
     */
    public function search_available()
    {
        // Permission: admin ή staff με δικαίωμα προβολής πελατών ή contactsplus_manage
        $allowed = false;
        if (function_exists('staff_can') && staff_can('contactsplus_manage')) $allowed = true;
        if (function_exists('has_permission') && has_permission('customers', '', 'view')) $allowed = true;
        if (is_admin()) $allowed = true;
        if (!$allowed) { show_404(); }

        $client_id = (int)$this->input->get('client_id');
        $q         = trim((string)$this->input->get('q'));
        $pref      = db_prefix();

        if ($client_id <= 0) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['results' => []], JSON_UNESCAPED_UNICODE));
        }

        $this->db->select('c.id, c.firstname, c.lastname, c.email, c.phonenumber AS phone');
        $this->db->from($pref.'contacts AS c');

        // ΜΟΝΟ αν υπάρχει ο bridge κάνε exclude με NOT EXISTS
        if ($this->db->table_exists($pref.'pmc_contacts_bridge')) {
            $this->db->where("NOT EXISTS (
                SELECT 1 FROM {$pref}pmc_contacts_bridge b
                WHERE b.tblcontact_id = c.id AND b.client_id = ".$this->db->escape($client_id)."
            )", null, false);
        }

        if ($q !== '') {
            $this->db->group_start()
                     ->like('c.firstname', $q)
                     ->or_like('c.lastname', $q)
                     ->or_like('c.email', $q)
                     ->or_like('c.phonenumber', $q)
                     ->group_end();

            $this->db->order_by('c.firstname', 'ASC');
            $this->db->order_by('c.lastname', 'ASC');
            $this->db->limit(100);
        } else {
            // αρχικό φόρτωμα: πιο πρόσφατες εγγραφές, όχι μόνο τα πρώτα αλφαβητικά “Α...”
            $this->db->order_by('c.id', 'DESC');
            $this->db->limit(100);
        }

        $rows = $this->db->get()->result_array();

        $results = array_map(function($r){
            $name = trim(($r['firstname'] ?? '').' '.($r['lastname'] ?? ''));
            return [
                'id'    => (int)$r['id'],
                'text'  => $name !== '' ? $name : ('#'.(int)$r['id']),
                'email' => $r['email'] ?? null,
                'phone' => $r['phone'] ?? null,
            ];
        }, $rows);

        return $this->output->set_content_type('application/json')
            ->set_output(json_encode(['results' => $results], JSON_UNESCAPED_UNICODE));
    }

    /**
     * GET /admin/contactsplus/search_customers?q=acme
     * Επιστρέφει λίστα πελατών για reassign (id, company)
     */
    public function search_customers()
    {
        // permission: admin ή view customers ή contactsplus_manage
        $allowed = is_admin()
            || (function_exists('has_permission') && has_permission('customers', '', 'view'))
            || (function_exists('staff_can') && staff_can('contactsplus_manage'));

        if (!$allowed) {
            show_404();
        }

        $q    = trim((string)$this->input->get('q'));
        $pref = db_prefix();

        $this->db->select('userid as id, company');
        $this->db->from($pref.'clients');
        if ($q !== '') {
            $this->db->group_start()
                     ->like('company', $q)
                     ->or_like('vat', $q)
                     ->or_like('phonenumber', $q)
                     ->group_end();
        }
        $this->db->order_by('company','ASC')->limit(50);
        $rows = $this->db->get()->result_array();

        $results = array_map(function($r){
            return ['id'=>(int)$r['id'], 'text'=>$r['company']];
        }, $rows);

        $this->output->set_content_type('application/json')
            ->set_output(json_encode(['results'=>$results], JSON_UNESCAPED_UNICODE));
    }
}