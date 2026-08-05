<?php

use app\services\utilities\Arr;

defined('BASEPATH') or exit('No direct script access allowed');

class Misc_model extends App_Model {
    public $notifications_limit;

    public function __construct() {
        parent::__construct();

        $this->notifications_limit = 15;        

        if (!$this->db->table_exists(db_prefix() . 'playground_milestones')) {
            $this->db->query('CREATE TABLE `' . db_prefix() . 'playground_milestones' . '` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(191) NOT NULL,
                `description` mediumtext DEFAULT NULL,
                `description_visible_to_customer` tinyint(1) DEFAULT 0,
                `start_date` date DEFAULT NULL,
                `due_date` date NOT NULL,
                `project_id` int(11) NOT NULL,
                `color` varchar(10) DEFAULT NULL,
                `milestone_order` int(11) NOT NULL DEFAULT 0,
                `datecreated` date NOT NULL,
                `hide_from_customer` int(11) DEFAULT 0,
                PRIMARY KEY (`id`));
            ');
        }

        if (!$this->db->table_exists(db_prefix() . 'playground_files')) {
            $this->db->query('CREATE TABLE `' . db_prefix() . 'playground_files' . '` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `rel_id` int(11) NOT NULL,
                `rel_type` varchar(20) NOT NULL,
                `file_name` varchar(191) NOT NULL,
                `filetype` varchar(40) DEFAULT NULL,
                `visible_to_customer` int(11) NOT NULL DEFAULT 0,
                `attachment_key` varchar(32) DEFAULT NULL,
                `external` varchar(40) DEFAULT NULL,
                `external_link` mediumtext DEFAULT NULL,
                `thumbnail_link` mediumtext DEFAULT NULL COMMENT \'For external usage\',
                `staffid` int(11) NOT NULL,
                `contact_id` int(11) DEFAULT 0,
                `task_comment_id` int(11) NOT NULL DEFAULT 0,
                `dateadded` datetime NOT NULL,
                PRIMARY KEY (`id`),
                KEY `rel_id` (`rel_id`),
                KEY `rel_type` (`rel_type`));
            ');
        }

        if (!$this->db->table_exists(db_prefix() . 'playground_taggables')) {
            $this->db->query('CREATE TABLE `' . db_prefix() . 'playground_taggables' . '` (
                `rel_id` int(11) NOT NULL,
                `rel_type` varchar(20) NOT NULL,
                `tag_id` int(11) NOT NULL,
                `tag_order` int(11) NOT NULL DEFAULT 0,
                KEY `rel_id` (`rel_id`),
                KEY `rel_type` (`rel_type`),
                KEY `tag_id` (`tag_id`));
            ');
        }

        if (!$this->db->table_exists(db_prefix() . 'playground_options')) {
            $this->db->query('CREATE TABLE `' . db_prefix() . 'playground_options' . '` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(191) NOT NULL,
                `value` longtext NOT NULL,
                `autoload` tinyint(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (`id`),
                KEY `name` (`name`));
            ');
        }

        if (!$this->db->table_exists(db_prefix() . 'playground_reminders')) {
            $this->db->query('CREATE TABLE `' . db_prefix() . 'playground_reminders' . '` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `description` mediumtext DEFAULT NULL,
                `date` datetime NOT NULL,
                `isnotified` int(11) NOT NULL DEFAULT 0,
                `rel_id` int(11) NOT NULL,
                `staff` int(11) NOT NULL,
                `rel_type` varchar(40) NOT NULL,
                `notify_by_email` int(11) NOT NULL DEFAULT 1,
                `creator` int(11) NOT NULL,
                PRIMARY KEY (`id`),
                KEY `rel_id` (`rel_id`),
                KEY `rel_type` (`rel_type`),
                KEY `staff` (`staff`));
            ');
        }

        if (!$this->db->table_exists(db_prefix() . 'playground_notes')) {
            $this->db->query('CREATE TABLE `' . db_prefix() . 'playground_notes' . '` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(191) NOT NULL,
                `value` longtext NOT NULL,
                `autoload` tinyint(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (`id`),
                KEY `name` (`name`));
            ');
        }

        if (!$this->db->table_exists(db_prefix() . 'playground_activity_log')) {
            $this->db->query('CREATE TABLE `' . db_prefix() . 'playground_activity_log' . '` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `description` longtext NOT NULL,
                `date` datetime NOT NULL,
                `staffid` varchar(100) DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `staffid` (`staffid`));
            ');
        }

        if (!$this->db->table_exists(db_prefix() . 'playground_notifications')) {
            $this->db->query('CREATE TABLE `' . db_prefix() . 'playground_notifications' . '` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `isread` int(11) NOT NULL DEFAULT 0,
                `isread_inline` tinyint(1) NOT NULL DEFAULT 0,
                `date` datetime NOT NULL,
                `description` mediumtext NOT NULL,
                `fromuserid` int(11) NOT NULL,
                `fromclientid` int(11) NOT NULL DEFAULT 0,
                `from_fullname` varchar(100) NOT NULL,
                `touserid` int(11) NOT NULL,
                `fromcompany` int(11) DEFAULT NULL,
                `link` longtext DEFAULT NULL,
                `additional_data` mediumtext DEFAULT NULL,
                PRIMARY KEY (`id`));
            ');
        }

        if (!$this->db->table_exists(db_prefix() . 'playground_dismissed_announcements')) {
            $this->db->query('CREATE TABLE `' . db_prefix() . 'playground_dismissed_announcements' . '` (
                `dismissedannouncementid` int(11) NOT NULL AUTO_INCREMENT,
                `announcementid` int(11) NOT NULL,
                `staff` int(11) NOT NULL,
                `userid` int(11) NOT NULL,
                PRIMARY KEY (`dismissedannouncementid`),
                KEY `announcementid` (`announcementid`),
                KEY `staff` (`staff`),
                KEY `userid` (`userid`));
            ');
        }

        if (!$this->db->table_exists(db_prefix() . 'playground_countries')) {
            $this->db->query('CREATE TABLE `' . db_prefix() . 'playground_countries' . '` (
                `country_id` int(11) NOT NULL AUTO_INCREMENT,
                `iso2` char(2) DEFAULT NULL,
                `short_name` varchar(80) NOT NULL DEFAULT "",
                `long_name` varchar(80) NOT NULL DEFAULT "",
                `iso3` char(3) DEFAULT NULL,
                `numcode` varchar(6) DEFAULT NULL,
                `un_member` varchar(12) DEFAULT NULL,
                `calling_code` varchar(8) DEFAULT NULL,
                `cctld` varchar(5) DEFAULT NULL,
                PRIMARY KEY (`country_id`));
            ');
        }

        if (!$this->db->table_exists(db_prefix() . 'playground_customer_admins')) {
            $this->db->query('CREATE TABLE `' . db_prefix() . 'playground_customer_admins' . '` (
                `staff_id` int(11) NOT NULL,
                `customer_id` int(11) NOT NULL,
                `date_assigned` mediumtext NOT NULL,
                KEY `customer_id` (`customer_id`),
                KEY `staff_id` (`staff_id`));
            ');
        }

        if (!$this->db->table_exists(db_prefix() . 'playground_knowledge_base')) {
            $this->db->query('CREATE TABLE `' . db_prefix() . 'playground_knowledge_base' . '` (
                `articleid` int(11) NOT NULL AUTO_INCREMENT,
                `articlegroup` int(11) NOT NULL,
                `subject` longtext NOT NULL,
                `description` mediumtext NOT NULL,
                `slug` longtext NOT NULL,
                `active` tinyint(4) NOT NULL,
                `datecreated` datetime NOT NULL,
                `article_order` int(11) NOT NULL DEFAULT 0,
                `staff_article` int(11) NOT NULL DEFAULT 0,
                `question_answers` int(11) DEFAULT 0,
                `file_name` varchar(255) DEFAULT "",
                `curator` varchar(11) DEFAULT "",
                `benchmark` int(11) DEFAULT 0,
                `score` int(11) DEFAULT 0,
                PRIMARY KEY (`articleid`));
            ');
        }

        if (!$this->db->table_exists(db_prefix() . 'playground_tags')) {
            $this->db->query('CREATE TABLE `' . db_prefix() . 'playground_tags' . '` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(100) NOT NULL,
                PRIMARY KEY (`id`),
                KEY `name` (`name`));
            ');
        }

        if (!$this->db->table_exists(db_prefix() . 'playground_tracked_mails')) {
            $this->db->query('CREATE TABLE `' . db_prefix() . 'playground_tracked_mails' . '` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `uid` varchar(32) NOT NULL,
                `rel_id` int(11) NOT NULL,
                `rel_type` varchar(40) NOT NULL,
                `date` datetime NOT NULL,
                `email` varchar(100) NOT NULL,
                `opened` tinyint(1) NOT NULL DEFAULT 0,
                `date_opened` datetime DEFAULT NULL,
                `subject` longtext DEFAULT NULL,
                PRIMARY KEY (`id`));
            ');
        }
    }

    public function get_notifications_limit() {
        return hooks()->apply_filters('notifications_limit', $this->notifications_limit);
    }

    public function get_taxes_dropdown_template($name, $taxname, $type = '', $item_id = '', $is_edit = false, $manual = false, $playground = false) {
        // if passed manually - like in proposal convert items or project
        if ($manual == true) {
            // + is no longer used and is here for backward compatibilities
            if (is_array($taxname) || strpos($taxname, '+') !== false) {
                if (!is_array($taxname)) {
                    $__tax = explode('+', $taxname);
                } else {
                    $__tax = $taxname;
                }
                // Multiple taxes found // possible option from default settings when invoicing project
                $taxname = [];
                foreach ($__tax as $t) {
                    $tax_array = explode('|', $t);
                    if (isset($tax_array[0]) && isset($tax_array[1])) {
                        array_push($taxname, $tax_array[0] . '|' . $tax_array[1]);
                    }
                }
            } else {
                $tax_array = explode('|', $taxname);
                // isset tax rate
                if (isset($tax_array[0]) && isset($tax_array[1])) {
                    $tax = get_tax_by_name($tax_array[0]);
                    if ($tax) {
                        $taxname = $tax->name . '|' . $tax->taxrate;
                    }
                }
            }
        }
        // First get all system taxes
        $this->load->model('taxes_model');
        $taxes = $this->taxes_model->get('', $playground);
        $i = 0;
        foreach ($taxes as $tax) {
            unset($taxes[$i]['id']);
            $taxes[$i]['name'] = $tax['name'] . '|' . $tax['taxrate'];
            $i++;
        }
        if ($is_edit == true) {
            // Lets check the items taxes in case of changes.
            // Separate functions exists to get item taxes for Invoice, Estimate, Proposal, Credit Note
            $func_taxes = 'get_' . $type . '_item_taxes';
            if (function_exists($func_taxes)) {
                $item_taxes = call_user_func($func_taxes, $item_id);
            }
            foreach ($item_taxes as $item_tax) {
                $new_tax = [];
                $new_tax['name'] = $item_tax['taxname'];
                $new_tax['taxrate'] = $item_tax['taxrate'];
                $taxes[] = $new_tax;
            }
        }
        // In case tax is changed and the old tax is still linked to estimate/proposal when converting
        // This will allow the tax that don't exists to be shown on the dropdowns too.
        if (is_array($taxname)) {
            foreach ($taxname as $tax) {
                // Check if tax empty
                if ((!is_array($tax) && $tax == '') || is_array($tax) && $tax['taxname'] == '') {
                    continue;
                };
                // Check if really the taxname NAME|RATE don't exists in all taxes
                if (!value_exists_in_array_by_key($taxes, 'name', $tax)) {
                    if (!is_array($tax)) {
                        $tmp_taxname = $tax;
                        $tax_array = explode('|', $tax);
                    } else {
                        $tax_array = explode('|', $tax['taxname']);
                        $tmp_taxname = $tax['taxname'];
                        if ($tmp_taxname == '') {
                            continue;
                        }
                    }
                    $taxes[] = ['name' => $tmp_taxname, 'taxrate' => $tax_array[1]];
                }
            }
        }
        // Clear the duplicates
        $taxes = Arr::uniqueByKey($taxes, 'name');
        $select = '<select class="selectpicker display-block tax" data-width="100%" name="' . $name . '" multiple data-none-selected-text="' . _l('no_tax') . '">';
        foreach ($taxes as $tax) {
            $selected = '';
            if (is_array($taxname)) {
                foreach ($taxname as $_tax) {
                    if (is_array($_tax)) {
                        if ($_tax['taxname'] == $tax['name']) {
                            $selected = 'selected';
                        }
                    } else {
                        if ($_tax == $tax['name']) {
                            $selected = 'selected';
                        }
                    }
                }
            } else {
                if ($taxname == $tax['name']) {
                    $selected = 'selected';
                }
            }
            $select.= '<option value="' . $tax['name'] . '" ' . $selected . ' data-taxrate="' . $tax['taxrate'] . '" data-taxname="' . $tax['name'] . '" data-subtext="' . $tax['name'] . '">' . $tax['taxrate'] . '%</option>';
        }
        $select.= '</select>';
        return $select;
    }

    public function add_attachment_to_database($rel_id, $rel_type, $attachment, $external = false, $playground = false) {
        $data['dateadded'] = date('Y-m-d H:i:s');
        $data['rel_id'] = $rel_id;
        if (!isset($attachment[0]['staffid'])) {
            $data['staffid'] = get_staff_user_id();
        } else {
            $data['staffid'] = $attachment[0]['staffid'];
        }
        if (isset($attachment[0]['task_comment_id'])) {
            $data['task_comment_id'] = $attachment[0]['task_comment_id'];
        }
        $data['rel_type'] = $rel_type;
        if (isset($attachment[0]['contact_id'])) {
            $data['contact_id'] = $attachment[0]['contact_id'];
            $data['visible_to_customer'] = 1;
            if (isset($data['staffid'])) {
                unset($data['staffid']);
            }
        }
        $data['attachment_key'] = app_generate_hash();
        if ($external == false) {
            $data['file_name'] = $attachment[0]['file_name'];
            $data['filetype'] = $attachment[0]['filetype'];
        } else {
            $path_parts = pathinfo($attachment[0]['name']);
            $data['file_name'] = $attachment[0]['name'];
            $data['external_link'] = $attachment[0]['link'];
            $data['filetype'] = !isset($attachment[0]['mime']) ? get_mime_by_extension('.' . $path_parts['extension']) : $attachment[0]['mime'];
            $data['external'] = $external;
            if (isset($attachment[0]['thumbnailLink'])) {
                $data['thumbnail_link'] = $attachment[0]['thumbnailLink'];
            }
        }
        $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'files', $data);
        $insert_id = $this->db->insert_id();
        if ($data['rel_type'] == 'customer' && isset($data['contact_id'])) {
            if (get_option('only_own_files_contacts') == 1) {
                $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'shared_customer_files', ['file_id' => $insert_id, 'contact_id' => $data['contact_id'], ]);
            } else {
                $this->db->select('id');
                $this->db->where('userid', $data['rel_id']);
                $contacts = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'contacts')->result_array();
                foreach ($contacts as $contact) {
                    $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'shared_customer_files', ['file_id' => $insert_id, 'contact_id' => $contact['id'], ]);
                }
            }
        }
        return $insert_id;
    }

    public function get_file($id, $playground = false) {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'files')->row();
    }

    public function get_staff_started_timers($playground = false) {
        $this->db->select(db_prefix() . ($playground ? 'playground_' : '') . 'taskstimers.*,' . db_prefix() . ($playground ? 'playground_' : '') . 'tasks.name as task_subject');
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'staff', db_prefix() . ($playground ? 'playground_' : '') . 'staff.staffid=' . db_prefix() . ($playground ? 'playground_' : '') . 'taskstimers.staff_id');
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'tasks', db_prefix() . ($playground ? 'playground_' : '') . 'tasks.id=' . db_prefix() . ($playground ? 'playground_' : '') . 'taskstimers.task_id', 'left');
        $this->db->where('staff_id', get_staff_user_id());
        $this->db->where('end_time IS NULL');
        return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'taskstimers')->result_array();
    }

    /**
     * Add reminder
     * @since  Version 1.0.2
     * @param mixed $data All $_POST data for the reminder
     * @param mixed $id   relid id
     * @return boolean
     */
    public function add_reminder($data, $id, $playground = false) {
        if (isset($data['notify_by_email'])) {
            $data['notify_by_email'] = 1;
        } //isset($data['notify_by_email'])
        else {
            $data['notify_by_email'] = 0;
        }
        $data['date'] = to_sql_date($data['date'], true);
        $data['description'] = nl2br($data['description']);
        $data['creator'] = get_staff_user_id();
        $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'reminders', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            if ($data['rel_type'] == 'lead') {
                $this->load->model('leads_model');
                $this->load->model('staff_model');
                $this->leads_model->log_lead_activity($data['rel_id'], 'not_activity_new_reminder_created', false, serialize([$this->staff_model->get_staff_full_name($data['staff'], $playground), _dt($data['date']), ]), $playground);
            }
            log_activity('New Reminder Added [' . ucfirst($data['rel_type']) . 'ID: ' . $data['rel_id'] . ' Description: ' . $data['description'] . ']');
            return true;
        } //$insert_id
        return false;
    }

    public function edit_reminder($data, $id, $playground = false) {
        if (isset($data['notify_by_email'])) {
            $data['notify_by_email'] = 1;
        } else {
            $data['notify_by_email'] = 0;
        }
        $data['date'] = to_sql_date($data['date'], true);
        $data['description'] = nl2br($data['description']);
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'reminders', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    public function get_notes($rel_id, $rel_type, $playground = false) {
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'staff', db_prefix() . ($playground ? 'playground_' : '') . 'staff.staffid=' . db_prefix() . ($playground ? 'playground_' : '') . 'notes.addedfrom');
        $this->db->where('rel_id', $rel_id);
        $this->db->where('rel_type', $rel_type);
        $this->db->order_by('dateadded', 'desc');
        $notes = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'notes')->result_array();
        return hooks()->apply_filters('get_notes', $notes, ['rel_id' => $rel_id, 'rel_type' => $rel_type]);
    }

    public function add_note($data, $rel_type, $rel_id, $playground = false) {
        $data['dateadded'] = date('Y-m-d H:i:s');
        $data['addedfrom'] = get_staff_user_id();
        $data['rel_type'] = $rel_type;
        $data['rel_id'] = $rel_id;
        $data['description'] = nl2br($data['description']);
        $data = hooks()->apply_filters('create_note_data', $data, $rel_type, $rel_id);
        $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'notes', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            hooks()->do_action('note_created', $insert_id, $data);
            return $insert_id;
        }
        return false;
    }

    public function edit_note($data, $id, $playground = false) {
        hooks()->do_action('before_update_note', ['data' => $data, 'id' => $id, ]);
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'notes', $data = ['description' => nl2br($data['description']), ]);
        if ($this->db->affected_rows() > 0) {
            hooks()->do_action('note_updated', $id, $data);
            return true;
        }
        return false;
    }

    public function get_activity_log($limit = 30, $playground = false) {
        $this->db->limit($limit);
        $this->db->order_by('date', 'desc');
        return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'activity_log')->result_array();
    }

    public function delete_note($note_id, $playground = false) {
        hooks()->do_action('before_delete_note', $note_id);
        $this->db->where('id', $note_id);
        $note = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'notes')->row();
        if ($note->addedfrom != get_staff_user_id() && !is_admin()) {
            return false;
        }
        $this->db->where('id', $note_id);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'notes');
        if ($this->db->affected_rows() > 0) {
            hooks()->do_action('note_deleted', $note_id, $note);
            return true;
        }
        return false;
    }

    /**
     * Get all reminders or 1 reminder if id is passed
     * @since Version 1.0.2
     * @param  mixed $id reminder id OPTIONAL
     * @return array or object
     */
    public function get_reminders($id = '', $playground = false) {
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'staff', '' . db_prefix() . ($playground ? 'playground_' : '') . 'staff.staffid = ' . db_prefix() . ($playground ? 'playground_' : '') . 'reminders.staff', 'left');
        if (is_numeric($id)) {
            $this->db->where(db_prefix() . ($playground ? 'playground_' : '') . 'reminders.id', $id);
            return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'reminders')->row();
        } //is_numeric($id)
        $this->db->order_by('date', 'desc');
        return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'reminders')->result_array();
    }

    /**
     * Remove client reminder from database
     * @since Version 1.0.2
     * @param  mixed $id reminder id
     * @return boolean
     */
    public function delete_reminder($id, $playground = false) {
        $reminder = $this->get_reminders($id, $playground);
        if ($reminder->creator == get_staff_user_id() || is_admin()) {
            $this->db->where('id', $id);
            $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'reminders');
            if ($this->db->affected_rows() > 0) {
                log_activity('Reminder Deleted [' . ucfirst($reminder->rel_type) . 'ID: ' . $reminder->id . ' Description: ' . $reminder->description . ']');
                return true;
            }
            //$this->db->affected_rows() > 0
            return false;
        }
        //$reminder->creator == get_staff_user_id() || is_admin()
        return false;
    }

    public function get_tasks_distinct_assignees($playground = false) {
        return $this->db->query('SELECT DISTINCT(' . db_prefix() . "task_assigned.staffid) as assigneeid, CONCAT(firstname,' ',lastname) as full_name FROM " . db_prefix() . ($playground ? 'playground_' : '') . 'task_assigned JOIN ' . db_prefix() . ($playground ? 'playground_' : '') . 'staff ON ' . db_prefix() . ($playground ? 'playground_' : '') . 'staff.staffid=' . db_prefix() . ($playground ? 'playground_' : '') . 'task_assigned.staffid')->result_array();
    }

    public function get_google_calendar_ids($playground = false) {
        $is_admin = is_admin();
        $this->load->model('departments_model');
        $departments = $this->departments_model->get($playground);
        $staff_departments = $this->departments_model->get_staff_departments(false, true, $playground);
        $ids = [];
        // Check departments google calendar ids
        foreach ($departments as $department) {
            if ($department['calendar_id'] == '') {
                continue;
            }
            if ($is_admin) {
                $ids[] = $department['calendar_id'];
            } else {
                if (in_array($department['departmentid'], $staff_departments)) {
                    $ids[] = $department['calendar_id'];
                }
            }
        }
        // Ok now check if main calendar is setup
        $main_id_calendar = get_option('google_calendar_main_calendar');
        if ($main_id_calendar != '') {
            $ids[] = $main_id_calendar;
        }
        return array_unique($ids);
    }

    /**
     * Get current user notifications
     * @param  boolean $read include and readed notifications
     * @return array
     */
    public function get_user_notifications($read = false, $playground = false) {
        $read = $read == false ? 0 : 1;
        $total = $this->notifications_limit;
        $staff_id = get_staff_user_id();
        $sql = 'SELECT COUNT(*) as total FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'notifications WHERE isread=' . $read . ' AND touserid=' . $staff_id;
        $sql.= ' UNION ALL ';
        $sql.= 'SELECT COUNT(*) as total FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'notifications WHERE isread_inline=' . $read . ' AND touserid=' . $staff_id;
        $res = $this->db->query($sql)->result();
        $total_unread = $res[0]->total;
        $total_unread_inline = $res[1]->total;
        if ($total_unread > $total) {
            $total = ($total_unread - $total) + $total;
        } else if ($total_unread_inline > $total) {
            $total = ($total_unread_inline - $total) + $total;
        }
        // In case user is not marking the notifications are read this process may be long because the script will always fetch the total from the not read notifications.
        // In this case we are limiting to 30
        $total = $total > 30 ? 30 : $total;
        $this->db->where('touserid', $staff_id);
        $this->db->limit($total);
        $this->db->order_by('date', 'desc');
        return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'notifications')->result_array();
    }

    /**
     * Set notification read when user open notification dropdown
     * @return boolean
     */
    public function set_notifications_read($playground = false) {
        $this->db->where('touserid', get_staff_user_id());
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'notifications', ['isread' => 1, ]);
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    public function set_notification_read_inline($id, $playground = false) {
        $this->db->where('touserid', get_staff_user_id());
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'notifications', ['isread_inline' => 1, ]);
    }

    public function set_desktop_notification_read($id, $playground = false) {
        $this->db->where('touserid', get_staff_user_id());
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'notifications', ['isread' => 1, 'isread_inline' => 1, ]);
    }

    public function mark_all_notifications_as_read_inline($playground = false) {
        $this->db->where('touserid', get_staff_user_id());
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'notifications', ['isread_inline' => 1, 'isread' => 1, ]);
    }

    /**
     * Dismiss announcement
     * @param  array  $data  announcement data
     * @param  boolean $staff is staff or client
     * @return boolean
     */
    public function dismiss_announcement($id, $staff = true, $playground = false) {
        if ($staff == false) {
            $userid = get_contact_user_id();
        } //$staff == false
        else {
            $userid = get_staff_user_id();
        }
        $data['announcementid'] = $id;
        $data['userid'] = $userid;
        $data['staff'] = $staff;
        $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'dismissed_announcements', $data);
        return true;
    }

    /**
     * Perform search on top header
     * @since  Version 1.0.1
     * @param  string $q search
     * @return array    search results
     */
    public function perform_search($q, $playground = false) {
        $q = trim($q);
        $is_admin = is_admin();
        $result = [];
        $limit = get_option('limit_top_search_bar_results_to');
        $have_assigned_customers = have_assigned_customers();
        $have_permission_customers_view = staff_can('view', 'customers');
        if ($have_assigned_customers || $have_permission_customers_view) {
            // Clients
            $this->load->model('clients_model');
            $this->db->select(implode(',', prefixed_table_fields_array(db_prefix() . ($playground ? 'playground_' : '') . 'clients')) . ',' . $this->clients_model->get_sql_select_client_company('company', $playground));
            $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'countries', db_prefix() . ($playground ? 'playground_' : '') . 'countries.country_id = ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.country', 'left');
            $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'contacts', db_prefix() . ($playground ? 'playground_' : '') . 'contacts.userid = ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.userid AND is_primary = 1', 'left');
            $this->db->from(db_prefix() . ($playground ? 'playground_' : '') . 'clients');
            if ($have_assigned_customers && !$have_permission_customers_view) {
                $this->db->where(db_prefix() . ($playground ? 'playground_' : '') . 'clients.userid IN (SELECT customer_id FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'customer_admins WHERE staff_id=' . get_staff_user_id() . ')');
            }
            $this->db->where('(company LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR vat LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.phonenumber LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR ' . db_prefix() . ($playground ? 'playground_' : '') . 'contacts.phonenumber LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR city LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR zip LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR state LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR zip LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR address LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR email LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR CONCAT(firstname, \' \', lastname) LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR CONCAT(lastname, \' \', firstname) LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR ' . db_prefix() . ($playground ? 'playground_' : '') . 'countries.short_name LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR ' . db_prefix() . ($playground ? 'playground_' : '') . 'countries.long_name LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR ' . db_prefix() . ($playground ? 'playground_' : '') . 'countries.numcode LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
            )');
            $this->db->limit($limit);
            $result[] = ['result' => $this->db->get()->result_array(), 'type' => 'clients', 'search_heading' => _l('clients'), ];
        }
        $staff_search = $this->search_staff($q, $limit, $playground);
        if (count($staff_search['result']) > 0) {
            $result[] = $staff_search;
        }
        $where_contacts = '';
        if ($have_assigned_customers && !$have_permission_customers_view) {
            $where_contacts = db_prefix() . ($playground ? 'playground_' : '') . 'contacts.userid IN (SELECT customer_id FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'customer_admins WHERE staff_id=' . get_staff_user_id() . ')';
        }
        $contacts_search = $this->_search_contacts($q, $limit, $where_contacts, $playground);
        if (count($contacts_search['result']) > 0) {
            $result[] = $contacts_search;
        }
        $tickets_search = $this->_search_tickets($q, $limit, $playground);
        if (count($tickets_search['result']) > 0) {
            $result[] = $tickets_search;
        }
        $leads_search = $this->_search_leads($q, $limit, $playground);
        if (count($leads_search['result']) > 0) {
            $result[] = $leads_search;
        }
        $proposals_search = $this->search_proposals($q, $limit, $playground);
        if (count($proposals_search['result']) > 0) {
            $result[] = $proposals_search;
        }
        $invoices_search = $this->_search_invoices($q, $limit, $playground);
        if (count($invoices_search['result']) > 0) {
            $result[] = $invoices_search;
        }
        $credit_notes_search = $this->_search_credit_notes($q, $limit, $playground);
        if (count($credit_notes_search['result']) > 0) {
            $result[] = $credit_notes_search;
        }
        $estimates_search = $this->search_estimates($q, $limit, $playground);
        if (count($estimates_search['result']) > 0) {
            $result[] = $estimates_search;
        }
        $expenses_search = $this->search_expenses($q, $limit, $playground);
        if (count($expenses_search['result']) > 0) {
            $result[] = $expenses_search;
        }
        $projects_search = $this->search_projects($q, $limit, false, null, $playground);
        if (count($projects_search['result']) > 0) {
            $result[] = $projects_search;
        }
        $contracts_search = $this->search_contracts($q, $limit, $playground);
        if (count($contracts_search['result']) > 0) {
            $result[] = $contracts_search;
        }
        if (staff_can('view', 'knowledge_base')) {
            // Knowledge base articles
            $this->db->select()->from(db_prefix() . ($playground ? 'playground_' : '') . 'knowledge_base')->like('subject', $q)->or_like('description', $q)->or_like('slug', $q)->limit($limit);
            $this->db->order_by('subject', 'ASC');
            $result[] = ['result' => $this->db->get()->result_array(), 'type' => 'knowledge_base_articles', 'search_heading' => _l('kb_string'), ];
        }
        // Tasks Search
        $tasks = staff_can('view', 'tasks');
        // Staff tasks
        $this->db->select();
        $this->db->from(db_prefix() . ($playground ? 'playground_' : '') . 'tasks');
        if (!$is_admin) {
            if (!$tasks) {
                $where = '(id IN (SELECT taskid FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'task_assigned WHERE staffid = ' . get_staff_user_id() . ') OR id IN (SELECT taskid FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'task_followers WHERE staffid = ' . get_staff_user_id() . ') OR (addedfrom=' . get_staff_user_id() . ' AND is_added_from_contact=0) ';
                if (get_option('show_all_tasks_for_project_member') == 1) {
                    $where.= ' OR (rel_type="project" AND rel_id IN (SELECT project_id FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'project_members WHERE staff_id=' . get_staff_user_id() . '))';
                }
                $where.= ' OR is_public = 1)';
                $this->db->where($where);
            } //!$tasks
            
        } //!$is_admin
        if (!startsWith($q, '#')) {
            $this->db->where('(name LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\' OR description LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\')');
        } else {
            $this->db->where('id IN
                (SELECT rel_id FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'taggables WHERE tag_id IN
                (SELECT id FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'tags WHERE name="' . $this->db->escape_str(strafter($q, '#')) . '")
                AND ' . db_prefix() . ($playground ? 'playground_' : '') . 'taggables.rel_type=\'task\' GROUP BY rel_id HAVING COUNT(tag_id) = 1)
            ');
        }
        $this->db->limit($limit);
        $this->db->order_by('name', 'ASC');
        $result[] = ['result' => $this->db->get()->result_array(), 'type' => 'tasks', 'search_heading' => _l('tasks'), ];
        // Payments search
        $has_permission_view_payments = staff_can('view', 'payments');
        $has_permission_view_invoices_own = staff_can('view_own', 'invoices');
        if (staff_can('view', 'payments') || $has_permission_view_invoices_own || get_option('allow_staff_view_invoices_assigned') == '1') {
            if (is_numeric($q)) {
                $q = trim($q);
                $q = ltrim($q, '0');
            } else if (startsWith($q, get_option('invoice_prefix'))) {
                $q = strafter($q, get_option('invoice_prefix'));
                $q = trim($q);
                $q = ltrim($q, '0');
            }
            $noPermissionQuery = get_invoices_where_sql_for_staff(get_staff_user_id());
            // Invoice payment records
            $this->db->select('*,' . db_prefix() . ($playground ? 'playground_' : '') . 'invoicepaymentrecords.id as paymentid');
            $this->db->from(db_prefix() . ($playground ? 'playground_' : '') . 'invoicepaymentrecords');
            $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'payment_modes', '' . db_prefix() . ($playground ? 'playground_' : '') . 'invoicepaymentrecords.paymentmode = ' . db_prefix() . ($playground ? 'playground_' : '') . 'payment_modes.id', 'LEFT');
            $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'invoices', '' . db_prefix() . ($playground ? 'playground_' : '') . 'invoices.id = ' . db_prefix() . ($playground ? 'playground_' : '') . 'invoicepaymentrecords.invoiceid');
            if (!$has_permission_view_payments) {
                $this->db->where('invoiceid IN (select id from ' . db_prefix() . ($playground ? 'playground_' : '') . 'invoices where ' . $noPermissionQuery . ')');
            }
            $this->db->where('(' . db_prefix() . ($playground ? 'playground_' : '') . 'invoicepaymentrecords.id LIKE "' . $this->db->escape_like_str($q) . '"
                OR paymentmode LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR ' . db_prefix() . ($playground ? 'playground_' : '') . 'payment_modes.name LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR ' . db_prefix() . ($playground ? 'playground_' : '') . 'invoicepaymentrecords.note LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR number LIKE "' . $this->db->escape_like_str($q) . ' ESCAPE \'!\'"
            )');
            $this->db->order_by(db_prefix() . ($playground ? 'playground_' : '') . 'invoicepaymentrecords.date', 'ASC');
            $result[] = ['result' => $this->db->get()->result_array(), 'type' => 'invoice_payment_records', 'search_heading' => _l('payments'), ];
        }
        // Custom fields only admins
        if ($is_admin) {
            $this->db->select()->from(db_prefix() . ($playground ? 'playground_' : '') . 'customfieldsvalues')->like('value', $q)->limit($limit);
            $result[] = ['result' => $this->db->get()->result_array(), 'type' => 'custom_fields', 'search_heading' => _l('custom_fields'), ];
        }
        // Invoice Items Search
        $has_permission_view_invoices = staff_can('view', 'invoices');
        $has_permission_view_invoices_own = staff_can('view_own', 'invoices');
        $allow_staff_view_invoices_assigned = get_option('allow_staff_view_invoices_assigned');
        if ($has_permission_view_invoices || $has_permission_view_invoices_own || $allow_staff_view_invoices_assigned == '1') {
            $noPermissionQuery = get_invoices_where_sql_for_staff(get_staff_user_id());
            $this->db->select()->from(db_prefix() . ($playground ? 'playground_' : '') . 'itemable');
            $this->db->where('rel_type', 'invoice');
            $this->db->where('(description LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\' OR long_description LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\')');
            if (!$has_permission_view_invoices) {
                $this->db->where('rel_id IN (select id from ' . db_prefix() . ($playground ? 'playground_' : '') . 'invoices where ' . $noPermissionQuery . ')');
            }
            $this->db->order_by('description', 'ASC');
            $result[] = ['result' => $this->db->get()->result_array(), 'type' => 'invoice_items', 'search_heading' => _l('invoice_items'), ];
        }
        // Estimate Items Search
        $has_permission_view_estimates = staff_can('view', 'estimates');
        $has_permission_view_estimates_own = staff_can('view_own', 'estimates');
        $allow_staff_view_estimates_assigned = get_option('allow_staff_view_estimates_assigned');
        if ($has_permission_view_estimates || $has_permission_view_estimates_own || $allow_staff_view_estimates_assigned) {
            $noPermissionQuery = get_estimates_where_sql_for_staff(get_staff_user_id());
            $this->db->select()->from(db_prefix() . ($playground ? 'playground_' : '') . 'itemable');
            $this->db->where('rel_type', 'estimate');
            if (!$has_permission_view_estimates) {
                $this->db->where('rel_id IN (select id from ' . db_prefix() . ($playground ? 'playground_' : '') . 'estimates where ' . $noPermissionQuery . ')');
            }
            $this->db->where('(description LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\' OR long_description LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\')');
            $this->db->order_by('description', 'ASC');
            $result[] = ['result' => $this->db->get()->result_array(), 'type' => 'estimate_items', 'search_heading' => _l('estimate_items'), ];
        }
        $result = hooks()->apply_filters('global_search_result_query', $result, $q, $limit);
        return $result;
    }

    public function _search_leads($q, $limit = 0, $where = [], $playground = false) {
        $result = ['result' => [], 'type' => 'leads', 'search_heading' => _l('leads'), ];
        $has_permission_view = staff_can('view', 'leads');
        if (is_staff_member()) {
            // Leads
            $this->db->select();
            $this->db->from(db_prefix() . ($playground ? 'playground_' : '') . 'leads');
            if (!$has_permission_view) {
                $this->db->where('(assigned = ' . get_staff_user_id() . ' OR addedfrom = ' . get_staff_user_id() . ' OR is_public=1)');
            }
            if (!startsWith($q, '#')) {
                $this->db->where('(name LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR title LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR company LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR zip LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR city LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR state LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR address LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR email LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR phonenumber LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                )');
            } else {
                $this->db->where('id IN
                    (SELECT rel_id FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'taggables WHERE tag_id IN
                    (SELECT id FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'tags WHERE name="' . $this->db->escape_str(strafter($q, '#')) . '")
                    AND ' . db_prefix() . ($playground ? 'playground_' : '') . 'taggables.rel_type=\'lead\' GROUP BY rel_id HAVING COUNT(tag_id) = 1)
                ');
            }
            $this->db->where($where);
            if ($limit != 0) {
                $this->db->limit($limit);
            }
            $this->db->order_by('name', 'ASC');
            $result['result'] = $this->db->get()->result_array();
        }
        return $result;
    }

    public function _search_tickets($q, $limit = 0, $playground = false) {
        $result = ['result' => [], 'type' => 'tickets', 'search_heading' => _l('support_tickets'), ];
        if (is_staff_member() || (!is_staff_member() && get_option('access_tickets_to_none_staff_members') == 1)) {
            $is_admin = is_admin();
            $where = '';
            if (!$is_admin && get_option('staff_access_only_assigned_departments') == 1) {
                $this->load->model('departments_model');
                $staff_deparments_ids = $this->departments_model->get_staff_departments(get_staff_user_id(), true, $playground);
                $departments_ids = [];
                if (count($staff_deparments_ids) == 0) {
                    $departments = $this->departments_model->get();
                    foreach ($departments as $department) {
                        array_push($departments_ids, $department['departmentid']);
                    }
                } else {
                    $departments_ids = $staff_deparments_ids;
                }
                if (count($departments_ids) > 0) {
                    $where = 'department IN (SELECT departmentid FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'staff_departments WHERE departmentid IN (' . implode(',', $departments_ids) . ') AND staffid="' . get_staff_user_id() . '")';
                }
            }
            $this->db->select();
            $this->db->from(db_prefix() . ($playground ? 'playground_' : '') . 'tickets');
            $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'departments', db_prefix() . ($playground ? 'playground_' : '') . 'departments.departmentid = ' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets.department');
            $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'clients', db_prefix() . ($playground ? 'playground_' : '') . 'clients.userid = ' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets.userid', 'left');
            $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'contacts', db_prefix() . ($playground ? 'playground_' : '') . 'contacts.id = ' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets.contactid', 'left');
            if (!startsWith($q, '#')) {
                $this->db->where('(
                    ticketid LIKE "' . $q . '%"
                    OR subject LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR message LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR ' . db_prefix() . ($playground ? 'playground_' : '') . 'contacts.email LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR CONCAT(firstname, \' \', lastname) LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR CONCAT(lastname, \' \', firstname) LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR company LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR vat LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR ' . db_prefix() . ($playground ? 'playground_' : '') . 'contacts.phonenumber LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.phonenumber LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR city LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR state LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR address LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR ' . db_prefix() . ($playground ? 'playground_' : '') . 'departments.name LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    )');
                if ($where != '') {
                    $this->db->where($where);
                }
            } else {
                $this->db->where('ticketid IN
                    (SELECT rel_id FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'taggables WHERE tag_id IN
                    (SELECT id FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'tags WHERE name="' . $this->db->escape_str(strafter($q, '#')) . '")
                    AND ' . db_prefix() . ($playground ? 'playground_' : '') . 'taggables.rel_type=\'ticket\' GROUP BY rel_id HAVING COUNT(tag_id) = 1)
                ');
            }
            if ($limit != 0) {
                $this->db->limit($limit);
            }
            $this->db->order_by('ticketid', 'DESC');
            $result['result'] = $this->db->get()->result_array();
        }
        return $result;
    }

    public function _search_contacts($q, $limit = 0, $where = '', $playground = false) {
        $result = ['result' => [], 'type' => 'contacts', 'search_heading' => _l('customer_contacts'), ];
        $have_assigned_customers = have_assigned_customers();
        $have_permission_customers_view = staff_can('view', 'customers');
        $tickets_contacts = $this->input->post('tickets_contacts') && get_option('staff_members_open_tickets_to_all_contacts') == 1;
        if ($have_assigned_customers || $have_permission_customers_view || $tickets_contacts) {
            // Contacts
            $this->db->select(implode(',', prefixed_table_fields_array(db_prefix() . ($playground ? 'playground_' : '') . 'contacts')) . ',company');
            $this->db->from(db_prefix() . ($playground ? 'playground_' : '') . 'contacts');
            $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'clients', '' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.userid=' . db_prefix() . ($playground ? 'playground_' : '') . 'contacts.userid', 'left');
            $this->db->where('(firstname LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR lastname LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR email LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR CONCAT(firstname, \' \', lastname) LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR CONCAT(lastname, \' \', firstname) LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR ' . db_prefix() . ($playground ? 'playground_' : '') . 'contacts.phonenumber LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR ' . db_prefix() . ($playground ? 'playground_' : '') . 'contacts.title LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR company LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
            )');
            if ($where != '') {
                $this->db->where($where);
            }
            if ($limit != 0) {
                $this->db->limit($limit);
            }
            $this->db->order_by('firstname', 'ASC');
            $result['result'] = $this->db->get()->result_array();
        }
        return $result;
    }

    public function search_contracts($q, $limit = 0, $playground = false) {
        $result = ['result' => [], 'type' => 'contracts', 'search_heading' => _l('contracts'), ];
        $has_permission_view_contracts = staff_can('view', 'contracts');
        if ($has_permission_view_contracts || staff_can('view_own', 'contracts')) {
            // Contracts
            $this->db->select();
            $this->db->from(db_prefix() . ($playground ? 'playground_' : '') . 'contracts');
            if (!$has_permission_view_contracts) {
                $this->db->where(db_prefix() . ($playground ? 'playground_' : '') . 'contracts.addedfrom', get_staff_user_id());
            }
            $this->db->where('(description LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\' OR subject LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\')');
            if ($limit != 0) {
                $this->db->limit($limit);
            }
            $this->db->order_by('subject', 'ASC');
            $result['result'] = $this->db->get()->result_array();
        }
        return $result;
    }

    public function search_projects($q, $limit = 0, $where = false, $rel_type = null, $playground = false) {
        $result = ['result' => [], 'type' => 'projects', 'search_heading' => _l('projects'), ];
        $projects = staff_can('view', 'projects');
        // Projects
        $this->db->select();
        $this->db->from(db_prefix() . ($playground ? 'playground_' : '') . 'projects');
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'clients', db_prefix() . ($playground ? 'playground_' : '') . 'clients.userid = ' . db_prefix() . ($playground ? 'playground_' : '') . 'projects.clientid');
        if (isset($rel_type) && 'lead' == $rel_type) {
            $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'leads', db_prefix() . ($playground ? 'playground_' : '') . 'leads.id = ' . db_prefix() . ($playground ? 'playground_' : '') . 'projects.clientid');
        } else {
            $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'clients', db_prefix() . ($playground ? 'playground_' : '') . 'clients.userid = ' . db_prefix() . ($playground ? 'playground_' : '') . 'projects.clientid', 'LEFT');
            $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'leads', db_prefix() . ($playground ? 'playground_' : '') . 'leads.id = ' . db_prefix() . ($playground ? 'playground_' : '') . 'projects.clientid', 'LEFT');
        }
        if (!$projects) {
            $this->db->where(db_prefix() . ($playground ? 'playground_' : '') . 'projects.id IN (SELECT project_id FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'project_members WHERE staff_id=' . get_staff_user_id() . ')');
        }
        if ($where != false) {
            $this->db->where($where);
        }
        if (!startsWith($q, '#')) {
            $this->db->where('(company LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR description LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR name LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR vat LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR phonenumber LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR city LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR zip LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR state LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR zip LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR address LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
            )');
        } else {
            $this->db->where('id IN
                (SELECT rel_id FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'taggables WHERE tag_id IN
                (SELECT id FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'tags WHERE name="' . $this->db->escape_str(strafter($q, '#')) . '")
                AND ' . db_prefix() . ($playground ? 'playground_' : '') . 'taggables.rel_type=\'project\' GROUP BY rel_id HAVING COUNT(tag_id) = 1)
            ');
        }
        if ($limit != 0) {
            $this->db->limit($limit);
        }
        $this->db->order_by('name', 'ASC');
        $result['result'] = $this->db->get()->result_array();
        return $result;
    }

    public function _search_invoices($q, $limit = 0, $playground = false) {
        $result = ['result' => [], 'type' => 'invoices', 'search_heading' => _l('invoices'), ];
        $has_permission_view_invoices = staff_can('view', 'invoices');
        $has_permission_view_invoices_own = staff_can('view_own', 'invoices');
        if ($has_permission_view_invoices || $has_permission_view_invoices_own || get_option('allow_staff_view_invoices_assigned') == '1') {
            if (is_numeric($q)) {
                $q = trim($q);
                $q = ltrim($q, '0');
            }
            $invoice_fields = prefixed_table_fields_array(db_prefix() . ($playground ? 'playground_' : '') . 'invoices');
            $clients_fields = prefixed_table_fields_array(db_prefix() . ($playground ? 'playground_' : '') . 'clients');
            $noPermissionQuery = get_invoices_where_sql_for_staff(get_staff_user_id());
            // Invoices
            $this->load->model('clients_model');
            $this->db->select(implode(',', $invoice_fields) . ',' . implode(',', $clients_fields) . ',' . db_prefix() . ($playground ? 'playground_' : '') . 'invoices.id as invoiceid,' . $this->clients_model->get_sql_select_client_company('company', $playground));
            $this->db->from(db_prefix() . ($playground ? 'playground_' : '') . 'invoices');
            $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'clients', db_prefix() . ($playground ? 'playground_' : '') . 'clients.userid = ' . db_prefix() . ($playground ? 'playground_' : '') . 'invoices.clientid', 'left');
            $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'currencies', db_prefix() . ($playground ? 'playground_' : '') . 'currencies.id = ' . db_prefix() . ($playground ? 'playground_' : '') . 'invoices.currency');
            $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'contacts', db_prefix() . ($playground ? 'playground_' : '') . 'contacts.userid = ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.userid AND is_primary = 1', 'left');
            if (!$has_permission_view_invoices) {
                $this->db->where($noPermissionQuery);
            }
            if (!startsWith($q, '#')) {
                $this->db->where('(
                    ' . db_prefix() . ($playground ? 'playground_' : '') . 'invoices.formatted_number = "' . $this->db->escape_like_str($q) . '"
                    OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'invoices.number = "' . $this->db->escape_like_str($q) . '"
                    OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.company LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'invoices.clientnote LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.vat LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.phonenumber LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.city LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.state LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.zip LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.address LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'invoices.adminnote LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR
                    CONCAT(firstname,\' \',lastname) LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR
                    CONCAT(lastname,\' \',firstname) LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'invoices.billing_street LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'invoices.billing_city LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'invoices.billing_state LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'invoices.billing_zip LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'invoices.shipping_street LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'invoices.shipping_city LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'invoices.shipping_state LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'invoices.shipping_zip LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.billing_street LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.billing_city LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.billing_state LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.billing_zip LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.shipping_street LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.shipping_city LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.shipping_state LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                    OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.shipping_zip LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                )');
            } else {
                $this->db->where(db_prefix() . ($playground ? 'playground_' : '') . 'invoices.id IN
                    (SELECT rel_id FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'taggables WHERE tag_id IN
                    (SELECT id FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'tags WHERE name="' . $this->db->escape_str(strafter($q, '#')) . '")
                    AND ' . db_prefix() . ($playground ? 'playground_' : '') . 'taggables.rel_type=\'invoice\' GROUP BY rel_id HAVING COUNT(tag_id) = 1)
                ');
            }
            $this->db->order_by('number,YEAR(date)', 'desc');
            if ($limit != 0) {
                $this->db->limit($limit);
            }
            $result['result'] = $this->db->get()->result_array();
        }
        return $result;
    }

    public function _search_credit_notes($q, $limit = 0, $playground = false) {
        $result = ['result' => [], 'type' => 'credit_note', 'search_heading' => _l('credit_notes'), ];
        $has_permission_view_credit_notes = staff_can('view', 'credit_notes');
        $has_permission_view_credit_notes_own = staff_can('view_own', 'credit_notes');
        if ($has_permission_view_credit_notes || $has_permission_view_credit_notes_own) {
            if (is_numeric($q)) {
                $q = trim($q);
                $q = ltrim($q, '0');
            }
            $credit_note_fields = prefixed_table_fields_array(db_prefix() . ($playground ? 'playground_' : '') . 'creditnotes');
            $clients_fields = prefixed_table_fields_array(db_prefix() . ($playground ? 'playground_' : '') . 'clients');
            // Invoices
            $this->load->model('clients_model');
            $this->db->select(implode(',', $credit_note_fields) . ',' . implode(',', $clients_fields) . ',' . db_prefix() . ($playground ? 'playground_' : '') . 'creditnotes.id as credit_note_id,' . $this->clients_model->get_sql_select_client_company('company', $playground));
            $this->db->from(db_prefix() . ($playground ? 'playground_' : '') . 'creditnotes');
            $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'clients', db_prefix() . ($playground ? 'playground_' : '') . 'clients.userid = ' . db_prefix() . ($playground ? 'playground_' : '') . 'creditnotes.clientid', 'left');
            $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'currencies', db_prefix() . ($playground ? 'playground_' : '') . 'currencies.id = ' . db_prefix() . ($playground ? 'playground_' : '') . 'creditnotes.currency');
            $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'contacts', db_prefix() . ($playground ? 'playground_' : '') . 'contacts.userid = ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.userid AND is_primary = 1', 'left');
            if (!$has_permission_view_credit_notes) {
                $this->db->where(db_prefix() . ($playground ? 'playground_' : '') . 'creditnotes.addedfrom', get_staff_user_id());
            }
            $this->db->where('(
                ' . db_prefix() . ($playground ? 'playground_' : '') . 'creditnotes.formatted_number = "' . $this->db->escape_like_str($q) . '"
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'creditnotes.number = "' . $this->db->escape_like_str($q) . '"
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.company LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'creditnotes.clientnote LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.vat LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.phonenumber LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.city LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.state LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.zip LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.address LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'creditnotes.adminnote LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR
                CONCAT(firstname,\' \',lastname) LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR
                CONCAT(lastname,\' \',firstname) LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'creditnotes.billing_street LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'creditnotes.billing_city LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'creditnotes.billing_state LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'creditnotes.billing_zip LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'creditnotes.shipping_street LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'creditnotes.shipping_city LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'creditnotes.shipping_state LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'creditnotes.shipping_zip LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.billing_street LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.billing_city LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.billing_state LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.billing_zip LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.shipping_street LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.shipping_city LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.shipping_state LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.shipping_zip LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
            )');
            $this->db->order_by('number', 'desc');
            if ($limit != 0) {
                $this->db->limit($limit);
            }
            $result['result'] = $this->db->get()->result_array();
        }
        return $result;
    }

    public function search_estimates($q, $limit = 0, $playground = false) {
        $result = ['result' => [], 'type' => 'estimates', 'search_heading' => _l('estimates'), ];
        $has_permission_view_estimates = staff_can('view', 'estimates');
        $has_permission_view_estimates_own = staff_can('view_own', 'estimates');
        if ($has_permission_view_estimates || $has_permission_view_estimates_own || get_option('allow_staff_view_estimates_assigned') == '1') {
            if (is_numeric($q)) {
                $q = trim($q);
                $q = ltrim($q, '0');
            }
            // Estimates
            $estimates_fields = prefixed_table_fields_array(db_prefix() . ($playground ? 'playground_' : '') . 'estimates');
            $clients_fields = prefixed_table_fields_array(db_prefix() . ($playground ? 'playground_' : '') . 'clients');
            $noPermissionQuery = get_estimates_where_sql_for_staff(get_staff_user_id());
            $this->load->model('clients_model');
            $this->db->select(implode(',', $estimates_fields) . ',' . implode(',', $clients_fields) . ',' . db_prefix() . ($playground ? 'playground_' : '') . 'estimates.id as estimateid,' . $this->clients_model->get_sql_select_client_company('company', $playground));
            $this->db->from(db_prefix() . ($playground ? 'playground_' : '') . 'estimates');
            $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'clients', db_prefix() . ($playground ? 'playground_' : '') . 'clients.userid = ' . db_prefix() . ($playground ? 'playground_' : '') . 'estimates.clientid', 'left');
            $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'currencies', db_prefix() . ($playground ? 'playground_' : '') . 'currencies.id = ' . db_prefix() . ($playground ? 'playground_' : '') . 'estimates.currency');
            $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'contacts', db_prefix() . ($playground ? 'playground_' : '') . 'contacts.userid = ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.userid AND is_primary = 1', 'left');
            if (!$has_permission_view_estimates) {
                $this->db->where($noPermissionQuery);
            }
            $this->db->where('(
                ' . db_prefix() . ($playground ? 'playground_' : '') . 'estimates.formatted_number = "' . $this->db->escape_like_str($q) . '"
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'estimates.number = "' . $this->db->escape_like_str($q) . '"
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.company LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'estimates.clientnote LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.vat LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.phonenumber LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.city LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.state LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.zip LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR address LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'estimates.adminnote LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'estimates.billing_street LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'estimates.billing_city LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'estimates.billing_state LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'estimates.billing_zip LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'estimates.shipping_street LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'estimates.shipping_city LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'estimates.shipping_state LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'estimates.shipping_zip LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.billing_street LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.billing_city LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.billing_state LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.billing_zip LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.shipping_street LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.shipping_city LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.shipping_state LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR  ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.shipping_zip LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
            )');
            $this->db->order_by('number,YEAR(date)', 'desc');
            if ($limit != 0) {
                $this->db->limit($limit);
            }
            $result['result'] = $this->db->get()->result_array();
        }
        return $result;
    }

    public function search_expenses($q, $limit = 0, $playground = false) {
        $result = ['result' => [], 'type' => 'expenses', 'search_heading' => _l('expenses'), ];
        $has_permission_expenses_view = staff_can('view', 'expenses');
        $has_permission_expenses_view_own = staff_can('view_own', 'expenses');
        if ($has_permission_expenses_view || $has_permission_expenses_view_own) {
            // Expenses
            $this->db->select('*,' . db_prefix() . ($playground ? 'playground_' : '') . 'expenses.amount as amount,' . db_prefix() . ($playground ? 'playground_' : '') . 'expenses_categories.name as category_name,' . db_prefix() . ($playground ? 'playground_' : '') . 'payment_modes.name as payment_mode_name,' . db_prefix() . ($playground ? 'playground_' : '') . 'taxes.name as tax_name, ' . db_prefix() . ($playground ? 'playground_' : '') . 'expenses.id as expenseid,' . db_prefix() . ($playground ? 'playground_' : '') . 'currencies.name as currency_name');
            $this->db->from(db_prefix() . ($playground ? 'playground_' : '') . 'expenses');
            $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'clients', db_prefix() . ($playground ? 'playground_' : '') . 'clients.userid = ' . db_prefix() . ($playground ? 'playground_' : '') . 'expenses.clientid', 'left');
            $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'payment_modes', db_prefix() . ($playground ? 'playground_' : '') . 'payment_modes.id = ' . db_prefix() . ($playground ? 'playground_' : '') . 'expenses.paymentmode', 'left');
            $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'taxes', db_prefix() . ($playground ? 'playground_' : '') . 'taxes.id = ' . db_prefix() . ($playground ? 'playground_' : '') . 'expenses.tax', 'left');
            $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'expenses_categories', db_prefix() . ($playground ? 'playground_' : '') . 'expenses_categories.id = ' . db_prefix() . ($playground ? 'playground_' : '') . 'expenses.category');
            $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'currencies', '' . db_prefix() . ($playground ? 'playground_' : '') . 'currencies.id = ' . db_prefix() . ($playground ? 'playground_' : '') . 'expenses.currency', 'left');
            if (!$has_permission_expenses_view) {
                $this->db->where(db_prefix() . ($playground ? 'playground_' : '') . 'expenses.addedfrom', get_staff_user_id());
            }
            $this->db->where('(company LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR paymentmode LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR ' . db_prefix() . ($playground ? 'playground_' : '') . 'payment_modes.name LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR vat LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR phonenumber LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR city LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR zip LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR address LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR state LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR ' . db_prefix() . ($playground ? 'playground_' : '') . 'expenses_categories.name LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR ' . db_prefix() . ($playground ? 'playground_' : '') . 'expenses.note LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR ' . db_prefix() . ($playground ? 'playground_' : '') . 'expenses.expense_name LIKE "%' . $this->db->escape_like_str($q) . '%" ESCAPE \'!\'
                )');
            if ($limit != 0) {
                $this->db->limit($limit);
            }
            $this->db->order_by('date', 'DESC');
            $result['result'] = $this->db->get()->result_array();
        }
        return $result;
    }

    public function search_tasks($q, $limit = 0, $playground = false)
    {
        $result = [
            'result'         => [],
            'type'           => 'tasks',
            'search_heading' => _l('tasks'),
        ];

        if (has_permission('tasks', '', 'view')) {
            // task
            $this->load->model('custom_fields_model');
            $fields = $this->custom_fields_model->get_custom_fields('tasks', [], false, $playground);
            $this->db->select(db_prefix() . ($playground ? 'playground_' : '') . 'tasks.*');
            $this->db->from(db_prefix() . ($playground ? 'playground_' : '') . 'tasks');
            $this->db->like('name', $q);
            $this->db->or_like(db_prefix() . ($playground ? 'playground_' : '') . 'tasks.id', $q);
            foreach ($fields as $key => $value) {
                $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'customfieldsvalues as ctable_'.$key.'', db_prefix() . ($playground ? 'playground_' : '') . 'tasks.id = ctable_'.$key.'.relid and ctable_'.$key.'.fieldto="tasks" AND ctable_'.$key.'.fieldid='.$value['id'], 'LEFT');
                $this->db->or_like('ctable_'.$key.'.value', $q);
            }

            if (0 != $limit) {
                $this->db->limit($limit);
            }
            $this->db->order_by('name', 'ASC');
            $result['result'] = $this->db->get()->result_array();
        }

        return $result;
    }

    public function search_staff($q, $limit = 0, $playground = false)
    {
        $result = [
            'result'         => [],
            'type'           => 'staff',
            'search_heading' => _l('staff_members'),
        ];

        if (has_permission('staff', '', 'view')) {
            // Staff
            $this->load->model('custom_fields_model');
            $fields = $this->custom_fields_model->get_custom_fields('staff', [], false, $playground);
            $this->db->select('staff.*');
            $this->db->from(db_prefix() . ($playground ? 'playground_' : '') . 'staff');
            $this->db->like('firstname', $q);
            $this->db->or_like('lastname', $q);
            $this->db->or_like("CONCAT(firstname, ' ', lastname)", $q, false);
            $this->db->or_like('facebook', $q);
            $this->db->or_like('linkedin', $q);
            $this->db->or_like('phonenumber', $q);
            $this->db->or_like('email', $q);
            $this->db->or_like('skype', $q);
            foreach ($fields as $key => $value) {
                $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'customfieldsvalues as ctable_'.$key.'', db_prefix() . ($playground ? 'playground_' : '') . 'staff.staffid = ctable_'.$key.'.relid and ctable_'.$key.'.fieldto="staff" AND ctable_'.$key.'.fieldid='.$value['id'], 'LEFT');
                $this->db->or_like('ctable_'.$key.'.value', $q);
            }

            if (0 != $limit) {
                $this->db->limit($limit);
            }
            $this->db->order_by('firstname', 'ASC');
            $result['result'] = $this->db->get()->result_array();
        }

        return $result;
    }

    public function search_proposals($q, $limit = 0, $playground = false)
    {
        $this->load->model('custom_fields_model');
        $fields = $this->custom_fields_model->get_custom_fields('proposal', [], false, $playground);
        $result = [
            'result'         => [],
            'type'           => 'proposals',
            'search_heading' => _l('proposals'),
        ];

        $has_permission_view_proposals     = has_permission('proposals', '', 'view');
        $has_permission_view_proposals_own = has_permission('proposals', '', 'view_own');

        if ($has_permission_view_proposals || $has_permission_view_proposals_own || '1' == get_option('allow_staff_view_proposals_assigned')) {
            if (is_numeric($q)) {
                $q = trim($q);
                $q = ltrim($q, '0');
            } else if (startsWith($q, get_option('proposal_number_prefix'))) {
                $q = strafter($q, get_option('proposal_number_prefix'));
                $q = trim($q);
                $q = ltrim($q, '0');
            }

            $where_string = '';
            foreach ($fields as $key => $value) {
                $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'customfieldsvalues as ctable_'.$key.'', db_prefix() . ($playground ? 'playground_' : '') . 'proposals.id = ctable_'.$key.'.relid and ctable_'.$key.'.fieldto="proposal" AND ctable_'.$key.'.fieldid='.$value['id'], 'LEFT');
                $where_string .= ' OR ctable_'.$key.'.value LIKE "%'.$q.'%"';
            }

            // Proposals
            $this->db->select('*,'.db_prefix() . ($playground ? 'playground_' : '') . 'proposals.id as id');
            $this->db->from(db_prefix() . ($playground ? 'playground_' : '') . 'proposals');
            $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'currencies', db_prefix() . ($playground ? 'playground_' : '') . 'currencies.id = '.db_prefix() . ($playground ? 'playground_' : '') . 'proposals.currency');

            $this->db->where('('.db_prefix() . ($playground ? 'playground_' : '') . 'proposals.id LIKE "'.$q.'%"
                OR '.db_prefix() . ($playground ? 'playground_' : '') . 'proposals.subject LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR '.db_prefix() . ($playground ? 'playground_' : '') . 'proposals.content LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR '.db_prefix() . ($playground ? 'playground_' : '') . 'proposals.proposal_to LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR '.db_prefix() . ($playground ? 'playground_' : '') . 'proposals.zip LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR '.db_prefix() . ($playground ? 'playground_' : '') . 'proposals.state LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR '.db_prefix() . ($playground ? 'playground_' : '') . 'proposals.city LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR '.db_prefix() . ($playground ? 'playground_' : '') . 'proposals.address LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR '.db_prefix() . ($playground ? 'playground_' : '') . 'proposals.email LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                OR '.db_prefix() . ($playground ? 'playground_' : '') . 'proposals.phone LIKE "%'.$this->db->escape_like_str($q).'%" ESCAPE \'!\'
                '.$where_string.'
            )');

            $this->db->order_by(db_prefix() . ($playground ? 'playground_' : '') . 'proposals.id', 'desc');
            if (0 != $limit) {
                $this->db->limit($limit);
            }
            $result['result'] = $this->db->get()->result_array();
        }

        return $result;
    }

    public function get_milestones($id = '', $where = [], $playground = false)
    {
        $this->db->select('*, (SELECT COUNT(id) FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'tasks WHERE milestone=' . db_prefix() . ($playground ? 'playground_' : '') . 'milestones.id) as total_tasks, (SELECT COUNT(id) FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'tasks WHERE rel_type="project" and milestone=' . db_prefix() . ($playground ? 'playground_' : '') . 'milestones.id AND status=5) as total_finished_tasks');
        if ('' != $id) {
            $this->db->where('id', $id);
        }
        if ((is_array($where) && count($where) > 0) || (is_string($where) && '' != $where)) {
            $this->db->where($where);
        }
        $this->db->order_by('milestone_order', 'ASC');
        $milestones = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'milestones')->result_array();

        return $milestones;
    }    

    public function get_milestone($id, $playground = false) {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'milestones')->row();
    }

    public function value($value)
    {
        if ($value) {
            return $value;
        }

        return '';
    }
    
    /**
     * Function that return full path for upload based on passed type
     * @param  string $type
     * @return string
     */
    public function get_upload_path_by_type($type, $playground = false) {
        $path = '';
        switch ($type) {
            case 'lead':
                $path = FCPATH . 'uploads/' . ($playground ? 'playground_' : '') . 'leads' . '/';
            break;
            case 'expense':
                $path = FCPATH . 'uploads/' . ($playground ? 'playground_' : '') . 'expenses' . '/';
            break;
            case 'project':
                $path = FCPATH . 'uploads/' . ($playground ? 'playground_' : '') . 'projects' . '/';
            break;
            case 'proposal':
                $path = FCPATH . 'uploads/' . ($playground ? 'playground_' : '') . 'proposals' . '/';
            break;
            case 'estimate':
                $path = FCPATH . 'uploads/' . ($playground ? 'playground_' : '') . 'estimates' . '/';
            break;
            case 'invoice':
                $path = FCPATH . 'uploads/' . ($playground ? 'playground_' : '') . 'invoices' . '/';
            break;
            case 'credit_note':
                $path = FCPATH . 'uploads/' . ($playground ? 'playground_' : '') . 'credit_notes' . '/';
            break;
            case 'task':
                $path = FCPATH . 'uploads/' . ($playground ? 'playground_' : '') . 'tasks' . '/';
            break;
            case 'contract':
                $path = FCPATH . 'uploads/' . ($playground ? 'playground_' : '') . 'contracts' . '/';
            break;
            case 'customer':
                $path = FCPATH . 'uploads/' . ($playground ? 'playground_' : '') . 'clients' . '/';
            break;
            case 'staff':
                $path = FCPATH . 'uploads/' . ($playground ? 'playground_' : '') . 'staff_profile_images' . '/';
            break;
            case 'company':
                $path = FCPATH . 'uploads/' . ($playground ? 'playground_' : '') . 'company' . '/';
            break;
            case 'ticket':
                $path = FCPATH . 'uploads/' . ($playground ? 'playground_' : '') . 'ticket_attachments' . '/';
            break;
            case 'contact_profile_images':
                $path = FCPATH . 'uploads/' . ($playground ? 'playground_' : '') . 'client_profile_images' . '/';
            break;
            case 'newsfeed':
                $path = FCPATH . 'uploads/' . ($playground ? 'playground_' : '') . 'newsfeed' . '/';
            break;
            case 'estimate_request':
                $path = FCPATH . 'uploads/' . ($playground ? 'playground_' : '') . 'newsfeed' . '/';
            break;
        }
        return hooks()->apply_filters('get_upload_path_by_type', $path, $type);
    }

    /**
    * Function that add and edit tags based on passed arguments
    * @param  string $tags
    * @param  mixed $rel_id
    * @param  string $rel_type
    * @return boolean
    */
    public function handle_tags_save($tags, $rel_id, $rel_type)
    {
        return _call_tags_method('save', $tags, $rel_id, $rel_type);
    }
    
    public function delete_tracked_emails($rel_id, $rel_type, $playground = false)
    {
        $this->db->where('rel_id', $rel_id);
        $this->db->where('rel_type', $rel_type);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'tracked_mails');
    }
}
