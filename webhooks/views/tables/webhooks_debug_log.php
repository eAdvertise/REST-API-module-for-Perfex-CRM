<?php

    defined('BASEPATH') || exit('No direct script access allowed');
    $aColumns = [
        'webhook_action_name',
        'response_code',
        'recorded_at',
        '1',
    ];
    $where = [];

    // Custom filters
    // Date filters
    if ($this->ci->input->post('date_created')) {
        if ($this->ci->input->post('date_created') != "custom") {
            $array_date = json_decode($this->ci->input->post('date_created'));
            $start_date = to_sql_date($array_date[0]);
            $end_date = to_sql_date($array_date[1]);
        } else {
            $start_date = to_sql_date($this->ci->input->post('from_date'));
            $end_date = to_sql_date($this->ci->input->post('to_date'));
        }
        array_push($where, ' AND DATE(recorded_at) BETWEEN DATE("' . $start_date . '") AND DATE("' . $end_date . '") ');
    }

    // Filtering based on response codes
    $response_code_filters = (array) $this->ci->input->post('response_codes');
    if (!empty($response_code_filters)) {
        $response_code_where = [];
        foreach ($response_code_filters as $filter) {
            switch ($filter) {
                case 'success':
                    $response_code_where[] = '(CAST(response_code AS UNSIGNED) BETWEEN 200 AND 299)';
                    break;
                case 'redirection':
                    $response_code_where[] = '(CAST(response_code AS UNSIGNED) BETWEEN 300 AND 399)';
                    break;
                case 'client_error':
                    $response_code_where[] = '(CAST(response_code AS UNSIGNED) BETWEEN 400 AND 499)';
                    break;
                case 'server_error':
                    $response_code_where[] = '(CAST(response_code AS UNSIGNED) BETWEEN 500 AND 599)';
                    break;
            }
        }

        if (!empty($response_code_where)) {
            array_push($where, 'AND (' . implode(' OR ', $response_code_where) . ')');
        }
    }

    $sIndexColumn = 'id';
    $sTable       = db_prefix().'webhooks_debug_log';
    $result       = data_tables_init($aColumns, $sIndexColumn, $sTable, [], $where, [db_prefix().'webhooks_debug_log.id']);
    $output       = $result['output'];
    $rResult      = $result['rResult'];
    foreach ($rResult as $aRow) {
        $row                = [];
        $row[]              = $aRow['webhook_action_name'];

        $color = "label-default";
        if ($aRow['response_code'] >= 200 && $aRow['response_code'] <=299) {
            $color = "label-success";
        }
        if ($aRow['response_code'] >= 300 && $aRow['response_code'] <=399) {
            $color = "label-info";
        }
        if ($aRow['response_code'] >= 400 && $aRow['response_code'] <=499) {
            $color = "label-warning";
        }
        if ($aRow['response_code'] >= 500 && $aRow['response_code'] <=599) {
            $color = "label-danger";
        }
        $row[]              = '<span class="label '.$color.'">'.$aRow['response_code'].'</span>';

        $row[]              = _dt($aRow['recorded_at']);
        $row[]              = '<a href="'.admin_url('webhooks/get_webhook_log_info/').$aRow['id'].'" class="btn btn-info btn-icon"><i class="fa fa-eye"></i></a>';
        $output['aaData'][] = $row;
    }
