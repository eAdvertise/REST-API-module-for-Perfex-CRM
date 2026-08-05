<?php


defined('BASEPATH') or exit('No direct script access allowed');

class Contracts_model extends App_Model {
    public function __construct() {
        parent::__construct();

        $this->load->model('contract_types_model');
        $this->load->model('clients_model');
    }

    /**
     * Get contract/s
     * @param  mixed  $id         contract id
     * @param  array   $where      perform where
     * @param  boolean $for_editor if for editor is false will replace the field if not will not replace
     * @return mixed
     */
    public function get($id = '', $where = [], $for_editor = false, $playground = false) {
        $this->db->select('*,' . db_prefix() . ($playground ? 'playground_' : '') . 'contracts_types.name as type_name,' . db_prefix() . ($playground ? 'playground_' : '') . 'contracts.id as id, ' . db_prefix() . ($playground ? 'playground_' : '') . 'contracts.addedfrom');
        $this->db->where($where);
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'contracts_types', '' . db_prefix() . ($playground ? 'playground_' : '') . 'contracts_types.id = ' . db_prefix() . ($playground ? 'playground_' : '') . 'contracts.contract_type', 'left');
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'clients', '' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.userid = ' . db_prefix() . ($playground ? 'playground_' : '') . 'contracts.client');
        if (is_numeric($id)) {
            $this->db->where(db_prefix() . ($playground ? 'playground_' : '') . 'contracts.id', $id);
            $contract = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'contracts')->row();
            if ($contract) {
                $merge_fields = $this->get_merge_fields($contract, $playground);
                $contract->attachments = $this->get_contract_attachments('', $contract->id, $playground);
                if ($contract->content !== null && $for_editor == false) {
                    foreach ($merge_fields as $key => $val) {
                        if (stripos($contract->content, $key) !== false) {
                            $contract->content = str_ireplace($key, $val, $contract->content);
                        } else {
                            $contract->content = str_ireplace($key, '', $contract->content);
                        }
                    }
                }
            }
            return $contract;
        }
        $contracts = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'contracts')->result_array();
        $i = 0;
        foreach ($contracts as $contract) {
            $contracts[$i]['attachments'] = $this->get_contract_attachments('', $contract['id'], $playground);
            $i++;
        }
        return $contracts;
    }

    /**
     * Select unique contracts years
     * @return array
     */
    public function get_contracts_years($playground = false) {
        return $this->db->query('SELECT DISTINCT(YEAR(datestart)) as year FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'contracts')->result_array();
    }

    /**
     * @param  integer ID
     * @return object
     * Retrieve contract attachments from database
     */
    public function get_contract_attachments($attachment_id = '', $id = '', $playground = false) {
        if (is_numeric($attachment_id)) {
            $this->db->where('id', $attachment_id);
            return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'files')->row();
        }
        $this->db->order_by('dateadded', 'desc');
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'contract');
        return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'files')->result_array();
    }

    /**
     * @param   array $_POST data
     * @return  integer Insert ID
     * Add new contract
     */
    public function add($data, $playground = false) {
        $data['dateadded'] = date('Y-m-d H:i:s');
        $data['addedfrom'] = get_staff_user_id();
        $data['datestart'] = to_sql_date($data['datestart']);
        unset($data['attachment']);
        if ($data['dateend'] == '') {
            unset($data['dateend']);
        } else {
            $data['dateend'] = to_sql_date($data['dateend']);
        }
        if (isset($data['trash']) && ($data['trash'] == 1 || $data['trash'] === 'on')) {
            $data['trash'] = 1;
        } else {
            $data['trash'] = 0;
        }
        if (isset($data['not_visible_to_client']) && ($data['not_visible_to_client'] == 1 || $data['not_visible_to_client'] === 'on')) {
            $data['not_visible_to_client'] = 1;
        } else {
            $data['not_visible_to_client'] = 0;
        }
        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }
        $data['hash'] = app_generate_hash();
        $data = hooks()->apply_filters('before_contract_added', $data);
        $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'contracts', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            if (isset($custom_fields)) {
                $this->load->model('custom_fields_model');
                $this->custom_fields_model->handle_custom_fields_post($insert_id, $custom_fields, false, $playground);
            }
            hooks()->do_action('after_contract_added', $insert_id);
            log_activity('New Contract Added [' . $data['subject'] . ']');
            return $insert_id;
        }
        return false;
    }

    /**
     * @param  array $_POST data
     * @param  integer Contract ID
     * @return boolean
     */
    public function update($data, $id, $playground = false) {
        $affectedRows = 0;
        $contract = $this->db->where('id', $id)->get(($playground ? 'playground_' : '') . 'contracts')->row();
        if (isset($data['datestart'])) {
            $data['datestart'] = to_sql_date($data['datestart']);
        }
        if (isset($data['dateend'])) {
            $data['dateend'] = $data['dateend'] == '' ? null : to_sql_date($data['dateend']);
        }
        if (isset($data['dateend']) && $data['dateend'] !== $contract->dateend) {
            $data['isexpirynotified'] = 0;
        }
        if ($data['dateend'] !== $contract) {
            if (isset($data['trash'])) {
                $data['trash'] = 1;
            } else {
                $data['trash'] = 0;
            }
        }
        if (isset($data['not_visible_to_client'])) {
            $data['not_visible_to_client'] = 1;
        } else {
            $data['not_visible_to_client'] = 0;
        }
        $data = hooks()->apply_filters('before_contract_updated', $data, $id);
        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            $this->load->model('custom_fields_model');
            if ($this->custom_fields_model->handle_custom_fields_post($id, $custom_fields, false, $playground)) {
                $affectedRows++;
            }
            unset($data['custom_fields']);
        }
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'contracts', $data);
        if ($this->db->affected_rows() > 0) {
            hooks()->do_action('after_contract_updated', $id);
            log_activity('Contract Updated [' . $data['subject'] . ']');
            return true;
        }
        return $affectedRows > 0;
    }

    public function add_signature($id, $playground = false): bool {
        $contract = $this->get($id, [], true, $playground);
        if ($contract) {
            $content = override_merge_fields($this->get_merge_fields($contract), $contract->content);
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'contracts', array_merge(get_acceptance_info_array(), ['signed' => 1, 'content' => $content]));
            // Notify contract creator that customer signed the contract
            send_contract_signed_notification_to_staff($id);
            return true;
        }
        return false;
    }

    public function clear_signature($id, $playground = false): bool {
        $this->db->select('signature');
        $this->db->where('id', $id);
        $contract = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'contracts')->row();
        if ($contract) {
            $contractData = $this->get($id, [], true, $playground);
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'contracts', array_merge(get_acceptance_info_array(true), ['signed' => 0, 'content' => restore_merge_fields($contractData->content) ]));
            if (!empty($contract->signature)) {
                $this->load->model('misc_model');
                unlink($this->misc_model->get_upload_path_by_type('contract', $playground) . $id . '/' . $contract->signature);
            }
            return true;
        }
        return false;
    }

    /**
     * Add contract comment
     * @param mixed  $data   $_POST comment data
     * @param boolean $client is request coming from the client side
     */
    public function add_comment($data, $client = false, $playground = false) {
        if (is_staff_logged_in()) {
            $client = false;
        }
        if (isset($data['action'])) {
            unset($data['action']);
        }
        $data['dateadded'] = date('Y-m-d H:i:s');
        if ($client == false) {
            $data['staffid'] = get_staff_user_id();
        }
        $data['content'] = nl2br($data['content']);
        $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'contract_comments', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            $contract = $this->get($data['contract_id'], [], false, $playground);
            if (($contract->not_visible_to_client == '1' || $contract->trash == '1') && $client == false) {
                return true;
            }
            if ($client == true) {
                // Get creator
                $this->db->select('staffid, email, phonenumber');
                $this->db->where('staffid', $contract->addedfrom);
                $staff_contract = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'staff')->result_array();
                $notifiedUsers = [];
                foreach ($staff_contract as $member) {
                    $notified = add_notification(['description' => 'not_contract_comment_from_client', 'touserid' => $member['staffid'], 'fromcompany' => 1, 'fromuserid' => 0, 'link' => 'contracts/contract/' . $data['contract_id'], 'additional_data' => serialize([$contract->subject, ]), ]);
                    if ($notified) {
                        array_push($notifiedUsers, $member['staffid']);
                    }
                    $template = mail_template('contract_comment_to_staff', $contract, $member);
                    $merge_fields = $template->get_merge_fields();
                    $template->send();
                    // Send email/sms to admin that client commented
                    $this->app_sms->trigger(SMS_TRIGGER_CONTRACT_NEW_COMMENT_TO_STAFF, $member['phonenumber'], $merge_fields);
                }
                pusher_trigger_notification($notifiedUsers);
            } else {
                $contacts = $this->clients_model->get_contacts($contract->client, ['active' => 1, 'contract_emails' => 1]);
                foreach ($contacts as $contact) {
                    $template = mail_template('contract_comment_to_customer', $contract, $contact);
                    $merge_fields = $template->get_merge_fields();
                    $template->send();
                    $this->app_sms->trigger(SMS_TRIGGER_CONTRACT_NEW_COMMENT_TO_CUSTOMER, $contact['phonenumber'], $merge_fields);
                }
            }
            return true;
        }
        return false;
    }

    public function edit_comment($data, $id, $playground = false) {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'contract_comments', ['content' => nl2br($data['content']), ]);
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * Get contract comments
     * @param  mixed $id contract id
     * @return array
     */
    public function get_comments($id, $playground = false) {
        $this->db->where('contract_id', $id);
        $this->db->order_by('dateadded', 'ASC');
        return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'contract_comments')->result_array();
    }

    /**
     * Get contract single comment
     * @param  mixed $id  comment id
     * @return object
     */
    public function get_comment($id, $playground = false) {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'contract_comments')->row();
    }

    /**
     * Remove contract comment
     * @param  mixed $id comment id
     * @return boolean
     */
    public function remove_comment($id, $playground = false) {
        $comment = $this->get_comment($id);
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'contract_comments');
        if ($this->db->affected_rows() > 0) {
            log_activity('Contract Comment Removed [Contract ID:' . $comment->contract_id . ', Comment Content: ' . $comment->content . ']');
            return true;
        }
        return false;
    }

    public function copy($id, $playground = false) {
        $contract = $this->get($id, [], true, $playground);
        $fields = $this->db->list_fields(db_prefix() . ($playground ? 'playground_' : '') . 'contracts');
        $newContactData = [];
        $contract->content = restore_merge_fields($contract->content);
        foreach ($fields as $field) {
            if (isset($contract->$field)) {
                $newContactData[$field] = $contract->$field;
            }
        }
        unset($newContactData['id']);
        $newContactData['trash'] = 0;
        $newContactData['isexpirynotified'] = 0;
        $newContactData['signed'] = 0;
        $newContactData['marked_as_signed'] = 0;
        $newContactData['signature'] = null;
        $newContactData = array_merge($newContactData, get_acceptance_info_array(true));
        if ($contract->dateend) {
            $dStart = new DateTime($contract->datestart);
            $dEnd = new DateTime($contract->dateend);
            $dDiff = $dStart->diff($dEnd);
            $newContactData['dateend'] = _d(date('Y-m-d', strtotime(date('Y-m-d', strtotime('+' . $dDiff->days . 'DAY')))));
        } else {
            $newContactData['dateend'] = '';
        }
        $newId = $this->add($newContactData);
        if ($newId) {
            $this->load->model('custom_fields_model');
            $custom_fields = $this->custom_fields_model->get_custom_fields('contracts', [], false, $playgroun);
            foreach ($custom_fields as $field) {
                $value = $this->custom_fields_model->get_custom_field_value($id, $field['id'], 'contracts', false, $playground);
                if ($value != '') {
                    $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'customfieldsvalues', ['relid' => $newId, 'fieldid' => $field['id'], 'fieldto' => 'contracts', 'value' => $value, ]);
                }
            }
        }
        return $newId;
    }

    /**
     * @param  integer ID
     * @return boolean
     * Delete contract, also attachment will be removed if any found
     */
    public function delete($id, $playground = false) {
        hooks()->do_action('before_contract_deleted', $id);
        $this->clear_signature($id);
        $contract = $this->get($id, [], false, $playground);
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'contracts');
        if ($this->db->affected_rows() > 0) {
            $this->db->where('contract_id', $id);
            $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'contract_comments');
            // Delete the custom field values
            $this->db->where('relid', $id);
            $this->db->where('fieldto', 'contracts');
            $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'customfieldsvalues');
            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'contract');
            $attachments = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'files')->result_array();
            foreach ($attachments as $attachment) {
                $this->delete_contract_attachment($attachment['id'], $playground);
            }
            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'contract');
            $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'notes');
            $this->db->where('contractid', $id);
            $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'contract_renewals');
            // Get related tasks
            $this->db->where('rel_type', 'contract');
            $this->db->where('rel_id', $id);
            $tasks = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'tasks')->result_array();
            $this->load->model('tasks_model');
            foreach ($tasks as $task) {
                $this->tasks_model->delete_task($task['id'], true, $playground);
            }
            $this->load->model('misc_model');
            $this->misc_model->delete_tracked_emails($id, 'contract', $playgroun);
            log_activity('Contract Deleted [' . $id . ']');
            hooks()->do_action('after_contract_deleted', $id);
            return true;
        }
        return false;
    }

    /**
     * Mark the contract as signed manually
     *
     * @param  int $id contract id
     *
     * @return boolean
     */
    public function mark_as_signed($id, $playground = false) {
        $contract = $this->get($id, [], true, $playground);
        if (!is_object($contract)) {
            return false;
        }
        $content = override_merge_fields($this->get_merge_fields($contract), $contract->content);
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'contracts', ['marked_as_signed' => 1, 'content' => $content, ]);
        return $this->db->affected_rows() > 0;
    }

    /**
     * Unmark the contract as signed manually
     *
     * @param  int $id contract id
     *
     * @return boolean
     */
    public function unmark_as_signed($id, $playground = false) {
        $contract = $this->get($id, [], true, $playground);
        if (!is_object($contract)) {
            return false;
        }
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'contracts', ['marked_as_signed' => 0, 'content' => restore_merge_fields($contract->content), ]);
        return $this->db->affected_rows() > 0;
    }

    /**
     * Function that send contract to customer
     * @param  mixed  $id        contract id
     * @param  boolean $attachpdf to attach pdf or not
     * @param  string  $cc        Email CC
     * @return boolean
     */
    public function send_contract_to_client($id, $attachpdf = true, $cc = '', $playground = false) {
        $contract = $this->get($id, [], false, $playground);
        if ($attachpdf) {
            set_mailing_constant();
            $pdf = contract_pdf($contract);
            $attach = $pdf->Output(slug_it($contract->subject) . '.pdf', 'S');
        }
        $sent_to = $this->input->post('sent_to');
        $sent = false;
        if (is_array($sent_to)) {
            $i = 0;
            foreach ($sent_to as $contact_id) {
                if ($contact_id != '') {
                    $contact = $this->clients_model->get_contact($contact_id, ['active' => 1], [], $playground);
                    // Send cc only for the first contact
                    if (!empty($cc) && $i > 0) {
                        $cc = '';
                    }
                    $template = mail_template('contract_send_to_customer', $contract, $contact, $cc);
                    if ($attachpdf) {
                        $template->add_attachment(['attachment' => $attach, 'filename' => slug_it($contract->subject) . '.pdf', 'type' => 'application/pdf', ]);
                    }
                    if ($template->send()) {
                        $sent = true;
                    }
                }
                $i++;
            }
        } else {
            return false;
        }
        if ($sent) {
            $contactsSent = [];
            if (!empty($contract->contacts_sent_to)) {
                $sentTo = json_decode($contract->contacts_sent_to, true);
                $cc = array_unique(array_merge(is_array($sentTo['cc']) ? $sentTo['cc'] : explode(',', $sentTo['cc']), explode(',', $cc)));
                $contactsSent = $sentTo['contact_ids'];
            }
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'contracts', ['last_sent_at' => date('c'), 'contacts_sent_to' => json_encode(['contact_ids' => array_unique(array_merge($contactsSent, $sent_to)), 'cc' => is_array($cc) ? join(',', $cc) : $cc, ]), ]);
            return true;
        }
        return false;
    }

    /**
     * Delete contract attachment
     * @param  mixed $attachment_id
     * @return boolean
     */
    public function delete_contract_attachment($attachment_id, $playground = false) {
        $deleted = false;
        $attachment = $this->get_contract_attachments($attachment_id, $playground);
        $this->load->model('misc_model');
        if ($attachment) {
            if (empty($attachment->external)) {
                unlink($this->misc_model->get_upload_path_by_type('contract', $playground) . $attachment->rel_id . '/' . $attachment->file_name);
            }
            $this->db->where('id', $attachment->id);
            $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'files');
            if ($this->db->affected_rows() > 0) {
                $deleted = true;
                log_activity('Contract Attachment Deleted [ContractID: ' . $attachment->rel_id . ']');
            }
            if (is_dir($this->misc_model->get_upload_path_by_type('contract', $playground) . $attachment->rel_id)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files($this->misc_model->get_upload_path_by_type('contract', $playground) . $attachment->rel_id);
                if (count($other_attachments) == 0) {
                    // okey only index.html so we can delete the folder also
                    delete_dir($this->misc_model->get_upload_path_by_type('contract', $playground) . $attachment->rel_id);
                }
            }
        }
        return $deleted;
    }

    /**
     * Renew contract
     * @param  mixed $data All $_POST data
     * @return mixed
     */
    public function renew($data, $playground = false) {
        $keepSignature = isset($data['renew_keep_signature']);
        if ($keepSignature) {
            unset($data['renew_keep_signature']);
        }
        $contract = $this->get($data['contractid'], [], false, $playground);
        if ($keepSignature) {
            $data['new_value'] = $contract->contract_value;
        }
        $data['new_start_date'] = to_sql_date($data['new_start_date']);
        $data['new_end_date'] = to_sql_date($data['new_end_date']);
        $data['date_renewed'] = date('Y-m-d H:i:s');
        $this->load->model('staff_model');
        $data['renewed_by'] = $this->staff_model->get_staff_full_name(get_staff_user_id());
        $data['renewed_by_staff_id'] = get_staff_user_id();
        if (!is_date($data['new_end_date'])) {
            unset($data['new_end_date']);
        }
        // get the original contract so we can check if is expiry notified on delete the expiry to revert
        $_contract = $this->get($data['contractid'], [], false, $playground);
        $data['is_on_old_expiry_notified'] = $_contract->isexpirynotified;
        $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'contract_renewals', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            $this->db->where('id', $data['contractid']);
            $_data = ['datestart' => $data['new_start_date'], 'contract_value' => $data['new_value'], 'isexpirynotified' => 0, ];
            if (isset($data['new_end_date'])) {
                $_data['dateend'] = $data['new_end_date'];
            }
            if (!$keepSignature) {
                $_data = array_merge($_data, get_acceptance_info_array(true));
                $_data['signed'] = 0;
                if (!empty($_contract->signature)) {
                    $this->load->model('misc_model');
                    unlink($this->misc_model->get_upload_path_by_type('contract', $playground) . $data['contractid'] . '/' . $_contract->signature);
                }
            }
            $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'contracts', $_data);
            if ($this->db->affected_rows() > 0) {
                log_activity('Contract Renewed [ID: ' . $data['contractid'] . ']');
                return true;
            }
            // delete the previous entry
            $this->db->where('id', $insert_id);
            $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'contract_renewals');
            return false;
        }
        return false;
    }

    /**
     * Delete contract renewal
     * @param  mixed $id         renewal id
     * @param  mixed $contractid contract id
     * @return boolean
     */
    public function delete_renewal($id, $contractid, $playground = false) {
        // check if this renewal is last so we can revert back the old values, if is not last we wont do anything
        $this->db->select('id')->from(db_prefix() . ($playground ? 'playground_' : '') . 'contract_renewals')->where('contractid', $contractid)->order_by('id', 'desc')->limit(1);
        $query = $this->db->get();
        $last_contract_renewal = $query->row()->id;
        $is_last = false;
        if ($last_contract_renewal == $id) {
            $is_last = true;
            $this->db->where('id', $id);
            $original_renewal = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'contract_renewals')->row();
        }
        $contract = $this->get($id, [], false, $playground);
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'contract_renewals');
        if ($this->db->affected_rows() > 0) {
            if (!is_null($contract->short_link)) {
                app_archive_short_link($contract->short_link);
            }
            if ($is_last == true) {
                $this->db->where('id', $contractid);
                $data = ['datestart' => $original_renewal->old_start_date, 'contract_value' => $original_renewal->old_value, 'isexpirynotified' => $original_renewal->is_on_old_expiry_notified, ];
                if ($original_renewal->old_end_date != '0000-00-00') {
                    $data['dateend'] = $original_renewal->old_end_date;
                }
                $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'contracts', $data);
            }
            log_activity('Contract Renewed [RenewalID: ' . $id . ', ContractID: ' . $contractid . ']');
            return true;
        }
        return false;
    }

    /**
     * Get the contracts about to expired in the given days
     *
     * @param  integer|null $staffId
     * @param  integer $days
     *
     * @return array
     */
    public function get_contracts_about_to_expire($staffId = null, $days = 7, $playground = false) {
        $diff1 = date('Y-m-d', strtotime('-' . $days . ' days'));
        $diff2 = date('Y-m-d', strtotime('+' . $days . ' days'));
        if ($staffId && !staff_can('view', 'contracts', $staffId)) {
            $this->db->where('addedfrom', $staffId);
        }
        $this->db->select('id,subject,client,datestart,dateend');
        $this->db->where('dateend IS NOT NULL');
        $this->db->where('trash', 0);
        $this->db->where('dateend >=', $diff1);
        $this->db->where('dateend <=', $diff2);
        return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'contracts')->result_array();
    }

    /**
     * Get contract renewals
     * @param  mixed $id contract id
     * @return array
     */
    public function get_contract_renewal_history($id, $playground = false) {
        $this->db->where('contractid', $id);
        $this->db->order_by('date_renewed', 'asc');
        return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'contract_renewals')->result_array();
    }

    /**
     * @param  integer ID (optional)
     * @return mixed
     * Get contract type object based on passed id if not passed id return array of all types
     */
    public function get_contract_types($id = '', $playground = false) {
        return $this->contract_types_model->get($id, $playground);
    }

    /**
     * @param  integer ID
     * @return mixed
     * Delete contract type from database, if used return array with key referenced
     */
    public function delete_contract_type($id, $playground = false) {
        return $this->contract_types_model->delete($id, $playground);
    }

    /**
     * Add new contract type
     * @param mixed $data All $_POST data
     */
    public function add_contract_type($data, $playground = false) {
        return $this->contract_types_model->add($data, $playground);
    }

    /**
     * Edit contract type
     * @param mixed $data All $_POST data
     * @param mixed $id Contract type id
     */
    public function update_contract_type($data, $id, $playground = false) {
        return $this->contract_types_model->update($data, $id, $playground);
    }

    /**
     * Get contract types data for chart
     * @return array
     */
    public function get_contracts_types_chart_data($playground = false) {
        return $this->contract_types_model->get_chart_data($playground);
    }

    /**
     * Get contract types values for chart
     * @return array
     */
    public function get_contracts_types_values_chart_data($playground = false) {
        return $this->contract_types_model->get_values_chart_data($playground);
    }

    /**
     * @param object $contract
     * @return array<string, string> i.e. ['{merge_field}' => 'value']
     */
    public function get_merge_fields(object $contract): array {
        $this->load->library('merge_fields/client_merge_fields');
        $this->load->library('merge_fields/contract_merge_fields');
        $this->load->library('merge_fields/other_merge_fields');
        
        $merge_fields = [];
        $merge_fields = array_merge($merge_fields, $this->contract_merge_fields->format($contract->id));
        $merge_fields = array_merge($merge_fields, $this->client_merge_fields->format($contract->client));
        $merge_fields = array_merge($merge_fields, $this->other_merge_fields->format());
        return $merge_fields;
    }
}