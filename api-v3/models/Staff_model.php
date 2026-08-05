<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Staff_model extends App_Model {
    public function __construct() {
        parent::__construct();
        
        $this->load->model('departments_model');
    }

    /**
    * Get staff full name
    * @param  string $userid Optional
    * @return string Firstname and Lastname
    */
    public function get_staff_full_name($userid = '', $playground = false)
    {
        $tmpStaffUserId = get_staff_user_id();
        if ($userid == '' || $userid == $tmpStaffUserId) {
            if (isset($GLOBALS['current_user'])) {
                return $GLOBALS['current_user']->firstname . ' ' . $GLOBALS['current_user']->lastname;
            }

            $userid = $tmpStaffUserId;
        }

        $staff = $this->app_object_cache->get('staff-full-name-data-' . $userid);

        if (!$staff) {
            $this->db->where('staffid', $userid);
            $staff = $this->db->select('firstname,lastname')->from(db_prefix() . ($playground ? 'playground_' : '') . 'staff')->get()->row();
            $this->app_object_cache->add('staff-full-name-data-' . $userid, $staff);
        }

        return $staff ? $staff->firstname . ' ' . $staff->lastname : '';
    }

    public function delete($id, $transfer_data_to, $playground = false) {
        if (!is_numeric($transfer_data_to)) {
            return false;
        }
        if ($id == $transfer_data_to) {
            return false;
        }
        hooks()->do_action('before_delete_staff_member', ['id' => $id, 'transfer_data_to' => $transfer_data_to, ]);
        $name = $this->get_staff_full_name($id, $playground);
        $transferred_to = $this->get_staff_full_name($transfer_data_to, $playground);
        $this->db->where('addedfrom', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'estimates', ['addedfrom' => $transfer_data_to, ]);
        $this->db->where('sale_agent', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'estimates', ['sale_agent' => $transfer_data_to, ]);
        $this->db->where('addedfrom', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'invoices', ['addedfrom' => $transfer_data_to, ]);
        $this->db->where('sale_agent', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'invoices', ['sale_agent' => $transfer_data_to, ]);
        $this->db->where('addedfrom', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'expenses', ['addedfrom' => $transfer_data_to, ]);
        $this->db->where('addedfrom', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'notes', ['addedfrom' => $transfer_data_to, ]);
        $this->db->where('userid', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'newsfeed_post_comments', ['userid' => $transfer_data_to, ]);
        $this->db->where('creator', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'newsfeed_posts', ['creator' => $transfer_data_to, ]);
        $this->db->where('staff_id', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'projectdiscussions', ['staff_id' => $transfer_data_to, ]);
        $this->db->where('addedfrom', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'projects', ['addedfrom' => $transfer_data_to, ]);
        $this->db->where('addedfrom', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'creditnotes', ['addedfrom' => $transfer_data_to, ]);
        $this->db->where('staff_id', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'credits', ['staff_id' => $transfer_data_to, ]);
        $this->db->where('staff_id', $id);
        $this->db->update(($playground ? 'playground_' : '') . 'filters', ['staff_id' => $transfer_data_to, ]);
        $this->db->where('staffid', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'project_files', ['staffid' => $transfer_data_to, ]);
        $this->db->where('staffid', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'proposal_comments', ['staffid' => $transfer_data_to, ]);
        $this->db->where('addedfrom', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'proposals', ['addedfrom' => $transfer_data_to, ]);
        $this->db->where('addedfrom', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'templates', ['addedfrom' => $transfer_data_to, ]);
        $this->db->where('staffid', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'task_comments', ['staffid' => $transfer_data_to, ]);
        $this->db->where('addedfrom', $id);
        $this->db->where('is_added_from_contact', 0);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'tasks', ['addedfrom' => $transfer_data_to, ]);
        $this->db->where('staffid', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'files', ['staffid' => $transfer_data_to, ]);
        $this->db->where('renewed_by_staff_id', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'contract_renewals', ['renewed_by_staff_id' => $transfer_data_to, ]);
        $this->db->where('addedfrom', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'task_checklist_items', ['addedfrom' => $transfer_data_to, ]);
        $this->db->where('assigned', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'task_checklist_items', ['assigned' => $transfer_data_to, ]);
        $this->db->where('finished_from', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'task_checklist_items', ['finished_from' => $transfer_data_to, ]);
        $this->db->where('admin', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'ticket_replies', ['admin' => $transfer_data_to, ]);
        $this->db->where('admin', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'tickets', ['admin' => $transfer_data_to, ]);
        $this->db->where('addedfrom', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'leads', ['addedfrom' => $transfer_data_to, ]);
        $this->db->where('assigned', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'leads', ['assigned' => $transfer_data_to, ]);
        $this->db->where('staff_id', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'taskstimers', ['staff_id' => $transfer_data_to, ]);
        $this->db->where('addedfrom', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'contracts', ['addedfrom' => $transfer_data_to, ]);
        $this->db->where('assigned_from', $id);
        $this->db->where('is_assigned_from_contact', 0);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'task_assigned', ['assigned_from' => $transfer_data_to, ]);
        $this->db->where('responsible', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'leads_email_integration', ['responsible' => $transfer_data_to, ]);
        $this->db->where('responsible', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'web_to_lead', ['responsible' => $transfer_data_to, ]);
        $this->db->where('responsible', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'estimate_request_forms', ['responsible' => $transfer_data_to, ]);
        $this->db->where('assigned', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'estimate_requests', ['assigned' => $transfer_data_to, ]);
        $this->db->where('created_from', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'subscriptions', ['created_from' => $transfer_data_to, ]);
        $this->db->where('notify_type', 'specific_staff');
        $web_to_lead = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'web_to_lead')->result_array();
        foreach ($web_to_lead as $form) {
            if (!empty($form['notify_ids'])) {
                $staff = unserialize($form['notify_ids']);
                if (is_array($staff) && in_array($id, $staff) && ($key = array_search($id, $staff)) !== false) {
                    unset($staff[$key]);
                    $staff = serialize(array_values($staff));
                    $this->db->where('id', $form['id']);
                    $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'web_to_lead', ['notify_ids' => $staff, ]);
                }
            }
        }
        $this->db->where('notify_type', 'specific_staff');
        $estimate_requests = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'estimate_request_forms')->result_array();
        foreach ($estimate_requests as $form) {
            if (!empty($form['notify_ids'])) {
                $staff = unserialize($form['notify_ids']);
                if (is_array($staff) && in_array($id, $staff) && ($key = array_search($id, $staff)) !== false) {
                    unset($staff[$key]);
                    $staff = serialize(array_values($staff));
                    $this->db->where('id', $form['id']);
                    $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'estimate_request_forms', ['notify_ids' => $staff, ]);
                }
            }
        }
        $this->db->where('id', 1);
        $leads_email_integration = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'leads_email_integration')->row();
        if ($leads_email_integration->notify_type == 'specific_staff') {
            if (!empty($leads_email_integration->notify_ids)) {
                $staff = unserialize($leads_email_integration->notify_ids);
                if (is_array($staff) && in_array($id, $staff) && ($key = array_search($id, $staff)) !== false) {
                    unset($staff[$key]);
                    $staff = serialize(array_values($staff));
                    $this->db->where('id', 1);
                    $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'leads_email_integration', ['notify_ids' => $staff, ]);
                }
            }
        }
        $this->db->where('assigned', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'tickets', ['assigned' => 0, ]);
        $this->db->where('staff', 1);
        $this->db->where('userid', $id);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'dismissed_announcements');
        $this->db->where('userid', $id);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'newsfeed_comment_likes');
        $this->db->where('userid', $id);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'newsfeed_post_likes');
        $this->db->where('staff_id', $id);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'customer_admins');
        $this->db->where('fieldto', 'staff');
        $this->db->where('relid', $id);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'customfieldsvalues');
        $this->db->where('userid', $id);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'events');
        $this->db->where('touserid', $id);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'notifications');
        $this->db->where('staff_id', $id);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'user_meta');
        $this->db->where('staff_id', $id);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'project_members');
        $this->db->where('staff_id', $id);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'project_notes');
        $this->db->where('creator', $id);
        $this->db->or_where('staff', $id);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'reminders');
        $this->db->where('staffid', $id);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'staff_departments');
        $this->db->where('staffid', $id);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'todos');
        $this->db->where('staff', 1);
        $this->db->where('user_id', $id);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'user_auto_login');
        $this->db->where('staff_id', $id);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'staff_permissions');
        $this->db->where('staffid', $id);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'task_assigned');
        $this->db->where('staffid', $id);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'task_followers');
        $this->db->where('staff_id', $id);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'pinned_projects');
        $this->db->where('staffid', $id);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'staff');
        log_activity('Staff Member Deleted [Name: ' . $name . ', Data Transferred To: ' . $transferred_to . ']');
        hooks()->do_action('staff_member_deleted', ['id' => $id, 'transfer_data_to' => $transfer_data_to, ]);
        return true;
    }

    /**
     * Get staff member/s
     * @param  mixed $id Optional - staff id
     * @param  mixed $where where in query
     * @return mixed if id is passed return object else array
     */
    public function get($id = '', $where = [], $playground = false) {
        $select_str = '*,CONCAT(firstname,\' \',lastname) as full_name';
        // Used to prevent multiple queries on logged in staff to check the total unread notifications in core/AdminController.php
        if (is_staff_logged_in() && $id != '' && $id == get_staff_user_id()) {
            $select_str.= ',(SELECT COUNT(*) FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'notifications WHERE touserid=' . get_staff_user_id() . ' and isread=0) as total_unread_notifications, (SELECT COUNT(*) FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'todos WHERE finished=0 AND staffid=' . get_staff_user_id() . ') as total_unfinished_todos';
        }
        $this->db->select($select_str);
        $this->db->where($where);
        if (is_numeric($id)) {
            $this->db->where('staffid', $id);
            $staff = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'staff')->row();
            if ($staff) {
                $staff->permissions = $this->get_staff_permissions($id, $playground);
            }
            return $staff;
        }
        $this->db->order_by('firstname', 'desc');
        return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'staff')->result_array();
    }

    /**
     * Get staff permissions
     * @param  mixed $id staff id
     * @return array
     */
    public function get_staff_permissions($id, $playground = false) {
        // Fix for version 2.3.1 tables upgrade
        if (defined('DOING_DATABASE_UPGRADE')) {
            return [];
        }
        $permissions = $this->app_object_cache->get('staff-' . $id . '-permissions');
        if (!$permissions && !is_array($permissions)) {
            $this->db->where('staff_id', $id);
            $permissions = $this->db->get(($playground ? 'playground_' : '') . 'staff_permissions')->result_array();
            $this->app_object_cache->add('staff-' . $id . '-permissions', $permissions);
        }
        return $permissions;
    }

    /**
     * Add new staff member
     * @param array $data staff $_POST data
     */
    public function add($data, $playground = false) {
        if (isset($data['fakeusernameremembered'])) {
            unset($data['fakeusernameremembered']);
        }
        if (isset($data['fakepasswordremembered'])) {
            unset($data['fakepasswordremembered']);
        }
        // First check for all cases if the email exists.
        $data = hooks()->apply_filters('before_create_staff_member', $data);
        $this->db->where('email', $data['email']);
        $email = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'staff')->row();
        if ($email) {
            die('Email already exists');
        }
        $data['admin'] = 0;
        if (is_admin()) {
            if (isset($data['administrator'])) {
                $data['admin'] = 1;
                unset($data['administrator']);
            }
        }
        $send_welcome_email = true;
        $original_password = $data['password'];
        if (!isset($data['send_welcome_email'])) {
            $send_welcome_email = false;
        } else {
            unset($data['send_welcome_email']);
        }
        $data['password'] = app_hash_password($data['password']);
        $data['datecreated'] = date('Y-m-d H:i:s');
        if (isset($data['departments'])) {
            $departments = $data['departments'];
            unset($data['departments']);
        }
        $permissions = [];
        if (isset($data['permissions'])) {
            $permissions = $data['permissions'];
            unset($data['permissions']);
        }
        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }
        if ($data['admin'] == 1) {
            $data['is_not_staff'] = 0;
        }
        $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'staff', $data);
        $staffid = $this->db->insert_id();
        if ($staffid) {
            $slug = $data['firstname'] . ' ' . $data['lastname'];
            if ($slug == ' ') {
                $slug = 'unknown-' . $staffid;
            }
            if ($send_welcome_email == true) {
                send_mail_template('staff_created', $data['email'], $staffid, $original_password, $playground);
            }
            $this->db->where('staffid', $staffid);
            $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'staff', ['media_path_slug' => slug_it($slug), ]);
            if (isset($custom_fields)) {
                $this->load->model('custom_fields_model');
                $this->custom_fields_model->handle_custom_fields_post($staffid, $custom_fields, false, $playground);
            }
            if (isset($departments)) {
                foreach ($departments as $department) {
                    $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'staff_departments', ['staffid' => $staffid, 'departmentid' => $department, ]);
                }
            }
            // Delete all staff permission if is admin we dont need permissions stored in database (in case admin check some permissions)
            $this->update_permissions($data['admin'] == 1 ? [] : $permissions, $staffid, $playground);
            log_activity('New Staff Member Added [ID: ' . $staffid . ', ' . $data['firstname'] . ' ' . $data['lastname'] . ']');
            // Get all announcements and set it to read.
            $this->db->select('announcementid');
            $this->db->from(db_prefix() . ($playground ? 'playground_' : '') . 'announcements');
            $this->db->where('showtostaff', 1);
            $announcements = $this->db->get()->result_array();
            foreach ($announcements as $announcement) {
                $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'dismissed_announcements', ['announcementid' => $announcement['announcementid'], 'staff' => 1, 'userid' => $staffid, ]);
            }
            hooks()->do_action('staff_member_created', $staffid);
            return $staffid;
        }
        return false;
    }

    /**
     * Update staff member info
     * @param  array $data staff data
     * @param  mixed $id   staff id
     * @return boolean
     */
    public function update($data, $id, $playground = false) {
        if (isset($data['fakeusernameremembered'])) {
            unset($data['fakeusernameremembered']);
        }
        if (isset($data['fakepasswordremembered'])) {
            unset($data['fakepasswordremembered']);
        }
        $data = hooks()->apply_filters('before_update_staff_member', $data, $id);
        if (is_admin()) {
            if (isset($data['administrator'])) {
                $data['admin'] = 1;
                unset($data['administrator']);
            } else {
                if ($id != get_staff_user_id()) {
                    if ($id == 1) {
                        return ['cant_remove_main_admin' => true, ];
                    }
                } else {
                    return ['cant_remove_yourself_from_admin' => true, ];
                }
                $data['admin'] = 0;
            }
        }
        $affectedRows = 0;
        if (isset($data['departments'])) {
            $departments = $data['departments'];
            unset($data['departments']);
        }
        $permissions = [];
        if (isset($data['permissions'])) {
            $permissions = $data['permissions'];
            unset($data['permissions']);
        }
        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            $this->load->model('custom_fields_model');
            if ($this->custom_fields_model->handle_custom_fields_post($id, $custom_fields, false, $playground)) {
                $affectedRows++;
            }
            unset($data['custom_fields']);
        }
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = app_hash_password($data['password']);
            $data['last_password_change'] = date('Y-m-d H:i:s');
        }
        // if (isset($data['two_factor_auth_enabled'])) {
        //     $data['two_factor_auth_enabled'] = 1;
        // } else {
        //     $data['two_factor_auth_enabled'] = 0;
        // }
        if (isset($data['is_not_staff'])) {
            $data['is_not_staff'] = 1;
        } else {
            $data['is_not_staff'] = 0;
        }
        if (isset($data['admin']) && $data['admin'] == 1) {
            $data['is_not_staff'] = 0;
        }
        $staff_departments = $this->departments_model->get_staff_departments($id, false, $playground);
        if (sizeof($staff_departments) > 0) {
            if (!isset($data['departments'])) {
                $this->db->where('staffid', $id);
                $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'staff_departments');
            } else {
                foreach ($staff_departments as $staff_department) {
                    if (isset($departments)) {
                        if (!in_array($staff_department['departmentid'], $departments)) {
                            $this->db->where('staffid', $id);
                            $this->db->where('departmentid', $staff_department['departmentid']);
                            $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'staff_departments');
                            if ($this->db->affected_rows() > 0) {
                                $affectedRows++;
                            }
                        }
                    }
                }
            }
            if (isset($departments)) {
                foreach ($departments as $department) {
                    $this->db->where('staffid', $id);
                    $this->db->where('departmentid', $department);
                    $_exists = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'staff_departments')->row();
                    if (!$_exists) {
                        $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'staff_departments', ['staffid' => $id, 'departmentid' => $department, ]);
                        if ($this->db->affected_rows() > 0) {
                            $affectedRows++;
                        }
                    }
                }
            }
        } else {
            if (isset($departments)) {
                foreach ($departments as $department) {
                    $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'staff_departments', ['staffid' => $id, 'departmentid' => $department, ]);
                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                    }
                }
            }
        }
        $this->db->where('staffid', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'staff', $data);
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }
        if ($this->update_permissions((isset($data['admin']) && $data['admin'] == 1 ? [] : $permissions), $id, $playground)) {
            $affectedRows++;
        }
        if ($affectedRows > 0) {
            hooks()->do_action('staff_member_updated', $id);
            log_activity('Staff Member Updated [ID: ' . $id . ', ' . $data['firstname'] . ' ' . $data['lastname'] . ']');
            return true;
        }
        return false;
    }

    public function update_permissions($permissions, $id, $playground = false) {
        $this->db->where('staff_id', $id);
        $this->db->delete(($playground ? 'playground_' : '') . 'staff_permissions');
        $is_staff_member = is_staff_member($id);
        foreach ($permissions as $feature => $capabilities) {
            foreach ($capabilities as $capability) {
                // Maybe do this via hook.
                if ($feature == 'leads' && !$is_staff_member) {
                    continue;
                }
                $this->db->insert(($playground ? 'playground_' : '') . 'staff_permissions', ['staff_id' => $id, 'feature' => $feature, 'capability' => $capability]);
            }
        }
        return true;
    }

    public function update_profile($data, $id, $playground = false) {
        $data = hooks()->apply_filters('before_staff_update_profile', $data, $id);
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = app_hash_password($data['password']);
            $data['last_password_change'] = date('Y-m-d H:i:s');
        }
        unset($data['two_factor_auth_enabled']);
        unset($data['google_auth_secret']);
        $this->db->where('staffid', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'staff', $data);
        if ($this->db->affected_rows() > 0) {
            hooks()->do_action('staff_member_profile_updated', $id);
            log_activity('Staff Profile Updated [Staff: ' . $this->get_staff_full_name($id, $playground) . ']');
            return true;
        }
        return false;
    }

    /**
     * Change staff passwordn
     * @param  mixed $data   password data
     * @param  mixed $userid staff id
     * @return mixed
     */
    public function change_password($data, $userid, $playground = false) {
        $data = hooks()->apply_filters('before_staff_change_password', $data, $userid);
        $member = $this->get($userid, [], $playground);
        // CHeck if member is active
        if ($member->active == 0) {
            return [['memberinactive' => true, ], ];
        }
        // Check new old password
        if (!app_hasher()->CheckPassword($data['oldpassword'], $member->password)) {
            return [['passwordnotmatch' => true, ], ];
        }
        $data['newpasswordr'] = app_hash_password($data['newpasswordr']);
        $this->db->where('staffid', $userid);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'staff', ['password' => $data['newpasswordr'], 'last_password_change' => date('Y-m-d H:i:s'), ]);
        if ($this->db->affected_rows() > 0) {
            log_activity('Staff Password Changed [' . $userid . ']');
            return true;
        }
        return false;
    }

    /**
     * Change staff status / active / inactive
     * @param  mixed $id     staff id
     * @param  mixed $status status(0/1)
     */
    public function change_staff_status($id, $status, $playground = false) {
        $status = hooks()->apply_filters('before_staff_status_change', $status, $id);
        $this->db->where('staffid', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'staff', ['active' => $status, ]);
        log_activity('Staff Status Changed [StaffID: ' . $id . ' - Status(Active/Inactive): ' . $status . ']');
        hooks()->do_action('after_staff_status_change', $id);
    }

    public function get_logged_time_data($id = '', $filter_data = [], $playground = false) {
        if ($id == '') {
            $id = get_staff_user_id();
        }
        $result['timesheets'] = [];
        $result['total'] = [];
        $result['this_month'] = [];
        $first_day_this_month = date('Y-m-01'); // hard-coded '01' for first day
        $last_day_this_month = date('Y-m-t 23:59:59');
        $result['last_month'] = [];
        $first_day_last_month = date('Y-m-01', strtotime('-1 MONTH')); // hard-coded '01' for first day
        $last_day_last_month = date('Y-m-t 23:59:59', strtotime('-1 MONTH'));
        $result['this_week'] = [];
        $first_day_this_week = date('Y-m-d', strtotime('monday this week'));
        $last_day_this_week = date('Y-m-d 23:59:59', strtotime('sunday this week'));
        $result['last_week'] = [];
        $first_day_last_week = date('Y-m-d', strtotime('monday last week'));
        $last_day_last_week = date('Y-m-d 23:59:59', strtotime('sunday last week'));
        $this->db->select('task_id,start_time,end_time,staff_id,' . db_prefix() . ($playground ? 'playground_' : '') . 'taskstimers.hourly_rate,name,' . db_prefix() . ($playground ? 'playground_' : '') . 'taskstimers.id,rel_id,rel_type, billed');
        $this->db->where('staff_id', $id);
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'tasks', db_prefix() . ($playground ? 'playground_' : '') . 'tasks.id = ' . db_prefix() . ($playground ? 'playground_' : '') . 'taskstimers.task_id', 'left');
        $timers = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'taskstimers')->result_array();
        $_end_time_static = time();
        $filter_period = false;
        if (isset($filter_data['period-from']) && $filter_data['period-from'] != '' && isset($filter_data['period-to']) && $filter_data['period-to'] != '') {
            $filter_period = true;
            $from = to_sql_date($filter_data['period-from']);
            $from = date('Y-m-d', strtotime($from));
            $to = to_sql_date($filter_data['period-to']);
            $to = date('Y-m-d', strtotime($to));
        }
        foreach ($timers as $timer) {
            $start_date = date('Y-m-d', $timer['start_time']);
            $end_time = $timer['end_time'];
            $notFinished = false;
            if ($timer['end_time'] == null) {
                $end_time = $_end_time_static;
                $notFinished = true;
            }
            $total = $end_time - $timer['start_time'];
            $result['total'][] = $total;
            $timer['total'] = $total;
            $timer['end_time'] = $end_time;
            $timer['not_finished'] = $notFinished;
            if ($start_date >= $first_day_this_month && $start_date <= $last_day_this_month) {
                $result['this_month'][] = $total;
                if (isset($filter_data['this_month']) && $filter_data['this_month'] != '') {
                    $result['timesheets'][$timer['id']] = $timer;
                }
            }
            if ($start_date >= $first_day_last_month && $start_date <= $last_day_last_month) {
                $result['last_month'][] = $total;
                if (isset($filter_data['last_month']) && $filter_data['last_month'] != '') {
                    $result['timesheets'][$timer['id']] = $timer;
                }
            }
            if ($start_date >= $first_day_this_week && $start_date <= $last_day_this_week) {
                $result['this_week'][] = $total;
                if (isset($filter_data['this_week']) && $filter_data['this_week'] != '') {
                    $result['timesheets'][$timer['id']] = $timer;
                }
            }
            if ($start_date >= $first_day_last_week && $start_date <= $last_day_last_week) {
                $result['last_week'][] = $total;
                if (isset($filter_data['last_week']) && $filter_data['last_week'] != '') {
                    $result['timesheets'][$timer['id']] = $timer;
                }
            }
            if ($filter_period == true) {
                if ($start_date >= $from && $start_date <= $to) {
                    $result['timesheets'][$timer['id']] = $timer;
                }
            }
        }
        $result['total'] = array_sum($result['total']);
        $result['this_month'] = array_sum($result['this_month']);
        $result['last_month'] = array_sum($result['last_month']);
        $result['this_week'] = array_sum($result['this_week']);
        $result['last_week'] = array_sum($result['last_week']);
        return $result;
    }
}
