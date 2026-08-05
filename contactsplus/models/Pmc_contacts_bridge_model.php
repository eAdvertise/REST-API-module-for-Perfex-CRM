<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pmc_contacts_bridge_model extends App_Model
{
    protected $table = 'pmc_contacts_bridge';

    public function upsert($pmc_contact_id, $client_id, $tblcontact_id)
    {
        $exists = $this->db->where('pmc_contact_id',$pmc_contact_id)->where('client_id',$client_id)->get(db_prefix().$this->table)->row();
        if ($exists) {
            $this->db->where('id', $exists->id)->update(db_prefix().$this->table, ['tblcontact_id'=>$tblcontact_id]);
            return $exists->id;
        }
        $this->db->insert(db_prefix().$this->table, [
            'pmc_contact_id'=>$pmc_contact_id,
            'client_id'=>$client_id,
            'tblcontact_id'=>$tblcontact_id
        ]);
        return $this->db->insert_id();
    }
}
