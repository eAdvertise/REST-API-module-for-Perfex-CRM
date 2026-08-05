<?php 

function task_added_from($id) {
    $CI = &get_instance();
    $CI->db->where('id', $id);
    $task = $CI->db->get(db_prefix() . 'tasks')->row();
    if($task->is_added_from_contact == "0"){
        return $CI->staff_model->get($task->addedfrom);
    } else {
        return $CI->clients_model->get($task->addedfrom);
    }
}


function task_rel_id($id) {
    $CI = &get_instance();
    $CI->db->where('id', $id);
    $task = $CI->db->get(db_prefix() . 'tasks')->row();
        
    switch ($task->rel_type) {
        case 'project':
            $CI->load->model("projects_model");
            $rel_data = $CI->projects_model->get($task->rel_id);
            break;
        case 'invoice':
            $CI->load->model("invoices_model");
            $rel_data = $CI->invoices_model->get($task->rel_id);
            break;
        case 'customer':
            $CI->load->model("clients_model");
            $rel_data = $CI->clients_model->get($task->rel_id);
            break;
        case 'estimate':
            $CI->load->model("estimates_model");
            $rel_data = $CI->estimates_model->get($task->rel_id);
            break;
        case 'contract':
            $CI->load->model("contracts_model");
            $rel_data = $CI->contracts_model->get($task->rel_id);
            break;
        case 'ticket':
            $CI->load->model("tickets_model");
            $rel_data = $CI->tickets_model->get($task->rel_id);
            break;
        case 'expense':
            $CI->load->model("expenses_model");
            $rel_data = $CI->expenses_model->get($task->rel_id);
            break;
        case 'lead':
            $CI->load->model("leads_model");
            $rel_data = $CI->leads_model->get($task->rel_id);
            break;
        case 'proposal':
            $CI->load->model("proposals_model");
            $rel_data = $CI->proposals_model->get($task->rel_id);
            break;
    }

    return $rel_data;
}

function proposal_rel_id($id) {
    $CI = &get_instance();
    $CI->load->model("proposals_model");
    $proposal = $CI->proposals_model->get($id);
    if($proposal->rel_type == "lead"){
        $CI->load->model("leads_model");
        return $CI->leads_model->get($proposal->rel_id);
    } else {
        $CI->load->model("clients_model");
        return $CI->clients_model->get($proposal->rel_id);
    }
}
