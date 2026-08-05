<?php

use app\services\projects\Gantt;
use app\services\projects\AllProjectsGantt;
use app\services\projects\HoursOverviewChart;

defined('BASEPATH') or exit('No direct script access allowed');

class Projects_model extends App_Model {
    private $project_settings;

    public function __construct() {
        parent::__construct();
        $project_settings = ['available_features', 'view_tasks', 'create_tasks', 'edit_tasks', 'comment_on_tasks', 'view_task_comments', 'view_task_attachments', 'view_task_checklist_items', 'upload_on_tasks', 'view_task_total_logged_time', 'view_finance_overview', 'upload_files', 'open_discussions', 'view_milestones', 'view_gantt', 'view_timesheets', 'view_activity_log', 'view_team_members', 'hide_tasks_on_main_tasks_table', ];
        
        $this->project_settings = hooks()->apply_filters('project_settings', $project_settings);
    }

    public function get_project_statuses() {
        $statuses = hooks()->apply_filters('before_get_project_statuses', [
            ['id' => 1, 'color' => '#475569', 'name' => _l('project_status_1'), 'order' => 1, 'filter_default' => true, ],
            ['id' => 2, 'color' => '#2563eb', 'name' => _l('project_status_2'), 'order' => 2, 'filter_default' => true, ],
            ['id' => 3, 'color' => '#f97316', 'name' => _l('project_status_3'), 'order' => 3, 'filter_default' => true, ],
            ['id' => 4, 'color' => '#16a34a', 'name' => _l('project_status_4'), 'order' => 100, 'filter_default' => false, ],
            ['id' => 5, 'color' => '#94a3b8', 'name' => _l('project_status_5'), 'order' => 4, 'filter_default' => false, ],
        ]);
        usort($statuses, function ($a, $b) {
            return $a['order'] - $b['order'];
        });
        return $statuses;
    }

    public function get_distinct_tasks_timesheets_staff($project_id, $playground = false) {
        return $this->db->query('SELECT DISTINCT staff_id FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'taskstimers LEFT JOIN ' . db_prefix() . ($playground ? 'playground_' : '') . 'tasks ON ' . db_prefix() . ($playground ? 'playground_' : '') . 'tasks.id = ' . db_prefix() . ($playground ? 'playground_' : '') . 'taskstimers.task_id WHERE rel_type="project" AND rel_id=' . $this->db->escape_str($project_id))->result_array();
    }

    public function get_distinct_projects_members($playground = false) {
        return $this->db->query('SELECT staff_id, firstname, lastname FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'project_members JOIN ' . db_prefix() . ($playground ? 'playground_' : '') . 'staff ON ' . db_prefix() . ($playground ? 'playground_' : '') . 'staff.staffid=' . db_prefix() . ($playground ? 'playground_' : '') . 'project_members.staff_id GROUP by staff_id order by firstname ASC')->result_array();
    }

    public function get_most_used_billing_type($playground = false) {
        return $this->db->query('SELECT billing_type, COUNT(*) AS total_usage
            FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'projects
            GROUP BY billing_type
            ORDER BY total_usage DESC
            LIMIT 1')->row();
    }

    public function timers_started_for_project($project_id, $where = [], $task_timers_where = [], $playground = false) {
        $this->db->where($where);
        $this->db->where('end_time IS NULL');
        $this->db->where(db_prefix() . ($playground ? 'playground_' : '') . 'tasks.rel_id', $project_id);
        $this->db->where(db_prefix() . ($playground ? 'playground_' : '') . 'tasks.rel_type', 'project');
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'tasks', db_prefix() . ($playground ? 'playground_' : '') . 'tasks.id=' . db_prefix() . ($playground ? 'playground_' : '') . 'taskstimers.task_id');
        $total = $this->db->count_all_results(db_prefix() . ($playground ? 'playground_' : '') . 'taskstimers');
        return $total > 0 ? true : false;
    }

    public function pin_action($id, $playground = false) {
        if (total_rows(db_prefix() . ($playground ? 'playground_' : '') . 'pinned_projects', ['staff_id' => get_staff_user_id(), 'project_id' => $id, ]) == 0) {
            $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'pinned_projects', ['staff_id' => get_staff_user_id(), 'project_id' => $id, ]);
            return true;
        }
        $this->db->where('project_id', $id);
        $this->db->where('staff_id', get_staff_user_id());
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'pinned_projects');
        return true;
    }

    public function get_currency($id, $playground = false) {
        $this->load->model('currencies_model');
        $this->load->model('clients_model');
        $customer_currency = $this->clients_model->get_customer_default_currency(get_client_id_by_project_id($id, $playground), $playground);
        if ($customer_currency != 0) {
            $currency = $this->currencies_model->get($customer_currency, $playground);
        } else {
            $currency = $this->currencies_model->get_base_currency($playground);
        }
        return $currency;
    }

    public function calc_progress($id, $playground = false) {
        $this->db->select('progress_from_tasks,progress,status');
        $this->db->where('id', $id);
        $project = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'projects')->row();
        if ($project->status == 4) {
            return 100;
        }
        if ($project->progress_from_tasks == 1) {
            return $this->calc_progress_by_tasks($id, $playground);
        }
        return $project->progress;
    }

    public function calc_progress_by_tasks($id, $playground = false) {
        $total_project_tasks = total_rows(db_prefix() . ($playground ? 'playground_' : '') . 'tasks', ['rel_type' => 'project', 'rel_id' => $id, ]);
        $total_finished_tasks = total_rows(db_prefix() . ($playground ? 'playground_' : '') . 'tasks', ['rel_type' => 'project', 'rel_id' => $id, 'status' => 5, ]);
        $percent = 0;
        if ($total_finished_tasks >= floatval($total_project_tasks)) {
            $percent = 100;
        } else {
            if ($total_project_tasks !== 0) {
                $percent = number_format(($total_finished_tasks * 100) / $total_project_tasks, 2);
            }
        }
        return $percent;
    }

    public function get_last_project_settings($playground = false) {
        $this->db->select('id');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $last_project = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'projects')->row();
        if ($last_project) {
            return $this->get_project_settings($last_project->id, $playground);
        }
        return [];
    }

    public function get_settings() {
        return $this->project_settings;
    }

    public function get($id = '', $where = [], $playground = false) {
        $this->load->model('clients_model');
        $this->db->where($where);
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            $project = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'projects')->row();
            if ($project) {
                $project->shared_vault_entries = $this->clients_model->get_vault_entries($project->clientid, ['share_in_projects' => 1]);
                $settings = $this->get_project_settings($id, $playground);
                // SYNC NEW TABS
                $tabs = get_project_tabs_admin();
                $tabs_flatten = [];
                $settings_available_features = [];
                $available_features_index = false;
                foreach ($settings as $key => $setting) {
                    if ($setting['name'] == 'available_features') {
                        $available_features_index = $key;
                        $available_features = unserialize($setting['value']);
                        if (is_array($available_features)) {
                            foreach ($available_features as $name => $avf) {
                                $settings_available_features[] = $name;
                            }
                        }
                    }
                }
                foreach ($tabs as $tab) {
                    if (isset($tab['collapse'])) {
                        foreach ($tab['children'] as $d) {
                            $tabs_flatten[] = $d['slug'];
                        }
                    } else {
                        $tabs_flatten[] = $tab['slug'];
                    }
                }
                if (count($settings_available_features) != $tabs_flatten) {
                    foreach ($tabs_flatten as $tab) {
                        if (!in_array($tab, $settings_available_features)) {
                            if ($available_features_index) {
                                $current_available_features_settings = $settings[$available_features_index];
                                $tmp = unserialize($current_available_features_settings['value']);
                                $tmp[$tab] = 1;
                                $this->db->where('id', $current_available_features_settings['id']);
                                $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'project_settings', ['value' => serialize($tmp) ]);
                            }
                        }
                    }
                }
                $project->settings = new StdClass();
                foreach ($settings as $setting) {
                    $project->settings->{$setting['name']} = $setting['value'];
                }
                $project->client_data = new StdClass();
                $project->client_data = $this->clients_model->get($project->clientid, $playground);
                $project = hooks()->apply_filters('project_get', $project);
                $GLOBALS['project'] = $project;
                return $project;
            }
            return null;
        }
        $this->db->select('*,' . $this->clients_model->get_sql_select_client_company('company', $playground));
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'clients', db_prefix() . ($playground ? 'playground_' : '') . 'clients.userid=' . db_prefix() . ($playground ? 'playground_' : '') . 'projects.clientid');
        $this->db->order_by('id', 'desc');
        return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'projects')->result_array();
    }
    
    public function get_project($id = '', $playground = false) {
        if (is_numeric($id)) {
            return $this->get($id, [], $playground);
        } else {
            $result = [];
            $projects = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'projects')->result_array();
            foreach ($projects as $project) {
                $result[] = (array)$this->get($project['id'], [], $playground);
            }
            return $result;
        }
    }

    public function calculate_total_by_project_hourly_rate($seconds, $hourly_rate, $playground = false) {
        $hours = seconds_to_time_format($seconds);
        $decimal = sec2qty($seconds);
        $total_money = 0;
        $total_money+= ($decimal * $hourly_rate);
        return ['hours' => $hours, 'total_money' => $total_money, ];
    }

    public function calculate_total_by_task_hourly_rate($tasks, $playground = false) {
        $total_money = 0;
        $_total_seconds = 0;
        foreach ($tasks as $task) {
            $seconds = $task['total_logged_time'];
            $_total_seconds+= $seconds;
            $total_money+= sec2qty($seconds) * $task['hourly_rate'];
        }
        return ['total_money' => $total_money, 'total_seconds' => $_total_seconds, ];
    }

    public function get_tasks($id, $where = [], $apply_restrictions = false, $count = false, $callback = null, $playground = false) {
        $has_permission = staff_can('view', 'tasks');
        $show_all_tasks_for_project_member = get_option('show_all_tasks_for_project_member');
        $select = implode(', ', prefixed_table_fields_array(db_prefix() . ($playground ? 'playground_' : '') . 'tasks')) . ',' . db_prefix() . ($playground ? 'playground_' : '') . 'milestones.name as milestone_name,
            (SELECT SUM(CASE
                WHEN end_time is NULL THEN ' . time() . '-start_time
                ELSE end_time-start_time
                END) FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'taskstimers WHERE task_id=' . db_prefix() . ($playground ? 'playground_' : '') . 'tasks.id) as total_logged_time,
            ' . get_sql_select_task_assignees_ids($playground) . ' as assignees_ids
        ';
        if (!is_client_logged_in() && is_staff_logged_in()) {
            $select.= ',(SELECT staffid FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'task_assigned WHERE taskid=' . db_prefix() . ($playground ? 'playground_' : '') . 'tasks.id AND staffid=' . get_staff_user_id() . ') as current_user_is_assigned';
        }
        if (is_client_logged_in()) {
            $this->db->where('visible_to_client', 1);
        }
        $this->db->select($select);
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'milestones', db_prefix() . ($playground ? 'playground_' : '') . 'milestones.id = ' . db_prefix() . ($playground ? 'playground_' : '') . 'tasks.milestone', 'left');
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'project');
        if ($apply_restrictions == true) {
            if (!is_client_logged_in() && !$has_permission && $show_all_tasks_for_project_member == 0) {
                $this->db->where('(
                    ' . db_prefix() . ($playground ? 'playground_' : '') . 'tasks.id IN (SELECT taskid FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'task_assigned WHERE staffid=' . get_staff_user_id() . ')
                    OR ' . db_prefix() . ($playground ? 'playground_' : '') . 'tasks.id IN(SELECT taskid FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'task_followers WHERE staffid=' . get_staff_user_id() . ')
                    OR is_public = 1
                    OR (addedfrom =' . get_staff_user_id() . ' AND is_added_from_contact = 0)
                    )');
            }
        }
        if (isset($where[db_prefix() . ($playground ? 'playground_' : '') . 'milestones.hide_from_customer'])) {
            $this->db->group_start();
            $this->db->where(db_prefix() . ($playground ? 'playground_' : '') . 'milestones.hide_from_customer', $where[db_prefix() . ($playground ? 'playground_' : '') . 'milestones.hide_from_customer']);
            $this->db->or_where(db_prefix() . ($playground ? 'playground_' : '') . 'tasks.milestone', 0);
            $this->db->group_end();
            unset($where[db_prefix() . ($playground ? 'playground_' : '') . 'milestones.hide_from_customer']);
        }
        $this->db->where($where);
        // Milestones kanban order
        // Request is admin/projects/milestones_kanban
        if ($this->uri->segment(3) == 'milestones_kanban' | $this->uri->segment(3) == 'milestones_kanban_load_more') {
            $this->db->order_by('milestone_order', 'asc');
        } else {
            $orderByString = hooks()->apply_filters('project_tasks_array_default_order', 'FIELD(status, 5), duedate IS NULL ASC, duedate');
            $this->db->order_by($orderByString, '', false);
        }
        if ($callback) {
            $callback();
        }
        if ($count == false) {
            $tasks = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'tasks')->result_array();
        } else {
            $tasks = $this->db->count_all_results(db_prefix() . ($playground ? 'playground_' : '') . 'tasks');
        }
        $tasks = hooks()->apply_filters('get_projects_tasks', $tasks, ['project_id' => $id, 'where' => $where, 'count' => $count, ]);
        return $tasks;
    }

    public function cancel_recurring_tasks($id, $playground = false) {
        $this->db->where('rel_type', 'project');
        $this->db->where('rel_id', $id);
        $this->db->where('recurring', 1);
        $this->db->where('(cycles != total_cycles OR cycles=0)');
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'tasks', ['recurring_type' => null, 'repeat_every' => 0, 'cycles' => 0, 'recurring' => 0, 'custom_recurring' => 0, 'last_recurring_date' => null, ]);
    }

    public function do_milestones_kanban_query($milestone_id, $project_id, $page = 1, $where = [], $count = false, $playground = false) {
        $where['milestone'] = $milestone_id;
        $limit = get_option('tasks_kanban_limit');
        $tasks = $this->get_tasks($project_id, $where, true, $count, function () use ($count, $page, $limit) {
            if ($count == false) {
                if ($page > 1) {
                    $position = (($page - 1) * $limit);
                    $this->db->limit($limit, $position);
                } else {
                    $this->db->limit($limit);
                }
            }
        });
        return $tasks;
    }

    public function get_files($project_id, $playground = false) {
        if (is_client_logged_in()) {
            $this->db->where('visible_to_customer', 1);
        }
        $this->db->where('project_id', $project_id);
        return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'project_files')->result_array();
    }

    public function get_file($id, $project_id = false, $playground = false) {
        if (is_client_logged_in()) {
            $this->db->where('visible_to_customer', 1);
        }
        $this->db->where('id', $id);
        $file = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'project_files')->row();
        if ($file && $project_id) {
            if ($file->project_id != $project_id) {
                return false;
            }
        }
        return $file;
    }

    public function update_file_data($data, $playground = false) {
        $this->db->where('id', $data['id']);
        unset($data['id']);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'project_files', $data);
    }

    public function change_file_visibility($id, $visible, $playground = false) {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'project_files', ['visible_to_customer' => $visible, ]);
    }

    public function change_activity_visibility($id, $visible, $playground = false) {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'project_activity', ['visible_to_customer' => $visible, ]);
    }

    public function remove_file($id, $logActivity = true, $playground = false) {
        hooks()->do_action('before_remove_project_file', $id);
        $this->db->where('id', $id);
        $file = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'project_files')->row();
        $this->load->model('misc_model');
        if ($file) {
            if (empty($file->external)) {
                $path = $this->misc_model->get_upload_path_by_type('project', $playground) . $file->project_id . '/';
                $fullPath = $path . $file->file_name;
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                    $fname = pathinfo($fullPath, PATHINFO_FILENAME);
                    $fext = pathinfo($fullPath, PATHINFO_EXTENSION);
                    $thumbPath = $path . $fname . '_thumb.' . $fext;
                    if (file_exists($thumbPath)) {
                        unlink($thumbPath);
                    }
                }
            }
            $this->db->where('id', $id);
            $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'project_files');
            if ($logActivity) {
                $this->log_activity($file->project_id, 'project_activity_project_file_removed', $file->file_name, $file->visible_to_customer, $playground);
            }
            // Delete discussion comments
            $this->_delete_discussion_comments($id, 'file', $playground);
            if (is_dir($this->misc_model->get_upload_path_by_type('project', $playground) . $file->project_id)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files($this->misc_model->get_upload_path_by_type('project', $playground) . $file->project_id);
                if (count($other_attachments) == 0) {
                    delete_dir($this->misc_model->get_upload_path_by_type('project', $playground) . $file->project_id);
                }
            }
            return true;
        }
        return false;
    }

    public function calc_milestone_logged_time($project_id, $id, $playground = false) {
        $total = [];
        $tasks = $this->get_tasks($project_id, ['milestone' => $id, ], false, false, null, $playground);
        foreach ($tasks as $task) {
            $total[] = $task['total_logged_time'];
        }
        return array_sum($total);
    }

    public function total_logged_time($id, $playground = false) {
        $q = $this->db->query('
            SELECT SUM(CASE
                WHEN end_time is NULL THEN ' . time() . '-start_time
                ELSE end_time-start_time
                END) as total_logged_time
            FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'taskstimers
            WHERE task_id IN (SELECT id FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'tasks WHERE rel_type="project" AND rel_id=' . $this->db->escape_str($id) . ')')->row();
        return $q->total_logged_time;
    }

    public function get_milestones($project_id, $where = [], $playground = false) {
        $this->db->select('*, (SELECT COUNT(id) FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'tasks WHERE rel_type="project" AND rel_id=' . $this->db->escape_str($project_id) . ' and milestone=' . db_prefix() . ($playground ? 'playground_' : '') . 'milestones.id) as total_tasks, (SELECT COUNT(id) FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'tasks WHERE rel_type="project" AND rel_id=' . $this->db->escape_str($project_id) . ' and milestone=' . db_prefix() . ($playground ? 'playground_' : '') . 'milestones.id AND status=5) as total_finished_tasks');
        $this->db->where('project_id', $project_id);
        $this->db->order_by('milestone_order', 'ASC');
        $this->db->where($where);
        $milestones = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'milestones')->result_array();
        $i = 0;
        foreach ($milestones as $milestone) {
            $milestones[$i]['total_logged_time'] = $this->calc_milestone_logged_time($project_id, $milestone['id'], $playground);
            $i++;
        }
        return $milestones;
    }

    public function add_milestone($data, $playground = false) {
        $data['due_date'] = to_sql_date($data['due_date']);
        $data['start_date'] = to_sql_date($data['start_date']);
        $data['datecreated'] = date('Y-m-d');
        $data['description'] = nl2br($data['description']);
        $data['description_visible_to_customer'] = isset($data['description_visible_to_customer']) ? 1 : 0;
        $data['hide_from_customer'] = isset($data['hide_from_customer']) ? 1 : 0;
        $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'milestones', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            $this->db->where('id', $insert_id);
            $milestone = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'milestones')->row();
            $project = $this->get($milestone->project_id);
            if ($project->settings->view_milestones == 1) {
                $show_to_customer = 1;
            } else {
                $show_to_customer = 0;
            }
            $this->log_activity($milestone->project_id, 'project_activity_created_milestone', $milestone->name, $show_to_customer);
            log_activity('Project Milestone Created [ID:' . $insert_id . ']');
            return $insert_id;
        }
        return false;
    }

    public function update_milestone($data, $id, $playground = false) {
        $this->db->where('id', $id);
        $milestone = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'milestones')->row();
        $data['due_date'] = to_sql_date($data['due_date']);
        $data['start_date'] = to_sql_date($data['start_date']);
        $data['description'] = nl2br($data['description']);
        $data['description_visible_to_customer'] = isset($data['description_visible_to_customer']) ? 1 : 0;
        $data['hide_from_customer'] = isset($data['hide_from_customer']) ? 1 : 0;
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'milestones', $data);
        if ($this->db->affected_rows() > 0) {
            $project = $this->get($milestone->project_id);
            if ($project->settings->view_milestones == 1) {
                $show_to_customer = 1;
            } else {
                $show_to_customer = 0;
            }
            $this->log_activity($milestone->project_id, 'project_activity_updated_milestone', $milestone->name, $show_to_customer, $playground);
            log_activity('Project Milestone Updated [ID:' . $id . ']');
            return true;
        }
        return false;
    }

    public function update_task_milestone($data, $playground = false) {
        $this->db->where('id', $data['task_id']);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'tasks', ['milestone' => $data['milestone_id'], ]);
        foreach ($data['order'] as $order) {
            $this->db->where('id', $order[0]);
            $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'tasks', ['milestone_order' => $order[1], ]);
        }
    }

    public function update_milestones_order($data, $playground = false) {
        foreach ($data['order'] as $status) {
            $this->db->where('id', $status[0]);
            $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'milestones', ['milestone_order' => $status[1], ]);
        }
    }

    public function update_milestone_color($data, $playground = false) {
        $this->db->where('id', $data['milestone_id']);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'milestones', ['color' => $data['color'], ]);
    }

    public function delete_milestone($id, $playground = false) {
        $this->db->where('id', $id);
        $milestone = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'milestones')->row();
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'milestones');
        if ($this->db->affected_rows() > 0) {
            $project = $this->get($milestone->project_id, [], $playground);
            if ($project->settings->view_milestones == 1) {
                $show_to_customer = 1;
            } else {
                $show_to_customer = 0;
            }
            $this->log_activity($milestone->project_id, 'project_activity_deleted_milestone', $milestone->name, $show_to_customer, $playground);
            $this->db->where('milestone', $id);
            $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'tasks', ['milestone' => 0, ], $playground);
            log_activity('Project Milestone Deleted [' . $id . ']');
            return true;
        }
        return false;
    }

    public function add($data, $playground = false) {
        if (isset($data['notify_project_members_status_change'])) {
            unset($data['notify_project_members_status_change']);
        }
        $send_created_email = false;
        if (isset($data['send_created_email'])) {
            unset($data['send_created_email']);
            $send_created_email = true;
        }
        $send_project_marked_as_finished_email_to_contacts = false;
        if (isset($data['project_marked_as_finished_email_to_contacts'])) {
            unset($data['project_marked_as_finished_email_to_contacts']);
            $send_project_marked_as_finished_email_to_contacts = true;
        }
        if (isset($data['settings'])) {
            $project_settings = $data['settings'];
            unset($data['settings']);
        }
        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }
        if (isset($data['progress_from_tasks'])) {
            $data['progress_from_tasks'] = 1;
        } else {
            $data['progress_from_tasks'] = 0;
        }
        if (isset($data['contact_notification'])) {
            if ($data['contact_notification'] == 2) {
                $data['notify_contacts'] = serialize($data['notify_contacts']);
            } else {
                $data['notify_contacts'] = serialize([]);
            }
        }
        $data['project_cost'] = !empty($data['project_cost']) ? $data['project_cost'] : null;
        $data['estimated_hours'] = !empty($data['estimated_hours']) ? $data['estimated_hours'] : null;
        $data['start_date'] = to_sql_date($data['start_date']);
        if (!empty($data['deadline'])) {
            $data['deadline'] = to_sql_date($data['deadline']);
        } else {
            unset($data['deadline']);
        }
        $data['project_created'] = date('Y-m-d');
        if (isset($data['project_members'])) {
            $project_members = $data['project_members'];
            unset($data['project_members']);
        }
        if ($data['billing_type'] == 1) {
            $data['project_rate_per_hour'] = 0;
        } else if ($data['billing_type'] == 2) {
            $data['project_cost'] = 0;
        } else {
            $data['project_rate_per_hour'] = 0;
            $data['project_cost'] = 0;
        }
        $data['addedfrom'] = get_staff_user_id();
        $items_to_convert = false;
        if (isset($data['items'])) {
            $items_to_convert = $data['items'];
            $estimate_id = $data['estimate_id'];
            $items_assignees = $data['items_assignee'];
            unset($data['items'], $data['estimate_id'], $data['items_assignee']);
        }
        $data = hooks()->apply_filters('before_add_project', $data);
        $tags = '';
        if (isset($data['tags'])) {
            $tags = $data['tags'];
            unset($data['tags']);
        }
        $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'projects', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            $this->load->model('misc_model');
            $this->misc_model->handle_tags_save($tags, $insert_id, 'project');
            if (isset($custom_fields)) {
                $this->load->model('custom_fields_model');
                $this->custom_fields_model->handle_custom_fields_post($insert_id, $custom_fields, false, $playground);
            }
            if (isset($project_members)) {
                $_pm['project_members'] = $project_members;
                $this->add_edit_members($_pm, $insert_id, $playground);
            }
            $original_settings = $this->get_settings();
            if (isset($project_settings)) {
                $_settings = [];
                $_values = [];
                foreach ($project_settings as $name => $val) {
                    array_push($_settings, $name);
                    $_values[$name] = $val;
                }
                foreach ($original_settings as $setting) {
                    if ($setting != 'available_features') {
                        if (in_array($setting, $_settings)) {
                            $value_setting = 1;
                        } else {
                            $value_setting = 0;
                        }
                    } else {
                        $tabs = get_project_tabs_admin();
                        $tab_settings = [];
                        foreach ($_values[$setting] as $tab) {
                            $tab_settings[$tab] = 1;
                        }
                        foreach ($tabs as $tab) {
                            if (!isset($tab['collapse'])) {
                                if (!in_array($tab['slug'], $_values[$setting])) {
                                    $tab_settings[$tab['slug']] = 0;
                                }
                            } else {
                                foreach ($tab['children'] as $tab_dropdown) {
                                    if (!in_array($tab_dropdown['slug'], $_values[$setting])) {
                                        $tab_settings[$tab_dropdown['slug']] = 0;
                                    }
                                }
                            }
                        }
                        $value_setting = serialize($tab_settings);
                    }
                    $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'project_settings', ['project_id' => $insert_id, 'name' => $setting, 'value' => $value_setting, ]);
                }
            } else {
                foreach ($original_settings as $setting) {
                    $value_setting = 0;
                    $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'project_settings', ['project_id' => $insert_id, 'name' => $setting, 'value' => $value_setting, ]);
                }
            }
            if ($items_to_convert && is_numeric($estimate_id)) {
                $this->convert_estimate_items_to_tasks($insert_id, $items_to_convert, $items_assignees, $data, $project_settings, $playground);
                $this->db->where('id', $estimate_id);
                $this->db->set('project_id', $insert_id);
                $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'estimates');
            }
            $this->log_activity($insert_id, 'project_activity_created', $playground);
            if ($send_created_email == true) {
                $this->send_project_customer_email($insert_id, 'project_created_to_customer', $playground);
            }
            if ($send_project_marked_as_finished_email_to_contacts == true) {
                $this->send_project_customer_email($insert_id, 'project_marked_as_finished_to_customer', $playground);
            }
            hooks()->do_action('after_add_project', $insert_id);
            log_activity('New Project Created [ID: ' . $insert_id . ']');
            return $insert_id;
        }
        return false;
    }

    public function update($data, $id, $playground = false) {
        $this->db->select('status');
        $this->db->where('id', $id);
        $old_status = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'projects')->row()->status;
        $send_created_email = false;
        if (isset($data['send_created_email'])) {
            unset($data['send_created_email']);
            $send_created_email = true;
        }
        $send_project_marked_as_finished_email_to_contacts = false;
        if (isset($data['project_marked_as_finished_email_to_contacts'])) {
            unset($data['project_marked_as_finished_email_to_contacts']);
            $send_project_marked_as_finished_email_to_contacts = true;
        }
        $original_project = $this->get($id, [], $playground);
        if (isset($data['notify_project_members_status_change'])) {
            $notify_project_members_status_change = true;
            unset($data['notify_project_members_status_change']);
        }
        $affectedRows = 0;
        if (!isset($data['settings'])) {
            $this->db->where('project_id', $id);
            $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'project_settings', ['value' => 0, ]);
            if ($this->db->affected_rows() > 0) {
                $affectedRows++;
            }
        } else {
            $_settings = [];
            $_values = [];
            foreach ($data['settings'] as $name => $val) {
                array_push($_settings, $name);
                $_values[$name] = $val;
            }
            unset($data['settings']);
            $original_settings = $this->get_project_settings($id, $playground);
            foreach ($original_settings as $setting) {
                if ($setting['name'] != 'available_features') {
                    if (in_array($setting['name'], $_settings)) {
                        $value_setting = 1;
                    } else {
                        $value_setting = 0;
                    }
                } else {
                    $tabs = get_project_tabs_admin();
                    $tab_settings = [];
                    foreach ($_values[$setting['name']] as $tab) {
                        $tab_settings[$tab] = 1;
                    }
                    foreach ($tabs as $tab) {
                        if (!isset($tab['collapse'])) {
                            if (!in_array($tab['slug'], $_values[$setting['name']])) {
                                $tab_settings[$tab['slug']] = 0;
                            }
                        } else {
                            foreach ($tab['children'] as $tab_dropdown) {
                                if (!in_array($tab_dropdown['slug'], $_values[$setting['name']])) {
                                    $tab_settings[$tab_dropdown['slug']] = 0;
                                }
                            }
                        }
                    }
                    $value_setting = serialize($tab_settings);
                }
                $this->db->where('project_id', $id);
                $this->db->where('name', $setting['name']);
                $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'project_settings', ['value' => $value_setting, ]);
                if ($this->db->affected_rows() > 0) {
                    $affectedRows++;
                }
            }
        }
        $data['project_cost'] = !empty($data['project_cost']) ? $data['project_cost'] : null;
        $data['estimated_hours'] = !empty($data['estimated_hours']) ? $data['estimated_hours'] : null;
        if ($old_status == 4 && $data['status'] != 4) {
            $data['date_finished'] = null;
        } else if (isset($data['date_finished'])) {
            $data['date_finished'] = to_sql_date($data['date_finished'], true);
        }
        if (isset($data['progress_from_tasks'])) {
            $data['progress_from_tasks'] = 1;
        } else {
            $data['progress_from_tasks'] = 0;
        }
        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            $this->load->model('custom_fields_model');
            if ($this->custom_fields_model->handle_custom_fields_post($id, $custom_fields, false, $playground)) {
                $affectedRows++;
            }
            unset($data['custom_fields']);
        }
        if (!empty($data['deadline'])) {
            $data['deadline'] = to_sql_date($data['deadline']);
        } else {
            $data['deadline'] = null;
        }
        $data['start_date'] = to_sql_date($data['start_date']);
        if ($data['billing_type'] == 1) {
            $data['project_rate_per_hour'] = 0;
        } else if ($data['billing_type'] == 2) {
            $data['project_cost'] = 0;
        } else {
            $data['project_rate_per_hour'] = 0;
            $data['project_cost'] = 0;
        }
        if (isset($data['project_members'])) {
            $project_members = $data['project_members'];
            unset($data['project_members']);
        }
        $_pm = [];
        if (isset($project_members)) {
            $_pm['project_members'] = $project_members;
        }
        if ($this->add_edit_members($_pm, $id, $playground)) {
            $affectedRows++;
        }
        if (isset($data['mark_all_tasks_as_completed'])) {
            $mark_all_tasks_as_completed = true;
            unset($data['mark_all_tasks_as_completed']);
        }
        if (isset($data['tags'])) {
            $this->load->model('misc_model');
            if ($this->misc_model->handle_tags_save($data['tags'], $id, 'project')) {
                $affectedRows++;
            }
            unset($data['tags']);
        }
        if (isset($data['cancel_recurring_tasks'])) {
            unset($data['cancel_recurring_tasks']);
            $this->cancel_recurring_tasks($id);
        }
        if (isset($data['contact_notification'])) {
            if ($data['contact_notification'] == 2) {
                $data['notify_contacts'] = serialize($data['notify_contacts']);
            } else {
                $data['notify_contacts'] = serialize([]);
            }
        }
        $data = hooks()->apply_filters('before_update_project', $data, $id);
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'projects', $data);
        if ($this->db->affected_rows() > 0) {
            if (isset($mark_all_tasks_as_completed)) {
                $this->_mark_all_project_tasks_as_completed($id, $playground);
            }
            $affectedRows++;
        }
        if ($send_created_email == true) {
            if ($this->send_project_customer_email($id, 'project_created_to_customer', $playground)) {
                $affectedRows++;
            }
        }
        if ($send_project_marked_as_finished_email_to_contacts == true) {
            if ($this->send_project_customer_email($id, 'project_marked_as_finished_to_customer', $playground)) {
                $affectedRows++;
            }
        }
        if ($affectedRows > 0) {
            $this->log_activity($id, 'project_activity_updated', $playground);
            log_activity('Project Updated [ID: ' . $id . ']');
            if ($original_project->status != $data['status']) {
                hooks()->do_action('project_status_changed', ['status' => $data['status'], 'project_id' => $id, ]);
                // Give space this log to be on top
                sleep(1);
                if ($data['status'] == 4) {
                    $this->log_activity($id, 'project_marked_as_finished');
                    $this->db->where('id', $id);
                    $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'projects', ['date_finished' => date('Y-m-d H:i:s') ]);
                } else {
                    $this->log_activity($id, 'project_status_updated', '<b><lang>project_status_' . $data['status'] . '</lang></b>');
                }
                if (isset($notify_project_members_status_change)) {
                    $this->_notify_project_members_status_change($id, $original_project->status, $data['status'], $playground);
                }
            }
            hooks()->do_action('after_update_project', $id);
            return true;
        }
        return false;
    }

    /**
     * Simplified function to send non complicated email templates for project contacts
     * @param mixed $id project id
     * @return boolean
     */
    public function send_project_customer_email($id, $template, $playground = false) {
        $sent = false;
        $this->load->model('clients_model');
        $contacts = $this->clients_model->get_contacts_for_project_notifications($id, 'project_emails', $playground);
        foreach ($contacts as $contact) {
            if (send_mail_template($template, $id, $contact['userid'], $contact)) {
                $sent = true;
            }
        }
        if ($sent) {
            hooks()->do_action('after_project_customer_email_sent', ['project_id' => $id, 'email_template' => $template, ]);
        }
        return $sent;
    }

    public function mark_as($data, $playground = false) {
        $this->db->select('status');
        $this->db->where('id', $data['project_id']);
        $old_status = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'projects')->row()->status;
        $this->db->where('id', $data['project_id']);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'projects', ['status' => $data['status_id'], ]);
        if ($this->db->affected_rows() > 0) {
            hooks()->do_action('project_status_changed', ['status' => $data['status_id'], 'project_id' => $data['project_id'], ]);
            if ($data['status_id'] == 4) {
                $this->log_activity($data['project_id'], 'project_marked_as_finished');
                $this->db->where('id', $data['project_id']);
                $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'projects', ['date_finished' => date('Y-m-d H:i:s') ]);
            } else {
                $this->log_activity($data['project_id'], 'project_status_updated', '<b><lang>project_status_' . $data['status_id'] . '</lang></b>');
                if ($old_status == 4) {
                    $this->db->where('id', $data['project_id']);
                    $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'projects', ['date_finished' => null]);
                }
            }
            if ($data['notify_project_members_status_change'] == 1) {
                $this->_notify_project_members_status_change($data['project_id'], $old_status, $data['status_id'], $playground);
            }
            if ($data['mark_all_tasks_as_completed'] == 1) {
                $this->_mark_all_project_tasks_as_completed($data['project_id'], $playground);
            }
            if (isset($data['cancel_recurring_tasks']) && $data['cancel_recurring_tasks'] == 'true') {
                $this->cancel_recurring_tasks($data['project_id'], $playground);
            }
            if (isset($data['send_project_marked_as_finished_email_to_contacts']) && $data['send_project_marked_as_finished_email_to_contacts'] == 1) {
                $this->send_project_customer_email($data['project_id'], 'project_marked_as_finished_to_customer', $playground);
            }
            return true;
        }
        return false;
    }

    private function _notify_project_members_status_change($id, $old_status, $new_status, $playground = false) {
        $members = $this->get_project_members($id, false, $playground);
        $notifiedUsers = [];
        foreach ($members as $member) {
            if ($member['staff_id'] != get_staff_user_id()) {
                $notified = add_notification(['fromuserid' => get_staff_user_id(), 'description' => 'not_project_status_updated', 'link' => 'projects/view/' . $id, 'touserid' => $member['staff_id'], 'additional_data' => serialize(['<lang>project_status_' . $old_status . '</lang>', '<lang>project_status_' . $new_status . '</lang>', ]), ]);
                if ($notified) {
                    array_push($notifiedUsers, $member['staff_id']);
                }
            }
        }
        pusher_trigger_notification($notifiedUsers);
    }

    private function _mark_all_project_tasks_as_completed($id, $playground = false) {
        $this->db->where('rel_type', 'project');
        $this->db->where('rel_id', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'tasks', ['status' => 5, 'datefinished' => date('Y-m-d H:i:s'), ]);
        $tasks = $this->get_tasks($id, [], false, false, null, $playground);
        foreach ($tasks as $task) {
            $this->db->where('task_id', $task['id']);
            $this->db->where('end_time IS NULL');
            $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'taskstimers', ['end_time' => time(), ]);
        }
        $this->log_activity($id, 'project_activity_marked_all_tasks_as_complete', $playground);
    }

    public function add_edit_members($data, $id, $playground = false) {
        $affectedRows = 0;
        if (isset($data['project_members'])) {
            $project_members = $data['project_members'];
        }
        $new_project_members_to_receive_email = [];
        $this->db->select('name,clientid');
        $this->db->where('id', $id);
        $project = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'projects')->row();
        $project_name = $project->name;
        $client_id = $project->clientid;
        $project_members_in = $this->get_project_members($id, false, $playground);
        if (sizeof($project_members_in) > 0) {
            foreach ($project_members_in as $project_member) {
                if (isset($project_members)) {
                    if (!in_array($project_member['staff_id'], $project_members)) {
                        $this->db->where('project_id', $id);
                        $this->db->where('staff_id', $project_member['staff_id']);
                        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'project_members');
                        if ($this->db->affected_rows() > 0) {
                            $this->db->where('staff_id', $project_member['staff_id']);
                            $this->db->where('project_id', $id);
                            $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'pinned_projects');
                            $this->load->model('staff_model');
                            $this->log_activity($id, 'project_activity_removed_team_member', $this->staff_model->get_staff_full_name($project_member['staff_id'], $playground));
                            $affectedRows++;
                        }
                    }
                } else {
                    $this->db->where('project_id', $id);
                    $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'project_members');
                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                    }
                }
            }
            if (isset($project_members)) {
                $notifiedUsers = [];
                foreach ($project_members as $staff_id) {
                    $this->db->where('project_id', $id);
                    $this->db->where('staff_id', $staff_id);
                    $_exists = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'project_members')->row();
                    if (!$_exists) {
                        if (empty($staff_id)) {
                            continue;
                        }
                        $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'project_members', ['project_id' => $id, 'staff_id' => $staff_id, ]);
                        if ($this->db->affected_rows() > 0) {
                            if ($staff_id != get_staff_user_id()) {
                                $notified = add_notification(['fromuserid' => get_staff_user_id(), 'description' => 'not_staff_added_as_project_member', 'link' => 'projects/view/' . $id, 'touserid' => $staff_id, 'additional_data' => serialize([$project_name, ]), ]);
                                array_push($new_project_members_to_receive_email, $staff_id);
                                if ($notified) {
                                    array_push($notifiedUsers, $staff_id);
                                }
                            }
                            $this->load->model('staff_model');
                            $this->log_activity($id, 'project_activity_added_team_member', $this->staff_model->get_staff_full_name($staff_id, $playground));
                            $affectedRows++;
                        }
                    }
                }
                pusher_trigger_notification($notifiedUsers);
            }
        } else {
            if (isset($project_members)) {
                $notifiedUsers = [];
                foreach ($project_members as $staff_id) {
                    if (empty($staff_id)) {
                        continue;
                    }
                    $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'project_members', ['project_id' => $id, 'staff_id' => $staff_id, ]);
                    if ($this->db->affected_rows() > 0) {
                        if ($staff_id != get_staff_user_id()) {
                            $notified = add_notification(['fromuserid' => get_staff_user_id(), 'description' => 'not_staff_added_as_project_member', 'link' => 'projects/view/' . $id, 'touserid' => $staff_id, 'additional_data' => serialize([$project_name, ]), ]);
                            array_push($new_project_members_to_receive_email, $staff_id);
                            if ($notifiedUsers) {
                                array_push($notifiedUsers, $staff_id);
                            }
                        }
                        $this->load->model('staff_model');
                        $this->log_activity($id, 'project_activity_added_team_member', $this->staff_model->get_staff_full_name($staff_id, $playground));
                        $affectedRows++;
                    }
                }
                pusher_trigger_notification($notifiedUsers);
            }
        }
        if (count($new_project_members_to_receive_email) > 0) {
            $all_members = $this->get_project_members($id, false, $playground);
            foreach ($all_members as $data) {
                if (in_array($data['staff_id'], $new_project_members_to_receive_email)) {
                    send_mail_template('project_staff_added_as_member', $data, $id, $client_id, $playground);
                }
            }
            hooks()->do_action('after_project_staff_added_as_member', ['project_id' => $id, 'new_project_members_to_receive_email' => $new_project_members_to_receive_email, ]);
        }
        if ($affectedRows > 0) {
            return true;
        }
        return false;
    }

    public function is_member($project_id, $staff_id = '', $playground = false) {
        if (!is_numeric($staff_id)) {
            $staff_id = get_staff_user_id();
        }
        $member = total_rows(db_prefix() . ($playground ? 'playground_' : '') . 'project_members', ['staff_id' => $staff_id, 'project_id' => $project_id, ]);
        if ($member > 0) {
            return true;
        }
        return false;
    }

    public function get_projects_for_ticket($client_id, $playground = false) {
        return $this->get('', ['clientid' => $client_id, ], $playground);
    }

    public function get_project_settings($project_id, $playground = false) {
        $this->db->where('project_id', $project_id);
        return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'project_settings')->result_array();
    }

    public function get_project_members($id, $with_name = false, $playground = false) {
        if ($with_name) {
            $this->db->select('firstname,lastname,email,project_id,staff_id');
        } else {
            $this->db->select('email,project_id,staff_id');
        }
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'staff', db_prefix() . ($playground ? 'playground_' : '') . 'staff.staffid=' . db_prefix() . ($playground ? 'playground_' : '') . 'project_members.staff_id');
        $this->db->where('project_id', $id);
        return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'project_members')->result_array();
    }

    public function remove_team_member($project_id, $staff_id, $playground = false) {
        $this->db->where('project_id', $project_id);
        $this->db->where('staff_id', $staff_id);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'project_members');
        if ($this->db->affected_rows() > 0) {
            $this->load->model('staff_model');
            // Remove member from tasks where is assigned
            $this->db->where('staffid', $staff_id);
            $this->db->where('taskid IN (SELECT id FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'tasks WHERE rel_type="project" AND rel_id="' . $this->db->escape_str($project_id) . '")');
            $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'task_assigned');
            $this->log_activity($project_id, 'project_activity_removed_team_member', $this->staff_model->get_staff_full_name($staff_id, $playground), $playground);
            return true;
        }
        return false;
    }

    public function get_timesheets($project_id, $tasks_ids = [], $playground = false) {
        if (count($tasks_ids) == 0) {
            $tasks = $this->get_tasks($project_id, [], false, false, null, $playground);
            $tasks_ids = [];
            foreach ($tasks as $task) {
                array_push($tasks_ids, $task['id']);
            }
        }
        if (count($tasks_ids) > 0) {
            $this->db->where('task_id IN(' . implode(', ', $tasks_ids) . ')');
            $timesheets = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'taskstimers')->result_array();
            $i = 0;
            $this->load->model('tasks_model');
            $this->load->model('staff_model');
            foreach ($timesheets as $t) {
                $task = $this->tasks_model->get($t['task_id'], [], $playground);
                $timesheets[$i]['task_data'] = $task;
                $timesheets[$i]['staff_name'] = $this->staff_model->get_staff_full_name($t['staff_id'], $playground);
                if (!is_null($t['end_time'])) {
                    $timesheets[$i]['total_spent'] = $t['end_time'] - $t['start_time'];
                } else {
                    $timesheets[$i]['total_spent'] = time() - $t['start_time'];
                }
                $i++;
            }
            return $timesheets;
        }
        return [];
    }

    public function get_discussion($id, $project_id = '', $playground = false) {
        if ($project_id != '') {
            $this->db->where('project_id', $project_id);
        }
        $this->db->where('id', $id);
        if (is_client_logged_in()) {
            $this->db->where('show_to_customer', 1);
            $this->db->where('project_id IN (SELECT id FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'projects WHERE clientid=' . get_client_user_id() . ')');
        }
        $discussion = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'projectdiscussions')->row();
        if ($discussion) {
            return $discussion;
        }
        return false;
    }

    public function get_discussion_comment($id, $playground = false) {
        $this->db->where('id', $id);
        $comment = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'projectdiscussioncomments')->row();
        if ($comment->contact_id != 0) {
            if (is_client_logged_in()) {
                if ($comment->contact_id == get_contact_user_id()) {
                    $comment->created_by_current_user = true;
                } else {
                    $comment->created_by_current_user = false;
                }
            } else {
                $comment->created_by_current_user = false;
            }
            $comment->profile_picture_url = contact_profile_image_url($comment->contact_id, $playground);
        } else {
            if (is_client_logged_in()) {
                $comment->created_by_current_user = false;
            } else {
                if (is_staff_logged_in()) {
                    if ($comment->staff_id == get_staff_user_id()) {
                        $comment->created_by_current_user = true;
                    } else {
                        $comment->created_by_current_user = false;
                    }
                } else {
                    $comment->created_by_current_user = false;
                }
            }
            if (is_admin($comment->staff_id)) {
                $comment->created_by_admin = true;
            } else {
                $comment->created_by_admin = false;
            }
            $comment->profile_picture_url = staff_profile_image_url($comment->staff_id, $playground);
        }
        $comment->created = (strtotime($comment->created) * 1000);
        if (!empty($comment->modified)) {
            $comment->modified = (strtotime($comment->modified) * 1000);
        }
        if (!is_null($comment->file_name)) {
            $comment->file_url = site_url('uploads/discussions/' . $comment->discussion_id . '/' . $comment->file_name);
        }
        return $comment;
    }

    public function get_discussion_comments($id, $type, $playground = false) {
        $this->db->where('discussion_id', $id);
        $this->db->where('discussion_type', $type);
        $comments = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'projectdiscussioncomments')->result_array();
        $i = 0;
        $allCommentsIDS = [];
        $allCommentsParentIDS = [];
        foreach ($comments as $comment) {
            $allCommentsIDS[] = $comment['id'];
            if (!empty($comment['parent'])) {
                $allCommentsParentIDS[] = $comment['parent'];
            }
            if ($comment['contact_id'] != 0) {
                if (is_client_logged_in()) {
                    if ($comment['contact_id'] == get_contact_user_id()) {
                        $comments[$i]['created_by_current_user'] = true;
                    } else {
                        $comments[$i]['created_by_current_user'] = false;
                    }
                } else {
                    $comments[$i]['created_by_current_user'] = false;
                }
                $comments[$i]['profile_picture_url'] = contact_profile_image_url($comment['contact_id']);
            } else {
                if (is_client_logged_in()) {
                    $comments[$i]['created_by_current_user'] = false;
                } else {
                    if (is_staff_logged_in()) {
                        if ($comment['staff_id'] == get_staff_user_id()) {
                            $comments[$i]['created_by_current_user'] = true;
                        } else {
                            $comments[$i]['created_by_current_user'] = false;
                        }
                    } else {
                        $comments[$i]['created_by_current_user'] = false;
                    }
                }
                if (is_admin($comment['staff_id'])) {
                    $comments[$i]['created_by_admin'] = true;
                } else {
                    $comments[$i]['created_by_admin'] = false;
                }
                $comments[$i]['profile_picture_url'] = staff_profile_image_url($comment['staff_id'], $playground);
            }
            if (!is_null($comment['file_name'])) {
                $comments[$i]['file_url'] = site_url('uploads/discussions/' . $id . '/' . $comment['file_name']);
            }
            $comments[$i]['created'] = (strtotime($comment['created']) * 1000);
            if (!empty($comment['modified'])) {
                $comments[$i]['modified'] = (strtotime($comment['modified']) * 1000);
            }
            $i++;
        }
        // Ticket #5471
        foreach ($allCommentsParentIDS as $parent_id) {
            if (!in_array($parent_id, $allCommentsIDS)) {
                foreach ($comments as $key => $comment) {
                    if ($comment['parent'] == $parent_id) {
                        $comments[$key]['parent'] = null;
                    }
                }
            }
        }
        return $comments;
    }

    public function get_discussions($project_id, $playground = false) {
        $this->db->where('project_id', $project_id);
        if (is_client_logged_in()) {
            $this->db->where('show_to_customer', 1);
        }
        $discussions = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'projectdiscussions')->result_array();
        $i = 0;
        foreach ($discussions as $discussion) {
            $discussions[$i]['total_comments'] = total_rows(db_prefix() . ($playground ? 'playground_' : '') . 'projectdiscussioncomments', ['discussion_id' => $discussion['id'], 'discussion_type' => 'regular', ]);
            $i++;
        }
        return $discussions;
    }

    public function add_discussion_comment($data, $discussion_id, $type, $playground = false) {
        $discussion = $this->get_discussion($discussion_id, '', $playground);
        $_data['discussion_id'] = $discussion_id;
        $_data['discussion_type'] = $type;
        if (isset($data['content'])) {
            $_data['content'] = $data['content'];
        }
        if (isset($data['parent']) && $data['parent'] != null) {
            $_data['parent'] = $data['parent'];
        }
        $this->load->model('staff_model');
        if (is_client_logged_in()) {
            $_data['contact_id'] = get_contact_user_id();
            $_data['fullname'] = get_contact_full_name($_data['contact_id'], $playground);
            $_data['staff_id'] = 0;
        } else {
            $_data['contact_id'] = 0;
            $_data['staff_id'] = get_staff_user_id();
            $_data['fullname'] = $this->staff_model->get_staff_full_name($_data['staff_id'], $playground);
        }
        $_data = $this->handle_project_discussion_comment_attachments($discussion_id, $data, $_data, $playground);
        $_data['created'] = date('Y-m-d H:i:s');
        $_data = hooks()->apply_filters('before_add_project_discussion_comment', $_data, $discussion_id);
        $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'projectdiscussioncomments', $_data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            if ($type == 'regular') {
                $discussion = $this->get_discussion($discussion_id, '', $playground);
                $not_link = 'projects/view/' . $discussion->project_id . '?group=project_discussions&discussion_id=' . $discussion_id;
            } else {
                $discussion = $this->get_file($discussion_id, $playground);
                $not_link = 'projects/view/' . $discussion->project_id . '?group=project_files&file_id=' . $discussion_id;
                $discussion->show_to_customer = $discussion->visible_to_customer;
            }
            $emailTemplateData = ['staff' => ['discussion_id' => $discussion_id, 'discussion_comment_id' => $insert_id, 'discussion_type' => $type, ], 'customers' => ['customer_template' => true, 'discussion_id' => $discussion_id, 'discussion_comment_id' => $insert_id, 'discussion_type' => $type, ], ];
            if (isset($_data['file_name'])) {
                $emailTemplateData['attachments'] = [['attachment' => PROJECT_DISCUSSION_ATTACHMENT_FOLDER . $discussion_id . '/' . $_data['file_name'], 'filename' => $_data['file_name'], 'type' => $_data['file_mime_type'], 'read' => true, ], ];
            }
            $notification_data = ['description' => 'not_commented_on_project_discussion', 'link' => $not_link, ];
            if (is_client_logged_in()) {
                $notification_data['fromclientid'] = get_contact_user_id();
            } else {
                $notification_data['fromuserid'] = get_staff_user_id();
            }
            $notifiedUsers = [];
            $regex = "/data\-mention\-id\=\"(\d+)\"/";
            if (isset($data['content']) && preg_match_all($regex, $data['content'], $mentionedStaff, PREG_PATTERN_ORDER)) {
                $members = array_unique($mentionedStaff[1], SORT_NUMERIC);
                $this->send_project_email_mentioned_users($discussion->project_id, 'project_new_discussion_comment_to_staff', $members, $emailTemplateData, $playground);
                foreach ($members as $memberId) {
                    if ($memberId == get_staff_user_id() && !is_client_logged_in()) {
                        continue;
                    }
                    $notification_data['touserid'] = $memberId;
                    if (add_notification($notification_data)) {
                        array_push($notifiedUsers, $memberId);
                    }
                }
            } else {
                $this->send_project_email_template($discussion->project_id, 'project_new_discussion_comment_to_staff', 'project_new_discussion_comment_to_customer', $discussion->show_to_customer, $emailTemplateData, $playground);
                $members = $this->get_project_members($discussion->project_id, false, $playground);
                foreach ($members as $member) {
                    if ($member['staff_id'] == get_staff_user_id() && !is_client_logged_in()) {
                        continue;
                    }
                    $notification_data['touserid'] = $member['staff_id'];
                    if (add_notification($notification_data)) {
                        array_push($notifiedUsers, $member['staff_id']);
                    }
                }
            }
            $this->log_activity($discussion->project_id, 'project_activity_commented_on_discussion', $discussion->subject, $discussion->show_to_customer, $playground);
            pusher_trigger_notification($notifiedUsers);
            $this->_update_discussion_last_activity($discussion_id, $type, $playground);
            hooks()->do_action('after_add_discussion_comment', $insert_id);
            return $this->get_discussion_comment($insert_id, $playground);
        }
        return false;
    }

    public function update_discussion_comment($data, $playground = false) {
        $comment = $this->get_discussion_comment($data['id'], $playground);
        $this->db->where('id', $data['id']);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'projectdiscussioncomments', ['modified' => date('Y-m-d H:i:s'), 'content' => $data['content'], ]);
        if ($this->db->affected_rows() > 0) {
            $this->_update_discussion_last_activity($comment->discussion_id, $comment->discussion_type, $playground);
        }
        return $this->get_discussion_comment($data['id'], $playground);
    }

    public function delete_discussion_comment($id, $logActivity = true, $playground = false) {
        $comment = $this->get_discussion_comment($id, $playground);
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'projectdiscussioncomments');
        if ($this->db->affected_rows() > 0) {
            $this->delete_discussion_comment_attachment($comment->file_name, $comment->discussion_id, $playground);
            if ($logActivity) {
                $additional_data = '';
                if ($comment->discussion_type == 'regular') {
                    $discussion = $this->get_discussion($comment->discussion_id, $playground);
                    $not = 'project_activity_deleted_discussion_comment';
                    $additional_data.= $discussion->subject . '<br />' . $comment->content;
                } else {
                    $discussion = $this->get_file($comment->discussion_id, $playground);
                    $not = 'project_activity_deleted_file_discussion_comment';
                    $additional_data.= $discussion->subject . '<br />' . $comment->content;
                }
                if (!is_null($comment->file_name)) {
                    $additional_data.= $comment->file_name;
                }
                $this->log_activity($discussion->project_id, $not, $additional_data, $playground);
            }
        }
        $this->db->where('parent', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'projectdiscussioncomments', ['parent' => null, ]);
        if ($this->db->affected_rows() > 0 && $logActivity) {
            $this->_update_discussion_last_activity($comment->discussion_id, $comment->discussion_type, $playground);
        }
        return true;
    }

    public function delete_discussion_comment_attachment($file_name, $discussion_id) {
        $path = PROJECT_DISCUSSION_ATTACHMENT_FOLDER . $discussion_id;
        if (!is_null($file_name)) {
            if (file_exists($path . '/' . $file_name)) {
                unlink($path . '/' . $file_name);
            }
        }
        if (is_dir($path)) {
            // Check if no attachments left, so we can delete the folder also
            $other_attachments = list_files($path);
            if (count($other_attachments) == 0) {
                delete_dir($path);
            }
        }
    }

    public function add_discussion($data, $playground = false) {
        if (is_client_logged_in()) {
            $data['contact_id'] = get_contact_user_id();
            $data['staff_id'] = 0;
            $data['show_to_customer'] = 1;
        } else {
            $data['staff_id'] = get_staff_user_id();
            $data['contact_id'] = 0;
            if (isset($data['show_to_customer'])) {
                $data['show_to_customer'] = 1;
            } else {
                $data['show_to_customer'] = 0;
            }
        }
        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['description'] = nl2br($data['description']);
        $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'projectdiscussions', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            $members = $this->get_project_members($data['project_id'], false, $playground);
            $notification_data = ['description' => 'not_created_new_project_discussion', 'link' => 'projects/view/' . $data['project_id'] . '?group=project_discussions&discussion_id=' . $insert_id, ];
            if (is_client_logged_in()) {
                $notification_data['fromclientid'] = get_contact_user_id();
            } else {
                $notification_data['fromuserid'] = get_staff_user_id();
            }
            $notifiedUsers = [];
            foreach ($members as $member) {
                if ($member['staff_id'] == get_staff_user_id() && !is_client_logged_in()) {
                    continue;
                }
                $notification_data['touserid'] = $member['staff_id'];
                if (add_notification($notification_data)) {
                    array_push($notifiedUsers, $member['staff_id']);
                }
            }
            pusher_trigger_notification($notifiedUsers);
            $this->send_project_email_template($data['project_id'], 'project_discussion_created_to_staff', 'project_discussion_created_to_customer', $data['show_to_customer'], ['staff' => ['discussion_id' => $insert_id, 'discussion_type' => 'regular', ], 'customers' => ['customer_template' => true, 'discussion_id' => $insert_id, 'discussion_type' => 'regular', ], ]);
            $this->log_activity($data['project_id'], 'project_activity_created_discussion', $data['subject'], $data['show_to_customer']);
            return $insert_id;
        }
        return false;
    }

    public function edit_discussion($data, $id, $playground = false) {
        $this->db->where('id', $id);
        if (isset($data['show_to_customer'])) {
            $data['show_to_customer'] = 1;
        } else {
            $data['show_to_customer'] = 0;
        }
        $data['description'] = nl2br($data['description']);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'projectdiscussions', $data);
        if ($this->db->affected_rows() > 0) {
            $this->log_activity($data['project_id'], 'project_activity_updated_discussion', $data['subject'], $data['show_to_customer']);
            return true;
        }
        return false;
    }

    public function delete_discussion($id, $logActivity = true, $playground = false) {
        $discussion = $this->get_discussion($id, '', $playground);
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'projectdiscussions');
        if ($this->db->affected_rows() > 0) {
            if ($logActivity) {
                $this->log_activity($discussion->project_id, 'project_activity_deleted_discussion', $discussion->subject, $discussion->show_to_customer);
            }
            $this->_delete_discussion_comments($id, 'regular', $playground);
            return true;
        }
        return false;
    }

    public function copy($project_id, $data, $playground = false) {
        $_new_data = [];
        $project = $this->get($project_id, [], $playground);
        $settings = $this->get_project_settings($project_id, $playground);
        $fields = $this->db->list_fields(db_prefix() . ($playground ? 'playground_' : '') . 'projects');
        foreach ($fields as $field) {
            if (isset($project->$field)) {
                $_new_data[$field] = $project->$field;
            }
        }
        unset($_new_data['id']);
        $_new_data['clientid'] = $data['clientid_copy_project'];
        unset($_new_data['clientid_copy_project']);
        $_new_data['start_date'] = to_sql_date($data['start_date']);
        if ($_new_data['start_date'] > date('Y-m-d')) {
            $_new_data['status'] = 1;
        } else {
            $_new_data['status'] = 2;
        }
        if ($data['deadline']) {
            $_new_data['deadline'] = to_sql_date($data['deadline']);
        } else {
            $_new_data['deadline'] = null;
        }
        if ($data['name']) {
            $_new_data['name'] = $data['name'];
        }
        $_new_data['project_created'] = date('Y-m-d H:i:s');
        $_new_data['addedfrom'] = get_staff_user_id();
        $_new_data['date_finished'] = null;
        if ($project->contact_notification == 2) {
            $this->load->model('clients_model');
            $contacts = $this->clients_model->get_contacts($_new_data['clientid'], ['active' => 1, 'project_emails' => 1], $playground);
            $_new_data['notify_contacts'] = serialize(array_column($contacts, 'id'));
        }
        $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'projects', $_new_data);
        $id = $this->db->insert_id();
        if ($id) {
            $tags = get_tags_in($project_id, 'project');
            $this->load->model('misc_model');
            $this->misc_model->handle_tags_save($tags, $id, 'project');
            foreach ($settings as $setting) {
                $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'project_settings', ['project_id' => $id, 'name' => $setting['name'], 'value' => $setting['value'], ]);
            }
            $added_tasks = [];
            $tasks = $this->get_tasks($project_id, [], false, false, null, $playground);
            $this->load->model('tasks_model');
            if (isset($data['tasks'])) {
                foreach ($tasks as $task) {
                    if (isset($data['task_include_followers'])) {
                        $copy_task_data['copy_task_followers'] = 'true';
                    }
                    if (isset($data['task_include_assignees'])) {
                        $copy_task_data['copy_task_assignees'] = 'true';
                    }
                    if (isset($data['tasks_include_checklist_items'])) {
                        $copy_task_data['copy_task_checklist_items'] = 'true';
                    }
                    $copy_task_data['copy_from'] = $task['id'];
                    // For new task start date, we will find the difference in days between
                    // the old project start and and the old task start date and then
                    // based on the new project start date, we will add e.q. 15 days to be
                    // new task start date to the task
                    // e.q. old project start date 2020-04-01, old task start date 2020-04-15 and due date 2020-04-30
                    // copy project and set start date 2020-06-01
                    // new task start date will be 2020-06-15 and below due date 2020-06-30
                    $dStart = new DateTime($project->start_date);
                    $dEnd = new DateTime($task['startdate']);
                    $dDiff = $dStart->diff($dEnd);
                    $startDate = new DateTime($_new_data['start_date']);
                    $startDate->modify('+' . $dDiff->days . ' DAY');
                    $newTaskStartDate = $startDate->format('Y-m-d');
                    $merge = ['rel_id' => $id, 'rel_type' => 'project', 'last_recurring_date' => null, 'startdate' => $newTaskStartDate, 'status' => $data['copy_project_task_status'], ];
                    // Calculate the diff in days between the task start and due date
                    // then add these days to the new task start date to be used as this task due date
                    if ($task['duedate']) {
                        $dStart = new DateTime($task['startdate']);
                        $dEnd = new DateTime($task['duedate']);
                        $dDiff = $dStart->diff($dEnd);
                        $dueDate = new DateTime($newTaskStartDate);
                        $dueDate->modify('+' . $dDiff->days . ' DAY');
                        $merge['duedate'] = $dueDate->format('Y-m-d');
                    }
                    $task_id = $this->tasks_model->copy($copy_task_data, $merge, $playground);
                    if ($task_id) {
                        array_push($added_tasks, $task_id);
                    }
                }
            }
            if (isset($data['milestones'])) {
                $milestones = $this->get_milestones($project_id, $playground);
                $_added_milestones = [];
                foreach ($milestones as $milestone) {
                    $newProjectStartDate = new DateTimeImmutable($_new_data['start_date']);
                    $oldProjectStartDate = new DateTime($project->start_date);
                    $oldMilestoneStartDate = new DateTime($milestone['start_date']); // assuming that the MySQL column added is start_date
                    $diffBetweenOldProjectStartDateAndOldMilesoneStartDate = $oldProjectStartDate->diff($oldMilestoneStartDate);
                    $newMilestoneStartDate = $newProjectStartDate->modify('+' . $diffBetweenOldProjectStartDateAndOldMilesoneStartDate->days . ' DAY');
                    $oldMilestoneDueDate = new DateTime($milestone['due_date']);
                    $diffBetweenOldMilestoneDueDateAndOldMilesoneStartDate = $oldMilestoneStartDate->diff($oldMilestoneDueDate);
                    $newMilestoneDueDate = $newMilestoneStartDate->modify('+' . $diffBetweenOldMilestoneDueDateAndOldMilesoneStartDate->days . ' DAY');
                    $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'milestones', ['name' => $milestone['name'], 'project_id' => $id, 'milestone_order' => $milestone['milestone_order'], 'description_visible_to_customer' => $milestone['description_visible_to_customer'], 'description' => $milestone['description'], 'start_date' => $newMilestoneStartDate->format('Y-m-d'), 'due_date' => $newMilestoneDueDate->format('Y-m-d'), 'datecreated' => date('Y-m-d'), 'color' => $milestone['color'], 'hide_from_customer' => $milestone['hide_from_customer'], ]);
                    $milestone_id = $this->db->insert_id();
                    if ($milestone_id) {
                        $_added_milestone_data = [];
                        $_added_milestone_data['id'] = $milestone_id;
                        $_added_milestone_data['name'] = $milestone['name'];
                        $_added_milestones[] = $_added_milestone_data;
                    }
                }
                if (isset($data['tasks'])) {
                    if (count($added_tasks) > 0) {
                        // Original project tasks
                        foreach ($tasks as $task) {
                            if ($task['milestone'] != 0) {
                                $this->db->where('id', $task['milestone']);
                                $milestone = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'milestones')->row();
                                if ($milestone) {
                                    foreach ($_added_milestones as $added_milestone) {
                                        if ($milestone->name == $added_milestone['name']) {
                                            $this->db->where('id IN (' . implode(', ', $added_tasks) . ')');
                                            $this->db->where('milestone', $task['milestone']);
                                            $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'tasks', ['milestone' => $added_milestone['id'], ]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                // milestones not set
                if (count($added_tasks)) {
                    foreach ($added_tasks as $task) {
                        $this->db->where('id', $task['id']);
                        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'tasks', ['milestone' => 0, ]);
                    }
                }
            }
            if (isset($data['members'])) {
                $members = $this->get_project_members($project_id, false, $playground);
                $_members = [];
                foreach ($members as $member) {
                    array_push($_members, $member['staff_id']);
                }
                $this->add_edit_members(['project_members' => $_members, ], $id, $playground);
            }
            $this->load->model('custom_fields_model');
            foreach ($this->custom_fields_model->get_custom_fields('projects', [], false, $playground) as $field) {
                $value = $this->custom_fields_model->get_custom_field_value($project_id, $field['id'], 'projects', false, $playground);
                if ($value != '') {
                    $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'customfieldsvalues', ['relid' => $id, 'fieldid' => $field['id'], 'fieldto' => 'projects', 'value' => $value, ]);
                }
            }
            $this->log_activity($id, 'project_activity_created', $playground);
            log_activity('Project Copied [ID: ' . $project_id . ', NewID: ' . $id . ']');
            hooks()->do_action('project_copied', ['project_id' => $project_id, 'new_project_id' => $id, ]);
            return $id;
        }
        return false;
    }

    public function get_staff_notes($project_id, $playground = false) {
        $this->db->where('project_id', $project_id);
        $this->db->where('staff_id', get_staff_user_id());
        $notes = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'project_notes')->row();
        if ($notes) {
            return $notes->content;
        }
        return '';
    }

    public function save_note($data, $project_id, $playground = false) {
        // Check if the note exists for this project;
        $this->db->where('project_id', $project_id);
        $this->db->where('staff_id', get_staff_user_id());
        $notes = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'project_notes')->row();
        if ($notes) {
            $this->db->where('id', $notes->id);
            $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'project_notes', ['content' => $data['content'], ]);
            if ($this->db->affected_rows() > 0) {
                return true;
            }
            return false;
        }
        $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'project_notes', ['staff_id' => get_staff_user_id(), 'content' => $data['content'], 'project_id' => $project_id, ]);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            return true;
        }
        return false;
        return false;
    }

    public function delete($project_id, $playground = false) {
        hooks()->do_action('before_project_deleted', $project_id);
        $project_name = get_project_name_by_id($project_id);
        $this->db->where('id', $project_id);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'projects');
        if ($this->db->affected_rows() > 0) {
            $this->db->where('project_id', $project_id);
            $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'project_members');
            $this->db->where('project_id', $project_id);
            $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'project_notes');
            $this->db->where('project_id', $project_id);
            $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'milestones');
            // Delete the custom field values
            $this->db->where('relid', $project_id);
            $this->db->where('fieldto', 'projects');
            $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'customfieldsvalues');
            $this->db->where('rel_id', $project_id);
            $this->db->where('rel_type', 'project');
            $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'taggables');
            $this->db->where('project_id', $project_id);
            $discussions = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'projectdiscussions')->result_array();
            foreach ($discussions as $discussion) {
                $discussion_comments = $this->get_discussion_comments($discussion['id'], 'regular', $playground);
                foreach ($discussion_comments as $comment) {
                    $this->delete_discussion_comment_attachment($comment['file_name'], $discussion['id'], $playground);
                }
                $this->db->where('discussion_id', $discussion['id']);
                $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'projectdiscussioncomments');
            }
            $this->db->where('project_id', $project_id);
            $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'projectdiscussions');
            $files = $this->get_files($project_id, $playground);
            foreach ($files as $file) {
                $this->remove_file($file['id'], $playground);
            }
            $tasks = $this->get_tasks($project_id, [], false, false, null, $playground);
            $this->load->model('tasks_model');
            foreach ($tasks as $task) {
                $this->tasks_model->delete_task($task['id'], false, $playground);
            }
            $this->db->where('project_id', $project_id);
            $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'project_settings');
            $this->db->where('project_id', $project_id);
            $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'project_activity');
            $this->db->where('project_id', $project_id);
            $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'expenses', ['project_id' => 0, ]);
            $this->db->where('project_id', $project_id);
            $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'contracts', ['project_id' => null, ]);
            $this->db->where('project_id', $project_id);
            $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'invoices', ['project_id' => 0, ]);
            $this->db->where('project_id', $project_id);
            $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'creditnotes', ['project_id' => 0, ]);
            $this->db->where('project_id', $project_id);
            $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'estimates', ['project_id' => 0, ]);
            $this->db->where('project_id', $project_id);
            $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'tickets', ['project_id' => 0, ]);
            $this->db->where('project_id', $project_id);
            $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'proposals', ['project_id' => null, ]);
            $this->db->where('project_id', $project_id);
            $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'pinned_projects');
            log_activity('Project Deleted [ID: ' . $project_id . ', Name: ' . $project_name . ']');
            hooks()->do_action('after_project_deleted', $project_id);
            return true;
        }
        return false;
    }

    /**
    * Get project status by passed project id
    * @param  mixed $id project id
    * @return array
    */
    public function get_project_status_by_id($id)
    {
       $statuses = $this->get_project_statuses();

        $status = [
            'id'    => 0,
            'color' => '#333',
            'name'  => '[Status Not Found]',
            'order' => 1,
        ];

        foreach ($statuses as $s) {
            if ($s['id'] == $id) {
                $status = $s;

                break;
            }
        }

        return $status;
    }

    public function get_activity($id = '', $limit = '', $only_project_members_activity = false, $playground = false) {
        if (!is_client_logged_in()) {
            $has_permission = staff_can('view', 'projects');
            if (!$has_permission) {
                $this->db->where('project_id IN (SELECT project_id FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'project_members WHERE staff_id=' . get_staff_user_id() . ')');
            }
        }
        if (is_client_logged_in()) {
            $this->db->where('visible_to_customer', 1);
        }
        if (is_numeric($id)) {
            $this->db->where('project_id', $id);
        }
        if (is_numeric($limit)) {
            $this->db->limit($limit);
        }
        $this->db->order_by('dateadded', 'desc');
        $activities = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'project_activity')->result_array();
        $i = 0;
        foreach ($activities as $activity) {
            $seconds = get_string_between($activity['additional_data'], '<seconds>', '</seconds>');
            $other_lang_keys = get_string_between($activity['additional_data'], '<lang>', '</lang>');
            $_additional_data = $activity['additional_data'];
            if ($seconds != '') {
                $_additional_data = str_replace('<seconds>' . $seconds . '</seconds>', seconds_to_time_format($seconds), $_additional_data);
            }
            if ($other_lang_keys != '') {
                $_additional_data = str_replace('<lang>' . $other_lang_keys . '</lang>', _l($other_lang_keys), $_additional_data);
            }
            if (strpos($_additional_data, 'project_status_') !== false) {
                $_additional_data = $this->get_project_status_by_id(strafter($_additional_data, 'project_status_'));
                if (isset($_additional_data['name'])) {
                    $_additional_data = $_additional_data['name'];
                }
            }
            $activities[$i]['description'] = _l($activities[$i]['description_key']);
            $activities[$i]['additional_data'] = $_additional_data;
            $activities[$i]['project_name'] = get_project_name_by_id($activity['project_id'], $playground);
            unset($activities[$i]['description_key']);
            $i++;
        }
        return $activities;
    }

    public function log_activity($project_id, $description_key, $additional_data = '', $visible_to_customer = 1, $playground = false) {
        if (!DEFINED('CRON')) {
            $this->load->model('staff_model');
            if (is_client_logged_in()) {
                $data['contact_id'] = get_contact_user_id();
                $data['staff_id'] = 0;
                $data['fullname'] = get_contact_full_name(get_contact_user_id());
            } else if (is_staff_logged_in()) {
                $data['contact_id'] = 0;
                $data['staff_id'] = get_staff_user_id();
                $data['fullname'] = $this->staff_model->get_staff_full_name(get_staff_user_id(), $playground);
            }
        } else {
            $data['contact_id'] = 0;
            $data['staff_id'] = 0;
            $data['fullname'] = '[CRON]';
        }
        $data['description_key'] = $description_key;
        $data['additional_data'] = $additional_data;
        $data['visible_to_customer'] = $visible_to_customer;
        $data['project_id'] = $project_id;
        $data['dateadded'] = date('Y-m-d H:i:s');
        $data = hooks()->apply_filters('before_log_project_activity', $data);
        $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'project_activity', $data);
    }

    public function new_project_file_notification($file_id, $project_id, $playground = false) {
        $file = $this->get_file($file_id, $playground);
        $additional_data = $file->file_name;
        $this->log_activity($project_id, 'project_activity_uploaded_file', $additional_data, $file->visible_to_customer);
        $members = $this->get_project_members($project_id, false, $playground);
        $notification_data = ['description' => 'not_project_file_uploaded', 'link' => 'projects/view/' . $project_id . '?group=project_files&file_id=' . $file_id, ];
        if (is_client_logged_in()) {
            $notification_data['fromclientid'] = get_contact_user_id();
        } else {
            $notification_data['fromuserid'] = get_staff_user_id();
        }
        $notifiedUsers = [];
        foreach ($members as $member) {
            if ($member['staff_id'] == get_staff_user_id() && !is_client_logged_in()) {
                continue;
            }
            $notification_data['touserid'] = $member['staff_id'];
            if (add_notification($notification_data)) {
                array_push($notifiedUsers, $member['staff_id']);
            }
        }
        pusher_trigger_notification($notifiedUsers);
        $this->send_project_email_template($project_id, 'project_file_to_staff', 'project_file_to_customer', $file->visible_to_customer, ['staff' => ['discussion_id' => $file_id, 'discussion_type' => 'file'], 'customers' => ['customer_template' => true, 'discussion_id' => $file_id, 'discussion_type' => 'file'], ], $playground);
    }

    public function add_external_file($data, $playground = false) {
        $insert['dateadded'] = date('Y-m-d H:i:s');
        $insert['project_id'] = $data['project_id'];
        $insert['external'] = $data['external'];
        $insert['visible_to_customer'] = $data['visible_to_customer'];
        $insert['file_name'] = $data['files'][0]['name'];
        $insert['subject'] = $data['files'][0]['name'];
        $insert['external_link'] = $data['files'][0]['link'];
        $path_parts = pathinfo($data['files'][0]['name']);
        $insert['filetype'] = get_mime_by_extension('.' . $path_parts['extension']);
        if (isset($data['files'][0]['thumbnailLink'])) {
            $insert['thumbnail_link'] = $data['files'][0]['thumbnailLink'];
        }
        if (isset($data['staffid'])) {
            $insert['staffid'] = $data['staffid'];
        } else if (isset($data['contact_id'])) {
            $insert['contact_id'] = $data['contact_id'];
        }
        $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'project_files', $insert);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            $this->new_project_file_notification($insert_id, $data['project_id'], $playground);
            return $insert_id;
        }
        return false;
    }

    public function send_project_email_template($project_id, $staff_template, $customer_template, $action_visible_to_customer, $additional_data = [], $playground = false) {
        if (count($additional_data) == 0) {
            $additional_data['customers'] = [];
            $additional_data['staff'] = [];
        } else if (count($additional_data) == 1) {
            if (!isset($additional_data['staff'])) {
                $additional_data['staff'] = [];
            } else {
                $additional_data['customers'] = [];
            }
        }
        $project = $this->get($project_id, [], $playground);
        $members = $this->get_project_members($project_id, false, $playground);
        foreach ($members as $member) {
            if (is_staff_logged_in() && $member['staff_id'] == get_staff_user_id()) {
                continue;
            }
            $mailTemplate = mail_template($staff_template, $project, $member, $additional_data['staff']);
            if (isset($additional_data['attachments'])) {
                foreach ($additional_data['attachments'] as $attachment) {
                    $mailTemplate->add_attachment($attachment);
                }
            }
            $mailTemplate->send();
        }
        if ($action_visible_to_customer == 1) {
            $this->load->model('clients_model');
            $contacts = $this->clients_model->get_contacts_for_project_notifications($project_id, 'project_emails', $playground);
            foreach ($contacts as $contact) {
                if (is_client_logged_in() && $contact['id'] == get_contact_user_id()) {
                    continue;
                }
                $mailTemplate = mail_template($customer_template, $project, $contact, $additional_data['customers']);
                if (isset($additional_data['attachments'])) {
                    foreach ($additional_data['attachments'] as $attachment) {
                        $mailTemplate->add_attachment($attachment);
                    }
                }
                $mailTemplate->send();
            }
        }
    }

    private function _get_project_billing_data($id, $playground = false) {
        $this->db->select('billing_type,project_rate_per_hour');
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'projects')->row();
    }

    public function total_logged_time_by_billing_type($id, $conditions = [], $playground = false) {
        $project_data = $this->_get_project_billing_data($id, $playground);
        $data = [];
        $this->load->model('projects_model');
        if ($project_data->billing_type == 2) {
            $seconds = $this->total_logged_time($id, $playground);
            $data = $this->projects_model->calculate_total_by_project_hourly_rate($seconds, $project_data->project_rate_per_hour, $playground);
            $data['logged_time'] = $data['hours'];
        } else if ($project_data->billing_type == 3) {
            $data = $this->_get_data_total_logged_time($id, $playground);
        }
        return $data;
    }

    public function data_billable_time($id, $playground = false) {
        return $this->_get_data_total_logged_time($id, ['billable' => 1, ], $playground);
    }

    public function data_billed_time($id, $playground = false) {
        return $this->_get_data_total_logged_time($id, ['billable' => 1, 'billed' => 1, ], $playground);
    }

    public function data_unbilled_time($id, $playground = false) {
        return $this->_get_data_total_logged_time($id, ['billable' => 1, 'billed' => 0, ], $playground);
    }

    private function _delete_discussion_comments($id, $type, $playground = false) {
        $this->db->where('discussion_id', $id);
        $this->db->where('discussion_type', $type);
        $comments = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'projectdiscussioncomments')->result_array();
        foreach ($comments as $comment) {
            $this->delete_discussion_comment_attachment($comment['file_name'], $id, $playground);
        }
        $this->db->where('discussion_id', $id);
        $this->db->where('discussion_type', $type);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'projectdiscussioncomments');
    }

    private function _get_data_total_logged_time($id, $conditions = [], $playground = false) {
        $project_data = $this->_get_project_billing_data($id, $playground);
        $tasks = $this->get_tasks($id, $conditions, false, false, null, $playground);
        if ($project_data->billing_type == 3) {
            $data = $this->calculate_total_by_task_hourly_rate($tasks);
            $data['logged_time'] = seconds_to_time_format($data['total_seconds']);
        } else if ($project_data->billing_type == 2) {
            $seconds = 0;
            foreach ($tasks as $task) {
                $seconds+= $task['total_logged_time'];
            }
            $data = $this->calculate_total_by_project_hourly_rate($seconds, $project_data->project_rate_per_hour);
            $data['logged_time'] = $data['hours'];
        }
        return $data;
    }

    private function _update_discussion_last_activity($id, $type, $playground = false) {
        if ($type == 'file') {
            $table = db_prefix() . ($playground ? 'playground_' : '') . 'project_files';
        } else if ($type == 'regular') {
            $table = db_prefix() . ($playground ? 'playground_' : '') . 'projectdiscussions';
        }
        $this->db->where('id', $id);
        $this->db->update($table, ['last_activity' => date('Y-m-d H:i:s'), ]);
    }

    public function send_project_email_mentioned_users($project_id, $staff_template, $staff, $additional_data = [], $playground = false) {
        $this->load->model('staff_model');
        $project = $this->get($project_id, [], $playground);
        foreach ($staff as $staffId) {
            if (is_staff_logged_in() && $staffId == get_staff_user_id()) {
                continue;
            }
            $member = (array)$this->staff_model->get($staffId, [], $playground);
            $member['staff_id'] = $member['staffid'];
            $mailTemplate = mail_template($staff_template, $project, $member, $additional_data['staff']);
            if (isset($additional_data['attachments'])) {
                foreach ($additional_data['attachments'] as $attachment) {
                    $mailTemplate->add_attachment($attachment);
                }
            }
            $mailTemplate->send();
        }
    }

    public function convert_estimate_items_to_tasks($project_id, $items, $assignees, $project_data, $project_settings, $playground = false) {
        $this->load->model('tasks_model');
        foreach ($items as $index => $itemId) {
            $this->db->where('id', $itemId);
            $_item = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'itemable')->row();
            $data = ['billable' => 'on', 'name' => $_item->description, 'description' => $_item->long_description, 'startdate' => _d($project_data['start_date']), 'duedate' => '', 'rel_type' => 'project', 'rel_id' => $project_id, 'hourly_rate' => $project_data['billing_type'] == 3 ? $_item->rate : 0, 'priority' => get_option('default_task_priority'), 'withDefaultAssignee' => false, ];
            if (isset($project_settings->view_tasks)) {
                $data['visible_to_client'] = 'on';
            }
            $task_id = $this->tasks_model->add($data, false, $playground);
            if ($task_id) {
                $staff_id = $assignees[$index];
                $this->tasks_model->add_task_assignees(['taskid' => $task_id, 'assignee' => intval($staff_id), ], false, false, $playground);
                if (!$this->is_member($project_id, $staff_id, $playground)) {
                    $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'project_members', ['project_id' => $project_id, 'staff_id' => $staff_id, ]);
                }
            }
        }
    }
    
    /**
     * Handles upload for project files
     * @param  mixed $project_id project id
     * @return boolean
     */
    public function handle_project_file_uploads($project_id, $playground = false) {
        $hookData = hooks()->apply_filters('before_handle_project_file_uploads', [
            'project_id' => $project_id,
            'index_name' => 'file',
            'handled_externally' => false, // e.g. module upload to s3
            'handled_externally_successfully' => false,
            'files' => $_FILES
        ]);
        if ($hookData['handled_externally']) {
            return $hookData['handled_externally_successfully'];
        }
        $filesIDS = [];
        $errors = [];
        $misc_model = new Misc_model();
        if (isset($_FILES['file']['name']) && ($_FILES['file']['name'] != '' || is_array($_FILES['file']['name']) && count($_FILES['file']['name']) > 0)) {
            hooks()->do_action('before_upload_project_attachment', $project_id);
            if (!is_array($_FILES['file']['name'])) {
                $_FILES['file']['name'] = [$_FILES['file']['name']];
                $_FILES['file']['type'] = [$_FILES['file']['type']];
                $_FILES['file']['tmp_name'] = [$_FILES['file']['tmp_name']];
                $_FILES['file']['error'] = [$_FILES['file']['error']];
                $_FILES['file']['size'] = [$_FILES['file']['size']];
            }
            $path = $this->misc_model->get_upload_path_by_type('project', $playground) . $project_id . '/';
            for ($i = 0;$i < count($_FILES['file']['name']);$i++) {
                if (_perfex_upload_error($_FILES['file']['error'][$i])) {
                    $errors[$_FILES['file']['name'][$i]] = _perfex_upload_error($_FILES['file']['error'][$i]);
                    continue;
                }
                // Get the temp file path
                $tmpFilePath = $_FILES['file']['tmp_name'][$i];
                // Make sure we have a filepath
                if (!empty($tmpFilePath) && $tmpFilePath != '') {
                    _maybe_create_upload_path($path);
                    $originalFilename = unique_filename($path, $_FILES['file']['name'][$i]);
                    $filename = app_generate_hash() . '.' . get_file_extension($originalFilename);
                    // In case client side validation is bypassed
                    if (!_upload_extension_allowed($filename)) {
                        continue;
                    }
                    $newFilePath = $path . $filename;
                    // Upload the file into the company uploads dir
                    if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                        if (is_client_logged_in()) {
                            $contact_id = get_contact_user_id();
                            $staffid = 0;
                        } else {
                            $staffid = get_staff_user_id();
                            $contact_id = 0;
                        }
                        $data = ['project_id' => $project_id, 'file_name' => $filename, 'original_file_name' => $originalFilename, 'filetype' => $_FILES['file']['type'][$i], 'dateadded' => date('Y-m-d H:i:s'), 'staffid' => $staffid, 'contact_id' => $contact_id, 'subject' => $originalFilename, ];
                        if (is_client_logged_in()) {
                            $data['visible_to_customer'] = 1;
                        } else {
                            $data['visible_to_customer'] = ($this->input->post('visible_to_customer') == 'true' ? 1 : 0);
                        }
                        $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'project_files', $data);
                        $insert_id = $this->db->insert_id();
                        if ($insert_id) {
                            if (is_image($newFilePath)) {
                                create_img_thumb($path, $filename);
                            }
                            array_push($filesIDS, $insert_id);
                        } else {
                            unlink($newFilePath);
                            return false;
                        }
                    }
                }
            }
        }
        if (count($filesIDS) > 0) {
            end($filesIDS);
            $lastFileID = key($filesIDS);
            $this->new_project_file_notification($filesIDS[$lastFileID], $project_id, $playground);
        }
        if (count($errors) > 0) {
            $message = '';
            foreach ($errors as $filename => $error_message) {
                $message.= $filename . ' - ' . $error_message . '<br />';
            }
            header('HTTP/1.0 400 Bad error');
            echo $message;
            die;
        }
        if (count($filesIDS) > 0) {
            return true;
        }
        return false;
    }

    /**
    * Handle upload for project discussions comment
    * Function for jquery-comment plugin
    * @param  mixed $discussion_id discussion id
    * @param  mixed $post_data     additional post data from the comment
    * @param  array $insert_data   insert data to be parsed if needed
    * @return array
    */
    public function handle_project_discussion_comment_attachments($discussion_id, $post_data, $insert_data, $playground = false) {
        if (isset($_FILES['file']['name']) && _perfex_upload_error($_FILES['file']['error'])) {
            header('HTTP/1.0 400 Bad error');
            echo json_encode(['message' => _perfex_upload_error($_FILES['file']['error']) ]);
            die;
        }
        $hookData = hooks()->apply_filters('before_handle_project_discussion_comment_attachment', [
            'discussion_id' => $discussion_id,
            'post_data' => $post_data,
            'insert_data' => $insert_data,
            'index_name' => 'file',
            'handled_externally' => false, // e.g. module upload to s3
            'handled_externally_successfully' => false,
            'files' => $_FILES
        ]);
        if ($hookData['handled_externally']) {
            return $insert_data;
        }
        if (isset($_FILES['file']['name'])) {
            hooks()->do_action('before_upload_project_discussion_comment_attachment');
            $path = FCPATH . 'uploads/' . ($playground ? 'playground_' : '') . 'discussions' . $discussion_id . '/';
            // Check for all cases if this extension is allowed
            if (!_upload_extension_allowed($_FILES['file']['name'])) {
                header('HTTP/1.0 400 Bad error');
                echo json_encode(['message' => _l('file_php_extension_blocked') ]);
                die;
            }
            // Get the temp file path
            $tmpFilePath = $_FILES['file']['tmp_name'];
            // Make sure we have a filepath
            if (!empty($tmpFilePath) && $tmpFilePath != '') {
                _maybe_create_upload_path($path);
                $filename = unique_filename($path, $_FILES['file']['name']);
                $newFilePath = $path . $filename;
                // Upload the file into the temp dir
                if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                    $insert_data['file_name'] = $filename;
                    if (isset($_FILES['file']['type'])) {
                        $insert_data['file_mime_type'] = $_FILES['file']['type'];
                    } else {
                        $insert_data['file_mime_type'] = get_mime_by_extension($filename);
                    }
                }
            }
        }
        return $insert_data;
    }
    
    /**
     * Get project billing type
     * @param  mixed $project_id
     * @return mixed
     */
    public function get_project_billing_type($project_id, $playground = false)
    {
        $CI = & get_instance();
        $CI->db->select('billing_type');
        $CI->db->where('id', $project_id);
        $project = $CI->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'projects')->row();
        if ($project) {
        return $project->billing_type;
        }

        return false;
    }

    /**
     * @deprecated
     *
     * @param int $id
     * @param string $type
     *
     * @return array
     */
    public function get_project_overview_weekly_chart_data($id, $type = 'this_week') {
        _deprecated_function('Projects_model::get_project_overview_weekly_chart_data', '2.9.2', 'HoursOverviewChart class');
        return (new HoursOverviewChart($id, $type))->get();
    }

    /**
     * @deprecated
     *
     * @param array $filters
     *
     * @return array
     */
    public function get_all_projects_gantt_data($filters = []) {
        _deprecated_function('Projects_model::get_all_projects_gantt_data', '2.9.2', 'AllProjectsGantt class');
        return (new AllProjectsGantt($filters))->get();
    }

    /**
     * @deprecated
     *
     * @return array
     */
    public function get_gantt_data($project_id, $type = 'milestones', $taskStatus = null, $type_where = []) {
        _deprecated_function('Projects_model::get_gantt_data', '2.9.2', 'Gantt class');
        return (new Gantt($project_id, $type))->forTaskStatus($taskStatus)->excludeMilestonesFromCustomer(isset($type_where['hide_from_customer']) && $type_where['hide_from_customer'] == 1)->get();
    }
}
