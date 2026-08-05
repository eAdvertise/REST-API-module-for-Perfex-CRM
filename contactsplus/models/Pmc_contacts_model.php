<?php
//modules/contactsplus/models/pmc_contacts_model.php
defined('BASEPATH') or exit('No direct script access allowed');

class Pmc_contacts_model extends App_Model
{
    protected $table = 'pmc_contacts';

    public function create($data)
    {
        if (!isset($data['firstname']) || $data['firstname'] === '') {
            throw new Exception(_l('contactsplus_err_firstname_required'));
        }
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception(_l('contactsplus_err_invalid_email'));
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix().$this->table, $data);
        return $this->db->insert_id();
    }

    public function find($id)
    {
        return $this->db->where('id', $id)->get(db_prefix().$this->table)->row();
    }

    public function search($q)
	{
		// Αν δεν υπάρχει query, δείξε πρόσφατες (π.χ. 20 τελευταίες)
		if (!$q) {
			$this->db->order_by('created_at', 'DESC');
			return $this->db->limit(20)->get(db_prefix().$this->table)->result_array();
		}

		$this->db->like('firstname', $q);
		$this->db->or_like('lastname', $q);
		$this->db->or_like('email', $q);
		$this->db->or_like('phone', $q);
		return $this->db->limit(20)->get(db_prefix().$this->table)->result_array();
	}


    public function set_portal_access($id, $flag)
    {
        $this->db->where('id', $id)->update(
            db_prefix().$this->table,
            [
                'has_portal_access' => (int)$flag,
                'updated_at'        => date('Y-m-d H:i:s')
            ]
        );
    }
}
