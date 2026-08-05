<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Custom_fields_model extends App_Model {
    private $pdf_fields = ['estimate', 'invoice', 'credit_note', 'items'];
    private $client_portal_fields = ['customers', 'estimate', 'invoice', 'proposal', 'contracts', 'tasks', 'projects', 'contacts', 'tickets', 'company', 'credit_note'];
    private $client_editable_fields = ['customers', 'contacts', 'tasks'];

    public function __construct() {
        parent::__construct();
    }

    /**
     * @param  integer (optional)
     * @return object
     * Get single custom field
     */
    public function get($id = false, $playground = false) {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'customfields')->row();
        }
        return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'customfields')->result_array();
    }

    /**
     * Add new custom field
     * @param mixed $data All $_POST data
     * @return  boolean
     */
    public function add($data, $playground = false) {
        if (isset($data['disabled'])) {
            $data['active'] = 0;
            unset($data['disabled']);
        } else {
            $data['active'] = 1;
        }
        if (isset($data['show_on_pdf'])) {
            if (in_array($data['fieldto'], $this->pdf_fields)) {
                $data['show_on_pdf'] = 1;
            } else {
                $data['show_on_pdf'] = 0;
            }
        } else {
            $data['show_on_pdf'] = 0;
        }
        if (isset($data['required'])) {
            $data['required'] = 1;
        } else {
            $data['required'] = 0;
        }
        if (isset($data['disalow_client_to_edit'])) {
            $data['disalow_client_to_edit'] = 1;
        } else {
            $data['disalow_client_to_edit'] = 0;
        }
        if (isset($data['show_on_table'])) {
            $data['show_on_table'] = 1;
        } else {
            $data['show_on_table'] = 0;
        }
        if (isset($data['only_admin'])) {
            $data['only_admin'] = 1;
        } else {
            $data['only_admin'] = 0;
        }
        if (isset($data['show_on_client_portal'])) {
            if (in_array($data['fieldto'], $this->client_portal_fields)) {
                $data['show_on_client_portal'] = 1;
            } else {
                $data['show_on_client_portal'] = 0;
            }
        } else {
            $data['show_on_client_portal'] = 0;
        }
        if ($data['field_order'] == '') {
            $data['field_order'] = 0;
        }
        $data['slug'] = slug_it($data['fieldto'] . '_' . $data['name'], ['separator' => '_', ]);
        $slugs_total = total_rows(db_prefix() . ($playground ? 'playground_' : '') . 'customfields', ['slug' => $data['slug']]);
        if ($slugs_total > 0) {
            $data['slug'].= '_' . ($slugs_total + 1);
        }
        if ($data['fieldto'] == 'company') {
            $data['show_on_pdf'] = 1;
            $data['show_on_client_portal'] = 1;
            $data['show_on_table'] = 1;
            $data['only_admin'] = 0;
            $data['disalow_client_to_edit'] = 0;
        } else if ($data['fieldto'] == 'items') {
            $data['show_on_pdf'] = 1;
            $data['show_on_client_portal'] = 1;
            $data['show_on_table'] = 1;
            $data['only_admin'] = 0;
            $data['disalow_client_to_edit'] = 0;
        }
        $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'customfields', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Custom Field Added [' . $data['name'] . ']');
            return $insert_id;
        }
        return false;
    }

    /**
     * Update custom field
     * @param mixed $data All $_POST data
     * @return  boolean
     */
    public function update($data, $id, $playground = false) {
        $original_field = $this->get($id, $playground);
        if (isset($data['disabled'])) {
            $data['active'] = 0;
            unset($data['disabled']);
        } else {
            $data['active'] = 1;
        }
        if (isset($data['disalow_client_to_edit'])) {
            $data['disalow_client_to_edit'] = 1;
        } else {
            $data['disalow_client_to_edit'] = 0;
        }
        if (isset($data['only_admin'])) {
            $data['only_admin'] = 1;
        } else {
            $data['only_admin'] = 0;
        }
        if (isset($data['required'])) {
            $data['required'] = 1;
        } else {
            $data['required'] = 0;
        }
        if (isset($data['show_on_pdf'])) {
            if (in_array($data['fieldto'], $this->pdf_fields)) {
                $data['show_on_pdf'] = 1;
            } else {
                $data['show_on_pdf'] = 0;
            }
        } else {
            $data['show_on_pdf'] = 0;
        }
        if ($data['field_order'] == '') {
            $data['field_order'] = 0;
        }
        if (isset($data['show_on_client_portal'])) {
            if (in_array($data['fieldto'], $this->client_portal_fields)) {
                $data['show_on_client_portal'] = 1;
            } else {
                $data['show_on_client_portal'] = 0;
            }
        } else {
            $data['show_on_client_portal'] = 0;
        }
        if (isset($data['show_on_table'])) {
            $data['show_on_table'] = 1;
        } else {
            $data['show_on_table'] = 0;
        }
        if (!isset($data['display_inline'])) {
            $data['display_inline'] = 0;
        }
        if (!isset($data['show_on_ticket_form'])) {
            $data['show_on_ticket_form'] = 0;
        }
        if ($data['fieldto'] == 'company') {
            $data['show_on_pdf'] = 1;
            $data['show_on_client_portal'] = 1;
            $data['show_on_table'] = 1;
            $data['only_admin'] = 0;
            $data['disalow_client_to_edit'] = 0;
        } else if ($data['fieldto'] == 'items') {
            $data['show_on_pdf'] = 1;
            $data['show_on_client_portal'] = 1;
            $data['show_on_table'] = 1;
            $data['only_admin'] = 0;
            $data['disalow_client_to_edit'] = 0;
        }
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'customfields', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Custom Field Updated [' . $data['name'] . ']');
            if ($data['type'] == 'checkbox' || $data['type'] == 'select' || $data['type'] == 'multiselect') {
                if (trim($data['options']) != trim($original_field->options)) {
                    $options_now = explode(',', $data['options']);
                    foreach ($options_now as $key => $val) {
                        $options_now[$key] = trim($val);
                    }
                    $options_before = explode(',', $original_field->options);
                    foreach ($options_before as $key => $val) {
                        $options_before[$key] = trim($val);
                    }
                    $removed_options_in_use = [];
                    foreach ($options_before as $option) {
                        if (!in_array($option, $options_now) && total_rows(db_prefix() . ($playground ? 'playground_' : '') . 'customfieldsvalues', ['fieldid' => $id, 'value' => $option, ])) {
                            array_push($removed_options_in_use, $option);
                        }
                    }
                    if (count($removed_options_in_use) > 0) {
                        $this->db->where('id', $id);
                        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'customfields', ['options' => implode(',', $options_now) . ',' . implode(',', $removed_options_in_use), ]);
                        return ['cant_change_option_custom_field' => true, ];
                    }
                }
            }
            return true;
        }
        return false;
    }

    /**
     * @param  integer
     * @return boolean
     * Delete Custom fields
     * All values for this custom field will be deleted from database
     */
    public function delete($id, $playground = false) {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'customfields');
        if ($this->db->affected_rows() > 0) {
            // Delete the values
            $this->db->where('fieldid', $id);
            $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'customfieldsvalues');
            log_activity('Custom Field Deleted [' . $id . ']');
            return true;
        }
        return false;
    }

    public function get_custom_data($data, $custom_field_type, $id = '', $is_invoice_item = false, $playground = false)
    {
        $this->db->where('active', 1);
        $this->db->where('fieldto', $custom_field_type);

        $this->db->order_by('field_order', 'asc');
        $fields       = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'customfields')->result_array();
        $customfields = [];
        if ('' === $id) {
            foreach ($data as $data_key => $value) {
                $data[$data_key]['customfields'] = [];
                $value_id                        = $value['id'] ?? '';
                if ('customers' == $custom_field_type) {
                    $value_id = $value['userid'];
                }
                if ('tickets' == $custom_field_type) {
                    $value_id = $value['ticketid'];
                }
                if ('staff' == $custom_field_type) {
                    $value_id = $value['staffid'];
                }
                foreach ($fields as $key => $field) {
                    $customfields[$key]        = new StdClass();
                    $customfields[$key]->label = $field['name'];
                    if ('items' == $custom_field_type && !$is_invoice_item) {
                        $custom_field_type = 'items_pr';
                        $value_id          = $value['itemid'] ?? $value['id'];
                    }
                    $customfields[$key]->value =  $this->custom_fields_model->get_custom_field_value($value_id, $field['id'], $custom_field_type, false, $playground);
                }
                $data[$data_key]['customfields'] = $customfields;
            }
        }
        if ('' !== $id && is_numeric($id)) {
            $data->customfields = new StdClass();
            foreach ($fields as $key => $field) {
                $customfields[$key]        = new StdClass();
                $customfields[$key]->label = $field['name'];
                if ('items' == $custom_field_type && !$is_invoice_item) {
                    $custom_field_type = 'items_pr';
                }
                $customfields[$key]->value =  $this->custom_fields_model->get_custom_field_value($id, $field['id'], $custom_field_type, false, $playground);
            }
            $data->customfields = $customfields;
        }

        return $data;
    }

    /**
     * Change custom field status  / active / inactive
     * @param  mixed $id     customfield id
     * @param  integer $status active or inactive
     */
    public function change_custom_field_status($id, $status, $playground = false) {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'customfields', ['active' => $status, ]);
        log_activity('Custom Field Status Changed [FieldID: ' . $id . ' - Active: ' . $status . ']');
    }

    public function get_relation_data_api($type, $search = '', $playground = false)
    {
        $q  = '';
        if ('' != $search) {
            $q = $search;
            $q = trim(urldecode($q));
        }
        $this->load->model('clients_model');
        $this->load->model('misc_model');
        $this->load->model('payment_modes_model');
        $data = [];
        if ('customer' == $type || 'customers' == $type) {
            $where_clients = db_prefix() . ($playground ? 'playground_' : '') . 'clients.active=1';

            if ($q) {
                $where_clients .= ' AND (';
                $where_clients .= 'company LIKE "%'.$q.'%" OR CONCAT(firstname, " ", lastname) LIKE "%'.$q.'%" OR email LIKE "%'.$q.'%"';

                $fields = $this->get_custom_fields('customers', [], false, $playground);
                foreach ($fields as $key => $value) {
                    $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'customfieldsvalues as ctable_'.$key.'', db_prefix() . ($playground ? 'playground_' : '') . 'clients.userid = ctable_'.$key.'.relid and ctable_'.$key.'.fieldto="customers" AND ctable_'.$key.'.fieldid='.$value['id'], 'LEFT');
                    $where_clients .= ' OR ctable_'.$key.'.value LIKE "%'.$q.'%"';
                }
                $where_clients .= ')';
            }
            $data = $this->clients_model->get('', $where_clients);
        } else if ('contacts' == $type) {
            $where_clients = db_prefix() . ($playground ? 'playground_' : '') . 'clients.active=1';
            if ($q) {
                $where_clients .= ' AND (';
                $where_clients .= ' company LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\' OR CONCAT(firstname, " ", lastname) LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\' OR email LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'';

                $fields = $this->get_custom_fields('contacts', [], false, $playground);
                foreach ($fields as $key => $value) {
                    $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'customfieldsvalues as ctable_'.$key.'', db_prefix() . ($playground ? 'playground_' : '') . 'contacts.id = ctable_'.$key.'.relid and ctable_'.$key.'.fieldto="contacts" AND ctable_'.$key.'.fieldid='.$value['id'], 'LEFT');
                    $where_clients .= ' OR ctable_'.$key.'.value LIKE "%'.$q.'%"';
                }

                $where_clients .= ') AND '.db_prefix() . ($playground ? 'playground_' : '') . 'clients.active = 1';
            }

            $this->db->select('contacts.id AS id,clients.*,contacts.*');
            $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'clients', ''.db_prefix() . ($playground ? 'playground_' : '') . 'contacts.userid = '.db_prefix() . ($playground ? 'playground_' : '') . 'clients.userid', 'left');

            $data = $this->clients_model->get_contacts('', $where_clients, [], $playground);
            // echo $this->db->last_query();
        } else if ('ticket' == $type) {
            $search = $this->_search_tickets($q, 0, true, $playground);
            $data   = $search['result'];
        } else if ('lead' == $type || 'leads' == $type) {
            $search = $this->_search_leads($q, 0, ['junk' => 0,], true, $playground);
            $data = $search['result'];
        } else if ('invoice' == $type || 'invoices' == $type) {
            $search = $this->_search_invoices($q, 0, [], true, $playground);
            $data   = $search['result'];
        } else if ('invoice_items' == $type) {
            $fields = $this->get_custom_fields('items', [], false, $playground);
            $this->db->select('rate, items.id, description as name, long_description as subtext');
            $this->db->like('description', $q);
            $this->db->or_like('long_description', $q);
            foreach ($fields as $key => $value) {
                $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'customfieldsvalues as ctable_'.$key.'', db_prefix() . ($playground ? 'playground_' : '') . 'items.id = ctable_'.$key.'.relid and ctable_'.$key.'.fieldto="items_pr" AND ctable_'.$key.'.fieldid='.$value['id'], 'LEFT');
                $this->db->or_like('ctable_'.$key.'.value', $q);
            }
            $items = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'items')->result_array();

            foreach ($items as $key => $item) {
                $items[$key]['subtext'] = strip_tags(mb_substr($item['subtext'], 0, 200)).'...';
                $items[$key]['name']    = '('.app_format_number($item['rate']).') '.$item['name'];
            }
            $data = $items;
        } else if ('project' == $type) {
            $where_projects = '';
            if ($this->input->post('customer_id')) {
                $where_projects .= '(clientid='.$this->input->post('customer_id').' or clientid in (select id from tblleads where client_id='.$this->input->post('customer_id').') )';
            }
            if ($this->input->post('rel_type')) {
                $where_projects .= ' and rel_type="'.$this->input->post('rel_type').'" ';
            }
            $search = $this->misc_model->search_projects($q, 0, $where_projects, $this->input->post('rel_type'), $playground);
            $data   = $search['result'];
        } else if ('staff' == $type) {
            $search = $this->misc_model->search_staff($q, 0, $playground);
            $data   = $search['result'];
        } else if ('tasks' == $type) {
            $search = $this->misc_model->search_tasks($q, 0, $playground);
            $data   = $search['result'];
        } else if ('payments' == $type) {
            $search = $this->payment_modes_model->search($q, 0, $playground);
            $data   = $search['result'];
        } else if ('proposals' == $type) {
            $search = $this->misc_model->search_proposals($q, 0, $playground);
            $data   = $search['result'];
        } else if ('estimates' == $type) {
            $search = $this->misc_model->search_estimates($q, 0, $playground);
            $data   = $search['result'];
        } else if ('expenses' == $type) {
            $search = $this->misc_model->search_expenses($q, 0, $playground);
            $data   = $search['result'];
        } else if ('creditnotes' == $type) {
            $search = $this->misc_model->search_credit_notes($q, 0, $playground);
            $data   = $search['result'];
        } else if ('milestones' == $type) {
            $where_milestones = '';
            if ($q) {
                $where_milestones .= '(name LIKE "%'.$q.'%" OR id LIKE "%'.$q.'%")';
            }
            $data = $this->misc_model->get_milestones('', $where_milestones, $playground);
        }

        return $data;
    }
    
    /**
    * Check for custom fields, update on $_POST
    * @param  mixed $rel_id        the main ID from the table
    * @param  array $custom_fields all custom fields with id and values
    * @return boolean
    */
    public function handle_custom_fields_post($rel_id, $custom_fields, $is_cf_items = false, $playground = false)
    {
        $affectedRows = 0;

        foreach ($custom_fields as $key => $fields) {
            foreach ($fields as $field_id => $field_value) {
                $this->db->where('relid', $rel_id);
                $this->db->where('fieldid', $field_id);
                $this->db->where('fieldto', ($is_cf_items ? 'items_pr' : $key));
                $row = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'customfieldsvalues')->row();
                if (!is_array($field_value)) {
                    $field_value = trim($field_value);
                }
                // Make necessary checkings for fields
                if (!defined('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST')) {
                    $this->db->where('id', $field_id);
                    $field_checker = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'customfields')->row();
                    if ($field_checker->type == 'date_picker') {
                        $field_value = to_sql_date($field_value);
                    } else if ($field_checker->type == 'date_picker_time') {
                        $field_value = to_sql_date($field_value, true);
                    } else if ($field_checker->type == 'textarea') {
                        $field_value = nl2br($field_value);
                    } else if ($field_checker->type == 'checkbox' || $field_checker->type == 'multiselect') {
                        if ($field_checker->disalow_client_to_edit == 1 && is_client_logged_in()) {
                            continue;
                        }
                        if (is_array($field_value)) {
                            $v = 0;
                            foreach ($field_value as $chk) {
                                if ($chk == 'cfk_hidden') {
                                    unset($field_value[$v]);
                                }
                                $v++;
                            }
                            $field_value = implode(', ', $field_value);
                        }
                    }
                }
                if ($row) {
                    $this->db->where('id', $row->id);
                    $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'customfieldsvalues', [
                        'value' => $field_value,
                    ]);
                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                    }
                } else {
                    if ($field_value != '') {
                        $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'customfieldsvalues', [
                            'relid'   => $rel_id,
                            'fieldid' => $field_id,
                            'fieldto' => $is_cf_items ? 'items_pr' : $key,
                            'value'   => $field_value,
                        ]);
                        $insert_id = $this->db->insert_id();
                        if ($insert_id) {
                            $affectedRows++;
                        }
                    }
                }
            }
        }

        if ($affectedRows > 0) {
            return true;
        }
        return false;
    }

    /**
    * Get custom fields
    * @param  string  $field_to
    * @param  array   $where
    * @param  boolean $exclude_only_admin
    * @return array
    */
    public function get_custom_fields($field_to, $where = [], $exclude_only_admin = false, $playground = false)
    {
        $is_admin = is_admin();

        $this->db->where('fieldto', $field_to);
        if ((is_array($where) && count($where) > 0) || (!is_array($where) && $where != '')) {
            $this->db->where($where);
        }
        if (!$is_admin || $exclude_only_admin == true) {
            $this->db->where('only_admin', 0);
        }
        $this->db->where('active', 1);
        $this->db->order_by('field_order', 'asc');
        $results = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'customfields')->result_array();
        foreach ($results as $key => $result) {
            $results[$key]['name'] = _maybe_translate_custom_field_name(e($result['name']), $result['slug']);
        }

        return $results;
    }

    /**
    * Get custom field value
    * @param  mixed $rel_id              the main ID from the table, e.q. the customer id, invoice id
    * @param  mixed $field_id_or_slug    field id, the custom field ID or custom field slug
    * @param  string $field_to           belongs to e.q leads, customers, staff
    * @param  string $format             format date values
    * @return string
    */
    public function get_custom_field_value($rel_id, $field_id_or_slug, $field_to, $format = true, $playground = false)
    {
        $this->db->select(db_prefix() . ($playground ? 'playground_' : '') . 'customfieldsvalues.value,' . db_prefix() . ($playground ? 'playground_' : '') . 'customfields.type');
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'customfields', db_prefix() . ($playground ? 'playground_' : '') . 'customfields.id=' . db_prefix() . ($playground ? 'playground_' : '') . 'customfieldsvalues.fieldid');
        $this->db->where(db_prefix() . ($playground ? 'playground_' : '') . 'customfieldsvalues.relid', $rel_id);
        if (is_numeric($field_id_or_slug)) {
            $this->db->where(db_prefix() . ($playground ? 'playground_' : '') . 'customfieldsvalues.fieldid', $field_id_or_slug);
        } else {
            $this->db->where(db_prefix() . ($playground ? 'playground_' : '') . 'customfields.slug', $field_id_or_slug);
        }
        $this->db->where(db_prefix() . ($playground ? 'playground_' : '') . 'customfieldsvalues.fieldto', $field_to);
        $row = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'customfieldsvalues')->row();

        $result = '';
        if ($row) {
            $result = $row->value;
            if ($format == true) {
                if ($row->type == 'date_picker') {
                    $result = _d($result);
                } elseif ($row->type == 'date_picker_time') {
                    $result = _dt($result);
                }
            }
        }

        return $result;
    }

    /**
     * Return field where Shown on PDF is allowed
     * @return array
     */
    public function get_pdf_allowed_fields() {
        return $this->pdf_fields;
    }

    /**
     * Return fields where Show on customer portal is allowed
     * @return array
     */
    public function get_client_portal_allowed_fields() {
        return $this->client_portal_fields;
    }

    /**
     * Return fields where are editable in customers area
     * @return array
     */
    public function get_client_editable_fields() {
        return $this->client_editable_fields;
    }
}
