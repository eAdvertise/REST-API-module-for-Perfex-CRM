<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Contactsplus_model extends App_Model
{
    /**
     * Επέστρεψε core contacts ΟΛΟΥ του συστήματος,
     * ΕΞΑΙΡΩΝΤΑΣ όσους έχουν userid = $client_id (ήδη συνδεδεμένοι με αυτόν τον πελάτη).
     * Optional αναζήτηση σε firstname/lastname/email/phonenumber.
     */
    public function search_available_core_contacts(int $client_id, string $q = '', int $limit = 30): array
    {
        $tbl = db_prefix().'contacts';

        $this->db->from($tbl);

        // exclude already linked to this client
        $this->db->group_start();
        $this->db->where("{$tbl}.userid IS NULL", null, false);
        $this->db->or_where("{$tbl}.userid !=", $client_id);
        $this->db->group_end();

        if ($q !== '') {
            $this->db->group_start();
            $this->db->like("{$tbl}.firstname", $q);
            $this->db->or_like("{$tbl}.lastname", $q);
            $this->db->or_like("{$tbl}.email", $q);
            $this->db->or_like("{$tbl}.phonenumber", $q);
            $this->db->group_end();
        }

        $this->db->order_by("{$tbl}.id", 'DESC');
        $this->db->limit($limit);

        $rows = $this->db->get()->result_array();

        return array_map(function($r){
            return [
                'id'        => (int)($r['id'] ?? 0),
                'firstname' => $r['firstname'] ?? '',
                'lastname'  => $r['lastname']  ?? '',
                'email'     => $r['email']     ?? '',
                'phone'     => $r['phonenumber'] ?? '',
            ];
        }, $rows);
    }
}
