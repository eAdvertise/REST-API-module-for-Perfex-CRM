<?php

use app\services\utilities\Arr;

defined('BASEPATH') or exit('No direct script access allowed');

class Invoice_items_model extends App_Model {
    public function __construct() {
        parent::__construct();
    }

    /**
     * Copy invoice item
     * @param array $data Invoice item data
     * @return boolean
     */
    public function copy($_data, $playground = false) {
        $this->load->model('custom_fields_model');
        $custom_fields_items = $this->custom_fields_model->get_custom_fields('items', [], false, $playground);
        $data = ['description' => $_data['description'] . ' - Copy', 'rate' => $_data['rate'], 'tax' => $_data['taxid'], 'tax2' => $_data['taxid_2'], 'group_id' => $_data['group_id'], 'unit' => $_data['unit'], 'long_description' => $_data['long_description'], ];
        foreach ($_data as $column => $value) {
            if (strpos($column, 'rate_currency_') !== false) {
                $data[$column] = $value;
            }
        }
        $columns = $this->db->list_fields(db_prefix() . ($playground ? 'playground_' : '') . 'items');
        $this->load->dbforge();
        foreach ($data as $column) {
            if (!in_array($column, $columns) && strpos($column, 'rate_currency_') !== false) {
                $field = [$column => ['type' => 'decimal(15,' . get_decimal_places() . ')', 'null' => true, ], ];
                $this->dbforge->add_column(($playground ? 'playground_' : '') . 'items', $field);
            }
        }
        foreach ($custom_fields_items as $cf) {
            $data['custom_fields']['items'][$cf['id']] = $this->custom_fields_model->get_custom_field_value($_data['itemid'], $cf['id'], 'items_pr', false, $playground);
            if (!defined('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST')) {
                define('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST', true);
            }
        }
        $insert_id = $this->add($data);
        if ($insert_id) {
            hooks()->do_action('item_coppied', $insert_id);
            log_activity('Copied Item  [ID:' . $_data['itemid'] . ', ' . $data['description'] . ']');
            return $insert_id;
        }
        return false;
    }

    /**
     * Get invoice item by ID
     * @param  mixed $id
     * @return mixed - array if not passed id, object if id passed
     */
    public function get($id = '', $playground = false) {
        $columns = $this->db->list_fields(db_prefix() . ($playground ? 'playground_' : '') . 'items');
        $rateCurrencyColumns = '';
        foreach ($columns as $column) {
            if (strpos($column, 'rate_currency_') !== false) {
                $rateCurrencyColumns.= $column . ',';
            }
        }
        $this->db->select($rateCurrencyColumns . '' . db_prefix() . ($playground ? 'playground_' : '') . 'items.id as itemid,rate,
            t1.taxrate as taxrate,t1.id as taxid,t1.name as taxname,
            t2.taxrate as taxrate_2,t2.id as taxid_2,t2.name as taxname_2,
            description,long_description,group_id,' . db_prefix() . ($playground ? 'playground_' : '') . 'items_groups.name as group_name,unit');
        $this->db->from(db_prefix() . ($playground ? 'playground_' : '') . 'items');
        $this->db->join('' . db_prefix() . ($playground ? 'playground_' : '') . 'taxes t1', 't1.id = ' . db_prefix() . ($playground ? 'playground_' : '') . 'items.tax', 'left');
        $this->db->join('' . db_prefix() . ($playground ? 'playground_' : '') . 'taxes t2', 't2.id = ' . db_prefix() . ($playground ? 'playground_' : '') . 'items.tax2', 'left');
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'items_groups', '' . db_prefix() . ($playground ? 'playground_' : '') . 'items_groups.id = ' . db_prefix() . ($playground ? 'playground_' : '') . 'items.group_id', 'left');
        $this->db->order_by('description', 'asc');
        if (is_numeric($id)) {
            $this->db->where(db_prefix() . ($playground ? 'playground_' : '') . 'items.id', $id);
            return $this->db->get()->row();
        }
        return $this->db->get()->result_array();
    }

    public function get_grouped($playground = false) {
        $items = [];
        $this->db->order_by('name', 'asc');
        $groups = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'items_groups')->result_array();
        array_unshift($groups, ['id' => 0, 'name' => '', ]);
        foreach ($groups as $group) {
            $this->db->select('*,' . db_prefix() . ($playground ? 'playground_' : '') . 'items_groups.name as group_name,' . db_prefix() . ($playground ? 'playground_' : '') . 'items.id as id');
            $this->db->where('group_id', $group['id']);
            $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'items_groups', '' . db_prefix() . ($playground ? 'playground_' : '') . 'items_groups.id = ' . db_prefix() . ($playground ? 'playground_' : '') . 'items.group_id', 'left');
            $this->db->order_by('description', 'asc');
            $_items = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'items')->result_array();
            if (count($_items) > 0) {
                $items[$group['id']] = [];
                foreach ($_items as $i) {
                    array_push($items[$group['id']], $i);
                }
            }
        }
        return $items;
    }

    /**
     * Add new invoice item
     * @param array $data Invoice item data
     * @return boolean
     */
    public function add($data, $playground = false) {
        unset($data['itemid']);
        if (isset($data['tax']) && $data['tax'] == '') {
            unset($data['tax']);
        }
        if (isset($data['tax2']) && $data['tax2'] == '') {
            unset($data['tax2']);
        }
        if (isset($data['group_id']) && $data['group_id'] == '') {
            $data['group_id'] = 0;
        }
        $columns = $this->db->list_fields(db_prefix() . ($playground ? 'playground_' : '') . 'items');
        $this->load->dbforge();
        foreach ($data as $column => $itemData) {
            if (!in_array($column, $columns) && strpos($column, 'rate_currency_') !== false) {
                $field = [$column => ['type' => 'decimal(15,' . get_decimal_places() . ')', 'null' => true, ], ];
                $this->dbforge->add_column('items', $field);
            }
        }
        $data = hooks()->apply_filters('before_item_created', $data);
        $custom_fields = Arr::pull($data, 'custom_fields') ?? [];
        $this->db->insert(($playground ? 'playground_' : '') . 'items', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            $this->load->model('custom_fields_model');
            $this->custom_fields_model->handle_custom_fields_post($insert_id, $custom_fields, true, $playground);
            hooks()->do_action('item_created', $insert_id);
            log_activity('New Invoice Item Added [ID:' . $insert_id . ', ' . $data['description'] . ']');
            return $insert_id;
        }
        return false;
    }

    /**
     * Update invoiec item
     * @param  array $data Invoice data to update
     * @return boolean
     */
    public function edit($data, $playground = false) {
        $itemid = $data['itemid'];
        unset($data['itemid']);
        if (isset($data['group_id']) && $data['group_id'] == '') {
            $data['group_id'] = 0;
        }
        if (isset($data['tax']) && $data['tax'] == '') {
            $data['tax'] = null;
        }
        if (isset($data['tax2']) && $data['tax2'] == '') {
            $data['tax2'] = null;
        }
        $columns = $this->db->list_fields(db_prefix() . ($playground ? 'playground_' : '') . 'items');
        $this->load->dbforge();
        foreach ($data as $column => $itemData) {
            if (!in_array($column, $columns) && strpos($column, 'rate_currency_') !== false) {
                $field = [$column => ['type' => 'decimal(15,' . get_decimal_places() . ')', 'null' => true, ], ];
                $this->dbforge->add_column('items', $field);
            }
        }
        $updated = false;
        $data = hooks()->apply_filters('before_update_item', $data, $itemid);
        $custom_fields = Arr::pull($data, 'custom_fields') ?? [];
        $this->db->where('id', $itemid);
        $this->db->update(($playground ? 'playground_' : '') . 'items', $data);
        if ($this->db->affected_rows() > 0) {
            $updated = true;
        }
        $this->load->model('custom_fields_model');
        if ($this->custom_fields_model->handle_custom_fields_post($itemid, $custom_fields, true, $playground)) {
            $updated = true;
        }
        do_action_deprecated('item_updated', [$itemid], '2.9.4', 'after_item_updated');
        hooks()->do_action('after_item_updated', ['id' => $itemid, 'data' => $data, 'custom_fields' => $custom_fields, 'updated' => & $updated, ]);
        if ($updated) {
            log_activity('Invoice Item Updated [ID: ' . $itemid . ', ' . $data['description'] . ']');
        }
        return $updated;
    }

    public function search($q, $playground = false) {
        $this->db->select('rate, id, description as name, long_description as subtext');
        $this->db->like('description', $q);
        $this->db->or_like('long_description', $q);
        $items = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'items')->result_array();
        foreach ($items as $key => $item) {
            $items[$key]['subtext'] = strip_tags(mb_substr($item['subtext'], 0, 200)) . '...';
            $items[$key]['name'] = '(' . app_format_number($item['rate']) . ') ' . $item['name'];
        }
        return $items;
    }

    /**
     * Delete invoice item
     * @param  mixed $id
     * @return boolean
     */
    public function delete($id, $playground = false) {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'items');
        if ($this->db->affected_rows() > 0) {
            $this->db->where('relid', $id);
            $this->db->where('fieldto', 'items_pr');
            $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'customfieldsvalues');
            log_activity('Invoice Item Deleted [ID: ' . $id . ']');
            hooks()->do_action('item_deleted', $id);
            return true;
        }
        return false;
    }

    public function get_groups($playground = false) {
        $this->db->order_by('name', 'asc');
        return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'items_groups')->result_array();
    }

    public function add_group($data, $playground = false) {
        $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'items_groups', $data);
        log_activity('Items Group Created [Name: ' . $data['name'] . ']');
        return $this->db->insert_id();
    }

    public function edit_group($data, $id, $playground = false) {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'items_groups', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Items Group Updated [Name: ' . $data['name'] . ']');
            return true;
        }
        return false;
    }

    public function delete_group($id, $playground = false) {
        $this->db->where('id', $id);
        $group = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'items_groups')->row();
        if ($group) {
            $this->db->where('group_id', $id);
            $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'items', ['group_id' => 0, ]);
            $this->db->where('id', $id);
            $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'items_groups');
            log_activity('Item Group Deleted [Name: ' . $group->name . ']');
            return true;
        }
        return false;
    }    
    
    public function get_items_by_type($type, $id, $playground = false)
    {
        $this->db->select();
        $this->db->from(db_prefix() . ($playground ? 'playground_' : '') . 'itemable');
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', $type);
        $this->db->order_by('item_order', 'asc');

        return $this->db->get()->result_array();
    }
    
    /**
    * Add new item do database, used for proposals,estimates,credit notes,invoices
    * This is repetitive action, that's why this function exists
    * @param array $item     item from $_POST
    * @param mixed $rel_id   relation id eq. invoice id
    * @param string $rel_type relation type eq invoice
    */
    public function add_new_sales_item_post($item, $rel_id, $rel_type, $playground = false)
    {
        $custom_fields = false;

        if (isset($item['custom_fields'])) {
            $custom_fields = $item['custom_fields'];
        }

        $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'itemable', [
            'description'      => $item['description'],
            'long_description' => nl2br($item['long_description']),
            'qty'              => $item['qty'],
            'rate'             => number_format($item['rate'], get_decimal_places(), '.', ''),
            'rel_id'           => $rel_id,
            'rel_type'         => $rel_type,
            'item_order'       => $item['order'],
            'unit'             => $item['unit'],
        ]);

        $id = $this->db->insert_id();

        if ($custom_fields !== false) {
            $this->load->model('custom_fields_model');
            $this->custom_fields_model->handle_custom_fields_post($id, $custom_fields);
        }

        return $id;
    }
    
    /**
    * Function used for sales eq. invoice, estimate, proposal, credit note
    * @param  mixed $item_id   item id
    * @param  array $post_item $item from $_POST
    * @param  mixed $rel_id    rel_id
    * @param  string $rel_type  where this item tax is related
    */
    public function _maybe_insert_post_item_tax($item_id, $post_item, $rel_id, $rel_type, $playground = false)
    {
        $affectedRows = 0;
        if (isset($post_item['taxname']) && is_array($post_item['taxname'])) {
            foreach ($post_item['taxname'] as $taxname) {
                if ($taxname != '') {
                    $tax_array = explode('|', $taxname);
                    if (isset($tax_array[0]) && isset($tax_array[1])) {
                        $tax_name = trim($tax_array[0]);
                        $tax_rate = trim($tax_array[1]);
                        if (total_rows(db_prefix() . ($playground ? 'playground_' : '') . 'item_tax', [
                            'itemid' => $item_id,
                            'taxrate' => $tax_rate,
                            'taxname' => $tax_name,
                            'rel_id' => $rel_id,
                            'rel_type' => $rel_type,
                        ]) == 0) {
                            $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'item_tax', [
                                'itemid'   => $item_id,
                                'taxrate'  => $tax_rate,
                                'taxname'  => $tax_name,
                                'rel_id'   => $rel_id,
                                'rel_type' => $rel_type,
                            ]);
                            $affectedRows++;
                        }
                    }
                }
            }
        }

        return $affectedRows > 0 ? true : false;
    }
}
