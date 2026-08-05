<?php
//modules/contactsplus/models/pmc_contact_company_model.php
defined('BASEPATH') or exit('No direct script access allowed');

class Pmc_contact_company_model extends App_Model
{
    protected $table = 'pmc_contact_company';

    public function get_by_client($client_id)
    {
        if (!$client_id) return [];

        // --- 1) Contacts+ links (pmc_*)
        $this->db->select('
            cc.id            AS link_id,
            cc.contact_id    AS pmc_contact_id,
            cc.client_id,
            cc.role,
            cc.is_primary,
            cc.billing,
            cc.notifications,
            c.firstname,
            c.lastname,
            c.email,
            c.phone,
            c.position
        ');
        $this->db->from(db_prefix().'pmc_contact_company AS cc');
        $this->db->join(db_prefix().'pmc_contacts AS c', 'c.id = cc.contact_id', 'inner');
        $this->db->where('cc.client_id', $client_id);
        $pmc = $this->db->get()->result_array();

        foreach ($pmc as &$r) {
            $r['source']       = 'pmc';
            $r['contact_id']   = (int)$r['pmc_contact_id'];
            $r['display_role'] = $r['role'] ?: ($r['position'] ?? '');
        }
        unset($r);

        // --- 2) Core contacts (tblcontacts) για τον ίδιο πελάτη
        $core = $this->db->query("
            SELECT
                t.id,
                t.firstname,
                t.lastname,
                t.email,
                t.title,
                t.phonenumber AS phone,
                t.is_primary
            FROM `".db_prefix()."contacts` AS t
            WHERE t.`userid` = ?
        ", [$client_id])->result_array();

        foreach ($core as &$r) {
            $r = [
                'link_id'       => null,
                'contact_id'    => null,
                'core_id'       => (int)$r['id'],
                'client_id'     => (int)$client_id,
                'display_role'  => $r['title'] ?? '',
                'is_primary'    => isset($r['is_primary']) ? (int)$r['is_primary'] : 0,
                'billing'       => 0,
                'notifications' => 1,
                'firstname'     => $r['firstname'] ?? '',
                'lastname'      => $r['lastname'] ?? '',
                'email'         => $r['email'] ?? '',
                'phone'         => $r['phone'] ?? '',
                'position'      => null,
                'source'        => 'core',
            ];
        }
        unset($r);

        return array_merge($pmc, $core);
    }

    public function link($pmc_contact_id, $client_id, array $attrs = [])
    {
        $defaults = [
            'role'          => null,
            'is_primary'    => 0,
            'billing'       => 0,
            'notifications' => 1,
            'perms'         => [],
            'email_notif'   => [],
        ];
        $attrs = array_merge($defaults, $attrs);

        $table = db_prefix().'pmc_contact_company';

        $exists = $this->db->where('contact_id', (int)$pmc_contact_id)
                           ->where('client_id', (int)$client_id)
                           ->get($table)
                           ->row_array();

        $data = [
            'contact_id'       => (int)$pmc_contact_id,
            'client_id'        => (int)$client_id,
            'role'             => $attrs['role'],
            'is_primary'       => (int)$attrs['is_primary'],
            'billing'          => (int)$attrs['billing'],
            'notifications'    => (int)$attrs['notifications'],
            'perms_json'       => !empty($attrs['perms'])
                ? json_encode(array_keys(array_filter($attrs['perms'])), JSON_UNESCAPED_UNICODE)
                : null,
            'email_notif_json' => !empty($attrs['email_notif'])
                ? json_encode(array_keys(array_filter($attrs['email_notif'])), JSON_UNESCAPED_UNICODE)
                : null,
            'created_at'       => date('Y-m-d H:i:s'),
        ];

        if ($exists) {
            // στο update δεν αλλάζουμε το created_at
            $update = $data;
            unset($update['created_at']);

            $this->db->where('id', (int)$exists['id'])->update($table, $update);
            return (int)$exists['id'];
        }

        $this->db->insert($table, $data);
        return (int)$this->db->insert_id();
    }

    public function unlink($link_id)
    {
        $this->db->where('id', $link_id)->delete(db_prefix().$this->table);
        return $this->db->affected_rows() > 0;
    }
}