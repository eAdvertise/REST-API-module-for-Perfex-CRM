<?php
use WpOrg\Requests\Requests as Webhooks_Requests;

function call_webhook($data, $webhook_for, $action, $data_id, $related_id = "", $request_from = "MANUAL")
{
    $CI = &get_instance();
    $CI->load->library('App_merge_fields');
    $CI->load->model(WEBHOOKS_MODULE . '/webhooks_model');
    $CI->load->helper(WEBHOOKS_MODULE . '/webhook_map');
    $CI->config->load(WEBHOOKS_MODULE . '/config');
    $message = "";

    $exclude_data = $CI->config->item('exclude_data');

    $payload_exclude_keys = [];
    if (is_array($exclude_data) && isset($exclude_data[$webhook_for]) && is_array($exclude_data[$webhook_for])) {
        $payload_exclude_keys = $exclude_data[$webhook_for];
    }

    // Exclude configured sensitive keys from base payload before mapping and sending.
    $data = webhooks_remove_excluded_fields($data, $payload_exclude_keys);
    $map_data = $data;
    if ($webhook_for == "client") {
        $contact_data = isset($data->ContactData) ? (array) $data->ContactData : [];
        $client_data = isset($data->ClientData) ? (array) $data->ClientData : [];
        $map_data = (object) array_merge($contact_data, $client_data);
    }

    /* Adding relation data based on config for spedific type */
    $mapping_fields = $CI->config->item('mapping_fields');
    $fields = $mapping_fields[$webhook_for] ?? [];
    foreach ($fields as $key => $field) {
        if (!is_array($field) || empty($field['fetch_method'])) {
            continue;
        }

        $exclude_keys = isset($field['exclude']) && is_array($field['exclude']) ? $field['exclude'] : [];

        if(!empty($map_data->{$key})){
            if($field['fetch_method'] == "model"){
                $CI->load->model($field['model']);
                $mapped_data = $CI->{$field['model']}->{$field['method']}($map_data->{$key});
                $data->{$key."_data"} = webhooks_remove_excluded_fields($mapped_data, $exclude_keys);
            }
            if($field['fetch_method'] == "database"){
                $mapped_data = $CI->db->get_where($field['table_name'], [$field['id_column'] => $map_data->{$key}])->row();
                $data->{$key."_data"} = webhooks_remove_excluded_fields($mapped_data, $exclude_keys);
            }
            if($field['fetch_method'] == "helper"){
                $index_key = $key."_data";
                if($webhook_for == "tasks" && $key == "rel_id"){
                    $index_key = $map_data->rel_type."_data";
                }
                $mapped_data = call_user_func($field['method'], (!empty($field['data_id'])) ? $data_id : $map_data->{$key});
                $data->{$index_key} = webhooks_remove_excluded_fields($mapped_data, $exclude_keys);
            }
        }
    }

    $merge_fields = [];
    $no_merge_fields = ["expenses"];
    if ($webhook_for == "ticket") {
        $merge_fields = $CI->app_merge_fields->format_feature("{$webhook_for}_merge_fields", 'new-ticket-opened-admin', $data_id);
    } elseif ($webhook_for == "client" && !empty($related_id)) {
        $merge_fields = $CI->app_merge_fields->format_feature("{$webhook_for}_merge_fields", $data_id, $related_id);
    } elseif (($webhook_for == "invoice" || $webhook_for == "client") && !empty($data_id)) {
        $payment_id = isset($data->payments) && is_array($data->payments) && !empty($data->payments) ? $data->payments[0]['paymentid'] : false;
        $merge_fields = $CI->app_merge_fields->format_feature("{$webhook_for}_merge_fields", $data_id, $payment_id);
    } elseif($webhook_for == "event") {
        $merge_fields = $CI->app_merge_fields->format_feature("{$webhook_for}_merge_fields", $data);
    } elseif($webhook_for == "leads") {
        $merge_fields = $CI->app_merge_fields->format_feature("{$webhook_for}_merge_fields", $data);
    } elseif($webhook_for == "tasks") {
        $merge_fields = $CI->app_merge_fields->format_feature("{$webhook_for}_merge_fields", $data_id);
    } elseif($webhook_for == "projects") {
        $merge_fields = $CI->app_merge_fields->format_feature("{$webhook_for}_merge_fields", $data_id);
    } elseif($webhook_for == "proposals") {
        $merge_fields = $CI->app_merge_fields->format_feature("{$webhook_for}_merge_fields", $data_id);
    } elseif($webhook_for == "credit_note") {
        $merge_fields = $CI->app_merge_fields->format_feature("{$webhook_for}_merge_fields", $data_id);
    } elseif($webhook_for == "estimate") {
        $merge_fields = $CI->app_merge_fields->format_feature("{$webhook_for}_merge_fields", $data_id);
    } elseif($webhook_for == "staff") {
        $merge_fields = $CI->app_merge_fields->format_feature("{$webhook_for}_merge_fields", $data_id);
        $staff_reminder = $CI->db->where('staff', $data_id)->order_by('id', 'DESC')->get(db_prefix() . 'reminders')->row();
        if (!empty($staff_reminder)) {
            $CI->load->library('merge_fields/Staff_merge_fields');
            $merge_fields = array_merge($merge_fields, $CI->staff_merge_fields->reminder($staff_reminder));
        }
    } elseif($webhook_for == "contract") {
        $merge_fields = $CI->app_merge_fields->format_feature("{$webhook_for}_merge_fields", $data_id);
    } elseif ($webhook_for == "invoice_payments" && !empty($related_id)) {
        $merge_fields = $CI->app_merge_fields->format_feature("invoice_merge_fields", $data_id, $related_id);
    } elseif(!in_array("expenses", $no_merge_fields)) {
        $merge_fields = $CI->app_merge_fields->format_feature("{$webhook_for}_merge_fields", $data_id);
    }

    switch ($webhook_for) {
        case 'client':
            $customFieldTypes = ["customers", "contacts"];
            break;

        case 'invoice':
            $customFieldTypes = ["invoice", "items"];
            break;

        case 'credit_note':
            $customFieldTypes = ["credit_note"];
            break;

        case 'proposals':
            $customFieldTypes = ["proposal"];
            break;

        case 'ticket':
            $customFieldTypes = ["tickets"];
            break;

        case 'contract':
            $customFieldTypes = ["contracts"];
            break;

        case 'invoice_items':
            $customFieldTypes = ["items"];
            break;

        default:
            $customFieldTypes = [$webhook_for];
            break;
    }

    $CI->db->where('active', 1);
    $CI->db->where_in('fieldto', $customFieldTypes);

    $CI->db->order_by('field_order', 'asc');
    $fields = $CI->db->get(db_prefix() . 'customfields')->result_array();

    foreach ($fields as $key => $field) {
        $rel_id = $data_id;
        if ($field['fieldto'] == "contacts" && !empty($related_id)) {
            $rel_id = $related_id;
        }
        $data->{$field['slug']} = get_custom_field_value($rel_id, $field['id'], $field['fieldto'] == 'items' ? 'items_pr' : $field['fieldto'] , false);
    }

    if ($webhook_for == "ticket") {
        $merge_fields['{staff_ticket_link}']  = site_url('clients/ticket/' . $data_id);
        $merge_fields['{client_ticket_link}'] = admin_url('tickets/ticket/' . $data_id);
    }

    if ($webhook_for == "tasks") {
        $CI->db->where('id', $data_id);
        $task = $CI->db->get(db_prefix() . 'tasks')->row();
        $merge_fields['{staff_task_link}'] = admin_url('tasks/view/' . $data_id);
        $merge_fields['{client_task_link}'] = site_url('clients/project/' . $task->rel_id . '?group=project_tasks&taskid=' . $data_id);
    }

    if ($webhook_for == "projects") {
        $merge_fields['{staff_project_link}'] = site_url('clients/project/' . $data_id);
        $merge_fields['{client_project_link}'] = admin_url('projects/view/' . $data_id);
    }
    //get comman merge fields
    $other_merge_fields = $CI->app_merge_fields->format_feature(
        'other_merge_fields'
    );

    $merge_fields = array_merge($merge_fields, $other_merge_fields);

    $all_hooks = $CI->webhooks_model->getAll($webhook_for);
    foreach ($all_hooks as $webhook) {
        $webhook_action = json_decode($webhook->webhook_action, true);
        if (!in_array($action, $webhook_action)) {
            continue;
        }

        if ($request_from != "MANUAL" && empty($webhook->webhook_after_number)) {
            continue;
        }

        if (!empty($webhook->webhook_after_number) && $request_from == "MANUAL") {
            $schedule_data['webhook_id'] = $webhook->id;
            $schedule_data['request_data'] = json_encode($data);
            $schedule_data['rel_id'] = $data_id;
            $schedule_data['rel_type'] = $webhook_for;
            $schedule_data['action'] = $action;
            $schedule_data['secondary_id'] = $related_id;
            $schedule_data['scheduled_at'] = date("Y-m-d H:i:s" ,strtotime("+ {$webhook->webhook_after_number} {$webhook->webhook_after_type}", time()));
            $CI->db->insert("scheduled_webhooks", $schedule_data);
            continue;
        }

        $headers = json_decode($webhook->request_header, true);
        $headers = array_map(static function ($header) use ($merge_fields) {
            $header_key = $header['header_choice'];
            if ('custom' === $header_key) {
                $header_key = $header['header_custom_choice'];
            }
            $header['value'] = preg_replace(
                '/@{(.*?)}/',
                '{$1}',
                $header['value']
            );
            foreach ($merge_fields as $key => $val) {
                $header['value'] =
                    false !== stripos($header['value'], $key)
                    ? str_replace($key, $val, $header['value'])
                    : str_replace($key, '', $header['value']);
            }

            return ['key' => trim($header_key), 'value' => trim($header['value'])];
        }, $headers);
        $headers = array_column($headers, 'value', 'key');

        $default_body = json_decode($webhook->request_body, true);
        $default_body = array_map(static function ($body) use ($merge_fields) {
            $body['value'] = preg_replace('/@{(.*?)}/', '{$1}', $body['value']);
            foreach ($merge_fields as $key => $val) {
                $body['value'] =
                    false !== stripos($body['value'], $key)
                    ? str_replace($key, $val, $body['value'])
                    : str_replace($key, '', $body['value']);
            }

            return [
                'key'   => trim($body['key']),
                'value' => trim($body['value']),
            ];
        }, $default_body);
        $default_body = array_column($default_body, 'value', 'key');

        $body_data = array_merge((array) $data, $default_body);
        if ('json' === strtolower($webhook->request_format) && 'GET' != $webhook->request_method && 'DELETE' != $webhook->request_method) {
            $body_data = json_encode($body_data);
        }

        // Send mail if webhook fails
        $CI->load->model('emails_model');

        $admin = $CI->staff_model->get('', ['admin'=>1, 'role'=>NULL]);
        $admin_email='';
        if (!empty($admin)) {
            $admin_email = $admin[0]['email'];
        }

        try {
            $request = Webhooks_Requests::request(
                $webhook->request_url,
                $headers,
                $body_data,
                $webhook->request_method
            );
            $response_code = $request->status_code;
            $response_data = htmlentities($request->body);

            $message = $response_code;

            $cron_status = "SUCCESS";

            if (($response_code >= 300 && $response_code <= 399) || ($response_code >= 400 && $response_code <= 499) || ($response_code >= 500 && $response_code <= 599)) {
                send_mail_template('webhooks_failed', WEBHOOKS_MODULE, $admin_email, $message);
                $cron_status = "FAILED";
                $error_message = $message;
            }
        } catch (Exception $e) {
            $response_code = 'EXCEPTION';
            $response_data = $e->getMessage();
            send_mail_template('webhooks_failed', WEBHOOKS_MODULE, $admin_email, $message);
            $cron_status = "FAILED";
            $error_message = $response_data;
        }

        if ($request_from != "MANUAL") {
            $CI->db->update("scheduled_webhooks", ['status' => $cron_status,'executed_at' => date("Y-m-d H:i:s"), "error_message" => $error_message ?? NULL], ['id' => $request_from]);
        }
        if ($webhook->debug_mode) {
            $insert_data = [
                'webhook_action_name' => $webhook->name,
                'request_url'    => $webhook->request_url,
                'request_method' => $webhook->request_method,
                'request_format' => $webhook->request_format,
                'webhook_for'    => $webhook_for,
                'webhook_action' => json_encode([$action]),
                'request_header' => json_encode($headers),
                'request_body'   => is_array($body_data) ? json_encode($body_data) : $body_data,
                'response_code'  => $response_code,
                'response_data'  => $response_data,
            ];
            $CI->webhooks_model->add_log($insert_data);
        }
    }
}

if (!function_exists('webhooks_remove_excluded_fields')) {
    function webhooks_remove_excluded_fields($value, array $excluded_keys = [])
    {
        if (empty($excluded_keys)) {
            return $value;
        }

        /* For object type of data */
        if (is_object($value)) {
            foreach ($excluded_keys as $excluded_key) {
                if (property_exists($value, $excluded_key)) {
                    unset($value->{$excluded_key});
                }
            }
            foreach (get_object_vars($value) as $key => $child_value) {
                $value->{$key} = webhooks_remove_excluded_fields($child_value, $excluded_keys);
            }
            return $value;
        }

        /* For array type of data */
        if (is_array($value)) {
            foreach ($excluded_keys as $excluded_key) {
                if (array_key_exists($excluded_key, $value)) {
                    unset($value[$excluded_key]);
                }
            }
            foreach ($value as $key => $child_value) {
                $value[$key] = webhooks_remove_excluded_fields($child_value, $excluded_keys);
            }
        }

        return $value;
    }
}