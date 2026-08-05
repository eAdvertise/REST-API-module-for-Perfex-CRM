<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Payment_attempts_model extends App_Model {
    public function __construct() {
        parent::__construct();
    }

    /**
     * @param array $data
     * @return object|null
     */
    public function add(array $data, $playground = false) {
        $data['created_at'] = date('c');
        $this->db->insert(($playground ? 'playground_' : '') . 'payment_attempts', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            return $this->get($insert_id, $data['payment_gateway'], $playground);
        }
        return null;
    }
    /**
     * @param  int  $id
     * @param  string  $gateway
     * @return object|null
     */
    public function get(int $id, string $gateway, $playground = false) {
        $this->db->where('id', $id);
        $this->db->where('payment_gateway', $gateway);
        return $this->db->get(($playground ? 'playground_' : '') . 'payment_attempts')->row();
    }
    /**
     * @param  string  $reference
     * @param  string  $gateway
     * @return object|null
     */
    public function getByReference(string $reference, string $gateway, $playground = false) {
        $this->db->where('reference', $reference);
        $this->db->where('payment_gateway', $gateway);
        return $this->db->get(($playground ? 'playground_' : '') . 'payment_attempts')->row();
    }
    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id, $playground = false) {
        $this->db->where('id', $id);
        return (bool)$this->db->delete(($playground ? 'playground_' : '') . 'payment_attempts');
    }
}
