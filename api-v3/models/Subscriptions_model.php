<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Subscriptions_model extends App_Model {
    public function __construct() {
        parent::__construct();

        $this->load->model('clients_model');
    }

    public function get($where = [], $playground = false) {
        $this->select($playground);
        $this->join($playground);
        $this->db->where($where);
        return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'subscriptions')->result_array();
    }

    public function get_by_id($id, $where = [], $playground = false) {
        $this->select($playground);
        $this->join($playground);
        $this->db->where(db_prefix() . ($playground ? 'playground_' : '') . 'subscriptions.id', $id);
        $this->db->where($where);
        return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'subscriptions')->row();
    }

    public function get_by_hash($hash, $where = [], $playground = false) {
        $this->select($playground);
        $this->join($playground);
        $this->db->where('hash', $hash);
        $this->db->where($where);
        return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'subscriptions')->row();
    }

    public function get_child_invoices($id, $playground = false) {
        $this->db->select('id');
        $this->db->where('subscription_id', $id);
        $invoices = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'invoices')->result_array();
        $child = [];
        $this->load->model('invoices_model');
        foreach ($invoices as $invoice) {
            $child[] = $this->invoices_model->get($invoice['id'], $playground);
        }
        return $child;
    }

    public function add($data, $playground = false) {
        $data = [
            'name' =>  $this->input->post('name'),
            'description' =>  $this->input->post('description'),
            'description_in_item' =>  $this->input->post('description_in_item'),
            'clientid ' => $this->input->post('clientid'),
            'date' =>  $this->input->post('date'),
            'terms' => $this->input->post('terms'),
            'currency' =>  $this->input->post('currency'),
            'tax_id' => $this->input->post('tax_id'),
            'stripe_tax_id' =>  $this->input->post('stripe_tax_id'),
            'tax_id_2' => $this->input->post('tax_id_2'),
            'stripe_tax_id_2' =>  $this->input->post('stripe_tax_id_2'),
            'stripe_plan_id' =>  $this->input->post('stripe_plan_id'),
            'next_billing_cycle' => $this->input->post('next_billing_cycle'),
            'ends_at' =>  $this->input->post('ends_at'),
            'status' =>  $this->input->post('status'),
            'quantity' =>  $this->input->post('quantity'),
            'project_id' =>  $this->input->post('project_id'),
            'hash' =>  $this->input->post('hash'),
            'created' =>  $this->input->post('created'),
            'created_from' =>  $this->input->post('created_from'),
            'date_subscribed' =>  $this->input->post('date_subscribed'),
            'in_test_environment' =>  $this->input->post('in_test_environment'),
            'last_sent_at' =>  $this->input->post('last_sent_at'),
        ];
 
        $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'subscriptions', $data);
        $insert_id = $this->db->insert_id();
     
        if ($insert_id) {
            return true;
        }

        return false;
    }

    public function create($data, $playground = false) {
        $data = $this->handleSelectedTax($data, $playground);
        $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'subscriptions', array_merge($data, ['created' => date('Y-m-d H:i:s'), 'hash' => app_generate_hash(), 'created_from' => get_staff_user_id(), ]));
        return $this->db->insert_id();
    }

    public function update($id = '', $data, $playground = false) {
        if ($id) {
            $data = $this->handleSelectedTax($data, $playground);
            $this->db->where(db_prefix() . ($playground ? 'playground_' : '') . 'subscriptions.id', $id);
            $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'subscriptions', $data);
            return $this->db->affected_rows() > 0;
        } else {            
            if (isset($data['id'])){
                $this->db->where('id', $data['id']);
                $event = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'subscriptions')->row();
                if (!$event){
                    return false;
                }
                $data = hooks()->apply_filters('event_update_data', $data, $data['id']);
                $this->db->where('id', $data['id']);
                $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'subscriptions', $data);
                if ($this->db->affected_rows() > 0){
                    return true;
                }
                return false;
            }
        }
    }

    private function select($playground = false) {
        $this->db->select(db_prefix() . ($playground ? 'playground_' : '') . 'subscriptions.id as id, stripe_tax_id, stripe_tax_id_2, terms, in_test_environment, date, next_billing_cycle, status, ' . db_prefix() . ($playground ? 'playground_' : '') . 'subscriptions.project_id as project_id, description, ' . db_prefix() . ($playground ? 'playground_' : '') . 'subscriptions.created_from as created_from, ' . db_prefix() . ($playground ? 'playground_' : '') . 'subscriptions.name as name, ' . db_prefix() . ($playground ? 'playground_' : '') . 'currencies.name as currency_name, ' . db_prefix() . ($playground ? 'playground_' : '') . 'currencies.symbol, currency, clientid, ends_at, date_subscribed, stripe_plan_id,stripe_subscription_id,quantity,hash,description_in_item,' . db_prefix() . ($playground ? 'playground_' : '') . 'taxes.name as tax_name, ' . db_prefix() . ($playground ? 'playground_' : '') . 'taxes.taxrate as tax_percent, ' . db_prefix() . ($playground ? 'playground_' : '') . 'taxes_2.name as tax_name_2, ' . db_prefix() . ($playground ? 'playground_' : '') . 'taxes_2.taxrate as tax_percent_2, tax_id, tax_id_2, stripe_id as stripe_customer_id,' . $this->clients_model->get_sql_select_client_company('company', $playground));
    }

    private function join($playground = false) {
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'currencies', db_prefix() . ($playground ? 'playground_' : '') . 'currencies.id=' . db_prefix() . ($playground ? 'playground_' : '') . 'subscriptions.currency');
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'taxes', '' . db_prefix() . ($playground ? 'playground_' : '') . 'taxes.id = ' . db_prefix() . ($playground ? 'playground_' : '') . 'subscriptions.tax_id', 'left');
        $this->db->join('' . db_prefix() . ($playground ? 'playground_' : '') . 'taxes as ' . db_prefix() . ($playground ? 'playground_' : '') . 'taxes_2', '' . db_prefix() . ($playground ? 'playground_' : '') . 'taxes_2.id = ' . db_prefix() . ($playground ? 'playground_' : '') . 'subscriptions.tax_id_2', 'left');
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'clients', db_prefix() . ($playground ? 'playground_' : '') . 'clients.userid=' . db_prefix() . ($playground ? 'playground_' : '') . 'subscriptions.clientid');
    }

    public function send_email_template($id, $cc = '', $template = 'subscription_send_to_customer', $playground = false) {
        $subscription = $this->get_by_id($id, [], $playground);
        $contact = $this->clients_model->get_contact($this->clients_model->get_primary_contact_user_id($subscription->clientid, $playground), ['active' => 1], [], $playground);
        if (!$contact) {
            return false;
        }
        $sent = send_mail_template($template, $subscription, $contact, $cc);
        if ($sent) {
            if ($template == 'subscription_send_to_customer') {
                $this->db->where(db_prefix() . ($playground ? 'playground_' : '') . 'subscriptions.id', $id);
                $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'subscriptions', ['last_sent_at' => date('c') ]);
            }
            return true;
        }
        return false;
    }

    public function delete($id, $simpleDelete = false, $playground = false) {
        $subscription = $this->get_by_id($id, [], $playground);
        if ($subscription->in_test_environment === '0') {
            if (!empty($subscription->stripe_subscription_id) && $simpleDelete == false) {
                return false;
            }
        }
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'subscriptions');
        if ($this->db->affected_rows() > 0) {
            $this->load->model('misc_model');
            $this->misc_model->delete_tracked_emails($id, 'subscription', $playground);
            $this->db->where('subscription_id');
            $this->db->update(($playground ? 'playground_' : '') . 'invoices', ['subscription_id' => 0]);
            return $subscription;
        }
        return false;
    }

    protected function handleSelectedTax($data, $playground = false) {
        $this->load->library('stripe_core');
        foreach (['stripe_tax_id', 'stripe_tax_id_2'] as $key) {
            $localKey = $key === 'stripe_tax_id' ? 'tax_id' : 'tax_id_2';
            if (isset($data[$key]) && !empty($data[$key])) {
                $stripe_tax = $this->stripe_core->retrieve_tax_rate($data[$key]);
                $displayName = $stripe_tax->display_name;
                // Region label when using Stripe Region Label field.
                $displayName.= !empty($stripe_tax->jurisdiction) ? ' - ' . $stripe_tax->jurisdiction : '';
                $this->db->where('name', $displayName);
                $this->db->where('taxrate', $percentage = number_format($stripe_tax->percentage, get_decimal_places()));
                $dbTax = $this->db->get(($playground ? 'playground_' : '') . 'taxes')->row();
                if (!$dbTax) {
                    $this->db->insert(($playground ? 'playground_' : '') . 'taxes', ['name' => $displayName, 'taxrate' => $percentage, ]);
                    $data[$localKey] = $this->db->insert_id();
                } else {
                    $data[$localKey] = $dbTax->id;
                }
            } else if (isset($data[$key]) && !$data[$key]) {
                $data[$localKey] = 0;
                $data[$key] = null;
            }
        }
        return $data;
    }
}
