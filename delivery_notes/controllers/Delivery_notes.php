<?php

use app\modules\delivery_notes\services\DeliveryNotePipeline;

defined('BASEPATH') or exit('No direct script access allowed');

class Delivery_notes extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('delivery_notes_model');
        $this->load->model('invoices_model');
        $this->load->model('estimates_model');
        $this->load->model('staff_model');
        $this->load->model('tasks_model');
    }

    /* Default -> list */
    public function index($id = '')
    {
        $this->list_delivery_notes($id);
    }

    /* List all delivery notes */
    public function list_delivery_notes($id = '')
    {
        if (staff_cant('view', 'delivery_notes') && staff_cant('view_own', 'delivery_notes') && get_option('allow_staff_view_delivery_notes_assigned') == '0') {
            access_denied('delivery_notes');
        }

        $isPipeline = $this->session->userdata('delivery_note_pipeline') == 'true';
        $data['delivery_note_statuses'] = $this->delivery_notes_model->get_statuses();

        if ($isPipeline && !$this->input->get('status') && !$this->input->get('filter')) {
            $data['title']           = _l('delivery_notes_pipeline');
            $data['bodyclass']       = 'delivery_notes-pipeline delivery_notes-total-manual identity-confirmation';
            $data['switch_pipeline'] = false;
            $data['delivery_noteid'] = is_numeric($id) ? $id : $this->session->flashdata('delivery_noteid');

            $this->load->view('admin/delivery_notes/pipeline/manage', $data);
            return;
        }

        // If pipeline active and filter applied, just init pipeline state
        if (($this->input->get('status') || $this->input->get('filter')) && $isPipeline) {
            $this->pipeline(0, true);
        }

        $data['delivery_noteid']            = $id;
        $data['switch_pipeline']            = true;
        $data['title']                      = _l('delivery_notes');
        $data['bodyclass']                  = 'delivery_notes-total-manual identity-confirmation';
        $data['delivery_notes_years']       = $this->delivery_notes_model->get_delivery_notes_years();
        $data['delivery_notes_sale_agents'] = $this->delivery_notes_model->get_sale_agents();

        $this->load->view('admin/delivery_notes/manage', $data);
    }

    public function table($clientid = '')
    {
        if (!has_permission('delivery_notes', '', 'view') && !has_permission('delivery_notes', '', 'view_own') && get_option('allow_staff_view_delivery_notes_assigned') == '0') {
            ajax_access_denied();
        }

        $this->app->get_table_data(module_views_path(DELIVERY_NOTE_MODULE_NAME, 'admin/tables/delivery_notes'), [
            'clientid' => $clientid,
        ]);
    }

    /* Add new or update */
    public function delivery_note($id = '')
    {
        if ($this->input->post()) {
            $delivery_note_data = $this->input->post();

            // normalize recurring from UI (recurring_chooser, custom_recurring, recurring, recurring_type, cycles)
            $delivery_note_data = $this->normalize_recurring_from_post($delivery_note_data);

            $save_and_send_later = false;
            if (isset($delivery_note_data['save_and_send_later'])) {
                unset($delivery_note_data['save_and_send_later']);
                $save_and_send_later = true;
            }

            if ($id == '') {
                if (staff_cant('create', 'delivery_notes')) {
                    access_denied('delivery_notes');
                }

                $id = $this->delivery_notes_model->add($delivery_note_data);

                if ($id) {
                    set_alert('success', _l('added_successfully', _l('delivery_note')));

                    // Auto-create TASK (link-only in description)
                    $this->create_task_for_delivery_note($id);

                    $redUrl = admin_url('delivery_notes/list_delivery_notes/' . $id);

                    if ($save_and_send_later) {
                        $this->session->set_userdata('send_later', true);
                    }

                    redirect(!$this->set_delivery_note_pipeline_autoload($id) ? $redUrl : admin_url('delivery_notes/list_delivery_notes/'));
                }
            } else {
                if (staff_cant('edit', 'delivery_notes')) {
                    access_denied('delivery_notes');
                }

                $success = $this->delivery_notes_model->update($delivery_note_data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('delivery_note')));
                }

                if ($this->set_delivery_note_pipeline_autoload($id)) {
                    redirect(admin_url('delivery_notes/list_delivery_notes/'));
                } else {
                    redirect(admin_url('delivery_notes/list_delivery_notes/' . $id));
                }
            }
        }

        if ($id == '') {
            $title = _l('create_new_delivery_note');
        } else {
            $delivery_note = $this->delivery_notes_model->get($id);

            if (!$delivery_note || !user_can_view_delivery_note($id)) {
                blank_page(_l('delivery_note_not_found'));
            }

            $data['delivery_note'] = $delivery_note;
            $data['edit']          = true;
            $title                 = _l('edit', _l('delivery_note_lowercase'));
        }

        if ($this->input->get('customer_id')) {
            $data['customer_id'] = $this->input->get('customer_id');
        }

        if ($this->input->get('delivery_note_request_id')) {
            $data['delivery_note_request_id'] = $this->input->get('delivery_note_request_id');
        }

        $this->load->model('taxes_model');
        $data['taxes']          = $this->taxes_model->get();
        $this->load->model('currencies_model');
        $data['currencies']     = $this->currencies_model->get();
        $data['base_currency']  = $this->currencies_model->get_base_currency();
        $this->load->model('invoice_items_model');

        $data['ajaxItems'] = false;
        if (total_rows(db_prefix() . 'items') <= ajax_on_total_items()) {
            $data['items'] = $this->invoice_items_model->get_grouped();
        } else {
            $data['items']     = [];
            $data['ajaxItems'] = true;
        }
        $data['items_groups']         = $this->invoice_items_model->get_groups();
        $data['staff']                = $this->staff_model->get('', ['active' => 1]);
        $data['delivery_note_statuses'] = $this->delivery_notes_model->get_statuses();
        $data['title']                = $title;

        $this->load->view('admin/delivery_notes/delivery_note', $data);
    }

    /* Persist prefix change (popover) */
    public function update_number_settings($id)
    {
        $response = ['success' => false, 'message' => ''];
        if (staff_can('edit',  'delivery_notes')) {
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'delivery_notes', ['prefix' => $this->input->post('prefix')]);
            if ($this->db->affected_rows() > 0) {
                $response['success'] = true;
                $response['message'] = _l('updated_successfully', _l('delivery_note'));
            }
        }
        echo json_encode($response);
        die;
    }

    public function validate_delivery_note_number()
    {
        $isedit          = $this->input->post('isedit');
        $number          = $this->input->post('number');
        $date            = $this->input->post('date');
        $original_number = $this->input->post('original_number');
        $number          = trim($number);
        $number          = ltrim($number, '0');

        if ($isedit == 'true' && $number == $original_number) {
            echo json_encode(true);
            die;
        }

        if (total_rows(db_prefix() . 'delivery_notes', [
            'YEAR(date)' => date('Y', strtotime(to_sql_date($date))),
            'number'     => $number,
        ]) > 0) {
            echo 'false';
        } else {
            echo 'true';
        }
    }

    public function delete_attachment($id)
    {
        $file = $this->misc_model->get_file($id);
        if ($file->staffid == get_staff_user_id() || is_admin()) {
            echo $this->delivery_notes_model->delete_attachment($id);
        } else {
            header('HTTP/1.0 400 Bad error');
            echo _l('access_denied');
            die;
        }
    }

    /* Ajax slide-in preview */
    public function get_delivery_note_data_ajax($id, $to_return = false)
    {
        if (staff_cant('view', 'delivery_notes') && staff_cant('view_own', 'delivery_notes') && get_option('allow_staff_view_delivery_notes_assigned') == '0') {
            echo _l('access_denied');
            die;
        }

        if (!$id) {
            die('No delivery_note found');
        }

        $delivery_note = $this->delivery_notes_model->get($id);

        if (!$delivery_note || !user_can_view_delivery_note($id)) {
            echo _l('delivery_note_not_found');
            die;
        }

        $delivery_note->date = _d($delivery_note->date);

        if ($delivery_note->invoiceid !== null) {
            $this->load->model('invoices_model');
            $delivery_note->invoice = $this->invoices_model->get($delivery_note->invoiceid);
        }

        $template_name = 'delivery_note_send_to_customer';
        $data = my_prepare_mail_preview_data($template_name, $delivery_note->clientid, [DELIVERY_NOTE_MODULE_NAME]);

        $data['activity']                 = $this->delivery_notes_model->get_delivery_note_activity($id);
        $data['delivery_note']            = $delivery_note;
        $data['members']                  = $this->staff_model->get('', ['active' => 1]);
        $data['delivery_note_statuses']   = $this->delivery_notes_model->get_statuses();
        $data['totalNotes']               = total_rows(db_prefix() . 'notes', ['rel_id' => $id, 'rel_type' => 'delivery_note']);
        $data['has_signature']            = !empty($delivery_note->signature) || !empty($delivery_note->staff_signatures);
        $data['staff_signature']          = $this->delivery_notes_model->get_staff_signatures($id, get_staff_user_id())[0] ?? '';
        $data['send_later']               = false;

        if ($this->session->has_userdata('send_later')) {
            $data['send_later'] = true;
            $this->session->unset_userdata('send_later');
        }

        if ($to_return == false) {
            $this->load->view('admin/delivery_notes/delivery_note_preview_template', $data);
        } else {
            return $this->load->view('admin/delivery_notes/delivery_note_preview_template', $data, true);
        }
    }

    public function add_note($rel_id)
    {
        if ($this->input->post() && user_can_view_delivery_note($rel_id)) {
            $this->misc_model->add_note($this->input->post(), 'delivery_note', $rel_id);
            echo $rel_id;
        }
    }

    public function get_notes($id)
    {
        if (user_can_view_delivery_note($id)) {
            $data['notes'] = $this->misc_model->get_notes($id, 'delivery_note');
            $this->load->view('admin/includes/sales_notes_template', $data);
        }
    }

    public function mark_action_status($status, $id)
    {
        if (staff_cant('edit', 'delivery_notes')) {
            access_denied('delivery_notes');
        }
        $success = $this->delivery_notes_model->mark_action_status($status, $id);

        if ($success) {
            set_alert('success', _l('delivery_note_status_changed_success'));
        } else {
            set_alert('danger', _l('delivery_note_status_changed_fail'));
        }
        if ($this->set_delivery_note_pipeline_autoload($id)) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(admin_url('delivery_notes/list_delivery_notes/' . $id));
        }
    }

    /* Email */
    public function send_to_email($id)
    {
        $canView = user_can_view_delivery_note($id);
        if (!$canView) {
            access_denied('delivery_notes');
        } else {
            if (staff_cant('view', 'delivery_notes') && staff_cant('view_own', 'delivery_notes') && $canView == false) {
                access_denied('delivery_notes');
            }
        }

        try {
            $success = $this->delivery_notes_model->send_delivery_note_to_client($id, '', $this->input->post('attach_pdf'), $this->input->post('cc'));
        } catch (Exception $e) {
            $message = $e->getMessage();
            echo $message;
            if (strpos($message, 'Unable to get the size of the image') !== false) {
                show_pdf_unable_to_get_image_size_error();
            }
            die;
        }

        load_admin_language();
        if ($success) {
            set_alert('success', _l('delivery_note_sent_to_client_success'));
        } else {
            set_alert('danger', _l('delivery_note_sent_to_client_fail'));
        }
        if ($this->set_delivery_note_pipeline_autoload($id)) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(admin_url('delivery_notes/list_delivery_notes/' . $id));
        }
    }

    /* Convert flows (unchanged) */
    public function convert_to_invoice($id)
    {
        if (staff_cant('create', 'invoices')) {
            access_denied('invoices');
        }
        if (!$id) {
            die('No delivery_note found');
        }
        $draft_invoice = $this->input->get('save_as_draft') ? true : false;
        $invoiceid = $this->delivery_notes_model->convert_to_invoice($id, false, $draft_invoice);
        if ($invoiceid) {
            set_alert('success', _l('delivery_note_convert_to_invoice_successfully'));
            redirect(admin_url('invoices/list_invoices/' . $invoiceid));
        } else {
            if ($this->session->has_userdata('delivery_note_pipeline') && $this->session->userdata('delivery_note_pipeline') == 'true') {
                $this->session->set_flashdata('delivery_noteid', $id);
            }
            if ($this->set_delivery_note_pipeline_autoload($id)) {
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                redirect(admin_url('delivery_notes/list_delivery_notes/' . $id));
            }
        }
    }

    public function convert_from_estimate($estimateid)
    {
        if (staff_cant('create', 'delivery_notes')) {
            access_denied('delivery_notes');
        }
        if (!$estimateid) {
            die('No estimate found');
        }
        $status = $this->input->get('save_as_new') ? 1 : 4;
        $new_id = $this->delivery_notes_model->convert_from_estimate($estimateid, $status);
        if ($new_id) {
            set_alert('success', _l('estimate_convert_to_delivery_note_successfully'));
            redirect(admin_url('delivery_notes/delivery_note/' . $new_id));
        }
        set_alert('danger', _l('estimate_convert_to_delivery_note_fail'));
        if ($this->set_delivery_note_pipeline_autoload($estimateid)) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(admin_url('estimates/estimate/' . $estimateid));
        }
    }

    public function convert_from_purchase_order($purchase_orderid)
    {
        if (staff_cant('create', 'delivery_notes')) {
            access_denied('delivery_notes');
        }
        if (!$purchase_orderid) {
            die('No purchase order found');
        }
        $status = $this->input->get('save_as_new') ? 1 : 4;
        $new_id = $this->delivery_notes_model->convert_from_purchase_order($purchase_orderid, $status);
        if ($new_id) {
            set_alert('success', _l('purchase_order_convert_to_delivery_note_successfully'));
            redirect(admin_url('delivery_notes/delivery_note/' . $new_id));
        }
        set_alert('danger', _l('purchase_order_convert_to_delivery_note_fail'));
        if ($this->set_delivery_note_pipeline_autoload($purchase_orderid)) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(admin_url('purchase_orders/purchase_order/' . $purchase_orderid));
        }
    }

    public function convert_from_invoice($invoiceid)
    {
        if (staff_cant('create', 'delivery_notes')) {
            access_denied('delivery_notes');
        }
        if (!$invoiceid) {
            die('No invoice found');
        }
        $status = $this->input->get('save_as_new') ? 1 : 4;
        $new_id = $this->delivery_notes_model->convert_from_invoice($invoiceid, $status);
        if ($new_id) {
            set_alert('success', _l('invoice_convert_to_delivery_note_successfully'));
            redirect(admin_url('delivery_notes/delivery_note/' . $new_id));
        }
        set_alert('danger', _l('invoice_convert_to_delivery_note_fail'));
        if ($this->set_delivery_note_pipeline_autoload($invoiceid)) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(admin_url('invoices/invoice/' . $invoiceid));
        }
    }

    public function copy($id)
    {
        if (staff_cant('create', 'delivery_notes')) {
            access_denied('delivery_notes');
        }
        if (!$id) {
            die('No delivery_note found');
        }
        $new_id = $this->delivery_notes_model->copy($id);
        if ($new_id) {
            set_alert('success', _l('delivery_note_copied_successfully'));
            if ($this->set_delivery_note_pipeline_autoload($new_id)) {
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                redirect(admin_url('delivery_notes/delivery_note/' . $new_id));
            }
        }
        set_alert('danger', _l('delivery_note_copied_fail'));
        if ($this->set_delivery_note_pipeline_autoload($id)) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(admin_url('delivery_notes/delivery_note/' . $id));
        }
    }

    /* Delete */
    public function delete($id)
    {
        if (staff_cant('delete', 'delivery_notes')) {
            access_denied('delivery_notes');
        }
        if (!$id) {
            redirect(admin_url('delivery_notes/list_delivery_notes'));
        }
        $success = $this->delivery_notes_model->delete($id);
        if (is_array($success)) {
            set_alert('warning', _l('is_invoiced_delivery_note_delete_error'));
        } elseif ($success == true) {
            set_alert('success', _l('deleted', _l('delivery_note')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('delivery_note_lowercase')));
        }
        redirect(admin_url('delivery_notes/list_delivery_notes'));
    }

    public function clear_acceptance_info($id)
    {
        if (is_admin()) {
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'delivery_notes', get_acceptance_info_array(true));
        }

        redirect(admin_url('delivery_notes/list_delivery_notes/' . $id));
    }

    /* PDF */
    public function pdf($id)
    {
        $canView = user_can_view_delivery_note($id);
        if (!$canView) {
            access_denied('Delivery_note');
        } else {
            if (staff_cant('view', 'delivery_notes') && staff_cant('view_own', 'delivery_notes') && $canView == false) {
                access_denied('Delivery_note');
            }
        }
        if (!$id) {
            redirect(admin_url('delivery_notes/list_delivery_notes'));
        }
        $delivery_note        = $this->delivery_notes_model->get($id);
        $delivery_note_number = format_delivery_note_number($delivery_note->id);

        try {
            $pdf = delivery_note_pdf($delivery_note);
        } catch (Exception $e) {
            $message = $e->getMessage();
            echo $message;
            if (strpos($message, 'Unable to get the size of the image') !== false) {
                show_pdf_unable_to_get_image_size_error();
            }
            die;
        }

        $type = $this->input->get('output_type') ?: 'D';
        if ($this->input->get('print')) {
            $type = 'I';
        }

        $fileNameHookData = hooks()->apply_filters('delivery_note_file_name_admin_area', [
            'file_name'   => mb_strtoupper(slug_it($delivery_note_number)) . '.pdf',
            'delivery_note' => $delivery_note,
        ]);

        $pdf->Output($fileNameHookData['file_name'], $type);
    }

    /* Pipeline */
    public function get_pipeline()
    {
        if (staff_can('view',  'delivery_notes') || staff_can('view_own',  'delivery_notes') || get_option('allow_staff_view_delivery_notes_assigned') == '1') {
            $data['delivery_note_statuses'] = $this->delivery_notes_model->get_statuses();
            $this->load->view('admin/delivery_notes/pipeline/pipeline', $data);
        }
    }

    public function pipeline_open($id)
    {
        $canView = user_can_view_delivery_note($id);
        if (!$canView) {
            access_denied('Delivery_note');
        } else {
            if (staff_cant('view', 'delivery_notes') && staff_cant('view_own', 'delivery_notes') && $canView == false) {
                access_denied('Delivery_note');
            }
        }

        $data['id']           = $id;
        $data['delivery_note'] = $this->get_delivery_note_data_ajax($id, true);
        $this->load->view('admin/delivery_notes/pipeline/delivery_note', $data);
    }

    public function update_pipeline()
    {
        if (staff_can('edit',  'delivery_notes')) {
            $this->delivery_notes_model->update_pipeline($this->input->post());
        }
    }

    public function pipeline($set = 0, $manual = false)
    {
        $this->session->set_userdata(['delivery_note_pipeline' => $set == 1 ? 'true' : 'false']);
        if ($manual == false) {
            redirect(admin_url('delivery_notes/list_delivery_notes'));
        }
    }

    public function pipeline_load_more()
    {
        $status = $this->input->get('status');
        $page   = $this->input->get('page');

        $delivery_notes = (new DeliveryNotePipeline($status))
            ->search($this->input->get('search'))
            ->sortBy($this->input->get('sort_by'), $this->input->get('sort'))
            ->page($page)->get();

        foreach ($delivery_notes as $delivery_note) {
            $this->load->view('admin/delivery_notes/pipeline/_kanban_card', [
                'delivery_note' => $delivery_note,
                'status'        => $status,
            ]);
        }
    }

    public function set_delivery_note_pipeline_autoload($id)
    {
        if ($id == '') {
            return false;
        }
        if ($this->session->has_userdata('delivery_note_pipeline') && $this->session->userdata('delivery_note_pipeline') == 'true') {
            $this->session->set_flashdata('delivery_noteid', $id);
            return true;
        }
        return false;
    }
	public function zip_delivery_notes($id)
    {
        $has_permission_view = staff_can('view',  'delivery_notes');
        if (
            !$has_permission_view && staff_cant('view_own', 'delivery_notes')
            && get_option('allow_staff_view_delivery_notes_assigned') == '0'
        ) {
            access_denied('Zip Customer delivery notes');
        }

        if ($this->input->post()) {
            $this->load->library('delivery_notes_bulk_pdf_export', [
                'export_type'       => 'delivery_notes',
                'status'            => $this->input->post('delivery_note_zip_status'),
                'date_from'         => $this->input->post('zip-from'),
                'date_to'           => $this->input->post('zip-to'),
                'redirect_on_error' => admin_url('clients/client/' . $id . '?group=delivery_notes'),
            ]);

            $this->delivery_notes_bulk_pdf_export->set_client_id($id);
            $this->delivery_notes_bulk_pdf_export->in_folder($this->input->post('file_name'));
            $this->delivery_notes_bulk_pdf_export->export();
        }
    }
    
	/**
     * Add signature
     *
     * @param mixed $id
     * @return void
     */
    public function append_signature($id)
    {
        $has_permission_view = staff_can('sign',  'delivery_notes');
        if (
            !$has_permission_view
        ) {
            access_denied(_l('delivery_note_append_signature'));
        }

        if ($this->input->post() && !empty($id)) {
            $base_dir = get_upload_path_by_type(DELIVERY_NOTE_MODULE_NAME);
            _maybe_create_upload_path($base_dir);
            process_digital_signature_image($this->input->post('signature', false), $base_dir . $id);

            $staffid = get_staff_user_id();

            if ($this->delivery_notes_model->add_staff_signature($id, $staffid))
                set_alert('success', _l('document_signed_successfully'));
        }
        return redirect(admin_url('delivery_notes/list_delivery_notes/' . $id));
    }
	/**
     * Clear signatures
     *
     * @param mixed $id
     * @return void
     */
    public function clear_signature($id)
    {

        if (!empty($id) && staff_can('delete',  'delivery_notes')) {
            $this->delivery_notes_model->clear_signatures($id);
        }

        return redirect(admin_url('delivery_notes/list_delivery_notes/' . $id));
    }

    /**
     * Show batch convert to invoice modal
     *
     * @return void
     */
    public function batch_invoice_modal()
    {
        $ids = $this->input->post('ids');
        if (!empty($ids)) {
            $ids = array_map('intval', $ids);
            $this->delivery_notes_model->db->where_in(db_prefix() . 'delivery_notes.id', $ids);
        }

        $this->delivery_notes_model->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid = ' . db_prefix() . 'delivery_notes.clientid', 'LEFT');
        $this->delivery_notes_model->db->where_not_in('status', [3, 4]); // excluded declined and delivered
        $data['delivery_notes'] = $this->delivery_notes_model->get();
        $data['customers'] = [];
        if (!empty($data['delivery_notes'])) {
            $data['customers'] = $this->db->select('userid,' . get_sql_select_client_company())
                ->where_in('userid', collect($data['delivery_notes'])->pluck('clientid')->toArray())
                ->get(db_prefix() . 'clients')->result();
        }
        $this->load->view('admin/delivery_notes/batch_delivery_note_modal', $data);
    }

    public function add_batch_delivery_to_invoice()
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        if (staff_cant('create', 'invoices')) {
            access_denied('Create Invoice');
        }

        $data = $this->input->post();

        $invoiceIds = [];
        $batch_single_draft = [];
        $batch_single_unpaid = [];

        foreach ($data['delivery_note'] as $row) {
            if (empty($row['delivery_noteid']) || empty($row['mode']) || !in_array($row['mode'] ?? '', ['draft', 'unpaid', 'draft-single', 'unpaid-single'])) {
                continue;
            }

            $id = $row['delivery_noteid'];

            if ($row['mode'] == 'draft-single') {
                $batch_single_draft[] = $id;
                continue;
            }
            if ($row['mode'] == 'unpaid-single') {
                $batch_single_unpaid[] = $id;
                continue;
            }

            $draft_invoice = $row['mode'] == 'draft';
            $invoiceid = $this->delivery_notes_model->convert_to_invoice($id, false, $draft_invoice);
            if ($invoiceid) {
                $invoiceIds[] = $invoiceid;
            }
        }

        if (!empty($batch_single_draft)) {
            $invoiceid = $this->delivery_notes_model->convert_many_to_invoice($batch_single_draft, false, true);
            $invoiceIds[] = $invoiceid;
        }

        if (!empty($batch_single_unpaid)) {
            $invoiceid = $this->delivery_notes_model->convert_many_to_invoice($batch_single_unpaid, false, false);
            $invoiceIds[] = $invoiceid;
        }

        $totalAdded = count($invoiceIds);
        if ($totalAdded > 0) {
            set_alert('success', $totalAdded . ' ' . _l('delivery_note_convert_to_invoice_successfully'));
            return redirect(admin_url('invoices/list_invoices/' . end($invoiceIds)));
        }

        return redirect(admin_url('delivery_notes'));
    }

    /* ---- Helpers ---- */

    /**
     * Normalize recurring UI values to DB-ready values
     * - recurring_chooser = '0' | '1'..'12' | 'custom'
     * - if '0' => recurring=0, custom_recurring=0, recurring_type='month', cycles=0
     * - if 'custom' => custom_recurring=1, recurring>=1, recurring_type from post
     * - else => custom_recurring=0, recurring=(int)chooser, recurring_type='month'
     */
    private function normalize_recurring_from_post(array $data): array
    {
        $chooser          = $data['recurring_chooser'] ?? '0';
        $recurring        = (int)($data['recurring'] ?? 0);
        $recurring_type   = $data['recurring_type'] ?? 'month';
        $custom_recurring = (int)($data['custom_recurring'] ?? 0);
        $cycles           = (int)($data['cycles'] ?? 0);

        if ($chooser === '0') {
            $data['recurring']        = 0;
            $data['custom_recurring'] = 0;
            $data['recurring_type']   = 'month';
            $data['cycles']           = 0;
        } elseif ($chooser === 'custom') {
            $data['custom_recurring'] = 1;
            $data['recurring']        = max(1, $recurring);
            $data['recurring_type']   = in_array($recurring_type, ['day','week','month','year']) ? $recurring_type : 'month';
            $data['cycles']           = max(0, $cycles); // 0 = infinity
        } else {
            // preset months
            $months = (int)$chooser;
            if ($months < 1 || $months > 12) { $months = 1; }
            $data['custom_recurring'] = 0;
            $data['recurring']        = $months;
            $data['recurring_type']   = 'month';
            $data['cycles']           = max(0, $cycles);
        }

        // καθάρισε βοήθημα UI
        unset($data['recurring_chooser']);

        return $data;
    }

    /**
	 * Create Task after DN creation, attach link in description
	 */
	private function create_task_for_delivery_note(int $delivery_note_id): void
	{
		$this->load->model('tasks_model');

		// Title & links
		$number_str = format_delivery_note_number($delivery_note_id);
		$admin_link = admin_url('delivery_notes/delivery_note/' . $delivery_note_id);

		// DN record
		$dn = $this->delivery_notes_model->get($delivery_note_id);
		if (!$dn) { return; }

		// Public link (αν χρειάζεται)
		$public_link = site_url('delivery_notes/client/dn/' . $delivery_note_id . '/' . $dn->hash);

		// Ημερομηνία DN
		$dnDateSql = !empty($dn->date) ? $dn->date : date('Y-m-d'); // SQL format
		$dnDateUi  = _d($dnDateSql); // UI/datepicker format (ώστε to_sql_date να δουλέψει σίγουρα)

		$description = "Edit: <a href=\"{$admin_link}\">{$number_str}</a>\n"
					 . "Customer: <a href=\"{$public_link}\">{$public_link}</a>";

		// Συσχέτιση: project αν υπάρχει, αλλιώς delivery_note (ή 'customer' αν προτιμάς)
		$rel_type = 'delivery_note';
		$rel_id   = $delivery_note_id;
		$title    = 'Delivery Note ' . $number_str;

		if ((int)$dn->project_id > 0) {
			$this->load->model('projects_model');
			$project = $this->projects_model->get((int)$dn->project_id);
			$rel_type = 'project';
			$rel_id   = (int)$dn->project_id;
			$title    = ($project && !empty($project->name))
				? '"'.$project->name.'" - Delivery Note '.$number_str
				: $title;
		}

		// 1) δίνουμε start/duedate σε UI format (θα γίνει to_sql_date στο add)
		$task_data = [
			'name'        => $title,
			'rel_type'    => $rel_type,
			'rel_id'      => $rel_id,
			'priority'    => 2,
			'startdate'   => $dnDateUi,  // <-- Ημερομηνία DN (όχι σήμερα)
			'duedate'     => $dnDateUi,  // (προαιρετικά: ίδια με start)
			'status'      => 1,          // Not started
			'description' => $description,
			'dateadded'   => date('Y-m-d H:i:s'),
			'addedfrom'   => get_staff_user_id(),
		];

		try {
			$task_id = $this->tasks_model->add($task_data);
			if ($task_id) {
				// 2) ΡΗΤΟ UPDATE: «καρφώνουμε» την SQL ημερομηνία για να μην υπάρξει ποτέ fallback στο σήμερα
				$this->db->where('id', $task_id)->update(db_prefix().'tasks', [
					'startdate' => $dnDateSql, // DATE column (χωρίς ώρα)
					'duedate'   => $dnDateSql,
				]);
			}
		} catch (\Throwable $e) {
			log_activity('DN Task auto-create failed for DN#'.$delivery_note_id.': '.$e->getMessage());
		}
	}


    /* --- Migrations που είχες, αφήνονται ως έχουν (optional to keep) --- */
    public function migrate_from_waybills()
    {
        if (!is_staff_logged_in()) { show_404(); }
        @ini_set('memory_limit', '1024M');
        @ini_set('max_execution_time', '0');
        $dryRun = (int)$this->input->get('dry_run') === 1;
        $this->output->set_content_type('application/json');
        $this->db->trans_begin();
        $report = [
            'dry_run'                => $dryRun,
            'waybills_found'         => 0,
            'delivery_notes_created' => 0,
            'itemable_updated'       => 0,
            'orphans_itemable'       => 0,
            'errors'                 => [],
        ];

        try {
            $waybills = $this->db->get('tblwaybills')->result_array();
            $report['waybills_found'] = count($waybills);
            $idMap = [];
            foreach ($waybills as $wb) {
                $ins = [
                    'sent'                           => (int)($wb['sent'] ?? 0),
                    'datesend'                       => $wb['datesend'] ?? null,
                    'clientid'                       => (int)($wb['clientid'] ?? 0),
                    'deleted_customer_name'          => $wb['deleted_customer_name'] ?? null,
                    'project_id'                     => (int)($wb['project_id'] ?? 0),
                    'number'                         => (int)($wb['number'] ?? 0),
                    'prefix'                         => $wb['prefix'] ?? null,
                    'number_format'                  => (int)($wb['number_format'] ?? 0),
                    'hash'                           => $wb['hash'] ?? null,
                    'datecreated'                    => $wb['datecreated'] ?? date('Y-m-d H:i:s'),
                    'date'                           => $wb['date'] ?? date('Y-m-d'),
                    'currency'                       => (int)($wb['currency'] ?? 0),
                    'subtotal'                       => (float)($wb['subtotal'] ?? 0),
                    'total_tax'                      => (float)($wb['total_tax'] ?? 0),
                    'total'                          => (float)($wb['total'] ?? 0),
                    'adjustment'                     => $wb['adjustment'] !== null ? (float)$wb['adjustment'] : null,
                    'addedfrom'                      => $wb['addedfrom'] !== null ? (int)$wb['addedfrom'] : 0,
                    'status'                         => (int)($wb['status'] ?? 1),
                    'clientnote'                     => $wb['clientnote'] ?? null,
                    'adminnote'                      => $wb['adminnote'] ?? null,
                    'discount_percent'               => (float)($wb['discount_percent'] ?? 0),
                    'discount_total'                 => (float)($wb['discount_total'] ?? 0),
                    'discount_type'                  => $wb['discount_type'] ?? null,
                    'invoiceid'                      => $wb['invoiceid'] !== null ? (int)$wb['invoiceid'] : null,
                    'invoiced_date'                  => $wb['invoiced_date'] ?? null,
                    'terms'                          => $wb['terms'] ?? null,
                    'reference_no'                   => $wb['reference_no'] ?? null,
                    'sale_agent'                     => (int)($wb['sale_agent'] ?? 0),
                    'billing_street'                 => $wb['billing_street'] ?? null,
                    'billing_city'                   => $wb['billing_city'] ?? null,
                    'billing_state'                  => $wb['billing_state'] ?? null,
                    'billing_zip'                    => $wb['billing_zip'] ?? null,
                    'billing_country'                => $wb['billing_country'] !== null ? (int)$wb['billing_country'] : null,
                    'shipping_street'                => $wb['shipping_street'] ?? null,
                    'shipping_city'                  => $wb['shipping_city'] ?? null,
                    'shipping_state'                 => $wb['shipping_state'] ?? null,
                    'shipping_zip'                   => $wb['shipping_zip'] ?? null,
                    'shipping_country'               => $wb['shipping_country'] !== null ? (int)$wb['shipping_country'] : null,
                    'include_shipping'               => (int)($wb['include_shipping'] ?? 0),
                    'show_shipping_on_delivery_note' => (int)($wb['show_shipping_on_waybill'] ?? 1),
                    'show_quantity_as'               => (int)($wb['show_quantity_as'] ?? 1),
                    'pipeline_order'                 => (int)($wb['pipeline_order'] ?? 1),
                    'is_expiry_notified'             => (int)($wb['is_expiry_notified'] ?? 0),
                    'acceptance_firstname'           => $wb['acceptance_firstname'] ?? null,
                    'acceptance_lastname'            => $wb['acceptance_lastname'] ?? null,
                    'acceptance_email'               => $wb['acceptance_email'] ?? null,
                    'acceptance_date'                => $wb['acceptance_date'] ?? null,
                    'acceptance_ip'                  => $wb['acceptance_ip'] ?? null,
                    'signature'                      => $wb['signature'] ?? null,
                    'short_link'                     => $wb['short_link'] ?? null,
                    'created_by'                     => $wb['addedfrom'] !== null ? (int)$wb['addedfrom'] : null,
                ];

                $this->db->insert('tbldelivery_notes', $ins);
                $newId = (int)$this->db->insert_id();

                if ($newId > 0) {
                    $idMap[(int)$wb['id']] = $newId;
                    $report['delivery_notes_created']++;
                } else {
                    $report['errors'][] = 'Insert failed for waybill ID ' . (int)$wb['id'];
                }
            }

            if (!empty($idMap)) {
                $this->db->where('rel_type', 'waybill');
                $items = $this->db->get('tblitemable')->result_array();

                foreach ($items as $it) {
                    $oldRelId = (int)$it['rel_id'];
                    if (!isset($idMap[$oldRelId])) {
                        $report['orphans_itemable']++;
                        continue;
                    }
                    $newRelId = $idMap[$oldRelId];

                    $this->db->where('id', (int)$it['id']);
                    $this->db->update('tblitemable', [
                        'rel_type' => 'delivery_note',
                        'rel_id'   => $newRelId,
                    ]);

                    if ($this->db->affected_rows() > 0) {
                        $report['itemable_updated']++;
                    }
                }
            }

            if ($dryRun) {
                $this->db->trans_rollback();
                $report['message'] = 'DRY RUN: No changes were committed.';
                echo json_encode(['success' => true] + $report);
                return;
            }

            if ($this->db->trans_status() === false) {
                throw new Exception('DB error, rollback.');
            }

            $this->db->trans_commit();
            $report['message'] = 'Migration completed.';
            echo json_encode(['success' => true] + $report);
        } catch (Throwable $e) {
            $this->db->trans_rollback();
            $report['errors'][] = $e->getMessage();
            echo json_encode(['success' => false] + $report);
        }
    }
	public function upcoming_recurring()
	{
		if (!has_permission('delivery_notes', '', 'view')) {
			access_denied('delivery_notes');
		}

		$this->load->model('delivery_notes_model');

		$data['title'] = _l('upcoming_recurring_delivery_notes');
		$data['delivery_notes'] = $this->delivery_notes_model->get_upcoming_recurring_notes(30); // επόμενες 30 ημέρες

		$this->load->view('admin/delivery_notes/upcoming_recurring', $data);
	}
	public function migrate_waybills_to_dns()
	{
		if (!is_admin()) { show_error('Admins only', 403); }

		$apply = ((int)$this->input->get('apply') === 1);

		$nowDT = date('Y-m-d H:i:s');
		$today = date('Y-m-d');

		$counters = [
			'waybills_total' => 0,
			'dns_inserted'   => 0,
			'dns_skipped_exists' => 0,
			'dns_failed'     => 0,
			'items_total'    => 0,
			'items_inserted' => 0,
			'items_failed'   => 0,
		];

		if ($apply) { $this->db->trans_start(); }

		/* ---------------------------
		 * 1) WAYBILLS -> DELIVERY NOTES (PRESERVE ID)
		 * --------------------------- */
		$waybills = $this->db->get('tblwaybills')->result_array();
		$counters['waybills_total'] = count($waybills);

		foreach ($waybills as $wb) {
			$id = (int)$wb['id'];

			// αν ήδη υπάρχει DN με το ίδιο id -> skip
			$exists = $this->db->select('id')->where('id', $id)->get('tbldelivery_notes')->row();
			if ($exists) {
				$counters['dns_skipped_exists']++;
				continue;
			}

			// Φτιάξε payload για tbldelivery_notes (με ασφαλή defaults)
			$dn = [
				'id'                         => $id, // preserve
				'sent'                       => (int)($wb['sent'] ?? 0),
				'datesend'                   => $wb['datesend'] ?? null,
				'clientid'                   => (int)($wb['clientid'] ?? 0),
				'deleted_customer_name'      => $wb['deleted_customer_name'] ?? null,
				'project_id'                 => (int)($wb['project_id'] ?? 0),
				'number'                     => (int)($wb['number'] ?? 0),
				'prefix'                     => $wb['prefix'] ?? null,
				'number_format'              => (int)($wb['number_format'] ?? 0),
				'hash'                       => $wb['hash'] ?? null,
				'datecreated'                => $wb['datecreated'] ?: $nowDT,
				'date'                       => $wb['date'] ?: $today,
				'currency'                   => (int)($wb['currency'] ?? 0),
				'subtotal'                   => (string)($wb['subtotal'] ?? '0.00'),
				'total_tax'                  => (string)($wb['total_tax'] ?? '0.00'),
				'total'                      => (string)($wb['total'] ?? '0.00'),
				'adjustment'                 => ($wb['adjustment'] ?? null),
				'addedfrom'                  => (int)($wb['addedfrom'] ?? 0),
				'status'                     => (int)($wb['status'] ?? 1),
				'clientnote'                 => $wb['clientnote'] ?? null,
				'adminnote'                  => $wb['adminnote'] ?? null,
				'discount_percent'           => (string)($wb['discount_percent'] ?? '0.00'),
				'discount_total'             => (string)($wb['discount_total'] ?? '0.00'),
				'discount_type'              => $wb['discount_type'] ?? null,
				'invoiceid'                  => $wb['invoiceid'] ?? null,
				'invoiced_date'              => $wb['invoiced_date'] ?? null,
				'terms'                      => $wb['terms'] ?? null,
				'reference_no'               => $wb['reference_no'] ?? null,
				'sale_agent'                 => (int)($wb['sale_agent'] ?? 0),
				'billing_street'             => $wb['billing_street'] ?? null,
				'billing_city'               => $wb['billing_city'] ?? null,
				'billing_state'              => $wb['billing_state'] ?? null,
				'billing_zip'                => $wb['billing_zip'] ?? null,
				'billing_country'            => $wb['billing_country'] ?? null,
				'shipping_street'            => $wb['shipping_street'] ?? null,
				'shipping_city'              => $wb['shipping_city'] ?? null,
				'shipping_state'             => $wb['shipping_state'] ?? null,
				'shipping_zip'               => $wb['shipping_zip'] ?? null,
				'shipping_country'           => $wb['shipping_country'] ?? null,
				'include_shipping'           => (int)($wb['include_shipping'] ?? 0),
				'show_shipping_on_delivery_note' => (int)($wb['show_shipping_on_waybill'] ?? 1),
				'show_quantity_as'           => (int)($wb['show_quantity_as'] ?? 1),
				'pipeline_order'             => (int)($wb['pipeline_order'] ?? 1),
				'is_expiry_notified'         => (int)($wb['is_expiry_notified'] ?? 0),
				'acceptance_firstname'       => $wb['acceptance_firstname'] ?? null,
				'acceptance_lastname'        => $wb['acceptance_lastname'] ?? null,
				'acceptance_email'           => $wb['acceptance_email'] ?? null,
				'acceptance_date'            => $wb['acceptance_date'] ?? null,
				'acceptance_ip'              => $wb['acceptance_ip'] ?? null,
				'signature'                  => $wb['signature'] ?? null,
				'short_link'                 => $wb['short_link'] ?? null,
				'created_by'                 => $wb['created_by'] ?? null,
				// recurring fields που δεν υπάρχουν στα waybills -> defaults
				'recurring'                  => 0,
				'recurring_type'             => 'month',
				'cycles'                     => 0,
				'custom_recurring'           => 0,
				// επιπλέον πεδία που έχει το tbldelivery_notes και δεν υπάρχουν στα waybills
				'cycles'  		             => 0,
				'prefix'                     => $wb['prefix'] ?? null,
			];

			// INSERT
			$ok = true;
			if ($apply) {
				$ok = $this->db->insert('tbldelivery_notes', $dn);
			}
			if ($ok) {
				$counters['dns_inserted']++;
			} else {
				$counters['dns_failed']++;
				log_activity('WB->DN insert failed for ID '.$id.' : '.$this->db->error()['message']);
			}
		}

		/* ---------------------------
		 * 2) ITEMS: tblitemable_old(waybill) -> tblitemable(delivery_note)
		 * --------------------------- */
		// Διάβασε σχήματα για να βρούμε κοινές στήλες (χωρίς id)
		$oldCols = $this->db->query("SHOW COLUMNS FROM `tblitemable_old`")->result_array();
		$newCols = $this->db->query("SHOW COLUMNS FROM `tblitemable`")->result_array();
		$oldSet = array_map(fn($r) => $r['Field'], $oldCols);
		$newSet = array_map(fn($r) => $r['Field'], $newCols);

		// Στήλες προς αντιγραφή = τομή (intersect) ΧΩΡΙΣ id και ΧΩΡΙΣ rel_type (θα το γράψουμε εμείς)
		$cols = array_values(array_intersect($oldSet, $newSet));
		$cols = array_values(array_filter($cols, fn($c) => $c !== 'id' && $c !== 'rel_type'));

		// Φέρε τα items για waybills
		$items = $this->db->where('rel_type','waybill')->get('tblitemable_old')->result_array();
		$counters['items_total'] = count($items);

		foreach ($items as $it) {
			// Χτίσε νέα γραμμή για insert στο tblitemable
			$row = [];
			foreach ($cols as $c) {
				$row[$c] = $it[$c] ?? null;
			}
			$row['rel_type'] = 'delivery_note'; // αλλαγή τύπου

			$ok = true;
			if ($apply) {
				$ok = $this->db->insert('tblitemable', $row);
			}
			if ($ok) {
				$counters['items_inserted']++;
			} else {
				$counters['items_failed']++;
				log_activity('Item migrate failed (old id '.($it['id'] ?? 'n/a').'): '.$this->db->error()['message']);
			}
		}

		if ($apply) {
			$this->db->trans_complete();
			if ($this->db->trans_status() === false) {
				show_error('Transaction failed. No changes committed.', 500);
				return;
			}
		}

		header('Content-Type: text/plain; charset=utf-8');
		echo "=== WAYBILLS → DELIVERY NOTES MIGRATION ".($apply?'[APPLY]':'[DRY-RUN]')." ===\n";
		echo "Waybills found:        {$counters['waybills_total']}\n";
		echo "DN inserted:           {$counters['dns_inserted']}\n";
		echo "DN skipped (exists):   {$counters['dns_skipped_exists']}\n";
		echo "DN failed:             {$counters['dns_failed']}\n\n";
		echo "Items (waybill) found: {$counters['items_total']}\n";
		echo "Items inserted:        {$counters['items_inserted']}\n";
		echo "Items failed:          {$counters['items_failed']}\n\n";
		echo "Tip: Τρέξε πρώτα χωρίς ?apply=1 (dry-run). Αν όλα δείχνουν ΟΚ, τρέξε ξανά με ?apply=1.\n";
	}
	/**
	 * Διορθώνει links σε task descriptions:
	 *  - /admin/waybills/waybill/{id} -> /admin/delivery_notes/delivery_note/{id}
	 *  - /waybill/{id}                -> /delivery_notes/client/dn/{id}
	 *
	 * Dry-run: /admin/paymentsonaccount/fix_task_links
	 * Apply:   /admin/paymentsonaccount/fix_task_links?apply=1
	 */
	public function fix_task_links()
	{
		if (!is_admin()) { show_error('Admins only', 403); }

		$apply = ((int)$this->input->get('apply') === 1);

		// Φέρε μόνο όσα tasks "μοιάζουν" να έχουν waybill links, για να είναι γρήγορο
		$this->db->select('id, description');
		$this->db->from(db_prefix().'tasks');
		$this->db->group_start()
			->like('description', 'https://andanax.eadcrm.eu/admin/waybills/waybill/', 'after')
			->or_like('description', 'https://andanax.eadcrm.eu/waybill/', 'after')
		->group_end();

		$tasks = $this->db->get()->result_array();

		$checked  = count($tasks);
		$changed  = 0;
		$updated  = 0;
		$skipped  = 0;

		if ($apply) { $this->db->trans_start(); }

		foreach ($tasks as $t) {
			$id   = (int)$t['id'];
			$desc = (string)($t['description'] ?? '');

			$newDesc = $this->rewrite_waybill_links_in_text($desc);

			if ($newDesc === $desc) {
				$skipped++;
				continue;
			}

			$changed++;

			if ($apply) {
				$this->db->where('id', $id)->update(db_prefix().'tasks', ['description' => $newDesc]);
				if ($this->db->affected_rows() > 0) {
					$updated++;
					log_activity("Task #{$id} link(s) rewritten from waybill->delivery_note");
				} else {
					// Αν δεν δηλωθεί affected_rows μπορεί να σημαίνει ίδια τιμή ή triggers — το newDesc != desc το έχουμε ήδη ελέγξει.
				}
			}
		}

		if ($apply) {
			$this->db->trans_complete();
			if ($this->db->trans_status() === false) {
				show_error('Transaction failed. No changes committed.', 500);
				return;
			}
		}

		header('Content-Type: text/plain; charset=utf-8');
		echo "=== FIX TASK LINKS ".($apply ? '[APPLY]' : '[DRY-RUN]')." ===\n";
		echo "Tasks scanned:   {$checked}\n";
		echo "Descriptions with changes detected: {$changed}\n";
		echo "Updated (written): {$updated}\n";
		echo "Unchanged/Skipped: {$skipped}\n";
		echo "Tip: Τρέξε πρώτα χωρίς ?apply=1. Αν όλα ΟΚ, ξανατρέξε με ?apply=1.\n";
	}

	/**
	 * Αντικαθιστά όλα τα waybill links στο δοσμένο text.
	 * - Πολλαπλές εμφανίσεις ανά text υποστηρίζονται.
	 * - Αφήνει ανέπαφα ήδη-διορθωμένα delivery_note links.
	 */
	private function rewrite_waybill_links_in_text(string $text): string
	{
		$original = $text;

		// 1) Admin links: /admin/waybills/waybill/{id} -> /admin/delivery_notes/delivery_note/{id}
		// Πιάνουμε ακριβώς το domain + path και digits για id, προαιρετικά και trailing slash.
		$patternAdmin = '#https://andanax\.eadcrm\.eu/admin/waybills/waybill/(\d+)(?=/|\b|$)#';
		$text = preg_replace_callback($patternAdmin, function ($m) {
			$id = $m[1];
			return "https://andanax.eadcrm.eu/admin/delivery_notes/delivery_note/{$id}";
		}, $text);

		// 2) Public links: /waybill/{id} -> /delivery_notes/client/dn/{id}
		$patternPublic = '#https://andanax\.eadcrm\.eu/waybill/(\d+)(?=/|\b|$)#';
		$text = preg_replace_callback($patternPublic, function ($m) {
			$id = $m[1];
			return "https://andanax.eadcrm.eu/delivery_notes/client/dn/{$id}";
		}, $text);

		return $text;
	}
	/**
	 * Διορθώνει links σε task timers notes:
	 *  - /admin/waybills/waybill/{id} -> /admin/delivery_notes/delivery_note/{id}
	 *  - /waybill/{id}                -> /delivery_notes/client/dn/{id}
	 *
	 * Dry-run: /admin/paymentsonaccount/fix_timer_links
	 * Apply:   /admin/paymentsonaccount/fix_timer_links?apply=1
	 */
	public function fix_timer_links()
	{
		if (!is_admin()) { show_error('Admins only', 403); }

		$apply = ((int)$this->input->get('apply') === 1);

		$this->db->select('id, note');
		$this->db->from(db_prefix().'taskstimers');
		$this->db->group_start()
			->like('note', 'https://andanax.eadcrm.eu/admin/waybills/waybill/', 'after')
			->or_like('note', 'https://andanax.eadcrm.eu/waybill/', 'after')
		->group_end();

		$timers = $this->db->get()->result_array();

		$checked = count($timers);
		$changed = 0;
		$updated = 0;
		$skipped = 0;

		if ($apply) { $this->db->trans_start(); }

		foreach ($timers as $t) {
			$id   = (int)$t['id'];
			$note = (string)($t['note'] ?? '');

			$newNote = $this->rewrite_waybill_links_in_text($note);

			if ($newNote === $note) {
				$skipped++;
				continue;
			}

			$changed++;

			if ($apply) {
				$this->db->where('id', $id)->update(db_prefix().'taskstimers', ['note' => $newNote]);
				if ($this->db->affected_rows() > 0) {
					$updated++;
					log_activity("TaskTimer #{$id} note link(s) rewritten waybill->delivery_note");
				}
			}
		}

		if ($apply) {
			$this->db->trans_complete();
			if ($this->db->trans_status() === false) {
				show_error('Transaction failed. No changes committed.', 500);
				return;
			}
		}

		header('Content-Type: text/plain; charset=utf-8');
		echo "=== FIX TIMER LINKS ".($apply ? '[APPLY]' : '[DRY-RUN]')." ===\n";
		echo "Timers scanned:   {$checked}\n";
		echo "Notes with changes detected: {$changed}\n";
		echo "Updated (written): {$updated}\n";
		echo "Unchanged/Skipped: {$skipped}\n";
		echo "Tip: Τρέξε πρώτα χωρίς ?apply=1. Αν όλα ΟΚ, ξανατρέξε με ?apply=1.\n";
	}

}
