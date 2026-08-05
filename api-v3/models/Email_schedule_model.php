<?php


defined('BASEPATH') or exit('No direct script access allowed');

class Email_schedule_model extends App_Model {
    public function __construct() {
        parent::__construct();
    }

    public function create($rel_id, $rel_type, $data, $playground = false) {
        $contacts = $data['contacts'];
        if (is_array($contacts)) {
            $contacts = implode(',', $contacts);
        }
        $this->db->insert(($playground ? 'playground_' : '') . 'scheduled_emails', ['rel_type' => $rel_type, 'rel_id' => $rel_id, 'scheduled_at' => $data['scheduled_at'], 'contacts' => $contacts, 'cc' => $data['cc'], 'attach_pdf' => $data['attach_pdf'], 'template' => $data['template'], ]);
    }

    public function update($id, $data, $playground = false) {
        if (is_array($data['contacts'])) {
            $data['contacts'] = implode(',', $data['contacts']);
        }
        $this->db->where('id', $id);
        $this->db->update(($playground ? 'playground_' : '') . 'scheduled_emails', $data);
        return $this->db->affected_rows() > 0;
    }

    public function getById($id, $playground = false) {
        $this->db->where('id', $id);
        return $this->db->get(($playground ? 'playground_' : '') . 'scheduled_emails')->row();
    }

    public function get($rel_id, $rel_type, $playground = false) {
        $this->db->where('rel_id', $rel_id);
        $this->db->where('rel_type', $rel_type);
        return $this->db->get(($playground ? 'playground_' : '') . 'scheduled_emails')->row();
    }
}
