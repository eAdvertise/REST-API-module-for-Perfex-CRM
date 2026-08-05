<?php

defined('BASEPATH') or exit('No direct script access allowed');

class REST_model extends App_Model
{
    /**
     * Token
     */
    protected $CI;

    /**
     * Token
     */
    protected $token;

    public function __construct()
    {
        parent::__construct();
        
        $this->load->config('api');
        $this->load->config('rest');

        $this->load->library('Authorization_Token');
        
        if (!$this->db->table_exists(db_prefix() . $this->config->item('rest_keys_table'))) {
            $this->db->query('CREATE TABLE `' . db_prefix() . $this->config->item('rest_keys_table') . '` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `user` VARCHAR(50) NOT NULL,
                `name` VARCHAR(50) NOT NULL,
                `token` VARCHAR(255) NOT NULL,
                `expiration_date` DATETIME NULL,
                `permission_enable` TINYINT(4) DEFAULT 0,
                `quota_limit` INT(11) NOT NULL DEFAULT 1000,
                `quota_remaining` INT(11) NOT NULL DEFAULT 1000,
                `quota_reset` DATETIME NOT NULL,
                `rate_limit`  INT(11) NOT NULL DEFAULT 60,
                `rate_remaining` INT(11) NOT NULL DEFAULT 60,
                `rate_reset` DATETIME NOT NULL,
                `level` INT(11) NULL,
                `ignore_limits` INT(11) NULL,
                PRIMARY KEY (`id`));
            ');
        }

        if (!$this->db->table_exists(db_prefix() . $this->config->item('rest_key_permissions_table'))) {
            $this->db->query('CREATE TABLE `' . db_prefix() . $this->config->item('rest_key_permissions_table') . '` (
                `key_id` int(11) NOT NULL,
                `feature` varchar(50) NOT NULL,
                `capability` varchar(50) NOT NULL);
            ');
        }

        if (!$this->db->table_exists(db_prefix() . $this->config->item('rest_key_limits_table'))) {
            $this->db->query('CREATE TABLE `' . db_prefix() . $this->config->item('rest_key_limits_table') . '` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `key_id` INT(11) NOT NULL,
                `uri` VARCHAR(511) NOT NULL,
                `class` VARCHAR(511) NOT NULL,
                `method` VARCHAR(511) NOT NULL,
                `ip_address` VARCHAR(63) NOT NULL,
                `time` DATETIME NOT NULL,
                `error` VARCHAR(63) NULL,
                PRIMARY KEY (`id`));
            ');
        }
    }

    public function get_permissions($id = '', $feature = '', $capability = '')
    {
        $this->db->select('*');
        if ('' != $id) {
            $this->db->where('key_id', $id);
            if ('' != $feature) {
                $this->db->where('feature', $feature);
            }
            if ('' != $capability) {
                $this->db->where('capability', $capability);
            }
    
            return $this->db->get(db_prefix() . $this->config->item('rest_key_permissions_table'))->result_array();
        }

        return [];
    }

    public function set_permissions($id, $permissions)
    {
        if ('' != $id) {
            if ($permissions) {
                foreach ($permissions as $feauture => $capabilities) {
                    foreach ($capabilities as $capability) {
                        if (!$this->get_permissions($id, $feauture, $capability)) {
                            $this->add_permissions($id, $feauture, $capability);
                        }
                    }
                    $feature_permissions = $this->get_permissions($id, $feauture);
                    foreach ($feature_permissions as $feature_permission) {
                        if (!in_array($feature_permission['capability'], array_values($capabilities))) {
                            $this->remove_permissions($id, $feauture, $feature_permission['capability']);
                        }
                    }
                }
            }
            $api_permissions = $this->get_permissions($id);
            foreach ($api_permissions as $permission) {
                $permission_exist = true;
                if (isset($permissions[$permission['feature']])) {
                    $permission_exist = false;
                    foreach ($permissions[$permission['feature']] as $capability) {
                        if ($capability == $permission['capability']) {
                            $permission_exist = true;
                        }
                    }
                } else {
                    $permission_exist = false;
                }
                if (!$permission_exist) {
                    $this->remove_permissions($id, $permission['feature'], $permission['capability']);
                }
            }
        }
    }
    
    public function add_permissions($id = '', $feature = '', $capability = '')
    {
        $permissions = [];
        if ('' != $id) {
            if ('' != $feature) {
                $api_permissions = get_available_api_permissions();
                foreach ($api_permissions as $api_feature => $api_permission) {
                    if ($api_feature == $feature) {
                        foreach ($api_permission['capabilities'] as $api_capability => $name) {
                            if ('' != $capability) {
                                if ($api_capability == $capability) {
                                    $permissions[] = [
                                        'key_id' => $id,
                                        'feature' => $feature,
                                        'capability' => $api_capability,
                                    ];
                                }
                            } else {
                                $permissions[] = [
                                    'key_id' => $id,
                                    'feature' => $feature,
                                    'capability' => $api_capability,
                                ];
                            }
                        }
                    }
                }
            }
        }

        foreach ($permissions as $permission) {
            $this->db->insert(db_prefix() . $this->config->item('rest_key_permissions_table'), $permission);
            if ($this->db->affected_rows() > 0) {
                log_activity('New API Permssion Added [API ID: ' . $permission['key_id'] . ', Feature: ' . $permission['feature'] . ', Capability: ' . $permission['capability'] . ']');
            }
        }
    }

    public function remove_permissions($id = '', $feature = '', $capability = '')
    {
        if ('' != $id) {
            $this->db->where('key_id', $id);
            if ('' != $feature) {
                $this->db->where('feature', $feature);
            }
            if ('' != $capability) {
                $this->db->where('capability', $capability);
            }
    
            $this->db->delete(db_prefix() . $this->config->item('rest_key_permissions_table'));
            if ($this->db->affected_rows() > 0) {
                log_activity('API Permssion Deleted [API ID: ' . $id . ', Feature: ' . $feature . ', Capability: ' . $capability . ']');
    
                return true;
            }
        }

        return false;
    }

    public function get_user($id = '')
    {
        $this->db->select('*');
        if ('' != $id) {
            $this->db->where('id', $id);
        }

        return $this->db->get(db_prefix() . $this->config->item('rest_keys_table'))->result_array();
    }

    public function add_user($data)
    {
        $permissions = isset($data['permissions']) ? $data['permissions'] : [];
        unset($data['permissions']);

        $payload = [
            'user' => $data['user'],
            'name' => $data['name'],
        ];
        // Load Authorization Library or Load in autoload config file
        // generate a token
        $data['token'] = $this->authorization_koken->generateToken($payload);
        $today         = date('Y-m-d H:i:s');

        $data['expiration_date'] = to_sql_date($data['expiration_date'], true);
        $data['permission_enable'] = 1;
        $this->db->insert(db_prefix() . $this->config->item('rest_keys_table'), $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New User Added [ID: ' . $insert_id . ', Name: ' . $data['name'] . ']');
        }

        $this->set_permissions($insert_id, $permissions);

        return $insert_id;
    }

    public function update_user($data, $id)
    {
        $permissions = isset($data['permissions']) ? $data['permissions'] : [];
        unset($data['permissions']);

        $data['expiration_date'] = to_sql_date($data['expiration_date'], true);
        $data['permission_enable'] = 1;

        $this->db->where('id', $id);
        $user = $this->db->get(db_prefix() . $this->config->item('rest_keys_table'))->row();
        if (isset($user)) {
            if ($user->quota_limit != $data['quota_limit']) {
                $data['quota_remaining'] = $data['quota_limit'];
            }
            if ($user->qrate_limit != $data['rate_limit']) {
                $data['rate_remaining'] = $data['rate_limit'];
            }
        }

        $result = false;
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . $this->config->item('rest_keys_table'), $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Ticket User Updated [ID: ' . $id . ' Name: ' . $data['name'] . ']');
            $result = true;
        }
        
        $this->set_permissions($id, $permissions);

        return $result;
    }

    public function delete_user($id)
    {
        $this->remove_permissions($id);

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . $this->config->item('rest_keys_table'));
        if ($this->db->affected_rows() > 0) {
            log_activity('User Deleted [ID: ' . $id . ']');

            return true;
        }

        return false;
    }

    public function check_token($token)
    {
        $this->db->where('token', $token);
        $user = $this->db->get(db_prefix() . $this->config->item('rest_keys_table'))->row();
        if (isset($user)) {
            return true;
        }

        return false;
    }
    
    public function check_token_permission($token, $feature = '', $capability = '')
    {
        $this->db->where('token', $token);
        $user = $this->db->get(db_prefix() . $this->config->item('rest_keys_table'))->row();
        if (isset($user)) {
            if ($user->permission_enable) {
                $this->db->where('key_id', $user->id);
                $this->db->where('feature', $feature);
                $this->db->where('capability', $capability);
                $permission = $this->db->get(db_prefix() . $this->config->item('rest_key_permissions_table'))->row();
    
                if (isset($permission)) {
                    return true;
                }
            } else {
                return true;
            }
        }

        return false;
    }

    public function statistics($period = 'hourly', $uri_method = '') {
        $now = time();
        $now_date = strtotime($now);
        $current_week_day = date('w', $now);
        $all_apis = [];

        $chart = [
            "labels" => [],
            "datasets" => [
                [
                    "label" => _l("success"),
                    "data" => [],
                    "borderColor" => "rgb(0, 255, 0)",
                    "backgroundColor" => "rgba(0, 255, 0, 0.1)"
                ],
                [
                    "label" => _l("expiration_date"),
                    "data" => [],
                    "borderColor" => "rgb(200, 50, 100)",
                    "backgroundColor" => "rgba(200, 50, 100, 0.1)"
                ],
                [
                    "label" => _l("quota_remaining"),
                    "data" => [],
                    "borderColor" => "rgb(200, 0, 200)",
                    "backgroundColor" => "rgba(200, 0, 200, 0.1)"
                ],
                [
                    "label" => _l("rate_remaining"),
                    "data" => [],
                    "borderColor" => "rgb(200, 100, 0)",
                    "backgroundColor" => "rgba(200, 100, 0, 0.1)"
                ],
            ]
        ];
        if ($period) {
            if ($period == 'hourly') {
                $period_total = 23;
            } else if ($period == 'daily') {
                $period_total = 30;
            } else if ($period == 'weekly') {
                $period_total = 3;
            } else if ($period == 'monthly') {
                $period_total = 11;
            }
            for ($period_index = $period_total; $period_index >= 0; $period_index--) {
                if ($period == 'hourly') {
                    $chart["labels"][] = date('M d H', $now - $period_index * 60 * 60);
                } else if ($period == 'daily') {
                    $chart["labels"][] = date('M d', $now - $period_index * 60 * 60 * 24);
                } else if ($period == 'weekly') {
                    $chart["labels"][] = date('M d', $now - ($current_week_day - 1 + $period_index * 7) * 60 * 60 * 24) . " ~ " . date('M d', $now - ($current_week_day - 1 + ($period_index - 1) * 7 + 1) * 60 * 60 * 24);
                } else if ($period == 'monthly') {
                    $chart["labels"][] = date('Y M', strtotime(date('Y-m-01', strtotime("$now_date -$period_index months"))));
                }

                foreach ($chart["datasets"] as $chart_dataset_index => $chart_dataset) {
                    $this->db->select("uri, method, time, COUNT(*) AS total, error");
                    if ($uri_method) {
                        $uri_methods = explode(" ", urldecode($uri_method));
                        $uri = $uri_methods[0];
                        $method = $uri_methods[1];
                        $this->db->where("uri", $uri);
                        $this->db->where("method", $method);
                    }
                    if ($chart_dataset["label"] == _l("success")) {
                        $this->db->where('error', NULL);
                    } else {
                        $this->db->where('error', $chart_dataset["label"]);
                    }
                    if ($period == 'hourly') {
                        $this->db->where('time >=', date('Y-m-d H', $now - $period_index * 60 * 60));
                        $this->db->where('time <', date('Y-m-d H', $now - ($period_index - 1) * 60 * 60));
                    } else if ($period == 'daily') {
                        $this->db->where('time >=', date('Y-m-d', $now - $period_index * 60 * 60 * 24));
                        $this->db->where('time <', date('Y-m-d', $now - ($period_index - 1) * 60 * 60 * 24));
                    } else if ($period == 'weekly') {
                        $this->db->where('time >=', date('Y-m-d', $now - ($current_week_day - 1 + $period_index * 7) * 60 * 60 * 24));
                        $this->db->where('time <', date('Y-m-d', $now - ($current_week_day - 1 + ($period_index - 1) * 7) * 60 * 60 * 24));
                    } else if ($period == 'monthly') {
                        $this->db->where('time >=', date('Y-m-01', strtotime("$now_date -$period_index months")));
                        $this->db->where('time <=', date('Y-m-t', strtotime("$now_date -$period_index months")));
                    }
                    $period_apis = $this->db->get(db_prefix() . $this->config->item('rest_key_limits_table'))->row();
                    $chart["datasets"][$chart_dataset_index]["data"][] = $period_apis->total;
                }
            }
        }

        echo json_encode($chart);
    }

    public function endpoints() {
        $endpoints = [];
        $this->db->select("CONCAT(uri, ' ', method) as uri_method");
        $this->db->group_by("uri_method");
        $endpoint_apis = $this->db->get(db_prefix() . $this->config->item('rest_key_limits_table'))->result_array();
        foreach ($endpoint_apis as $endpoint_api) {
            $endpoints[] = $endpoint_api["uri_method"];
        }
        return $endpoints;
    }
}