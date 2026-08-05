<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Roles_model extends App_Model {
    public function __construct() {
        parent::__construct();
    }

    /**
     * Add new employee role
     * @param mixed $data
     */
    public function add($data, $playground = false) {
        $permissions = [];
        if (isset($data['permissions'])) {
            $permissions = $data['permissions'];
        }
        $data['permissions'] = serialize($permissions);
        $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'roles', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Role Added [ID: ' . $insert_id . '.' . $data['name'] . ']');
            return $insert_id;
        }
        return false;
   }

    /**
     * Update employee role
     * @param  array $data role data
     * @param  mixed $id   role id
     * @return boolean
     */
    public function update($data, $id, $playground = false) {
        $affectedRows = 0;
        $permissions = [];
        if (isset($data['permissions'])) {
            $permissions = $data['permissions'];
        }
        $data['permissions'] = serialize($permissions);
        $update_staff_permissions = false;
        if (isset($data['update_staff_permissions'])) {
            $update_staff_permissions = true;
            unset($data['update_staff_permissions']);
        }
        $this->db->where('roleid', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'roles', $data);
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }
        if ($update_staff_permissions == true) {
            $this->load->model('staff_model');
            $staff = $this->staff_model->get('', ['role' => $id, ], $playground);
            foreach ($staff as $member) {
                if ($this->staff_model->update_permissions($permissions, $member['staffid'], $playground)) {
                    $affectedRows++;
                }
            }
        }
        if ($affectedRows > 0) {
            log_activity('Role Updated [ID: ' . $id . ', Name: ' . $data['name'] . ']');
            return true;
        }
        return false;
   }

    /**
     * Get employee role by id
     * @param  mixed $id Optional role id
     * @return mixed     array if not id passed else object
     */
    public function get($id = '', $playground = false) {
        if (is_numeric($id)) {
            $role = $this->app_object_cache->get('role-' . $id);
            if ($role) {
                return $role;
            }
            $this->db->where('roleid', $id);
            $role = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'roles')->row();
            $role->permissions = !empty($role->permissions) ? unserialize($role->permissions) : [];
            $this->app_object_cache->add('role-' . $id, $role);
            return $role;
        }
        return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'roles')->result_array();
   }

    /**
     * Delete employee role
     * @param  mixed $id role id
     * @return mixed
     */
    public function delete($id, $playground = false) {
        $current = $this->get($id);
        // Check first if role is used in table
        if (is_reference_in_table('role', db_prefix() . ($playground ? 'playground_' : '') . 'staff', $id)) {
            return ['referenced' => true, ];
        }
        $affectedRows = 0;
        $this->db->where('roleid', $id);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'roles');
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }
        if ($affectedRows > 0) {
            log_activity('Role Deleted [ID: ' . $id);
            return true;
        }
        return false;
    }

    public function get_contact_permissions($id, $playground = false) {
        $this->db->where('userid', $id);
        return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'contact_permissions')->result_array();
    }

    public function get_role_staff($role_id, $playground = false) {
        $this->db->where('role', $role_id);
        return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'staff')->result_array();
    }
}