<?php

use app\services\AbstractKanban;

defined('BASEPATH') or exit('No direct script access allowed');

class Delivery_notes_model extends App_Model
{
    private $statuses;

    private $shipping_fields = ['shipping_street', 'shipping_city', 'shipping_city', 'shipping_state', 'shipping_zip', 'shipping_country'];

    public function __construct()
    {
        parent::__construct();

        $this->statuses = hooks()->apply_filters('before_set_delivery_note_statuses', [
            1,
            2,
            3,
            5,
            6,
            4,
			7,
        ]);
    }

    /**
     * Get unique sale agent for delivery_notes / Used for filters
     * @return array
     */
    public function get_sale_agents()
    {
        return $this->db->query("SELECT DISTINCT(sale_agent) as sale_agent, CONCAT(firstname, ' ', lastname) as full_name FROM " . db_prefix() . 'delivery_notes JOIN ' . db_prefix() . 'staff on ' . db_prefix() . 'staff.staffid=' . db_prefix() . 'delivery_notes.sale_agent WHERE sale_agent != 0')->result_array();
    }

    /**
     * Unlink invoice from items
     */
    public function unlink_invoice($invoiceid)
    {
        if (empty($invoiceid)) return false;


        $data = ['invoiceid' => NULL];

        $new_status = (int)get_option('delivery_notes_status_on_invoice_delete');
        if (!empty($new_status))
            $data['status'] = $new_status;

        $this->db->where('invoiceid', $invoiceid);
        $this->db->update(db_prefix() . 'delivery_notes', $data);
    }

    /**
     * Get delivery_note/s
     * @param mixed $id delivery_note id
     * @param array $where perform where
     * @return mixed
     */
    public function get($id = '', $where = [])
    {
        $this->db->select('*,' . db_prefix() . 'currencies.id as currencyid, ' . db_prefix() . 'delivery_notes.id as id, ' . db_prefix() . 'currencies.name as currency_name');
        $this->db->from(db_prefix() . 'delivery_notes');
        $this->db->join(db_prefix() . 'currencies', db_prefix() . 'currencies.id = ' . db_prefix() . 'delivery_notes.currency', 'left');
        $this->db->where($where);
        if (is_numeric($id)) {
            $this->db->where(db_prefix() . 'delivery_notes.id', $id);
            $delivery_note = $this->db->get()->row();
            if ($delivery_note) {
                $delivery_note->attachments                           = (array) $this->get_attachments($id);
                $delivery_note->visible_attachments_to_customer_found = false;

                if (!empty($delivery_note->attachments)) {
                foreach ($delivery_note->attachments as $attachment) {
                    if ($attachment['visible_to_customer'] == 1) {
                        $delivery_note->visible_attachments_to_customer_found = true;

                        break;
                    }
                }
                }

                $delivery_note->items = get_items_by_type('delivery_note', $id);

                if ($delivery_note->project_id) {
                    $this->load->model('projects_model');
                    $delivery_note->project_data = $this->projects_model->get($delivery_note->project_id);
                }

                $delivery_note->client = $this->clients_model->get($delivery_note->clientid);

                if (!$delivery_note->client) {
                    $delivery_note->client          = new stdClass();
                    $delivery_note->client->company = $delivery_note->deleted_customer_name;
                }

                $this->load->model('email_schedule_model');
                $delivery_note->scheduled_email = $this->email_schedule_model->get($id, 'delivery_note');

                $delivery_note->staff_signatures = $this->get_staff_signatures($id);

                // Add invoice
                $invoice = $this->invoices_model->db->where('delivery_noteid', $id)->from(db_prefix() . 'invoices')->get()->row();
                if ($invoice) {
                    $delivery_note->invoice = $invoice;
                    $delivery_note->invoiceid = $invoice->id;
                }

                // Add estimate
                $estimate = $this->estimates_model->db->where('delivery_noteid', $id)->from(db_prefix() . 'estimates')->get()->row();
                if ($estimate) {
                    $delivery_note->estimate = $estimate;
                }

                if (defined('PURCHASE_ORDER_MODULE_NAME') && $this->db->table_exists(db_prefix() . 'purchase_orders')) {
                    // Add purchase order
                    $purchase_order = $this->purchase_orders_model->db->where('delivery_noteid', $id)->from(db_prefix() . 'purchase_orders')->get()->row();
                    if ($purchase_order) {
                        $delivery_note->purchase_order = $purchase_order;
                    }
                }
            }

            return $delivery_note;
        }
        $this->db->order_by('number,YEAR(date)', 'desc');

        return $this->db->get()->result_array();
    }

    /**
     * Get delivery_note statuses
     * @return array
     */
    public function get_statuses()
    {
        return $this->statuses;
    }

    /**
     * Convert delivery_note to invoice
     * @param mixed $id delivery_note id
     * @return mixed     New invoice ID
     */
    public function convert_to_invoice($id, $client = false, $draft_invoice = false, $invoices_to_merge = [])
    {
        // Recurring invoice date is okey lets convert it to new invoice
        $_delivery_note = $this->get($id);
        if (!empty($_delivery_note->invoiceid)) {
            if (!empty($this->invoices_model->get($_delivery_note->invoiceid)))
                return $_delivery_note->invoiceid;
        }

        $new_invoice_data = [];

        if ($draft_invoice == true) {
            $new_invoice_data['save_as_draft'] = true;
        }

        if (!empty($invoices_to_merge))
            $new_invoice_data['invoices_to_merge'] = $invoices_to_merge;

        $new_invoice_data['clientid']   = $_delivery_note->clientid;
        $new_invoice_data['project_id'] = $_delivery_note->project_id;
        $new_invoice_data['number']     = get_option('next_invoice_number');
        $new_invoice_data['date']       = _d(date('Y-m-d'));
        $new_invoice_data['duedate']    = _d(date('Y-m-d'));
        if (get_option('invoice_due_after') != 0) {
            $new_invoice_data['duedate'] = _d(date('Y-m-d', strtotime('+' . get_option('invoice_due_after') . ' DAY', strtotime(date('Y-m-d')))));
        }
        $new_invoice_data['show_quantity_as'] = $_delivery_note->show_quantity_as;
        $new_invoice_data['currency']         = $_delivery_note->currency;
        $new_invoice_data['subtotal']         = $_delivery_note->subtotal;
        $new_invoice_data['total']            = $_delivery_note->total;
        $new_invoice_data['adjustment']       = $_delivery_note->adjustment;
        $new_invoice_data['discount_percent'] = $_delivery_note->discount_percent;
        $new_invoice_data['discount_total']   = $_delivery_note->discount_total;
        $new_invoice_data['discount_type']    = $_delivery_note->discount_type;
        $new_invoice_data['sale_agent']       = $_delivery_note->sale_agent;
        // Since version 1.0.6
        $new_invoice_data['billing_street']   = clear_textarea_breaks($_delivery_note->billing_street);
        $new_invoice_data['billing_city']     = $_delivery_note->billing_city;
        $new_invoice_data['billing_state']    = $_delivery_note->billing_state;
        $new_invoice_data['billing_zip']      = $_delivery_note->billing_zip;
        $new_invoice_data['billing_country']  = $_delivery_note->billing_country;
        $new_invoice_data['shipping_street']  = clear_textarea_breaks($_delivery_note->shipping_street);
        $new_invoice_data['shipping_city']    = $_delivery_note->shipping_city;
        $new_invoice_data['shipping_state']   = $_delivery_note->shipping_state;
        $new_invoice_data['shipping_zip']     = $_delivery_note->shipping_zip;
        $new_invoice_data['shipping_country'] = $_delivery_note->shipping_country;

        if ($_delivery_note->include_shipping == 1) {
            $new_invoice_data['include_shipping'] = 1;
        }

        $new_invoice_data['show_shipping_on_invoice'] = $_delivery_note->show_shipping_on_delivery_note;
        $new_invoice_data['terms']                    = get_option('predefined_terms_invoice');
        $new_invoice_data['clientnote']               = get_option('predefined_clientnote_invoice');
        // Set to unpaid status automatically
        $new_invoice_data['status']    = 1;
        $new_invoice_data['adminnote'] = '';

        $this->load->model('payment_modes_model');
        $modes = $this->payment_modes_model->get('', [
            'expenses_only !=' => 1,
        ]);
        $temp_modes = [];
        foreach ($modes as $mode) {
            if ($mode['selected_by_default'] == 0) {
                continue;
            }
            $temp_modes[] = $mode['id'];
        }
        $new_invoice_data['allowed_payment_modes'] = $temp_modes;
        $new_invoice_data['newitems']              = [];
        $custom_fields_items                       = get_custom_fields('items');
        $key                                       = 1;
        foreach ($_delivery_note->items as $item) {
            $new_invoice_data['newitems'][$key]['description']      = $item['description'];
            $new_invoice_data['newitems'][$key]['long_description'] = clear_textarea_breaks($item['long_description']);
            $new_invoice_data['newitems'][$key]['qty']              = $item['qty'];
            $new_invoice_data['newitems'][$key]['unit']             = $item['unit'];
            $new_invoice_data['newitems'][$key]['taxname']          = [];
            $taxes                                                  = get_delivery_note_item_taxes($item['id']);
            foreach ($taxes as $tax) {
                // tax name is in format TAX1|10.00
                array_push($new_invoice_data['newitems'][$key]['taxname'], $tax['taxname']);
            }
            $new_invoice_data['newitems'][$key]['rate']  = $item['rate'];
            $new_invoice_data['newitems'][$key]['order'] = $item['item_order'];
            foreach ($custom_fields_items as $cf) {
                $new_invoice_data['newitems'][$key]['custom_fields']['items'][$cf['id']] = get_custom_field_value($item['id'], $cf['id'], 'items', false);

                if (!defined('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST')) {
                    define('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST', true);
                }
            }
            $key++;
        }

        $new_invoice_data['delivery_noteid'] = $_delivery_note->id;

        $id = $this->invoices_model->add($new_invoice_data);
        if ($id) {
            // Customer accepted the delivery_note and is auto converted to invoice
            if (!is_staff_logged_in()) {
                $this->db->where('rel_type', 'invoice');
                $this->db->where('rel_id', $id);
                $this->db->delete(db_prefix() . 'sales_activity');
                $this->invoices_model->log_invoice_activity($id, 'invoice_activity_auto_converted_from_delivery_note', true, serialize([
                    '<a href="' . admin_url('delivery_notes/list_delivery_notes/' . $_delivery_note->id) . '">' . format_delivery_note_number($_delivery_note->id) . '</a>',
                ]));
            }
            // For all cases update addefrom and sale agent from the invoice
            // May happen staff is not logged in and these values to be 0
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'invoices', [
                'addedfrom'  => $_delivery_note->addedfrom,
                'sale_agent' => $_delivery_note->sale_agent,
            ]);

            // Update delivery_note with the new invoice data and set to status accepted
            $this->db->where('id', $_delivery_note->id);
            $this->db->update(db_prefix() . 'delivery_notes', [
                'invoiced_date' => date('Y-m-d H:i:s'),
                'invoiceid'     => $id,
                'status'        => 4,
            ]);


            $this->transfer_custom_fields('delivery_note', 'invoice', $_delivery_note->id, $id);

            if ($client == false) {
                $this->log_delivery_note_activity($_delivery_note->id, 'delivery_note_activity_converted', false, serialize([
                    '<a href="' . admin_url('invoices/list_invoices/' . $id) . '">' . format_invoice_number($id) . '</a>',
                ]));
            }

            hooks()->do_action('delivery_note_converted_to_invoice', ['invoice_id' => $id, 'delivery_noteid' => $_delivery_note->id]);
        }

        return $id;
    }

    /**
     * Convert delivery_note to invoice
     * @param mixed $id delivery_note id
     * @return mixed     New invoice ID
     */
    public function convert_many_to_invoice($ids, $client = false, $draft_invoice = false)
    {
        $return_list = [];
        $total = 0;
        $subtotal = 0;
        $adjustment = 0;
        $discount_total = 0;
        $discount_percent = 0;

        $items = [];
        $custom_fields_items                       = get_custom_fields('items');
        $key                                       = 1;

        foreach ($ids as $_id_k => $_id) {
            $_delivery_note = $this->get($_id);
            if (!empty($_delivery_note->invoiceid)) {
                if (!empty($this->invoices_model->get($_delivery_note->invoiceid))) {
                    $return_list[] = $_delivery_note->invoiceid;
                    unset($ids[$_id_k]);
                    continue;
                }
            }

            $total += $_delivery_note->total;
            $subtotal += $_delivery_note->subtotal;
            $adjustment += $_delivery_note->adjustment;
            $discount_total += $_delivery_note->discount_total;
            $discount_percent += $_delivery_note->discount_percent;

            foreach ($_delivery_note->items as $item) {
                $items[$key]['description']      = $item['description'];
                $items[$key]['long_description'] = clear_textarea_breaks($item['long_description']);
                $items[$key]['qty']              = $item['qty'];
                $items[$key]['unit']             = $item['unit'];
                $items[$key]['taxname']          = [];
                $taxes                                                  = get_delivery_note_item_taxes($item['id']);
                foreach ($taxes as $tax) {
                    // tax name is in format TAX1|10.00
                    array_push($items[$key]['taxname'], $tax['taxname']);
                }
                $items[$key]['rate']  = $item['rate'];
                $items[$key]['order'] = $item['item_order'];
                foreach ($custom_fields_items as $cf) {
                    $items[$key]['custom_fields']['items'][$cf['id']] = get_custom_field_value($item['id'], $cf['id'], 'items', false);

                    if (!defined('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST')) {
                        define('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST', true);
                    }
                }
                $key++;
            }
        }

        $new_invoice_data = [];
        if ($draft_invoice == true) {
            $new_invoice_data['save_as_draft'] = true;
        }
        $new_invoice_data['clientid']   = $_delivery_note->clientid;
        $new_invoice_data['project_id'] = $_delivery_note->project_id;
        $new_invoice_data['number']     = get_option('next_invoice_number');
        $new_invoice_data['date']       = _d(date('Y-m-d'));
        $new_invoice_data['duedate']    = _d(date('Y-m-d'));
        if (get_option('invoice_due_after') != 0) {
            $new_invoice_data['duedate'] = _d(date('Y-m-d', strtotime('+' . get_option('invoice_due_after') . ' DAY', strtotime(date('Y-m-d')))));
        }
        $new_invoice_data['show_quantity_as'] = $_delivery_note->show_quantity_as;
        $new_invoice_data['currency']         = $_delivery_note->currency;
        $new_invoice_data['subtotal']         = $subtotal;
        $new_invoice_data['total']            = $total;
        $new_invoice_data['adjustment']       = $adjustment;
        $new_invoice_data['discount_percent'] = $discount_percent;
        $new_invoice_data['discount_total']   = $discount_total;
        $new_invoice_data['discount_type']    = $_delivery_note->discount_type;
        $new_invoice_data['sale_agent']       = $_delivery_note->sale_agent;
        // Since version 1.0.6
        $new_invoice_data['billing_street']   = clear_textarea_breaks($_delivery_note->billing_street);
        $new_invoice_data['billing_city']     = $_delivery_note->billing_city;
        $new_invoice_data['billing_state']    = $_delivery_note->billing_state;
        $new_invoice_data['billing_zip']      = $_delivery_note->billing_zip;
        $new_invoice_data['billing_country']  = $_delivery_note->billing_country;
        $new_invoice_data['shipping_street']  = clear_textarea_breaks($_delivery_note->shipping_street);
        $new_invoice_data['shipping_city']    = $_delivery_note->shipping_city;
        $new_invoice_data['shipping_state']   = $_delivery_note->shipping_state;
        $new_invoice_data['shipping_zip']     = $_delivery_note->shipping_zip;
        $new_invoice_data['shipping_country'] = $_delivery_note->shipping_country;

        if ($_delivery_note->include_shipping == 1) {
            $new_invoice_data['include_shipping'] = 1;
        }

        $new_invoice_data['show_shipping_on_invoice'] = $_delivery_note->show_shipping_on_delivery_note;
        $new_invoice_data['terms']                    = get_option('predefined_terms_invoice');
        $new_invoice_data['clientnote']               = get_option('predefined_clientnote_invoice');
        // Set to unpaid status automatically
        $new_invoice_data['status']    = 1;
        $new_invoice_data['adminnote'] = '';

        $this->load->model('payment_modes_model');
        $modes = $this->payment_modes_model->get('', [
            'expenses_only !=' => 1,
        ]);
        $temp_modes = [];
        foreach ($modes as $mode) {
            if ($mode['selected_by_default'] == 0) {
                continue;
            }
            $temp_modes[] = $mode['id'];
        }
        $new_invoice_data['allowed_payment_modes'] = $temp_modes;
        $new_invoice_data['newitems']              = $items;

        $new_invoice_data['delivery_noteid'] = implode(',', $ids);

        $id = $this->invoices_model->add($new_invoice_data);
        if ($id) {
            // Customer accepted the delivery_note and is auto converted to invoice
            if (!is_staff_logged_in()) {
                $this->db->where('rel_type', 'invoice');
                $this->db->where('rel_id', $id);
                $this->db->delete(db_prefix() . 'sales_activity');
                $this->invoices_model->log_invoice_activity($id, 'invoice_activity_auto_converted_from_delivery_note', true, serialize([
                    '<a href="' . admin_url('delivery_notes/list_delivery_notes/' . $_delivery_note->id) . '">' . format_delivery_note_number($_delivery_note->id) . '</a>',
                ]));
            }
            // For all cases update addefrom and sale agent from the invoice
            // May happen staff is not logged in and these values to be 0
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'invoices', [
                'addedfrom'  => $_delivery_note->addedfrom,
                'sale_agent' => $_delivery_note->sale_agent,
            ]);

            foreach ($ids as $_id) {

                // Update delivery_note with the new invoice data and set to status accepted
                $this->db->where_in('id', $_id);
                $this->db->update(db_prefix() . 'delivery_notes', [
                    'invoiced_date' => date('Y-m-d H:i:s'),
                    'invoiceid'     => $id,
                    'status'        => 4,
                ]);

                $this->transfer_custom_fields('delivery_note', 'invoice', $_id, $id);

                if ($client == false) {
                    $this->log_delivery_note_activity($_id, 'delivery_note_activity_converted', false, serialize([
                        '<a href="' . admin_url('invoices/list_invoices/' . $id) . '">' . format_invoice_number($id) . '</a>',
                    ]));
                }

                hooks()->do_action('delivery_note_converted_to_invoice', ['invoice_id' => $id, 'delivery_noteid' => $_id]);
            }
        }

        return $id;
    }

    public function convert_from_invoice($invoiceid, $status)
    {
        $_invoice = $this->invoices_model->get($invoiceid);
        if (!empty($_invoice->delivery_noteid)) {
            $this->db->where_in(db_prefix() . 'delivery_notes.id', explode(',', $_invoice->delivery_noteid));
            if (!empty($this->get()))
                return $_invoice->delivery_noteid;
        }

        $new_delivery_note_data               = [];
        $new_delivery_note_data['clientid']   = $_invoice->clientid;
        $new_delivery_note_data['project_id'] = $_invoice->project_id;
        $new_delivery_note_data['number']     = get_option('next_delivery_note_number');
        $new_delivery_note_data['date']       = _d(date('Y-m-d'));

        $new_delivery_note_data['show_quantity_as'] = $_invoice->show_quantity_as;
        $new_delivery_note_data['currency']         = $_invoice->currency;
        $new_delivery_note_data['subtotal']         = $_invoice->subtotal;
        $new_delivery_note_data['total']            = $_invoice->total;
        $new_delivery_note_data['adminnote']        = $_invoice->adminnote;
        $new_delivery_note_data['adjustment']       = $_invoice->adjustment;
        $new_delivery_note_data['discount_percent'] = $_invoice->discount_percent;
        $new_delivery_note_data['discount_total']   = $_invoice->discount_total;
        $new_delivery_note_data['discount_type']    = $_invoice->discount_type;
        $new_delivery_note_data['terms']            = $_invoice->terms;
        $new_delivery_note_data['sale_agent']       = $_invoice->sale_agent;
        $new_delivery_note_data['reference_no']     = $_invoice->reference_no;
        // Since version 1.0.6
        $new_delivery_note_data['billing_street']   = clear_textarea_breaks($_invoice->billing_street);
        $new_delivery_note_data['billing_city']     = $_invoice->billing_city;
        $new_delivery_note_data['billing_state']    = $_invoice->billing_state;
        $new_delivery_note_data['billing_zip']      = $_invoice->billing_zip;
        $new_delivery_note_data['billing_country']  = $_invoice->billing_country;
        $new_delivery_note_data['shipping_street']  = clear_textarea_breaks($_invoice->shipping_street);
        $new_delivery_note_data['shipping_city']    = $_invoice->shipping_city;
        $new_delivery_note_data['shipping_state']   = $_invoice->shipping_state;
        $new_delivery_note_data['shipping_zip']     = $_invoice->shipping_zip;
        $new_delivery_note_data['shipping_country'] = $_invoice->shipping_country;
        if ($_invoice->include_shipping == 1) {
            $new_delivery_note_data['include_shipping'] = $_invoice->include_shipping;
        }
        $new_delivery_note_data['show_shipping_on_delivery_note'] = $_invoice->show_shipping_on_invoice;
        // Set to unpaid status automatically
        $new_delivery_note_data['status']     = $status;
        $new_delivery_note_data['clientnote'] = $_invoice->clientnote;
        $new_delivery_note_data['adminnote']  = '';
        $new_delivery_note_data['newitems']   = [];
        $custom_fields_items             = get_custom_fields('items');
        $key                             = 1;
        foreach ($_invoice->items as $item) {
            $new_delivery_note_data['newitems'][$key]['description']      = $item['description'];
            $new_delivery_note_data['newitems'][$key]['long_description'] = clear_textarea_breaks($item['long_description']);
            $new_delivery_note_data['newitems'][$key]['qty']              = $item['qty'];
            $new_delivery_note_data['newitems'][$key]['unit']             = $item['unit'];
            $new_delivery_note_data['newitems'][$key]['taxname']          = [];
            $taxes                                                   = get_invoice_item_taxes($item['id']);
            foreach ($taxes as $tax) {
                // tax name is in format TAX1|10.00
                array_push($new_delivery_note_data['newitems'][$key]['taxname'], $tax['taxname']);
            }
            $new_delivery_note_data['newitems'][$key]['rate']  = $item['rate'];
            $new_delivery_note_data['newitems'][$key]['order'] = $item['item_order'];
            foreach ($custom_fields_items as $cf) {
                $new_delivery_note_data['newitems'][$key]['custom_fields']['items'][$cf['id']] = get_custom_field_value($item['id'], $cf['id'], 'items', false);

                if (!defined('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST')) {
                    define('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST', true);
                }
            }
            $key++;
        }

        $new_delivery_note_data['invoiced_date'] = date('Y-m-d H:i:s');
        $new_delivery_note_data['invoiceid'] = $_invoice->id;

        $new_delivery_note_data = $this->check_default_info_before_conversion($new_delivery_note_data);

        $id = $this->add($new_delivery_note_data);

        if ($id) {

            // Update invoice with the new delivery note data
            $this->db->where('id', $_invoice->id);
            $this->db->update(db_prefix() . 'invoices', [
                'delivery_noteid'     => $id,
            ]);

            $tags = get_tags_in($_invoice->id, 'invoice');
            handle_tags_save($tags, $id, 'delivery_note');

            $this->transfer_custom_fields('invoice', 'delivery_note', $_invoice->id, $id);

            $this->invoices_model->log_invoice_activity($_invoice->id, 'invoice_activity_converted_to_delivery_note', false, serialize([
                '<a href="' . admin_url('delivery_notes/delivery_note/' . $id) . '">' . format_delivery_note_number($id) . '</a>',
            ]));


            hooks()->do_action('invoice_converted_to_delivery_note', ['delivery_noteid' => $id, 'invoice_id' => $_invoice->id]);

            return $id;
        }

        return false;
    }

    public function convert_from_estimate($estimateid, $status)
    {
        $this->load->model('estimates_model');

        $_estimate = $this->estimates_model->get($estimateid);
        if (!empty($_estimate->delivery_noteid)) {
            if (!empty($this->get($_estimate->delivery_noteid)))
                return $_estimate->delivery_noteid;
        }

        $new_delivery_note_data               = [];
        $new_delivery_note_data['clientid']   = $_estimate->clientid;
        $new_delivery_note_data['project_id'] = $_estimate->project_id;
        $new_delivery_note_data['number']     = get_option('next_delivery_note_number');
        $new_delivery_note_data['date']       = _d(date('Y-m-d'));

        $new_delivery_note_data['show_quantity_as'] = $_estimate->show_quantity_as;
        $new_delivery_note_data['currency']         = $_estimate->currency;
        $new_delivery_note_data['subtotal']         = $_estimate->subtotal;
        $new_delivery_note_data['total']            = $_estimate->total;
        $new_delivery_note_data['adminnote']        = $_estimate->adminnote;
        $new_delivery_note_data['adjustment']       = $_estimate->adjustment;
        $new_delivery_note_data['discount_percent'] = $_estimate->discount_percent;
        $new_delivery_note_data['discount_total']   = $_estimate->discount_total;
        $new_delivery_note_data['discount_type']    = $_estimate->discount_type;
        $new_delivery_note_data['terms']            = $_estimate->terms;
        $new_delivery_note_data['sale_agent']       = $_estimate->sale_agent;
        $new_delivery_note_data['reference_no']     = $_estimate->reference_no;
        // Since version 1.0.6
        $new_delivery_note_data['billing_street']   = clear_textarea_breaks($_estimate->billing_street);
        $new_delivery_note_data['billing_city']     = $_estimate->billing_city;
        $new_delivery_note_data['billing_state']    = $_estimate->billing_state;
        $new_delivery_note_data['billing_zip']      = $_estimate->billing_zip;
        $new_delivery_note_data['billing_country']  = $_estimate->billing_country;
        $new_delivery_note_data['shipping_street']  = clear_textarea_breaks($_estimate->shipping_street);
        $new_delivery_note_data['shipping_city']    = $_estimate->shipping_city;
        $new_delivery_note_data['shipping_state']   = $_estimate->shipping_state;
        $new_delivery_note_data['shipping_zip']     = $_estimate->shipping_zip;
        $new_delivery_note_data['shipping_country'] = $_estimate->shipping_country;
        if ($_estimate->include_shipping == 1) {
            $new_delivery_note_data['include_shipping'] = $_estimate->include_shipping;
        }
        $new_delivery_note_data['show_shipping_on_delivery_note'] = $_estimate->show_shipping_on_estimate;
        // Set to unpaid status automatically
        $new_delivery_note_data['status']     = $status;
        $new_delivery_note_data['clientnote'] = $_estimate->clientnote;
        $new_delivery_note_data['adminnote']  = '';
        $new_delivery_note_data['newitems']   = [];
        $custom_fields_items             = get_custom_fields('items');
        $key                             = 1;
        foreach ($_estimate->items as $item) {
            $new_delivery_note_data['newitems'][$key]['description']      = $item['description'];
            $new_delivery_note_data['newitems'][$key]['long_description'] = clear_textarea_breaks($item['long_description']);
            $new_delivery_note_data['newitems'][$key]['qty']              = $item['qty'];
            $new_delivery_note_data['newitems'][$key]['unit']             = $item['unit'];
            $new_delivery_note_data['newitems'][$key]['taxname']          = [];
            $taxes                                                   = get_estimate_item_taxes($item['id']);
            foreach ($taxes as $tax) {
                // tax name is in format TAX1|10.00
                array_push($new_delivery_note_data['newitems'][$key]['taxname'], $tax['taxname']);
            }
            $new_delivery_note_data['newitems'][$key]['rate']  = $item['rate'];
            $new_delivery_note_data['newitems'][$key]['order'] = $item['item_order'];
            foreach ($custom_fields_items as $cf) {
                $new_delivery_note_data['newitems'][$key]['custom_fields']['items'][$cf['id']] = get_custom_field_value($item['id'], $cf['id'], 'items', false);

                if (!defined('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST')) {
                    define('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST', true);
                }
            }
            $key++;
        }

        $new_delivery_note_data = $this->check_default_info_before_conversion($new_delivery_note_data);

        $id = $this->add($new_delivery_note_data);

        if ($id) {

            // Update estimate with the new delivery note data
            $this->db->where('id', $_estimate->id);
            $this->db->update(db_prefix() . 'estimates', [
                'delivery_noteid'     => $id,
            ]);

            $tags = get_tags_in($_estimate->id, 'estimate');
            handle_tags_save($tags, $id, 'delivery_note');

            $this->transfer_custom_fields('estimate', 'delivery_note', $_estimate->id, $id);

            $this->estimates_model->log_estimate_activity($_estimate->id, 'estimate_activity_converted_to_delivery_note', false, serialize([
                '<a href="' . admin_url('delivery_notes/delivery_note/' . $id) . '">' . format_delivery_note_number($id) . '</a>',
            ]));


            hooks()->do_action('estimate_converted_to_delivery_note', ['delivery_noteid' => $id, 'estimate_id' => $_estimate->id]);

            return $id;
        }

        return false;
    }

    public function convert_from_purchase_order($purchase_orderid, $status)
    {

        $this->load->model('purchase_orders_model');

        $_purchase_order = $this->purchase_orders_model->get($purchase_orderid);
        if (!empty($_purchase_order->delivery_noteid)) {
            if (!empty($this->get($_purchase_order->delivery_noteid)))
                return $_purchase_order->delivery_noteid;
        }

        $new_delivery_note_data               = [];
        $new_delivery_note_data['clientid']   = $_purchase_order->clientid;
        $new_delivery_note_data['project_id'] = $_purchase_order->project_id;
        $new_delivery_note_data['number']     = get_option('next_delivery_note_number');
        $new_delivery_note_data['date']       = _d(date('Y-m-d'));

        $new_delivery_note_data['show_quantity_as'] = $_purchase_order->show_quantity_as;
        $new_delivery_note_data['currency']         = $_purchase_order->currency;
        $new_delivery_note_data['subtotal']         = $_purchase_order->subtotal;
        $new_delivery_note_data['total']            = $_purchase_order->total;
        $new_delivery_note_data['adminnote']        = $_purchase_order->adminnote;
        $new_delivery_note_data['adjustment']       = $_purchase_order->adjustment;
        $new_delivery_note_data['discount_percent'] = $_purchase_order->discount_percent;
        $new_delivery_note_data['discount_total']   = $_purchase_order->discount_total;
        $new_delivery_note_data['discount_type']    = $_purchase_order->discount_type;
        $new_delivery_note_data['terms']            = $_purchase_order->terms;
        $new_delivery_note_data['sale_agent']       = $_purchase_order->sale_agent;
        $new_delivery_note_data['reference_no']     = $_purchase_order->reference_no;
        // Since version 1.0.6
        $new_delivery_note_data['billing_street']   = clear_textarea_breaks($_purchase_order->billing_street);
        $new_delivery_note_data['billing_city']     = $_purchase_order->billing_city;
        $new_delivery_note_data['billing_state']    = $_purchase_order->billing_state;
        $new_delivery_note_data['billing_zip']      = $_purchase_order->billing_zip;
        $new_delivery_note_data['billing_country']  = $_purchase_order->billing_country;
        $new_delivery_note_data['shipping_street']  = clear_textarea_breaks($_purchase_order->shipping_street);
        $new_delivery_note_data['shipping_city']    = $_purchase_order->shipping_city;
        $new_delivery_note_data['shipping_state']   = $_purchase_order->shipping_state;
        $new_delivery_note_data['shipping_zip']     = $_purchase_order->shipping_zip;
        $new_delivery_note_data['shipping_country'] = $_purchase_order->shipping_country;
        if ($_purchase_order->include_shipping == 1) {
            $new_delivery_note_data['include_shipping'] = $_purchase_order->include_shipping;
        }
        $new_delivery_note_data['show_shipping_on_delivery_note'] = $_purchase_order->show_shipping_on_purchase_order;
        // Set to unpaid status automatically
        $new_delivery_note_data['status']     = $status;
        $new_delivery_note_data['clientnote'] = $_purchase_order->clientnote;
        $new_delivery_note_data['adminnote']  = '';
        $new_delivery_note_data['newitems']   = [];
        $custom_fields_items             = get_custom_fields('items');
        $key                             = 1;
        foreach ($_purchase_order->items as $item) {
            $new_delivery_note_data['newitems'][$key]['description']      = $item['description'];
            $new_delivery_note_data['newitems'][$key]['long_description'] = clear_textarea_breaks($item['long_description']);
            $new_delivery_note_data['newitems'][$key]['qty']              = $item['qty'];
            $new_delivery_note_data['newitems'][$key]['unit']             = $item['unit'];
            $new_delivery_note_data['newitems'][$key]['taxname']          = [];
            $taxes                                                   = get_purchase_order_item_taxes($item['id']);
            foreach ($taxes as $tax) {
                // tax name is in format TAX1|10.00
                array_push($new_delivery_note_data['newitems'][$key]['taxname'], $tax['taxname']);
            }
            $new_delivery_note_data['newitems'][$key]['rate']  = $item['rate'];
            $new_delivery_note_data['newitems'][$key]['order'] = $item['item_order'];
            foreach ($custom_fields_items as $cf) {
                $new_delivery_note_data['newitems'][$key]['custom_fields']['items'][$cf['id']] = get_custom_field_value($item['id'], $cf['id'], 'items', false);

                if (!defined('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST')) {
                    define('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST', true);
                }
            }
            $key++;
        }

        $new_delivery_note_data = $this->check_default_info_before_conversion($new_delivery_note_data);

        $id = $this->add($new_delivery_note_data);

        if ($id) {

            // Update purchase_order with the new delivery note data
            $this->db->where('id', $_purchase_order->id);
            $this->db->update(db_prefix() . 'purchase_orders', [
                'delivery_noteid'     => $id,
            ]);

            $tags = get_tags_in($_purchase_order->id, 'purchase_order');
            handle_tags_save($tags, $id, 'delivery_note');

            $this->transfer_custom_fields('purchase_order', 'delivery_note', $_purchase_order->id, $id);

            $this->purchase_orders_model->log_purchase_order_activity($_purchase_order->id, 'purchase_order_activity_converted_to_delivery_note', false, serialize([
                '<a href="' . admin_url('delivery_notes/delivery_note/' . $id) . '">' . format_delivery_note_number($id) . '</a>',
            ]));


            hooks()->do_action('purchase_order_converted_to_delivery_note', ['delivery_noteid' => $id, 'purchase_orderid' => $_purchase_order->id]);

            return $id;
        }

        return false;
    }

    /**
     * Copy a resources (i.e estiamte, invoice ) custom field to another custom field (i.e delivery note)
     * 
     * @param string $from_res i.e estimate
     * @param string $to_res i.e invoice
     * @param string|number $from_res_id The source resources id
     * @param string|number $to_res_id The target resources id
     * @return void
     */
    private function transfer_custom_fields($from_res, $to_res, $from_res_id, $to_res_id)
    {
        if (!is_custom_fields_smart_transfer_enabled()) return false;

        $this->db->where('fieldto', $from_res);
        $this->db->where('active', 1);
        $from_res_custom_feilds = $this->db->get(db_prefix() . 'customfields')->result_array();
        foreach ($from_res_custom_feilds as $field) {

            // Replace source resources i.e delivery_ntoe_sku_xxx becomes _sku_xxx
            $tmpSlug = str_ireplace($from_res, '', $field['slug']);
            // Split and get the slug i.e _sku_xxx
            $tmpSlug = explode('_', $tmpSlug, 2);

            if (isset($tmpSlug[1])) {
                $this->db->where('fieldto', $to_res);

                $this->db->group_start();
                $this->db->like('slug', $to_res . '_' . $tmpSlug[1], 'after');
                $this->db->where('type', $field['type']);
                $this->db->where('options', $field['options']);
                $this->db->where('active', 1);
                $this->db->group_end();

                $cfTransfer = $this->db->get(db_prefix() . 'customfields')->result_array();
                $value = get_custom_field_value($from_res_id, $field['id'], $from_res, false);

                // Don't make mistakes
                // Only valid if 1 result returned
                // + if field names similarity is equal or more then CUSTOM_FIELD_TRANSFER_SIMILARITY%
                if (count($cfTransfer) == 1 && ((similarity($field['name'], $cfTransfer[0]['name']) * 100) >= CUSTOM_FIELD_TRANSFER_SIMILARITY)) {
                    if ($value == '') {
                        continue;
                    }

                    $this->db->insert(db_prefix() . 'customfieldsvalues', [
                        'relid'   => $to_res_id,
                        'fieldid' => $cfTransfer[0]['id'],
                        'fieldto' => $to_res,
                        'value'   => $value,
                    ]);
                } else if ((int)get_option('delivery_note_allow_transfer_of_non_similar_custom_fields')) { // If no similar field found
                    // Create the custom field
                    $new_field = $field;
                    $new_field['id'] = null;
                    $new_field['fieldto'] = $to_res;
                    $new_field['slug'] = str_ireplace($from_res, $to_res, $field['slug']);

                    $this->db->insert(db_prefix() . 'customfields', $new_field);
                    $new_field_id = $this->db->insert_id();

                    $this->db->insert(db_prefix() . 'customfieldsvalues', [
                        'relid'   => $to_res_id,
                        'fieldid' => $new_field_id,
                        'fieldto' => $to_res,
                        'value'   => $value,
                    ]);
                }
            }
        }
    }

    /**
     * Copy delivery_note
     * @param mixed $id delivery_note id to copy
     * @return mixed
     */
    public function copy($id)
    {
        $_delivery_note                       = $this->get($id);
        $new_delivery_note_data               = [];
        $new_delivery_note_data['clientid']   = $_delivery_note->clientid;
        $new_delivery_note_data['project_id'] = $_delivery_note->project_id;
        $new_delivery_note_data['number']     = get_option('next_delivery_note_number');
        $new_delivery_note_data['date']       = _d(date('Y-m-d'));

        $new_delivery_note_data['show_quantity_as'] = $_delivery_note->show_quantity_as;
        $new_delivery_note_data['currency']         = $_delivery_note->currency;
        $new_delivery_note_data['subtotal']         = $_delivery_note->subtotal;
        $new_delivery_note_data['total']            = $_delivery_note->total;
        $new_delivery_note_data['adminnote']        = $_delivery_note->adminnote;
        $new_delivery_note_data['adjustment']       = $_delivery_note->adjustment;
        $new_delivery_note_data['discount_percent'] = $_delivery_note->discount_percent;
        $new_delivery_note_data['discount_total']   = $_delivery_note->discount_total;
        $new_delivery_note_data['discount_type']    = $_delivery_note->discount_type;
        $new_delivery_note_data['terms']            = $_delivery_note->terms;
        $new_delivery_note_data['sale_agent']       = $_delivery_note->sale_agent;
        $new_delivery_note_data['reference_no']     = $_delivery_note->reference_no;
        // Since version 1.0.6
        $new_delivery_note_data['billing_street']   = clear_textarea_breaks($_delivery_note->billing_street);
        $new_delivery_note_data['billing_city']     = $_delivery_note->billing_city;
        $new_delivery_note_data['billing_state']    = $_delivery_note->billing_state;
        $new_delivery_note_data['billing_zip']      = $_delivery_note->billing_zip;
        $new_delivery_note_data['billing_country']  = $_delivery_note->billing_country;
        $new_delivery_note_data['shipping_street']  = clear_textarea_breaks($_delivery_note->shipping_street);
        $new_delivery_note_data['shipping_city']    = $_delivery_note->shipping_city;
        $new_delivery_note_data['shipping_state']   = $_delivery_note->shipping_state;
        $new_delivery_note_data['shipping_zip']     = $_delivery_note->shipping_zip;
        $new_delivery_note_data['shipping_country'] = $_delivery_note->shipping_country;
        if ($_delivery_note->include_shipping == 1) {
            $new_delivery_note_data['include_shipping'] = $_delivery_note->include_shipping;
        }
        $new_delivery_note_data['show_shipping_on_delivery_note'] = $_delivery_note->show_shipping_on_delivery_note;
        // Set to unpaid status automatically
        $new_delivery_note_data['status']     = 1;
        $new_delivery_note_data['clientnote'] = $_delivery_note->clientnote;
        $new_delivery_note_data['adminnote']  = '';
        $new_delivery_note_data['newitems']   = [];
        $custom_fields_items             = get_custom_fields('items');
        $key                             = 1;
        foreach ($_delivery_note->items as $item) {
            $new_delivery_note_data['newitems'][$key]['description']      = $item['description'];
            $new_delivery_note_data['newitems'][$key]['long_description'] = clear_textarea_breaks($item['long_description']);
            $new_delivery_note_data['newitems'][$key]['qty']              = $item['qty'];
            $new_delivery_note_data['newitems'][$key]['unit']             = $item['unit'];
            $new_delivery_note_data['newitems'][$key]['taxname']          = [];
            $taxes                                                   = get_delivery_note_item_taxes($item['id']);
            foreach ($taxes as $tax) {
                // tax name is in format TAX1|10.00
                array_push($new_delivery_note_data['newitems'][$key]['taxname'], $tax['taxname']);
            }
            $new_delivery_note_data['newitems'][$key]['rate']  = $item['rate'];
            $new_delivery_note_data['newitems'][$key]['order'] = $item['item_order'];
            foreach ($custom_fields_items as $cf) {
                $new_delivery_note_data['newitems'][$key]['custom_fields']['items'][$cf['id']] = get_custom_field_value($item['id'], $cf['id'], 'items', false);

                if (!defined('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST')) {
                    define('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST', true);
                }
            }
            $key++;
        }
        $id = $this->add($new_delivery_note_data);
        if ($id) {
            $custom_fields = get_custom_fields('delivery_note');
            foreach ($custom_fields as $field) {
                $value = get_custom_field_value($_delivery_note->id, $field['id'], 'delivery_note', false);
                if ($value == '') {
                    continue;
                }

                $this->db->insert(db_prefix() . 'customfieldsvalues', [
                    'relid'   => $id,
                    'fieldid' => $field['id'],
                    'fieldto' => 'delivery_note',
                    'value'   => $value,
                ]);
            }

            $tags = get_tags_in($_delivery_note->id, 'delivery_note');
            handle_tags_save($tags, $id, 'delivery_note');

            log_activity('Copied delivery_note ' . format_delivery_note_number($_delivery_note->id));

            return $id;
        }

        return false;
    }

    /**
     * Insert new delivery_note to database
     * @param array $data invoiec data
     * @return mixed - false if not insert, delivery_note ID if succes
     */
    public function add($data)
    {
        $data['datecreated'] = date('Y-m-d H:i:s');

        $data['addedfrom'] = get_staff_user_id();

        $data['prefix'] = get_option('delivery_note_prefix');

        $data['number_format'] = get_option('delivery_note_number_format');

        $save_and_send = isset($data['save_and_send']);

        $delivery_noteRequestID = false;
        if (isset($data['delivery_note_request_id'])) {
            $delivery_noteRequestID = $data['delivery_note_request_id'];
            unset($data['delivery_note_request_id']);
        }

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }

        $data['hash'] = app_generate_hash();
        $tags         = isset($data['tags']) ? $data['tags'] : '';

        $items = [];
        if (isset($data['newitems'])) {
            $items = $data['newitems'];
            unset($data['newitems']);
        }

        $data = $this->map_shipping_columns($data);

        $data['billing_street'] = trim($data['billing_street']);
        $data['billing_street'] = nl2br($data['billing_street']);

        if (isset($data['shipping_street'])) {
            $data['shipping_street'] = trim($data['shipping_street']);
            $data['shipping_street'] = nl2br($data['shipping_street']);
        }

        if (!isset($data['created_by']) && !is_client_logged_in()) {
            $data['created_by'] = get_staff_user_id();
        }

        $hook = hooks()->apply_filters('before_delivery_note_added', [
            'data'  => $data,
            'items' => $items,
        ]);

        $data  = $hook['data'];
        $items = $hook['items'];

        $this->db->insert(db_prefix() . 'delivery_notes', $data);
        $insert_id = $this->db->insert_id();
		$data = $this->normalize_recurring_fields($data);

        if ($insert_id) {
            // Update next delivery_note number in settings
            $this->db->where('name', 'next_delivery_note_number');
            $this->db->set('value', 'value+1', false);
            $this->db->update(db_prefix() . 'options');

            if ($delivery_noteRequestID !== false && $delivery_noteRequestID != '') {
                $this->load->model('delivery_note_request_model');
                $completedStatus = $this->delivery_note_request_model->get_status_by_flag('completed');
                $this->delivery_note_request_model->update_request_status([
                    'requestid' => $delivery_noteRequestID,
                    'status'    => $completedStatus->id,
                ]);
            }

            if (isset($custom_fields)) {
                handle_custom_fields_post($insert_id, $custom_fields);
            }

            handle_tags_save($tags, $insert_id, 'delivery_note');

            foreach ($items as $key => $item) {
                if ($itemid = add_new_sales_item_post($item, $insert_id, 'delivery_note')) {
                    _maybe_insert_post_item_tax($itemid, $item, $insert_id, 'delivery_note');
                }
            }

            update_sales_total_tax_column($insert_id, 'delivery_note', db_prefix() . 'delivery_notes');
            $this->log_delivery_note_activity($insert_id, 'delivery_note_activity_created', false);

            hooks()->do_action('after_delivery_note_added', $insert_id);

            if ($save_and_send === true) {
                $this->send_delivery_note_to_client($insert_id, '', true, '', true);
            }

            return $insert_id;
        }

        return false;
    }

    /**
     * Get item by id
     * @param mixed $id item id
     * @return object
     */
    public function get_delivery_note_item($id)
    {
        $this->db->where('id', $id);

        return $this->db->get(db_prefix() . 'itemable')->row();
    }

    /**
     * Update delivery_note data
     * @param array $data delivery_note data
     * @param mixed $id delivery_noteid
     * @return boolean
     */
    public function update($data, $id)
    {
        $affectedRows = 0;

        $data['number'] = trim($data['number']);

        $original_delivery_note = $this->get($id);

        $original_status = $original_delivery_note->status;

        $original_number = $original_delivery_note->number;

        $original_number_formatted = format_delivery_note_number($id);

        $save_and_send = isset($data['save_and_send']);

        $items = [];
        if (isset($data['items'])) {
            $items = $data['items'];
            unset($data['items']);
        }

        $newitems = [];
        if (isset($data['newitems'])) {
            $newitems = $data['newitems'];
            unset($data['newitems']);
        }

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            if (handle_custom_fields_post($id, $custom_fields)) {
                $affectedRows++;
            }
            unset($data['custom_fields']);
        }

        if (isset($data['tags'])) {
            if (handle_tags_save($data['tags'], $id, 'delivery_note')) {
                $affectedRows++;
            }
        }

        $data['billing_street'] = trim($data['billing_street']);
        $data['billing_street'] = nl2br($data['billing_street']);

        $data['shipping_street'] = trim($data['shipping_street']);
        $data['shipping_street'] = nl2br($data['shipping_street']);

        $data = $this->map_shipping_columns($data);

        $hook = hooks()->apply_filters('before_delivery_note_updated', [
            'data'          => $data,
            'items'         => $items,
            'newitems'      => $newitems,
            'removed_items' => isset($data['removed_items']) ? $data['removed_items'] : [],
        ], $id);

        $data                  = $hook['data'];
        $items                 = $hook['items'];
        $newitems              = $hook['newitems'];
        $data['removed_items'] = $hook['removed_items'];

        // Delete items checked to be removed from database
        foreach ($data['removed_items'] as $remove_item_id) {
            $original_item = $this->get_delivery_note_item($remove_item_id);
            if (handle_removed_sales_item_post($remove_item_id, 'delivery_note')) {
                $affectedRows++;
                $this->log_delivery_note_activity($id, 'invoice_delivery_note_activity_removed_item', false, serialize([
                    $original_item->description,
                ]));
            }
        }

        unset($data['removed_items']);
		$data = $this->normalize_recurring_fields($data);

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'delivery_notes', $data);

        if ($this->db->affected_rows() > 0) {
            // Check for status change
            if ($original_status != $data['status']) {
                $this->log_delivery_note_activity($original_delivery_note->id, 'not_delivery_note_status_updated', false, serialize([
                    '<original_status>' . $original_status . '</original_status>',
                    '<new_status>' . $data['status'] . '</new_status>',
                ]));
                if ($data['status'] == 2) {
                    $this->db->where('id', $id);
                    $this->db->update(db_prefix() . 'delivery_notes', ['sent' => 1, 'datesend' => date('Y-m-d H:i:s')]);
                }
            }
            if ($original_number != $data['number']) {
                $this->log_delivery_note_activity($original_delivery_note->id, 'delivery_note_activity_number_changed', false, serialize([
                    $original_number_formatted,
                    format_delivery_note_number($original_delivery_note->id),
                ]));
            }
            $affectedRows++;
        }

        foreach ($items as $key => $item) {
            $original_item = $this->get_delivery_note_item($item['itemid']);

            if (update_sales_item_post($item['itemid'], $item, 'item_order')) {
                $affectedRows++;
            }

            if (update_sales_item_post($item['itemid'], $item, 'unit')) {
                $affectedRows++;
            }

            if (update_sales_item_post($item['itemid'], $item, 'rate')) {
                $this->log_delivery_note_activity($id, 'invoice_delivery_note_activity_updated_item_rate', false, serialize([
                    $original_item->rate,
                    $item['rate'],
                ]));
                $affectedRows++;
            }

            if (update_sales_item_post($item['itemid'], $item, 'qty')) {
                $this->log_delivery_note_activity($id, 'invoice_delivery_note_activity_updated_qty_item', false, serialize([
                    $item['description'],
                    $original_item->qty,
                    $item['qty'],
                ]));
                $affectedRows++;
            }

            if (update_sales_item_post($item['itemid'], $item, 'description')) {
                $this->log_delivery_note_activity($id, 'invoice_delivery_note_activity_updated_item_short_description', false, serialize([
                    $original_item->description,
                    $item['description'],
                ]));
                $affectedRows++;
            }

            if (update_sales_item_post($item['itemid'], $item, 'long_description')) {
                $this->log_delivery_note_activity($id, 'invoice_delivery_note_activity_updated_item_long_description', false, serialize([
                    $original_item->long_description,
                    $item['long_description'],
                ]));
                $affectedRows++;
            }

            if (isset($item['custom_fields'])) {
                if (handle_custom_fields_post($item['itemid'], $item['custom_fields'])) {
                    $affectedRows++;
                }
            }

            if (!isset($item['taxname']) || (isset($item['taxname']) && count($item['taxname']) == 0)) {
                if (delete_taxes_from_item($item['itemid'], 'delivery_note')) {
                    $affectedRows++;
                }
            } else {
                $item_taxes        = get_delivery_note_item_taxes($item['itemid']);
                $_item_taxes_names = [];
                foreach ($item_taxes as $_item_tax) {
                    array_push($_item_taxes_names, $_item_tax['taxname']);
                }

                $i = 0;
                foreach ($_item_taxes_names as $_item_tax) {
                    if (!in_array($_item_tax, $item['taxname'])) {
                        $this->db->where('id', $item_taxes[$i]['id'])
                            ->delete(db_prefix() . 'item_tax');
                        if ($this->db->affected_rows() > 0) {
                            $affectedRows++;
                        }
                    }
                    $i++;
                }
                if (_maybe_insert_post_item_tax($item['itemid'], $item, $id, 'delivery_note')) {
                    $affectedRows++;
                }
            }
        }

        foreach ($newitems as $key => $item) {
            if ($new_item_added = add_new_sales_item_post($item, $id, 'delivery_note')) {
                _maybe_insert_post_item_tax($new_item_added, $item, $id, 'delivery_note');
                $this->log_delivery_note_activity($id, 'invoice_delivery_note_activity_added_item', false, serialize([
                    $item['description'],
                ]));
                $affectedRows++;
            }
        }

        if ($affectedRows > 0) {
            update_sales_total_tax_column($id, 'delivery_note', db_prefix() . 'delivery_notes');
        }

        if ($save_and_send === true) {
            $this->send_delivery_note_to_client($id, '', true, '', true);
        }
		
		// Ενημέρωση recurring meta όταν αλλάξει από τη φόρμα
		if (isset($data['recurring'])) {
			$recurring        = (int)$data['recurring'];
			$recurring_type   = $data['recurring_type'] ?? 'month';
			$custom_recurring = (int)($data['custom_recurring'] ?? 0);
			$cycles           = (int)($data['cycles'] ?? 0);

			if ($recurring > 0) {
				$base = to_sql_date($data['date'] ?? date('Y-m-d'));
				$next = $this->calc_next_dn_date($base, $recurring, $recurring_type);
				$this->db->where('id',$id)->update(db_prefix().'delivery_notes',[
					'recurring'           => $recurring,
					'recurring_type'      => $recurring_type,
					'custom_recurring'    => $custom_recurring,
					'cycles'              => $cycles,
					'next_recurring_date' => $next,
				]);
			} else {
				$this->db->where('id',$id)->update(db_prefix().'delivery_notes',[
					'recurring'           => 0,
					'recurring_type'      => null,
					'custom_recurring'    => 0,
					'cycles'              => 0,
					'cycles'        => 0,
					'is_recurring_from'   => null,
					'last_recurring_date' => null,
					'next_recurring_date' => null,
				]);
			}
		}


        if ($affectedRows > 0) {
            hooks()->do_action('after_delivery_note_updated', $id);

            return true;
        }

        return false;
    }

    public function mark_action_status($action, $id, $client = false)
    {
        $delivery_note = $this->get($id);
        if ($client) {
            // Client can only confirm delivery or set as partially delivered
            if (!in_array($action, [4, 5]) || empty($delivery_note->signature) || empty($delivery_note->acceptance_firstname))
                return false;
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'delivery_notes', [
            'status' => $action,
        ]);

        $notifiedUsers = [];

        if ($this->db->affected_rows() > 0) {
            $delivery_note = $this->get($id);

            $editor =  new stdClass();
            if ($client) {
                $editor->firstname = $delivery_note->acceptance_firstname;
                $editor->lastname = $delivery_note->acceptance_lastname;
                $editor->email = $delivery_note->acceptance_email;
            } else $editor = get_staff();

            $this->db->where('staffid', $delivery_note->addedfrom);
            $this->db->or_where('staffid', $delivery_note->sale_agent);
            $staff_delivery_note = $this->db->get(db_prefix() . 'staff')->result_array();

            $contact_id = get_primary_contact_user_id($delivery_note->clientid);
            if ($action == 4) {

                $additional_data = '';
                if ($client)
                    $additional_data = serialize([$delivery_note->acceptance_firstname . ' ' . $delivery_note->acceptance_lastname . ' - ' . $delivery_note->acceptance_ip]);
                $this->log_delivery_note_activity($id, 'delivery_note_activity_confirmed', $client, $additional_data);

                foreach ($staff_delivery_note as $member) {
                    $notified = add_notification([
                        'fromcompany'     => true,
                        'touserid'        => $member['staffid'],
                        'description'     => 'not_delivery_note_confirmed',
                        'link'            => 'delivery_notes/list_delivery_notes/' . $id,
                        'additional_data' => serialize([
                            format_delivery_note_number($delivery_note->id)
                        ]),
                    ]);

                    if ($notified) {
                        array_push($notifiedUsers, $member['staffid']);
                    }

                    send_mail_template('delivery_note_delivered_to_staff', DELIVERY_NOTE_MODULE_NAME, $delivery_note, $member, $contact_id, $editor);
                }

                pusher_trigger_notification($notifiedUsers);
                hooks()->do_action('delivery_note_confirmed', $id);
				// ✅ Event-based recurring: μόλις γίνει Delivered, προσπάθησε να δημιουργήσεις το επόμενο DN
				if (method_exists($this, 'maybe_generate_next_recurring')) {
					$this->maybe_generate_next_recurring((int)$id);
				}

            } elseif ($action == 3) {
                foreach ($staff_delivery_note as $member) {
                    $notified = add_notification([
                        'fromcompany'     => true,
                        'touserid'        => $member['staffid'],
                        'description'     => 'not_delivery_note_cancelled',
                        'link'            => 'delivery_notes/list_delivery_notes/' . $id,
                        'additional_data' => serialize([
                            format_delivery_note_number($delivery_note->id)
                        ]),
                    ]);

                    if ($notified) {
                        array_push($notifiedUsers, $member['staffid']);
                    }
                    // Send staff email notification that delivery note is cancelled
                    send_mail_template('delivery_note_cancelled_to_staff', DELIVERY_NOTE_MODULE_NAME, $delivery_note, $member, $contact_id, $editor);
                }

                pusher_trigger_notification($notifiedUsers);
                $this->log_delivery_note_activity($id, 'delivery_note_activity_cancelled', false);
                hooks()->do_action('delivery_note_cancelled', $id);
            } else {

                // Other status update
                $additional_data = [
                    '<status>' . $action . '</status>',
                ];
                if ($client && !empty($delivery_note->acceptance_firstname))
                    $additional_data[] = $delivery_note->acceptance_firstname . ' ' . $delivery_note->acceptance_lastname . ' - ' . ($delivery_note->acceptance_ip ?? '');

                $this->log_delivery_note_activity($id, 'delivery_note_activity_marked', $client, serialize($additional_data));

                foreach ($staff_delivery_note as $member) {
                    $notified = add_notification([
                        'fromcompany'     => true,
                        'touserid'        => $member['staffid'],
                        'description'     => 'not_delivery_note_status_updated',
                        'link'            => 'delivery_notes/list_delivery_notes/' . $id,
                        'additional_data' => serialize([
                            format_delivery_note_number($delivery_note->id),
                            format_delivery_note_status($action)
                        ]),
                    ]);

                    if ($notified) {
                        array_push($notifiedUsers, $member['staffid']);
                    }
                    // Send staff email notification that delivery note is cancelled
                    send_mail_template('delivery_note_status_updated_to_staff', DELIVERY_NOTE_MODULE_NAME, $delivery_note, $member, $contact_id, $editor);
                }

                pusher_trigger_notification($notifiedUsers);
            }

            if ($action == 2) {
                $this->db->where('id', $id);
                $this->db->update(db_prefix() . 'delivery_notes', ['sent' => 1, 'datesend' => date('Y-m-d H:i:s')]);
            }

            return true;
        }

        return false;
    }

    /**
     * Get delivery_note attachments
     * @param mixed $delivery_noteid
     * @param string $id attachment id
     * @return mixed
     */
    public function get_attachments($delivery_noteid, $id = '')
    {
        // If is passed id get return only 1 attachment
        if (is_numeric($id)) {
            $this->db->where('id', $id);
        } else {
            $this->db->where('rel_id', $delivery_noteid);
        }
        $this->db->where('rel_type', 'delivery_note');
        $result = $this->db->get(db_prefix() . 'files');
        if (is_numeric($id)) {
            return $result->row();
        }

        return $result->result_array();
    }

    /**
     *  Delete delivery_note attachment
     * @param mixed $id attachmentid
     * @return  boolean
     */
    public function delete_attachment($id)
    {
        $attachment = $this->get_attachments('', $id);
        $deleted    = false;
        if ($attachment) {
            if (empty($attachment->external)) {
                unlink(get_upload_path_by_type('delivery_note') . $attachment->rel_id . '/' . $attachment->file_name);
            }
            $this->db->where('id', $attachment->id);
            $this->db->delete(db_prefix() . 'files');
            if ($this->db->affected_rows() > 0) {
                $deleted = true;
                log_activity('delivery_note Attachment Deleted [delivery_noteID: ' . $attachment->rel_id . ']');
            }

            if (is_dir(get_upload_path_by_type('delivery_note') . $attachment->rel_id)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files(get_upload_path_by_type('delivery_note') . $attachment->rel_id);
                if (count($other_attachments) == 0) {
                    // okey only index.html so we can delete the folder also
                    delete_dir(get_upload_path_by_type('delivery_note') . $attachment->rel_id);
                }
            }
        }

        return $deleted;
    }

    /**
     * Delete delivery_note items and all connections
     * @param mixed $id delivery_noteid
     * @return boolean
     */
    public function delete($id, $simpleDelete = false)
    {
        if (get_option('delete_only_on_last_delivery_note') == 1 && $simpleDelete == false) {
            if (!is_last_delivery_note($id)) {
                return false;
            }
        }
        $delivery_note = $this->get($id);
        if (!empty($delivery_note->invoice->id) && $simpleDelete == false) {
            return [
                'is_invoiced_delivery_note_delete_error' => true,
            ];
        }
        hooks()->do_action('before_delivery_note_deleted', $id);

        $number = format_delivery_note_number($id);

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'delivery_notes');

        if ($this->db->affected_rows() > 0) {
            if (!is_null($delivery_note->short_link)) {
                app_archive_short_link($delivery_note->short_link);
            }

            if (get_option('delivery_note_number_decrement_on_delete') == 1 && $simpleDelete == false) {
                $current_next_delivery_note_number = get_option('next_delivery_note_number');
                if ($current_next_delivery_note_number > 1) {
                    // Decrement next delivery_note number to
                    $this->db->where('name', 'next_delivery_note_number');
                    $this->db->set('value', 'value-1', false);
                    $this->db->update(db_prefix() . 'options');
                }
            }

            if (total_rows(db_prefix() . 'estimates', [
                'delivery_noteid' => $id,
            ]) > 0) {
                $this->db->where('delivery_noteid', $delivery_note->id);
                $this->db->update(db_prefix() . 'estimates', [
                    'delivery_noteid'    => null,
                ]);
            }

            if ($this->db->table_exists(db_prefix() . 'purchase_orders')) {
                if (total_rows(db_prefix() . 'purchase_orders', [
                    'delivery_noteid' => $id,
                ]) > 0) {
                    $this->db->where('delivery_noteid', $delivery_note->id);
                    $this->db->update(db_prefix() . 'purchase_orders', [
                        'delivery_noteid'    => null,
                    ]);
                }
            }

            delete_tracked_emails($id, 'delivery_note');

            $this->db->where('relid IN (SELECT id from ' . db_prefix() . 'itemable WHERE rel_type="delivery_note" AND rel_id="' . $this->db->escape_str($id) . '")');
            $this->db->where('fieldto', 'items');
            $this->db->delete(db_prefix() . 'customfieldsvalues');

            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'delivery_note');
            $this->db->delete(db_prefix() . 'notes');

            $this->db->where('rel_type', 'delivery_note');
            $this->db->where('rel_id', $id);
            $this->db->delete(db_prefix() . 'views_tracking');

            $this->db->where('rel_type', 'delivery_note');
            $this->db->where('rel_id', $id);
            $this->db->delete(db_prefix() . 'taggables');

            $this->db->where('rel_type', 'delivery_note');
            $this->db->where('rel_id', $id);
            $this->db->delete(db_prefix() . 'reminders');

            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'delivery_note');
            $this->db->delete(db_prefix() . 'itemable');

            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'delivery_note');
            $this->db->delete(db_prefix() . 'item_tax');

            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'delivery_note');
            $this->db->delete(db_prefix() . 'sales_activity');

            // Delete the custom field values
            $this->db->where('relid', $id);
            $this->db->where('fieldto', 'delivery_note');
            $this->db->delete(db_prefix() . 'customfieldsvalues');

            $attachments = $this->get_attachments($id);
            foreach ($attachments as $attachment) {
                $this->delete_attachment($attachment['id']);
            }

            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'delivery_note');
            $this->db->delete('scheduled_emails');

            // Get related tasks
            $this->db->where('rel_type', 'delivery_note');
            $this->db->where('rel_id', $id);
            $tasks = $this->db->get(db_prefix() . 'tasks')->result_array();
            foreach ($tasks as $task) {
                $this->tasks_model->delete_task($task['id']);
            }
            if ($simpleDelete == false) {
                log_activity('Delivery_note Deleted [Number: ' . $number . ']');
            }

            // Clear signatures 
            $this->clear_signatures($id);

            hooks()->do_action('after_delivery_note_deleted', $id);

            return true;
        }

        return false;
    }

    /**
     * Set delivery_note to sent when email is successfuly sended to client
     * @param mixed $id delivery_noteid
     */
    public function set_delivery_note_sent($id, $emails_sent = [])
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'delivery_notes', [
            'sent'     => 1,
            'datesend' => date('Y-m-d H:i:s'),
        ]);

        $this->log_delivery_note_activity($id, 'invoice_delivery_note_activity_sent_to_client', false, serialize([
            '<custom_data>' . implode(', ', $emails_sent) . '</custom_data>',
        ]));

        // Update delivery_note status to sent
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'delivery_notes', [
            'status' => 2,
        ]);

        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'delivery_note');
        $this->db->delete('scheduled_emails');
    }

    /**
     * Send delivery_note to client
     * @param mixed $id delivery_noteid
     * @param string $template email template to sent
     * @param boolean $attachpdf attach delivery_note pdf or not
     * @return boolean
     */
    public function send_delivery_note_to_client($id, $template_name = '', $attachpdf = true, $cc = '', $manually = false)
    {
        $delivery_note = $this->get($id);

        if ($template_name == '') {
            $template_name = 'delivery_note_send_to_customer';
        }

        $delivery_note_number = format_delivery_note_number($delivery_note->id);

        $emails_sent = [];
        $send_to     = [];

        // Manually is used when sending the delivery_note via add/edit area button Save & Send
        if (!DEFINED('CRON') && $manually === false) {
            $send_to = $this->input->post('sent_to');
        } elseif (isset($GLOBALS['scheduled_email_contacts'])) {
            $send_to = $GLOBALS['scheduled_email_contacts'];
        } else {
            $contacts = $this->clients_model->get_contacts(
                $delivery_note->clientid,
                ['active' => 1, 'delivery_note_emails' => 1]
            );

            foreach ($contacts as $contact) {
                array_push($send_to, $contact['id']);
            }
        }

        $status_auto_updated = false;
        $status_now          = $delivery_note->status;

        if (is_array($send_to) && count($send_to) > 0) {
            $i = 0;

            // Auto update status to sent in case when user sends the delivery_note is with status draft
            if ($status_now == 1) {
                $this->db->where('id', $delivery_note->id);
                $this->db->update(db_prefix() . 'delivery_notes', [
                    'status' => 4,
                ]);
                $status_auto_updated = true;
            }

            if ($attachpdf) {
                $_pdf_delivery_note = $this->get($delivery_note->id);
                set_mailing_constant();
                $pdf = delivery_note_pdf($_pdf_delivery_note);

                $attach = $pdf->Output($delivery_note_number . '.pdf', 'S');
            }

            foreach ($send_to as $contact_id) {
                if ($contact_id != '') {
                    // Send cc only for the first contact
                    if (!empty($cc) && $i > 0) {
                        $cc = '';
                    }

                    $contact = $this->clients_model->get_contact($contact_id);

                    if (!$contact) {
                        continue;
                    }

                    $template = mail_template($template_name, DELIVERY_NOTE_MODULE_NAME, $delivery_note, $contact, $cc);

                    if ($attachpdf) {
                        $hook = hooks()->apply_filters('send_delivery_note_to_customer_file_name', [
                            'file_name' => str_replace('/', '-', $delivery_note_number . '.pdf'),
                            'delivery_note'  => $_pdf_delivery_note,
                        ]);

                        $template->add_attachment([
                            'attachment' => $attach,
                            'filename'   => $hook['file_name'],
                            'type'       => 'application/pdf',
                        ]);
                    }

                    if ($template->send()) {
                        array_push($emails_sent, $contact->email);
                    }
                }
                $i++;
            }
        } else {
            return false;
        }

        if (count($emails_sent) > 0) {
            $this->set_delivery_note_sent($id, $emails_sent);
            hooks()->do_action('delivery_note_sent', $id);

            return true;
        }

        if ($status_auto_updated) {
            // delivery_note not send to customer but the status was previously updated to sent now we need to revert back to new
            $this->db->where('id', $delivery_note->id);
            $this->db->update(db_prefix() . 'delivery_notes', [
                'status' => $status_now,
            ]);
        }

        return false;
    }

    /**
     * All delivery_note activity
     * @param mixed $id delivery_noteid
     * @return array
     */
    public function get_delivery_note_activity($id)
    {
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'delivery_note');
        $this->db->order_by('date', 'asc');

        return $this->db->get(db_prefix() . 'sales_activity')->result_array();
    }

    /**
     * Log delivery_note activity to database
     * @param mixed $id delivery_noteid
     * @param string $description activity description
     */
    public function log_delivery_note_activity($id, $description = '', $client = false, $additional_data = '')
    {
        $staffid   = get_staff_user_id();
        $full_name = get_staff_full_name(get_staff_user_id());
        if (DEFINED('CRON')) {
            $staffid   = '[CRON]';
            $full_name = '[CRON]';
        } elseif ($client == true) {
            $staffid   = null;
            $full_name = '';
        }

        $this->db->insert(db_prefix() . 'sales_activity', [
            'description'     => $description,
            'date'            => date('Y-m-d H:i:s'),
            'rel_id'          => $id,
            'rel_type'        => 'delivery_note',
            'staffid'         => $staffid,
            'full_name'       => $full_name,
            'additional_data' => $additional_data,
        ]);
    }

    /**
     * Updates pipeline order when drag and drop
     * @param mixe $data $_POST data
     * @return void
     */
    public function update_pipeline($data)
    {
        $this->mark_action_status($data['status'], $data['delivery_noteid']);
        AbstractKanban::updateOrder($data['order'], 'pipeline_order', 'delivery_notes', $data['status']);
    }

    /**
     * Get delivery_note unique year for filtering
     * @return array
     */
    public function get_delivery_notes_years()
    {
        return $this->db->query('SELECT DISTINCT(YEAR(date)) as year FROM ' . db_prefix() . 'delivery_notes ORDER BY year DESC')->result_array();
    }

    private function map_shipping_columns($data)
    {
        if (!isset($data['include_shipping'])) {
            foreach ($this->shipping_fields as $_s_field) {
                if (isset($data[$_s_field])) {
                    $data[$_s_field] = null;
                }
            }
            $data['show_shipping_on_delivery_note'] = 1;
            $data['include_shipping']          = 0;
        } else {
            $data['include_shipping'] = 1;
            // set by default for the next time to be checked
            if (isset($data['show_shipping_on_delivery_note']) && ($data['show_shipping_on_delivery_note'] == 1 || $data['show_shipping_on_delivery_note'] == 'on')) {
                $data['show_shipping_on_delivery_note'] = 1;
            } else {
                $data['show_shipping_on_delivery_note'] = 0;
            }
        }

        return $data;
    }

    /**
     * Get list of all staff signatures for the delivery note
     *
     * @param mixed $id The delivery note ID
     * @param mixed $staffid Optional. When provided only return for the specified staff
     * @return array 
     */
    public function get_staff_signatures($id, $staffid = '')
    {
        $table = db_prefix() . 'delivery_note_staff_signatures';
        $this->db->select('*');
        $this->db->where('delivery_noteid', $id);
        if (!empty($staffid)) {
            $this->db->where('staff_id', $staffid);
        }
        $signatures = $this->db->get($table)->result();
        return $signatures;
    }

    /**
     * Add a staff signature to delivery note
     *
     * @param mixed $id The delivery note id
     * @param mixed $staffid
     * @return bool
     */
    public function add_staff_signature($id, $staffid)
    {
        // Remove existing signatures for the staff for this delivery note
        $this->clear_signatures($id, $staffid);

        // Add new signature
        $table = db_prefix() . 'delivery_note_staff_signatures';
        $signature_data = get_acceptance_info_array();
        $signature_data['staff_id'] = $staffid;
        $signature_data['delivery_noteid'] = $id;
        $signature_data['signature_title'] = $this->input->post('signature_title', true);
        $signature_data['datecreated']           = date('Y-m-d H:i:s');

        // Rename to unique file
        $this->load->helper('string');
        $basepath =  get_upload_path_by_type(DELIVERY_NOTE_MODULE_NAME) . $id . '/';
        $original_name = $signature_data['signature'];
        $new_name = time() . '_' . random_string() . '_' . $original_name;
        if (rename($basepath . $original_name, $basepath . $new_name)) {

            $signature_data['signature'] = $new_name;
            if ($this->db->insert($table, $signature_data)) {
                $this->load->helper('file');
                $path = $basepath . $signature_data['signature'];
                $mime_type = get_mime_by_extension($path);
                $signature_url = 'data:' . $mime_type . ';base64,' . base64_encode(file_get_contents($path));
                $this->log_delivery_note_activity($id, 'delivery_note_activity_signed', false, serialize([
                    '<img src="' . $signature_url . '" alt="">',
                ]));

                return true;
            }
        }
        return false;
    }

    /**
     * Add a client signature to delivery note
     *
     * @param mixed $id The delivery note id
     * @return bool
     */
    public function add_client_signature($id)
    {
        // Add new signature
        $table = db_prefix() . 'delivery_notes';
        $signature_data = get_acceptance_info_array();

        // Rename to unique file
        $this->load->helper('string');
        $basepath =  get_upload_path_by_type(DELIVERY_NOTE_MODULE_NAME) . $id . '/';
        $original_name = $signature_data['signature'];
        $new_name = time() . '_' . random_string() . '_' . $original_name;
        if (rename($basepath . $original_name, $basepath . $new_name)) {

            $signature_data['signature'] = $new_name;
            $this->db->where('id', $id);
            if ($this->db->update($table, $signature_data)) {

                // Mark as delivered
                $status = hooks()->apply_filters('delivery_note_add_client_signature_status', 4);
                $this->mark_action_status($status, $id, true);

                $this->load->helper('file');
                $path = $basepath . $signature_data['signature'];
                $mime_type = get_mime_by_extension($path);
                $signature_url = 'data:' . $mime_type . ';base64,' . base64_encode(file_get_contents($path));
                $name = is_client_logged_in() ? get_contact_full_name() : '';
                $name = empty($name) ? $signature_data['acceptance_firstname'] . ' ' . $signature_data['acceptance_lastname'] . ' - ' . $signature_data['acceptance_ip'] : $name;
                $this->log_delivery_note_activity($id, 'delivery_note_activity_signed_by_client', true, serialize([
                    $name,
                    '<img src="' . $signature_url . '" alt="">',
                ]));

                return true;
            }
        }
        return false;
    }

    /**
     * Clear all signature attached to a delivery note
     *
     * @param mixed $id The delivery note ID
     * @param mixed $staffid The staff id - optional. Will only clear for the staff when provided
     * @return bool
     */
    public function clear_signatures($id, $staffid = '')
    {
        $upload_path = get_upload_path_by_type(DELIVERY_NOTE_MODULE_NAME);

        $signatures = $this->get_staff_signatures($id, $staffid);
        foreach ($signatures as $signature) {

            if (!empty($signature->signature)) {
                $path = $upload_path . $id . '/' . $signature->signature;
                if (file_exists($path) && !is_dir($path))
                    unlink($path);
            }
        }

        // Remove alls signatures for the staff for this delivery note
        $table = db_prefix() . 'delivery_note_staff_signatures';
        $this->db->where('delivery_noteid', $id);
        if (!empty($staffid)) {
            $this->db->where('staff_id', $staffid);
        }
        $this->db->delete($table);

        // Remove user signature
        if (!empty($staffid)) {
            $this->log_delivery_note_activity($id, 'delivery_note_activity_staff_signatures_cleared', false, serialize([get_staff_full_name($staffid)]));
            return true;
        }

        // Clear client signature
        $delivery_note = $this->get($id);
        if ($delivery_note) {

            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'delivery_notes', get_acceptance_info_array(true));

            if (!empty($delivery_note->signature)) {
                $path = $upload_path . $id . '/' . $delivery_note->signature;
                if (file_exists($path) && !is_dir($path))
                    unlink($path);
            }

            $this->log_delivery_note_activity($id, 'delivery_note_activity_signatures_cleared', false);

            return true;
        }

        return false;
    }

    /**
     * Add the predefined note when converting from other source
     *
     * @param arra $data
     * @return array
     */
    protected function check_default_info_before_conversion($data)
    {
        $terms = get_option('predefined_terms_delivery_note') ?? '';
        if (!empty(trim($terms)))
            $data['terms']       = $terms;

        $client_note = get_option('predefined_clientnote_delivery_note') ?? '';
        if (!empty(trim($client_note)))
            $data['clientnote']  = $client_note;

        return $data;
    }
	
	/** Δημιουργεί Task για DN (χωρίς assign, μόνο link στην περιγραφή) */
	private function create_task_for_dn(int $dn_id, array $src = []): ?int
	{
		$this->load->model('tasks_model');
		$this->load->model('projects_model');

		$dn = $this->get($dn_id);
		if (!$dn) { return null; }

		// Ημερομηνία DN (SQL & UI)
		$dnDateSql = !empty($dn->date) ? $dn->date : date('Y-m-d'); // π.χ. 2025-09-26
		$dnDateUi  = _d($dnDateSql); // format όπως το datepicker του Perfex, ώστε to_sql_date να δουλέψει σωστά
		log_activity('[DN] Task [dnDateSql: '.$dnDateSql .', dnDateUi: '.$dnDateUi .']');
		$number     = format_delivery_note_number($dn_id);
		$defaultTmpl= 'Delivery Note #{number}';
		$titleTmpl  = get_option('dn_task_title_template') ?: $defaultTmpl;
		$title      = str_ireplace('{number}', $number, $titleTmpl);

		$dnUrl    = admin_url('delivery_notes/client/dn/'.$dn_id. '/' . $dn->hash);
		$priority = (int)(get_option('dn_task_priority') ?: 2);

		// Συσχέτιση: customer ή project
		$rel_type = 'customer';
		$rel_id   = (int)$dn->clientid;
		if ((int)$dn->project_id > 0) {
			$rel_type = 'project';
			$rel_id   = (int)$dn->project_id;
			$project  = $this->projects_model->get($rel_id);
			if ($project && !empty($project->name)) {
				$title = '"'.$project->name.'" - Delivery Note #'.$number;
			}
		}
		
		// Title & links
		$number_str = format_delivery_note_number($dn_id);
		$admin_link = admin_url('delivery_notes/delivery_note/' . $dn_id);

		// DN record
		$dn = $this->get($dn_id);
		

		// Public link (αν χρειάζεται)
		$public_link = site_url('delivery_notes/client/dn/' . $dn_id . '/' . $dn->hash);
		
		$description = "Edit: <a href=\"{$admin_link}\">{$number_str}</a>\n"
					 . "Customer: <a href=\"{$public_link}\">{$public_link}</a>";

		// 1) Δίνουμε start/duedate σε UI format (θα γίνει to_sql_date() στο add)
		$task_data = [
			'name'        => $title,
			'description' => $description,
			'priority'    => $priority,
			'startdate'   => $dnDateUi, // UI format → to_sql_date() στο core
			'duedate'     => $dnDateUi, // ίδιο με το DN
			'rel_type'    => $rel_type,
			'rel_id'      => $rel_id,
			'dateadded'   => date('Y-m-d H:i:s'),
			'addedfrom'   => get_staff_user_id(),
			'status'      => 1, // Not started
		];

		$task_id = $this->tasks_model->add($task_data, false);
		if (!$task_id) {
			return null;
		}

		// 2) ΡΗΤΟ UPDATE: «καρφώνουμε» ξανά την ημερομηνία σε SQL format, ώστε να μη μείνει ποτέ σήμερα.
		// Προσοχή: το tasks.startdate/duedate είναι DATE, όχι DATETIME.
		$this->db->where('id', $task_id)->update(db_prefix().'tasks', [
			'startdate' => $dnDateSql, // YYYY-mm-dd
			'duedate'   => $dnDateSql,
		]);

		// Log
		$this->log_delivery_note_activity($dn_id, 'delivery_note_activity_task_created', false, serialize([$task_id]));

		return (int)$task_id;
	}



	/** Υπολογίζει επόμενη recurring ημερομηνία */
	private function calc_next_dn_date(string $base, int $every, ?string $type): string
	{
		$dt = new DateTime($base);
		$n  = max(1, (int)$every);
		switch ($type) {
			case 'day':   $dt->modify("+{$n} day"); break;
			case 'week':  $dt->modify("+{$n} week"); break;
			case 'month': $dt->modify("+{$n} month"); break;
			case 'year':  $dt->modify("+{$n} year"); break;
			default:      $dt->modify("+{$n} month"); break;
		}
		return $dt->format('Y-m-d');
	}
	/** Καλείται από cron: δημιουργεί instances απ’ τα templates */
	public function cron_generate_recurring(): array
	{
		$out = ['created'=>0,'errors'=>0];
		$today = date('Y-m-d');

		$this->db->where('recurring >', 0);
		$this->db->where('next_recurring_date IS NOT NULL', null, false);
		$this->db->where('next_recurring_date <=', $today);
		$templates = $this->db->get(db_prefix().'delivery_notes')->result();

		foreach ($templates as $tpl) {
			// Respect cycles
			if ((int)$tpl->cycles > 0 && (int)$tpl->cycles >= (int)$tpl->cycles) {
				continue;
			}

			$this->db->trans_start();

			// clone head
			$new = (array)$tpl;
			unset($new['id']);
			$new['datecreated']         = date('Y-m-d H:i:s');
			$new['date']                = $tpl->next_recurring_date ?: date('Y-m-d');
			$new['sent']                = 0;
			$new['datesend']            = null;
			$new['status']              = 1;
			$new['invoiceid']           = null;
			$new['invoiced_date']       = null;
			$new['is_recurring_from']   = $tpl->id;
			$new['last_recurring_date'] = null;
			$new['hash']                = app_generate_hash();

			// “σπάμε” recurring meta στο instance
			$new['recurring']           = 0;
			$new['recurring_type']      = null;
			$new['custom_recurring']    = 0;
			$new['cycles']              = 0;
			$new['cycles']        = 0;
			$new['next_recurring_date'] = null;

			$this->db->insert(db_prefix().'delivery_notes', $new);
			$new_id = (int)$this->db->insert_id();

			if ($new_id) {
				// copy items
				$items = $this->db->where('rel_type','delivery_note')
					->where('rel_id',$tpl->id)
					->get(db_prefix().'itemable')->result_array();
				foreach ($items as $it) {
					unset($it['id']);
					$it['rel_id'] = $new_id;
					$this->db->insert(db_prefix().'itemable', $it);
				}

				// auto task (αν ρύθμιση ενεργή)
				if ((int)get_option('dn_auto_task_on_create') === 1) {
					$this->create_task_for_dn($new_id, []);
				}

				// update template next/last/total
				$next = $this->calc_next_dn_date(
					$tpl->next_recurring_date ?: date('Y-m-d'),
					(int)$tpl->recurring,
					$tpl->recurring_type
				);
				$this->db->where('id', $tpl->id)->update(db_prefix().'delivery_notes', [
					'last_recurring_date' => $tpl->next_recurring_date ?: date('Y-m-d'),
					'next_recurring_date' => $next,
					'cycles'        => (int)$tpl->cycles + 1,
				]);
			}

			$this->db->trans_complete();
			if ($this->db->trans_status() && !empty($new_id)) $out['created']++; else $out['errors']++;
		}

		return $out;
	}

	private function normalize_recurring_fields(array $data): array
	{
		$data['recurring']        = (int)($data['recurring']        ?? 0);
		$data['custom_recurring'] = (int)($data['custom_recurring'] ?? 0);
		$data['recurring_type']   = (string)($data['recurring_type'] ?? 'month');
		$data['cycles']           = (int)($data['cycles']           ?? 0);

		// Αν recurring=0 → reset όλα
		if ($data['recurring'] === 0) {
			$data['custom_recurring'] = 0;
			$data['recurring_type']   = 'month';
			$data['cycles']           = 0;
		}
		return $data;
	}
	public function get_upcoming_recurring_notes($days_ahead = 30)
	{
		$today = new DateTime();
		$future_date = (clone $today)->modify("+{$days_ahead} days");

		$notes = $this->db->where('recurring >', 0)
			->get(db_prefix() . 'delivery_notes')
			->result();

		$results = [];

		foreach ($notes as $note) {
			if ($note->cycles > 0 && $note->cycles >= $note->cycles) {
				continue; // έχει λήξει
			}

			$start_date = new DateTime($note->date);

			for ($i = 0; $i <= $note->cycles; $i++) {
				switch ($note->recurring_type) {
					case 'day':
						$start_date->modify("+{$note->recurring} days");
						break;
					case 'week':
						$start_date->modify("+{$note->recurring} weeks");
						break;
					case 'year':
						$start_date->modify("+{$note->recurring} years");
						break;
					default:
						$start_date->modify("+{$note->recurring} months");
						break;
				}
			}

			$next_date = $start_date;

			if ($next_date >= $today && $next_date <= $future_date) {
				$note->next_date = $next_date->format('Y-m-d');
				$results[] = $note;
			}
		}

		return $results;
	}
	
	/**
	 * Δημιουργεί ΑΜΕΣΑ το επόμενο recurring Delivery Note όταν το parent DN γίνει Delivered.
	 * Επιστρέφει το child_id ή null αν δεν δημιουργήθηκε.
	 */
	public function maybe_generate_next_recurring(int $parent_id): ?int
	{
		$tpl = $this->get($parent_id);
		if (!$tpl) { log_message('error', "[DN] maybe_generate_next_recurring: parent $parent_id not found"); return null; }

		log_message('error', "[DN] maybe_generate_next_recurring: tpl id={$tpl->id} rec={$tpl->recurring} type={$tpl->recurring_type} cycles={$tpl->cycles} total={$tpl->total_cycles} next={$tpl->next_recurring_date} date={$tpl->date}");

		// Πρέπει να είναι recurring & να μην έχουν εξαντληθεί οι κύκλοι
		if ((int)$tpl->recurring <= 0) { log_message('error', "[DN] not recurring"); return null; }
		if ((int)$tpl->cycles > 0 && (int)$tpl->total_cycles >= (int)$tpl->cycles) { log_message('error', "[DN] cycles exhausted"); return null; }

		// Βασική ημερομηνία για το επόμενο instance
		$baseNext = $tpl->next_recurring_date ?: $this->calc_next_dn_date(
			$tpl->date,
			(int)$tpl->recurring,
			(string)$tpl->recurring_type
		);
		log_message('error', "[DN] baseNext=$baseNext");

		// Anti-dup: υπάρχει ήδη παιδί για ίδια ημερομηνία;
		$exists = $this->db->select('id')->from(db_prefix().'delivery_notes')
			->where('is_recurring_from', $tpl->id)
			->where('date', $baseNext)
			->limit(1)->get()->row();
		if ($exists) { log_message('error', "[DN] child already exists id={$exists->id}"); return (int)$exists->id; }

		// Ετοιμασία δεδομένων για add() ώστε να παίξουν numbering, hooks κ.λπ.
		$new = [
			'clientid'    => $tpl->clientid,
			'project_id'  => $tpl->project_id,
			'number'      => get_option('next_delivery_note_number'),
			'date'        => _d($baseNext), // Perfex expects formatted date
			'show_quantity_as' => $tpl->show_quantity_as,
			'currency'    => $tpl->currency,
			'subtotal'    => $tpl->subtotal,
			'total'       => $tpl->total,
			'adminnote'   => '',
			'adjustment'  => $tpl->adjustment,
			'discount_percent' => $tpl->discount_percent,
			'discount_total'   => $tpl->discount_total,
			'discount_type'    => $tpl->discount_type,
			'terms'       => $tpl->terms,
			'sale_agent'  => $tpl->sale_agent,
			'reference_no'=> $tpl->reference_no,
			'billing_street'   => clear_textarea_breaks($tpl->billing_street),
			'billing_city'     => $tpl->billing_city,
			'billing_state'    => $tpl->billing_state,
			'billing_zip'      => $tpl->billing_zip,
			'billing_country'  => $tpl->billing_country,
			'shipping_street'  => clear_textarea_breaks($tpl->shipping_street),
			'shipping_city'    => $tpl->shipping_city,
			'shipping_state'   => $tpl->shipping_state,
			'shipping_zip'     => $tpl->shipping_zip,
			'shipping_country' => $tpl->shipping_country,
			'include_shipping' => $tpl->include_shipping,
			'show_shipping_on_delivery_note' => $tpl->show_shipping_on_delivery_note,
			'status'      => 1, // Draft
			'clientnote'  => $tpl->clientnote,
		];

		// Items ως post data για add()
		$new['newitems'] = [];
		$custom_fields_items = get_custom_fields('items');
		$items = get_items_by_type('delivery_note', $tpl->id);
		$k = 1;
		foreach ($items as $item) {
			$row = [
				'description'      => $item['description'],
				'long_description' => clear_textarea_breaks($item['long_description']),
				'qty'              => $item['qty'],
				'unit'             => $item['unit'],
				'rate'             => $item['rate'],
				'order'            => $item['item_order'],
				'taxname'          => [],
			];
			foreach (get_delivery_note_item_taxes($item['id']) as $tax) {
				$row['taxname'][] = $tax['taxname'];
			}
			foreach ($custom_fields_items as $cf) {
				$row['custom_fields']['items'][$cf['id']] = get_custom_field_value($item['id'], $cf['id'], 'items', false);
				if (!defined('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST')) {
					define('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST', true);
				}
			}
			$new['newitems'][$k++] = $row;
		}

		// Δημιουργία παιδιού
		$child_id = $this->add($new);
		if (!$child_id) { log_message('error', "[DN] add() failed to create child"); return null; }

		// Συνδέσεις & καθάρισμα recurring meta στο παιδί
		$this->db->where('id', $child_id)->update(db_prefix().'delivery_notes', [
			'is_recurring_from'   => $tpl->id,
			'recurring'           => 0,
			'recurring_type'      => null,
			'custom_recurring'    => 0,
			'cycles'              => 0,
			'total_cycles'        => 0,
			'next_recurring_date' => null,
			'last_recurring_date' => null,
			'invoiceid'           => null,
			'invoiced_date'       => null,
			'sent'                => 0,
			'datesend'            => null,
			'signature'           => null,
			'acceptance_firstname'=> null,
			'acceptance_lastname' => null,
			'acceptance_email'    => null,
			'acceptance_date'     => null,
			'acceptance_ip'       => null,
		]);

		// Ενημέρωση parent (κύκλοι & επόμενη ημερομηνία)
		$next = $this->calc_next_dn_date($baseNext, (int)$tpl->recurring, (string)$tpl->recurring_type);
		$parentUpdate = [
			'last_recurring_date' => $baseNext,
			'total_cycles'        => (int)$tpl->total_cycles + 1,
			'next_recurring_date' => $next,
		];
		if ((int)$tpl->cycles > 0 && ((int)$tpl->total_cycles + 1) >= (int)$tpl->cycles) {
			$parentUpdate['next_recurring_date'] = null;
		}
		$this->db->where('id', $tpl->id)->update(db_prefix().'delivery_notes', $parentUpdate);

		$this->log_delivery_note_activity($tpl->id, 'delivery_note_activity_recurring_child_created', false, serialize([$child_id, $baseNext]));
		$this->log_delivery_note_activity($child_id, 'delivery_note_activity_recurring_child_of', false, serialize([$tpl->id]));

		// Προαιρετικό: αυτόματο task
		if ((int)get_option('dn_auto_task_on_create') === 1) {
			$this->create_task_for_dn($child_id, []);
		}

		log_message('error', "[DN] child created id={$child_id} for parent={$tpl->id}");
		return $child_id;
	}



}