<?php

use app\services\MergeTickets;

defined('BASEPATH') or exit('No direct script access allowed');

class Tickets_model extends App_Model {
    private $piping = false;

    public function __construct() {
        parent::__construct();
    }

    public function ticket_count($status = null, $playground = false) {
        $where = 'AND merged_ticket_id is NULL';
        if (!is_admin()) {
            $this->load->model('departments_model');
            $staff_deparments_ids = $this->departments_model->get_staff_departments(get_staff_user_id(), true, $playground);
            if (get_option('staff_access_only_assigned_departments') == 1) {
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
                    $where = 'AND department IN (SELECT departmentid FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'staff_departments WHERE departmentid IN (' . implode(',', $departments_ids) . ') AND staffid="' . get_staff_user_id() . '")';
                }
            }
        }
        $_where = '';
        if (!is_null($status)) {
            if ($where == '') {
                $_where = 'status=' . $status;
            } else {
                $_where = 'status=' . $status . ' ' . $where;
            }
        }
        return total_rows(db_prefix() . ($playground ? 'playground_' : '') . 'tickets', $_where);
    }

    public function insert_piped_ticket($data, $playground = false) {
        $data = hooks()->apply_filters('piped_ticket_data', $data);
        $this->piping = true;
        $attachments = $data['attachments'];
        $subject = $data['subject'];
        // Prevent insert ticket to database if mail delivery error happen
        // This will stop createing a thousand tickets
        $system_blocked_subjects = ['Mail delivery failed', 'failure notice', 'Returned mail: see transcript for details', 'Undelivered Mail Returned to Sender', ];
        $subject_blocked = false;
        foreach ($system_blocked_subjects as $sb) {
            if (strpos('x' . $subject, $sb) !== false) {
                $subject_blocked = true;
                break;
            }
        }
        if ($subject_blocked == true) {
            return;
        }
        $message = $data['body'];
        $name = $data['fromname'];
        $email = $data['email'];
        $to = $data['to'];
        $cc = $data['cc'] ?? [];
        $subject = $subject;
        $message = $message;
        $this->load->model('spam_filters_model');
        $mailstatus = $this->spam_filters_model->check($email, $subject, $message, 'tickets', $playground);
        // No spam found
        if (!$mailstatus) {
            $pos = strpos($subject, '[Ticket ID: ');
            if ($pos === false) {
            } else {
                $tid = substr($subject, $pos + 12);
                $tid = substr($tid, 0, strpos($tid, ']'));
                $this->db->where('ticketid', $tid);
                $data = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'tickets')->row();
                $tid = $data->ticketid;
            }
            $to = trim($to);
            $toemails = explode(',', $to);
            $department_id = false;
            $userid = false;
            foreach ($toemails as $toemail) {
                if (!$department_id) {
                    $this->db->where('email', trim($toemail));
                    $data = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'departments')->row();
                    if ($data) {
                        $department_id = $data->departmentid;
                        $to = $data->email;
                    }
                }
            }
            if (!$department_id) {
                $mailstatus = 'Department Not Found';
            } else {
                if ($to == $email) {
                    $mailstatus = 'Blocked Potential Email Loop';
                } else {
                    $message = trim($message);
                    $this->db->where('active', 1);
                    $this->db->where('email', $email);
                    $result = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'staff')->row();
                    if ($result) {
                        if ($tid) {
                            $data = [];
                            $data['message'] = $message;
                            $data['status'] = get_option('default_ticket_reply_status');
                            if (!$data['status']) {
                                $data['status'] = 3; // Answered
                                
                            }
                            if ($userid == false) {
                                $data['name'] = $name;
                                $data['email'] = $email;
                            }
                            if (count($cc) > 0) {
                                $data['cc'] = $cc;
                            }
                            $reply_id = $this->add_reply($data, $tid, $result->staffid, $attachments, $playground);
                            if ($reply_id) {
                                $mailstatus = 'Ticket Reply Imported Successfully';
                            }
                        } else {
                            $mailstatus = 'Ticket ID Not Found';
                        }
                    } else {
                        $this->db->where('email', $email);
                        $result = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'contacts')->row();
                        if ($result) {
                            $userid = $result->userid;
                            $contactid = $result->id;
                        }
                        if ($userid == false && get_option('email_piping_only_registered') == '1') {
                            $mailstatus = 'Unregistered Email Address';
                        } else {
                            $filterdate = date('Y-m-d H:i:s', strtotime('-15 minutes'));
                            $query = 'SELECT count(*) as total FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets WHERE date > "' . $filterdate . '" AND (email="' . $this->db->escape($email) . '"';
                            if ($userid) {
                                $query.= ' OR userid=' . (int)$userid;
                            }
                            $query.= ')';
                            $result = $this->db->query($query)->row();
                            if (10 < $result->total) {
                                $mailstatus = 'Exceeded Limit of 10 Tickets within 15 Minutes';
                            } else {
                                if (isset($tid)) {
                                    $data = [];
                                    $data['message'] = $message;
                                    $data['status'] = 1;
                                    if ($userid == false) {
                                        $data['name'] = $name;
                                        $data['email'] = $email;
                                    } else {
                                        $data['userid'] = $userid;
                                        $data['contactid'] = $contactid;
                                        $this->db->where('ticketid', $tid);
                                        $this->db->group_start();
                                        $this->db->where('userid', $userid);
                                        // Allow CC'ed user to reply to the ticket
                                        $this->db->or_like('cc', $email);
                                        $this->db->group_end();
                                        $t = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'tickets')->row();
                                        if (!$t) {
                                            $abuse = true;
                                        }
                                    }
                                    if (!isset($abuse)) {
                                        if (count($cc) > 0) {
                                            $data['cc'] = $cc;
                                        }
                                        $reply_id = $this->add_reply($data, $tid, null, $attachments, $playground);
                                        if ($reply_id) {
                                            // Dont change this line
                                            $mailstatus = 'Ticket Reply Imported Successfully';
                                        }
                                    } else {
                                        $mailstatus = 'Ticket ID Not Found For User';
                                    }
                                } else {
                                    if (get_option('email_piping_only_registered') == 1 && !$userid) {
                                        $mailstatus = 'Blocked Ticket Opening from Unregistered User';
                                    } else {
                                        if (get_option('email_piping_only_replies') == '1') {
                                            $mailstatus = 'Only Replies Allowed by Email';
                                        } else {
                                            $data = [];
                                            $data['department'] = $department_id;
                                            $data['subject'] = $subject;
                                            $data['message'] = $message;
                                            $data['contactid'] = $contactid;
                                            $data['priority'] = get_option('email_piping_default_priority');
                                            if ($userid == false) {
                                                $data['name'] = $name;
                                                $data['email'] = $email;
                                            } else {
                                                $data['userid'] = $userid;
                                            }
                                            $tid = $this->add($data, null, $attachments, $playground);
                                            if ($tid && count($cc) > 0) {
                                                // A customer opens a ticket by mail to "support@example".com, with one or many 'Cc'
                                                // Remember those 'Cc'.
                                                $this->db->where('ticketid', $tid);
                                                $this->db->update(($playground ? 'playground_' : '') . 'tickets', ['cc' => implode(',', $cc) ]);
                                            }
                                            // Dont change this line
                                            $mailstatus = 'Ticket Imported Successfully';
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        if ($mailstatus == '') {
            $mailstatus = 'Ticket Import Failed';
        }
        $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'tickets_pipe_log', ['date' => date('Y-m-d H:i:s'), 'email_to' => $to, 'name' => $name ? : 'Unknown', 'email' => $email ? : 'N/A', 'subject' => $subject ? : 'N/A', 'message' => $message, 'status' => $mailstatus, ]);
        return $mailstatus;
    }

    private function process_pipe_attachments($attachments, $ticket_id, $reply_id = '', $playground = false) {
        if (!empty($attachments)) {
            $ticket_attachments = [];
            $allowed_extensions = array_map(function ($ext) {
                return strtolower(trim($ext));
            }, explode(',', get_option('ticket_attachments_file_extensions')));
            $path = FCPATH . 'uploads/' . ($playground ? 'playground_' : '') . 'ticket_attachments' . '/' . $ticket_id . '/';
            foreach ($attachments as $attachment) {
                $filename = $attachment['filename'];
                $filenameparts = explode('.', $filename);
                $extension = end($filenameparts);
                $extension = strtolower($extension);
                if (in_array('.' . $extension, $allowed_extensions)) {
                    $filename = implode(array_slice($filenameparts, 0, 0 - 1));
                    $filename = trim(preg_replace('/[^a-zA-Z0-9-_ ]/', '', $filename));
                    if (!$filename) {
                        $filename = 'attachment';
                    }
                    if (!file_exists($path)) {
                        mkdir($path, 0755);
                        $fp = fopen($path . 'index.html', 'w');
                        fclose($fp);
                    }
                    $filename = unique_filename($path, $filename . '.' . $extension);
                    file_put_contents($path . $filename, $attachment['data']);
                    array_push($ticket_attachments, ['file_name' => $filename, 'filetype' => get_mime_by_extension($filename), ]);
                }
            }
            $this->insert_ticket_attachments_to_database($ticket_attachments, $ticket_id, $reply_id, $playground);
        }
    }

    public function get($id = '', $where = [], $playground = false) {
        $this->db->select('*,' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets.userid,' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets.name as from_name,' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets.email as ticket_email, ' . db_prefix() . ($playground ? 'playground_' : '') . 'departments.name as department_name, ' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets_priorities.name as priority_name, statuscolor, ' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets.admin, ' . db_prefix() . ($playground ? 'playground_' : '') . 'services.name as service_name, service, ' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets_status.name as status_name,' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets.ticketid, ' . db_prefix() . ($playground ? 'playground_' : '') . 'contacts.firstname as user_firstname, ' . db_prefix() . ($playground ? 'playground_' : '') . 'contacts.lastname as user_lastname,' . db_prefix() . ($playground ? 'playground_' : '') . 'staff.firstname as staff_firstname, ' . db_prefix() . ($playground ? 'playground_' : '') . 'staff.lastname as staff_lastname,lastreply,message,' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets.status,subject,department,priority,' . db_prefix() . ($playground ? 'playground_' : '') . 'contacts.email,adminread,clientread,date');
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'departments', db_prefix() . ($playground ? 'playground_' : '') . 'departments.departmentid = ' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets.department', 'left');
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'tickets_status', db_prefix() . ($playground ? 'playground_' : '') . 'tickets_status.ticketstatusid = ' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets.status', 'left');
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'services', db_prefix() . ($playground ? 'playground_' : '') . 'services.serviceid = ' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets.service', 'left');
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'clients', db_prefix() . ($playground ? 'playground_' : '') . 'clients.userid = ' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets.userid', 'left');
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'contacts', db_prefix() . ($playground ? 'playground_' : '') . 'contacts.id = ' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets.contactid', 'left');
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'staff', db_prefix() . ($playground ? 'playground_' : '') . 'staff.staffid = ' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets.admin', 'left');
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'tickets_priorities', db_prefix() . ($playground ? 'playground_' : '') . 'tickets_priorities.priorityid = ' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets.priority', 'left');
        $this->db->where($where);
        if (is_numeric($id)) {
            $this->db->where(db_prefix() . ($playground ? 'playground_' : '') . 'tickets.ticketid', $id);
            return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'tickets')->row();
        }
        $this->db->order_by('lastreply', 'asc');
        if (is_client_logged_in()) {
            $this->db->where(db_prefix() . ($playground ? 'playground_' : '') . 'tickets.merged_ticket_id IS NULL', null, false);
        }
        return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'tickets')->result_array();
    }

    /**
     * Get ticket by id and all data
     * @param  mixed  $id     ticket id
     * @param  mixed $userid Optional - Tickets from USER ID
     * @return object
     */
    public function get_ticket_by_id($id, $userid = '', $playground = false) {
        $this->db->select('*, ' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets.userid, ' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets.name as from_name, ' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets.email as ticket_email, ' . db_prefix() . ($playground ? 'playground_' : '') . 'departments.name as department_name, ' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets_priorities.name as priority_name, statuscolor, ' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets.admin, ' . db_prefix() . ($playground ? 'playground_' : '') . 'services.name as service_name, service, ' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets_status.name as status_name, ' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets.ticketid, ' . db_prefix() . ($playground ? 'playground_' : '') . 'contacts.firstname as user_firstname, ' . db_prefix() . ($playground ? 'playground_' : '') . 'contacts.lastname as user_lastname, ' . db_prefix() . ($playground ? 'playground_' : '') . 'staff.firstname as staff_firstname, ' . db_prefix() . ($playground ? 'playground_' : '') . 'staff.lastname as staff_lastname, lastreply, message, ' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets.status, subject, department, priority, ' . db_prefix() . ($playground ? 'playground_' : '') . 'contacts.email, adminread, clientread, date');
        $this->db->from(db_prefix() . ($playground ? 'playground_' : '') . 'tickets');
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'departments', db_prefix() . ($playground ? 'playground_' : '') . 'departments.departmentid = ' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets.department', 'left');
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'tickets_status', db_prefix() . ($playground ? 'playground_' : '') . 'tickets_status.ticketstatusid = ' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets.status', 'left');
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'services', db_prefix() . ($playground ? 'playground_' : '') . 'services.serviceid = ' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets.service', 'left');
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'clients', db_prefix() . ($playground ? 'playground_' : '') . 'clients.userid = ' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets.userid', 'left');
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'staff', db_prefix() . ($playground ? 'playground_' : '') . 'staff.staffid = ' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets.admin', 'left');
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'contacts', db_prefix() . ($playground ? 'playground_' : '') . 'contacts.id = ' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets.contactid', 'left');
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'tickets_priorities', db_prefix() . ($playground ? 'playground_' : '') . 'tickets_priorities.priorityid = ' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets.priority', 'left');
        if (strlen($id) === 32) {
            $this->db->where(db_prefix() . ($playground ? 'playground_' : '') . 'tickets.ticketkey', $id);
        } else {
            $this->db->where(db_prefix() . ($playground ? 'playground_' : '') . 'tickets.ticketid', $id);
        }
        if (is_numeric($userid)) {
            $this->db->where(db_prefix() . ($playground ? 'playground_' : '') . 'tickets.userid', $userid);
        }
        $ticket = $this->db->get()->row();
        if ($ticket) {
            $ticket->submitter = $ticket->contactid != 0 ? ($ticket->user_firstname . ' ' . $ticket->user_lastname) : $ticket->from_name;
            if (!($ticket->admin == null || $ticket->admin == 0)) {
                $ticket->opened_by = $ticket->staff_firstname . ' ' . $ticket->staff_lastname;
            }
            $ticket->attachments = $this->get_ticket_attachments($ticket->ticketid, '', $playground);
        }
        return $ticket;
    }

    /**
     * Insert ticket attachments to database
     * @param  array  $attachments array of attachment
     * @param  mixed  $ticketid
     * @param  boolean $replyid If is from reply
     */
    public function insert_ticket_attachments_to_database($attachments, $ticketid, $replyid = false, $playground = false) {
        foreach ($attachments as $attachment) {
            $attachment['ticketid'] = $ticketid;
            $attachment['dateadded'] = date('Y-m-d H:i:s');
            if ($replyid !== false && is_int($replyid)) {
                $attachment['replyid'] = $replyid;
            }
            $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'ticket_attachments', $attachment);
        }
    }

    /**
     * Get ticket attachments from database
     * @param  mixed $id      ticket id
     * @param  mixed $replyid Optional - reply id if is from from reply
     * @return array
     */
    public function get_ticket_attachments($id, $replyid = '', $playground = false) {
        $this->db->where('ticketid', $id);
        $this->db->where('replyid', is_numeric($replyid) ? $replyid : null);
        return $this->db->get(($playground ? 'playground_' : '') . 'ticket_attachments')->result_array();
    }

    /**
     * Add new reply to ticket
     * @param mixed $data  reply $_POST data
     * @param mixed $id    ticket id
     * @param boolean $admin staff id if is staff making reply
     */
    public function add_reply($data, $id, $admin = null, $pipe_attachments = false, $playground = false) {
        if (isset($data['assign_to_current_user'])) {
            $assigned = get_staff_user_id();
            unset($data['assign_to_current_user']);
        }
        $unsetters = ['note_description', 'department', 'priority', 'subject', 'assigned', 'project_id', 'service', 'status_top', 'attachments', 'DataTables_Table_0_length', 'DataTables_Table_1_length', 'custom_fields', ];
        foreach ($unsetters as $unset) {
            if (isset($data[$unset])) {
                unset($data[$unset]);
            }
        }
        if ($admin !== null) {
            $data['admin'] = $admin;
            $status = $data['status'];
        } else {
            $status = 1;
        }
        if (isset($data['status'])) {
            unset($data['status']);
        }
        $cc = '';
        if (isset($data['cc'])) {
            $cc = $data['cc'];
            unset($data['cc']);
        }
        // if ticket is merged
        $ticket = $this->get($id, [], $playground);
        $data['ticketid'] = ($ticket && $ticket->merged_ticket_id != null) ? $ticket->merged_ticket_id : $id;
        $data['date'] = date('Y-m-d H:i:s');
        $data['message'] = trim($data['message']);
        if ($this->piping == true) {
            // $data['message'] = preg_replace('/\v+/u', '<br>', $data['message']);
            
        }
        $is_html_stripped = $this->piping === true;
        // admin can have html
        if (!$is_html_stripped && $admin == null && hooks()->apply_filters('ticket_message_without_html_for_non_admin', true)) {
            $data['message'] = _strip_tags($data['message']);
            $data['message'] = nl2br_save_html($data['message']);
        }
        if (!isset($data['userid'])) {
            $data['userid'] = 0;
        }
        // $data['message'] = remove_emojis($data['message']);
        $data = hooks()->apply_filters('before_ticket_reply_add', $data, $id, $admin);
        $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'ticket_replies', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            /**
             * When a ticket is in status "In progress" and the customer reply to the ticket
             * it changes the status to "Open" which is not normal.
             *
             * The ticket should keep the status "In progress"
             */
            $this->db->select('status');
            $this->db->where('ticketid', $id);
            $old_ticket_status = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'tickets')->row()->status;
            $newStatus = hooks()->apply_filters('ticket_reply_status', ($old_ticket_status == 2 && $admin == null ? $old_ticket_status : $status), ['ticket_id' => $id, 'reply_id' => $insert_id, 'admin' => $admin, 'old_status' => $old_ticket_status]);
            if (isset($assigned)) {
                $this->db->where('ticketid', $id);
                $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'tickets', ['assigned' => $assigned, ]);
            }
            if ($pipe_attachments != false) {
                $this->process_pipe_attachments($pipe_attachments, $id, $insert_id, $playground);
            } else {
                $attachments = $this->handle_ticket_attachments($id, 'attachments', $playground);
                if ($attachments) {
                    $this->insert_ticket_attachments_to_database($attachments, $id, $insert_id, $playground);
                }
            }
            $_attachments = $this->get_ticket_attachments($id, $insert_id, $playground);
            log_activity('New Ticket Reply [ReplyID: ' . $insert_id . ']');
            $this->db->where('ticketid', $id);
            $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'tickets', ['lastreply' => date('Y-m-d H:i:s'), 'status' => $newStatus, 'adminread' => 0, 'clientread' => 0, ]);
            if ($old_ticket_status != $newStatus) {
                hooks()->do_action('after_ticket_status_changed', ['id' => $id, 'status' => $newStatus, ]);
            }
            $ticket = $this->get_ticket_by_id($id, '', $playground);
            $userid = $ticket->userid;
            $isContact = false;
            $this->load->model('clients_model');
            if ($ticket->userid != 0 && $ticket->contactid != 0) {
                $email = $clients_model->get_contact($ticket->contactid, $playground)->email;
                $isContact = true;
            } else {
                $email = $ticket->ticket_email;
            }
            if ($admin == null) {
                $this->load->model('departments_model');
                $notifiedUsers = [];
                $staff = $this->getStaffMembersForTicketNotification($ticket->department, $ticket->assigned, $playground);
                foreach ($staff as $staff_key => $member) {
                    send_mail_template('ticket_new_reply_to_staff', $ticket, $member, $_attachments);
                    if (get_option('receive_notification_on_new_ticket_replies') == 1) {
                        $notified = add_notification(['description' => 'not_new_ticket_reply', 'touserid' => $member['staffid'], 'fromcompany' => 1, 'fromuserid' => 0, 'link' => 'tickets/ticket/' . $id, 'additional_data' => serialize([$ticket->subject, ]), ]);
                        if ($notified) {
                            array_push($notifiedUsers, $member['staffid']);
                        }
                    }
                }
                pusher_trigger_notification($notifiedUsers);
            } else {
                $this->update_staff_replying($id, '', $playground);
                $total_staff_replies = total_rows(db_prefix() . ($playground ? 'playground_' : '') . 'ticket_replies', ['admin is NOT NULL', 'ticketid' => $ticket->ticketid]);
                if ($ticket->assigned == 0 && get_option('automatically_assign_ticket_to_first_staff_responding') == '1' && $total_staff_replies == 1) {
                    $this->db->where('ticketid', $id);
                    $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'tickets', ['assigned' => $admin]);
                }
                $sendEmail = true;
                if ($isContact && total_rows(db_prefix() . ($playground ? 'playground_' : '') . 'contacts', ['ticket_emails' => 1, 'id' => $ticket->contactid]) == 0) {
                    $sendEmail = false;
                }
                if ($sendEmail) {
                    send_mail_template('ticket_new_reply_to_customer', $ticket, $email, $_attachments, $cc);
                }
            }
            if ($cc) {
                // imported reply
                if (is_array($cc)) {
                    if ($ticket->cc) {
                        $currentCC = explode(',', $ticket->cc);
                        $cc = array_unique([$cc, $currentCC]);
                    }
                    $cc = implode(',', $cc);
                }
                $this->db->where('ticketid', $id);
                $this->db->update(($playground ? 'playground_' : '') . 'tickets', ['cc' => $cc]);
            }
            hooks()->do_action('after_ticket_reply_added', ['data' => $data, 'id' => $id, 'admin' => $admin, 'replyid' => $insert_id, ]);
            return $insert_id;
        }
        return false;
    }

    /**
     *  Delete ticket reply
     * @param   mixed $ticket_id    ticket id
     * @param   mixed $reply_id     reply id
     * @return  boolean
     */
    public function delete_ticket_reply($ticket_id, $reply_id, $playground = false) {
        hooks()->do_action('before_delete_ticket_reply', ['ticket_id' => $ticket_id, 'reply_id' => $reply_id]);
        $this->db->where('id', $reply_id);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'ticket_replies');
        if ($this->db->affected_rows() > 0) {
            // Get the reply attachments by passing the reply_id to get_ticket_attachments method
            $attachments = $this->get_ticket_attachments($ticket_id, $reply_id, $playground);
            if (count($attachments) > 0) {
                foreach ($attachments as $attachment) {
                    $this->delete_ticket_attachment($attachment['id'], $playground);
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Remove ticket attachment by id
     * @param  mixed $id attachment id
     * @return boolean
     */
    public function delete_ticket_attachment($id, $playground = false) {
        $deleted = false;
        $this->db->where('id', $id);
        $attachment = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'ticket_attachments')->row();
        $this->load->model('misc_model');
        if ($attachment) {
            if (unlink($this->misc_model->get_upload_path_by_type('ticket', $playground) . $attachment->ticketid . '/' . $attachment->file_name)) {
                $this->db->where('id', $attachment->id);
                $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'ticket_attachments');
                $deleted = true;
            }
            // Check if no attachments left, so we can delete the folder also
            $other_attachments = list_files($this->misc_model->get_upload_path_by_type('ticket', $playground) . $attachment->ticketid);
            if (count($other_attachments) == 0) {
                delete_dir($this->misc_model->get_upload_path_by_type('ticket', $playground) . $attachment->ticketid);
            }
        }
        return $deleted;
    }

    /**
     * Get ticket attachment by id
     * @param  mixed $id attachment id
     * @return mixed
     */
    public function get_ticket_attachment($id, $playground = false) {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'ticket_attachments')->row();
    }

    /**
     * This functions is used when staff open client ticket
     * @param  mixed $userid client id
     * @param  mixed $id     ticketid
     * @return array
     */
    public function get_user_other_tickets($userid, $id, $playground = false) {
        $this->db->select(db_prefix() . ($playground ? 'playground_' : '') . 'departments.name as department_name, ' . db_prefix() . ($playground ? 'playground_' : '') . 'services.name as service_name,' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets_status.name as status_name,' . db_prefix() . ($playground ? 'playground_' : '') . 'staff.firstname as staff_firstname, ' . db_prefix() . ($playground ? 'playground_' : '') . 'clients.lastname as staff_lastname,ticketid,subject,firstname,lastname,lastreply');
        $this->db->from(db_prefix() . ($playground ? 'playground_' : '') . 'tickets');
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'departments', db_prefix() . ($playground ? 'playground_' : '') . 'departments.departmentid = ' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets.department', 'left');
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'tickets_status', db_prefix() . ($playground ? 'playground_' : '') . 'tickets_status.ticketstatusid = ' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets.status', 'left');
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'services', db_prefix() . ($playground ? 'playground_' : '') . 'services.serviceid = ' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets.service', 'left');
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'clients', db_prefix() . ($playground ? 'playground_' : '') . 'clients.userid = ' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets.userid', 'left');
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'staff', db_prefix() . ($playground ? 'playground_' : '') . 'staff.staffid = ' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets.admin', 'left');
        $this->db->where(db_prefix() . ($playground ? 'playground_' : '') . 'tickets.userid', $userid);
        $this->db->where(db_prefix() . ($playground ? 'playground_' : '') . 'tickets.ticketid !=', $id);
        $tickets = $this->db->get()->result_array();
        $i = 0;
        foreach ($tickets as $ticket) {
            $tickets[$i]['submitter'] = $ticket['firstname'] . ' ' . $ticket['lastname'];
            unset($ticket['firstname']);
            unset($ticket['lastname']);
            $i++;
        }
        return $tickets;
    }

    /**
     * Get all ticket replies
     * @param  mixed  $id     ticketid
     * @param  mixed $userid specific client id
     * @return array
     */
    public function get_ticket_replies($id, $playground = false) {
        $ticket_replies_order = get_option('ticket_replies_order');
        // backward compatibility for the action hook
        $ticket_replies_order = hooks()->apply_filters('ticket_replies_order', $ticket_replies_order);
        $this->db->select(db_prefix() . ($playground ? 'playground_' : '') . 'ticket_replies.id,' . db_prefix() . ($playground ? 'playground_' : '') . 'ticket_replies.name as from_name,' . db_prefix() . ($playground ? 'playground_' : '') . 'ticket_replies.email as reply_email, ' . db_prefix() . ($playground ? 'playground_' : '') . 'ticket_replies.admin, ' . db_prefix() . ($playground ? 'playground_' : '') . 'ticket_replies.userid,' . db_prefix() . ($playground ? 'playground_' : '') . 'staff.firstname as staff_firstname, ' . db_prefix() . ($playground ? 'playground_' : '') . 'staff.lastname as staff_lastname,' . db_prefix() . ($playground ? 'playground_' : '') . 'contacts.firstname as user_firstname,' . db_prefix() . ($playground ? 'playground_' : '') . 'contacts.lastname as user_lastname,message,date,contactid');
        $this->db->from(db_prefix() . ($playground ? 'playground_' : '') . 'ticket_replies');
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'clients', db_prefix() . ($playground ? 'playground_' : '') . 'clients.userid = ' . db_prefix() . ($playground ? 'playground_' : '') . 'ticket_replies.userid', 'left');
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'staff', db_prefix() . ($playground ? 'playground_' : '') . 'staff.staffid = ' . db_prefix() . ($playground ? 'playground_' : '') . 'ticket_replies.admin', 'left');
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'contacts', db_prefix() . ($playground ? 'playground_' : '') . 'contacts.id = ' . db_prefix() . ($playground ? 'playground_' : '') . 'ticket_replies.contactid', 'left');
        $this->db->where('ticketid', $id);
        $this->db->order_by('date', $ticket_replies_order);
        $replies = $this->db->get()->result_array();
        $i = 0;
        foreach ($replies as $reply) {
            if ($reply['admin'] !== null || $reply['admin'] != 0) {
                // staff reply
                $replies[$i]['submitter'] = $reply['staff_firstname'] . ' ' . $reply['staff_lastname'];
            } else {
                if ($reply['contactid'] != 0) {
                    $replies[$i]['submitter'] = $reply['user_firstname'] . ' ' . $reply['user_lastname'];
                } else {
                    $replies[$i]['submitter'] = $reply['from_name'];
                }
            }
            unset($replies[$i]['staff_firstname']);
            unset($replies[$i]['staff_lastname']);
            unset($replies[$i]['user_firstname']);
            unset($replies[$i]['user_lastname']);
            $replies[$i]['attachments'] = $this->get_ticket_attachments($id, $reply['id'], $playground);
            $i++;
        }
        return $replies;
    }

    /**
     * Add new ticket to database
     * @param mixed $data  ticket $_POST data
     * @param mixed $admin If admin adding the ticket passed staff id
     */
    public function add($data, $admin = null, $pipe_attachments = false, $playground = false) {
        if ($admin !== null) {
            $data['admin'] = $admin;
            unset($data['ticket_client_search']);
        }
        if (isset($data['assigned']) && $data['assigned'] == '') {
            $data['assigned'] = 0;
        }
        if (isset($data['project_id']) && $data['project_id'] == '') {
            $data['project_id'] = 0;
        }
        if ($admin == null) {
            if (isset($data['email'])) {
                $data['userid'] = 0;
                $data['contactid'] = 0;
            } else {
                // Opened from customer portal otherwise is passed from pipe or admin area
                if (!isset($data['userid']) && !isset($data['contactid'])) {
                    $data['userid'] = get_client_user_id();
                    $data['contactid'] = get_contact_user_id();
                }
            }
            $data['status'] = 1;
        }
        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }
        // CC is only from admin area
        $cc = '';
        if (isset($data['cc'])) {
            $cc = $data['cc'];
            unset($data['cc']);
        }
        $data['date'] = date('Y-m-d H:i:s');
        $data['ticketkey'] = app_generate_hash();
        $data['status'] = 1;
        $data['message'] = trim($data['message']);
        $data['subject'] = trim($data['subject']);
        // if ($this->piping == true) {
        //     $data['message'] = preg_replace('/\v+/u', '<br>', $data['message']);
        // }
        $is_html_stripped = $this->piping === true;
        // Admin can have html
        if (!$is_html_stripped && $admin == null && hooks()->apply_filters('ticket_message_without_html_for_non_admin', true)) {
            $data['message'] = _strip_tags($data['message']);
            $data['subject'] = _strip_tags($data['subject']);
            $data['message'] = nl2br_save_html($data['message']);
        }
        if (!isset($data['userid'])) {
            $data['userid'] = 0;
        }
        if (isset($data['priority']) && $data['priority'] == '' || !isset($data['priority'])) {
            $data['priority'] = 0;
        }
        $tags = '';
        if (isset($data['tags'])) {
            $tags = $data['tags'];
            unset($data['tags']);
        }
        // $data['message'] = remove_emojis($data['message']);
        $data = hooks()->apply_filters('before_ticket_created', $data, $admin);
        $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'tickets', $data);
        $ticketid = $this->db->insert_id();
        if ($ticketid) {
            $this->load->model('misc_model');
            $this->misc_model->handle_tags_save($tags, $ticketid, 'ticket');
            if (isset($custom_fields)) {
                $this->load->model('custom_fields_model');
                $this->custom_fields_model->handle_custom_fields_post($ticketid, $custom_fields, $playground);
            }
            if (isset($data['assigned']) && $data['assigned'] != 0) {
                if ($data['assigned'] != get_staff_user_id()) {
                    $notified = add_notification(['description' => 'not_ticket_assigned_to_you', 'touserid' => $data['assigned'], 'fromcompany' => 1, 'fromuserid' => 0, 'link' => 'tickets/ticket/' . $ticketid, 'additional_data' => serialize([$data['subject'], ]), ]);
                    if ($notified) {
                        pusher_trigger_notification([$data['assigned']]);
                    }
                    send_mail_template('ticket_assigned_to_staff', get_staff($data['assigned'])->email, $data['assigned'], $ticketid, $data['userid'], $data['contactid']);
                }
            }
            if ($pipe_attachments != false) {
                $this->process_pipe_attachments($pipe_attachments, $ticketid, $playground);
            } else {
                $attachments = $this->handle_ticket_attachments($ticketid, 'attachments', $playground);
                if ($attachments) {
                    $this->insert_ticket_attachments_to_database($attachments, $ticketid, false, $playground);
                }
            }
            $_attachments = $this->get_ticket_attachments($ticketid, '', $playground);
            $isContact = false;
            if (isset($data['userid']) && $data['userid'] != false) {
                $this->load->model('clients_model');
                $email = $this->clients_model->get_contacts($data['contactid'], ['active' => 1], [], $playground)->email;
                $isContact = true;
            } else {
                $email = $data['email'];
            }
            $template = 'ticket_created_to_customer';
            if ($admin == null) {
                $template = 'ticket_autoresponse';
                $notifiedUsers = [];
                $staffToNotify = $this->getStaffMembersForTicketNotification($data['department'], $data['assigned'] ?? 0, $playground);
                foreach ($staffToNotify as $member) {
                    send_mail_template('ticket_created_to_staff', $ticketid, $data['userid'], $data['contactid'], $member, $_attachments);
                    if (get_option('receive_notification_on_new_ticket') == 1) {
                        $notified = add_notification(['description' => 'not_new_ticket_created', 'touserid' => $member['staffid'], 'fromcompany' => 1, 'fromuserid' => 0, 'link' => 'tickets/ticket/' . $ticketid, 'additional_data' => serialize([$data['subject'], ]), ]);
                        if ($notified) {
                            $notifiedUsers[] = $member['staffid'];
                        }
                    }
                }
                pusher_trigger_notification($notifiedUsers);
            } else {
                if ($cc) {
                    $this->db->where('ticketid', $ticketid);
                    $this->db->update(($playground ? 'playground_' : '') . 'tickets', ['cc' => is_array($cc) ? implode(',', $cc) : $cc]);
                }
            }
            $sendEmail = true;
            if ($isContact && total_rows(db_prefix() . ($playground ? 'playground_' : '') . 'contacts', ['ticket_emails' => 1, 'id' => $data['contactid']]) == 0) {
                $sendEmail = false;
            }
            if ($sendEmail) {
                $ticket = $this->get_ticket_by_id($ticketid, '', $playground);
                // $admin == null ? [] : $_attachments - Admin opened ticket from admin area add the attachments to the email
                send_mail_template($template, $ticket, $email, $admin == null ? [] : $_attachments, $cc);
            }
            hooks()->do_action('ticket_created', $ticketid);
            log_activity('New Ticket Created [ID: ' . $ticketid . ']');
            return $ticketid;
        }
        return false;
    }

    /**
     * Get latest 5 client tickets
     * @param  integer $limit  Optional limit tickets
     * @param  mixed $userid client id
     * @return array
     */
    public function get_client_latests_ticket($limit = 5, $userid = '', $playground = false) {
        $this->db->select(db_prefix() . ($playground ? 'playground_' : '') . 'tickets.userid, ticketstatusid, statuscolor, ' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets_status.name as status_name,' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets.ticketid, subject, date');
        $this->db->from(db_prefix() . ($playground ? 'playground_' : '') . 'tickets');
        $this->db->join(db_prefix() . ($playground ? 'playground_' : '') . 'tickets_status', db_prefix() . ($playground ? 'playground_' : '') . 'tickets_status.ticketstatusid = ' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets.status', 'left');
        if (is_numeric($userid)) {
            $this->db->where(db_prefix() . ($playground ? 'playground_' : '') . 'tickets.userid', $userid);
        } else {
            $this->db->where(db_prefix() . ($playground ? 'playground_' : '') . 'tickets.userid', get_client_user_id());
        }
        $this->db->limit($limit);
        $this->db->where(db_prefix() . ($playground ? 'playground_' : '') . 'tickets.merged_ticket_id IS NULL', null, false);
        return $this->db->get()->result_array();
    }

    /**
     * Delete ticket from database and all connections
     * @param  mixed $ticketid ticketid
     * @return boolean
     */
    public function delete($ticketid, $playground = false) {
        $affectedRows = 0;
        hooks()->do_action('before_ticket_deleted', $ticketid);
        // final delete ticket
        $this->db->where('ticketid', $ticketid);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'tickets');
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }
        $this->load->model('misc_model');
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
            $this->db->where('merged_ticket_id', $ticketid);
            $this->db->set('merged_ticket_id', null);
            $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'tickets');
            $this->db->where('ticketid', $ticketid);
            $attachments = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'ticket_attachments')->result_array();
            if (count($attachments) > 0) {
                if (is_dir($this->misc_model->get_upload_path_by_type('ticket', $playground) . $ticketid)) {
                    if (delete_dir($this->misc_model->get_upload_path_by_type('ticket', $playground) . $ticketid)) {
                        foreach ($attachments as $attachment) {
                            $this->db->where('id', $attachment['id']);
                            $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'ticket_attachments');
                            if ($this->db->affected_rows() > 0) {
                                $affectedRows++;
                            }
                        }
                    }
                }
            }
            $this->db->where('relid', $ticketid);
            $this->db->where('fieldto', 'tickets');
            $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'customfieldsvalues');
            // Delete replies
            $this->db->where('ticketid', $ticketid);
            $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'ticket_replies');
            $this->db->where('rel_id', $ticketid);
            $this->db->where('rel_type', 'ticket');
            $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'notes');
            $this->db->where('rel_id', $ticketid);
            $this->db->where('rel_type', 'ticket');
            $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'taggables');
            $this->db->where('rel_type', 'ticket');
            $this->db->where('rel_id', $ticketid);
            $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'reminders');
            // Get related tasks
            $this->db->where('rel_type', 'ticket');
            $this->db->where('rel_id', $ticketid);
            $tasks = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'tasks')->result_array();
            $this->load->model('tasks_model');
            foreach ($tasks as $task) {
                $this->tasks_model->delete_task($task['id'], true, $playground);
            }
        }
        if ($affectedRows > 0) {
            log_activity('Ticket Deleted [ID: ' . $ticketid . ']');
            hooks()->do_action('after_ticket_deleted', $ticketid);
            return true;
        }
        return false;
    }

    /**
     * Update ticket data / admin use
     * @param  mixed $data ticket $_POST data
     * @return boolean
     */
    public function update_single_ticket_settings($data, $playground = false) {
        $affectedRows = 0;
        $data = hooks()->apply_filters('before_ticket_settings_updated', $data);
        $ticketBeforeUpdate = $this->get_ticket_by_id($data['ticketid'], '', $playground);
        if (isset($data['merge_ticket_ids'])) {
            $tickets = explode(',', $data['merge_ticket_ids']);
            if ($this->merge($data['ticketid'], $ticketBeforeUpdate->status, $tickets)) {
                $affectedRows++;
            }
            unset($data['merge_ticket_ids']);
        }
        if (isset($data['custom_fields']) && count($data['custom_fields']) > 0) {
            $this->load->model('custom_fields_model');
            if ($this->custom_fields_model->handle_custom_fields_post($data['ticketid'], $data['custom_fields'])) {
                $affectedRows++;
            }
            unset($data['custom_fields']);
        }
        $tags = '';
        if (isset($data['tags'])) {
            $tags = $data['tags'];
            unset($data['tags']);
        }
        $this->load->model('misc_model');
        if ($this->misc_model->handle_tags_save($tags, $data['ticketid'], 'ticket')) {
            $affectedRows++;
        }
        if (isset($data['priority']) && $data['priority'] == '' || !isset($data['priority'])) {
            $data['priority'] = 0;
        }
        if ($data['assigned'] == '') {
            $data['assigned'] = 0;
        }
        if (isset($data['project_id']) && $data['project_id'] == '') {
            $data['project_id'] = 0;
        }
        if (isset($data['contactid']) && $data['contactid'] != '') {
            $data['name'] = null;
            $data['email'] = null;
        }
        $this->db->where('ticketid', $data['ticketid']);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'tickets', $data);
        if ($this->db->affected_rows() > 0) {
            hooks()->do_action('ticket_settings_updated', ['ticket_id' => $data['ticketid'], 'original_ticket' => $ticketBeforeUpdate, 'data' => $data, ]);
            $affectedRows++;
        }
        $sendAssignedEmail = false;
        $current_assigned = $ticketBeforeUpdate->assigned;
        if ($current_assigned != 0) {
            if ($current_assigned != $data['assigned']) {
                if ($data['assigned'] != 0 && $data['assigned'] != get_staff_user_id()) {
                    $sendAssignedEmail = true;
                    $notified = add_notification(['description' => 'not_ticket_reassigned_to_you', 'touserid' => $data['assigned'], 'fromcompany' => 1, 'fromuserid' => 0, 'link' => 'tickets/ticket/' . $data['ticketid'], 'additional_data' => serialize([$data['subject'], ]), ]);
                    if ($notified) {
                        pusher_trigger_notification([$data['assigned']]);
                    }
                }
            }
        } else {
            if ($data['assigned'] != 0 && $data['assigned'] != get_staff_user_id()) {
                $sendAssignedEmail = true;
                $notified = add_notification(['description' => 'not_ticket_assigned_to_you', 'touserid' => $data['assigned'], 'fromcompany' => 1, 'fromuserid' => 0, 'link' => 'tickets/ticket/' . $data['ticketid'], 'additional_data' => serialize([$data['subject'], ]), ]);
                if ($notified) {
                    pusher_trigger_notification([$data['assigned']]);
                }
            }
        }
        if ($sendAssignedEmail === true) {
            $this->db->where('staffid', $data['assigned']);
            $assignedEmail = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'staff')->row()->email;
            send_mail_template('ticket_assigned_to_staff', $assignedEmail, $data['assigned'], $data['ticketid'], $data['userid'], $data['contactid']);
        }
        if ($affectedRows > 0) {
            log_activity('Ticket Updated [ID: ' . $data['ticketid'] . ']');
            return true;
        }
        return false;
    }

    /**
     * C<ha></ha>nge ticket status
     * @param  mixed $id     ticketid
     * @param  mixed $status status id
     * @return array
     */
    public function change_ticket_status($id, $status, $playground = false) {
        $this->db->where('ticketid', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'tickets', ['status' => $status, ]);
        $alert = 'warning';
        $message = _l('ticket_status_changed_fail');
        if ($this->db->affected_rows() > 0) {
            $alert = 'success';
            $message = _l('ticket_status_changed_successfully');
            hooks()->do_action('after_ticket_status_changed', ['id' => $id, 'status' => $status, ]);
        }
        return ['alert' => $alert, 'message' => $message, ];
    }

    // Priorities
    
    /**
     * Get ticket priority by id
     * @param  mixed $id priority id
     * @return mixed     if id passed return object else array
     */
    public function get_priority($id = '', $playground = false) {
        if (is_numeric($id)) {
            $this->db->where('priorityid', $id);
            return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'tickets_priorities')->row();
        }
        return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'tickets_priorities')->result_array();
    }

    /**
     * Add new ticket priority
     * @param array $data ticket priority data
     */
    public function add_priority($data, $playground = false) {
        $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'tickets_priorities', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Ticket Priority Added [ID: ' . $insert_id . ', Name: ' . $data['name'] . ']');
        }
        return $insert_id;
    }

    /**
     * Update ticket priority
     * @param  array $data ticket priority $_POST data
     * @param  mixed $id   ticket priority id
     * @return boolean
     */
    public function update_priority($data, $id, $playground = false) {
        $this->db->where('priorityid', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'tickets_priorities', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Ticket Priority Updated [ID: ' . $id . ' Name: ' . $data['name'] . ']');
            return true;
        }
        return false;
    }

    /**
     * Delete ticket priorit
     * @param  mixed $id ticket priority id
     * @return mixed
     */
    public function delete_priority($id, $playground = false) {
        $current = $this->get($id, [], $playground);
        // Check if the priority id is used in tickets table
        if (is_reference_in_table('priority', db_prefix() . ($playground ? 'playground_' : '') . 'tickets', $id)) {
            return ['referenced' => true, ];
        }
        $this->db->where('priorityid', $id);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'tickets_priorities');
        if ($this->db->affected_rows() > 0) {
            if (get_option('email_piping_default_priority') == $id) {
                update_option('email_piping_default_priority', '');
            }
            log_activity('Ticket Priority Deleted [ID: ' . $id . ']');
            return true;
        }
        return false;
    }

    // Predefined replies
    
    /**
     * Get predefined reply  by id
     * @param  mixed $id predefined reply id
     * @return mixed if id passed return object else array
     */
    public function get_predefined_reply($id = '', $playground = false) {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'tickets_predefined_replies')->row();
        }
        return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'tickets_predefined_replies')->result_array();
    }

    /**
     * Add new predefined reply
     * @param array $data predefined reply $_POST data
     */
    public function add_predefined_reply($data, $playground = false) {
        $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'tickets_predefined_replies', $data);
        $insertid = $this->db->insert_id();
        log_activity('New Predefined Reply Added [ID: ' . $insertid . ', ' . $data['name'] . ']');
        return $insertid;
    }

    /**
     * Update predefined reply
     * @param  array $data predefined $_POST data
     * @param  mixed $id   predefined reply id
     * @return boolean
     */
    public function update_predefined_reply($data, $id, $playground = false) {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'tickets_predefined_replies', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Predefined Reply Updated [ID: ' . $id . ', ' . $data['name'] . ']');
            return true;
        }
        return false;
    }

    /**
     * Delete predefined reply
     * @param  mixed $id predefined reply id
     * @return boolean
     */
    public function delete_predefined_reply($id, $playground = false) {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'tickets_predefined_replies');
        if ($this->db->affected_rows() > 0) {
            log_activity('Predefined Reply Deleted [' . $id . ']');
            return true;
        }
        return false;
    }

    // Ticket statuses
    
    /**
     * Get ticket status by id
     * @param  mixed $id status id
     * @return mixed     if id passed return object else array
     */
    public function get_ticket_status($id = '', $playground = false) {
        if (is_numeric($id)) {
            $this->db->where('ticketstatusid', $id);
            return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'tickets_status')->row();
        }
        $this->db->order_by('statusorder', 'asc');
        return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'tickets_status')->result_array();
    }

    /**
     * Add new ticket status
     * @param array ticket status $_POST data
     * @return mixed
     */
    public function add_ticket_status($data, $playground = false) {
        $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'tickets_status', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Ticket Status Added [ID: ' . $insert_id . ', ' . $data['name'] . ']');
            return $insert_id;
        }
        return false;
    }

    /**
     * Update ticket status
     * @param  array $data ticket status $_POST data
     * @param  mixed $id   ticket status id
     * @return boolean
     */
    public function update_ticket_status($data, $id, $playground = false) {
        $this->db->where('ticketstatusid', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'tickets_status', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Ticket Status Updated [ID: ' . $id . ' Name: ' . $data['name'] . ']');
            return true;
        }
        return false;
    }

    /**
     * Delete ticket status
     * @param  mixed $id ticket status id
     * @return mixed
     */
    public function delete_ticket_status($id, $playground = false) {
        $current = $this->get_ticket_status($id, $playground);
        // Default statuses cant be deleted
        if ($current->isdefault == 1) {
            return ['default' => true, ];
            // Not default check if if used in table
            
        } else if (is_reference_in_table('status', db_prefix() . ($playground ? 'playground_' : '') . 'tickets', $id)) {
            return ['referenced' => true, ];
        }
        $this->db->where('ticketstatusid', $id);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'tickets_status');
        if ($this->db->affected_rows() > 0) {
            log_activity('Ticket Status Deleted [ID: ' . $id . ']');
            return true;
        }
        return false;
    }

    // Ticket services
    public function get_service($id = '', $playground = false) {
        if (is_numeric($id)) {
            $this->db->where('serviceid', $id);
            return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'services')->row();
        }
        $this->db->order_by('name', 'asc');
        return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'services')->result_array();
    }

    public function add_service($data, $playground = false) {
        $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'services', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Ticket Service Added [ID: ' . $insert_id . '.' . $data['name'] . ']');
        }
        return $insert_id;
    }

    public function update_service($data, $id, $playground = false) {
        $this->db->where('serviceid', $id);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'services', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Ticket Service Updated [ID: ' . $id . ' Name: ' . $data['name'] . ']');
            return true;
        }
        return false;
    }

    public function delete_service($id, $playground = false) {
        if (is_reference_in_table('service', db_prefix() . ($playground ? 'playground_' : '') . 'tickets', $id)) {
            return ['referenced' => true, ];
        }
        $this->db->where('serviceid', $id);
        $this->db->delete(db_prefix() . ($playground ? 'playground_' : '') . 'services');
        if ($this->db->affected_rows() > 0) {
            log_activity('Ticket Service Deleted [ID: ' . $id . ']');
            return true;
        }
        return false;
    }

    /**
     * @return array
     * Used in home dashboard page
     * Displays weekly ticket openings statistics (chart)
     */
    public function get_weekly_tickets_opening_statistics($playground = false) {
        $departments_ids = [];
        if (!is_admin()) {
            if (get_option('staff_access_only_assigned_departments') == 1) {
                $this->load->model('departments_model');
                $staff_deparments_ids = $this->departments_model->get_staff_departments(get_staff_user_id(), true, $playground);
                $departments_ids = [];
                if (count($staff_deparments_ids) == 0) {
                    $departments = $this->departments_model->get($playground);
                    foreach ($departments as $department) {
                        array_push($departments_ids, $department['departmentid']);
                    }
                } else {
                    $departments_ids = $staff_deparments_ids;
                }
            }
        }
        $chart = ['labels' => get_weekdays(), 'datasets' => [['label' => _l('home_weekend_ticket_opening_statistics'), 'backgroundColor' => 'rgba(197, 61, 169, 0.5)', 'borderColor' => '#c53da9', 'borderWidth' => 1, 'tension' => false, 'data' => [0, 0, 0, 0, 0, 0, 0, ], ], ], ];
        $monday = new DateTime(date('Y-m-d', strtotime('monday this week')));
        $sunday = new DateTime(date('Y-m-d', strtotime('sunday this week')));
        $thisWeekDays = get_weekdays_between_dates($monday, $sunday);
        $byDepartments = count($departments_ids) > 0;
        if (isset($thisWeekDays[1])) {
            $i = 0;
            foreach ($thisWeekDays[1] as $weekDate) {
                $this->db->like('DATE(date)', $weekDate, 'after');
                $this->db->where(db_prefix() . ($playground ? 'playground_' : '') . 'tickets.merged_ticket_id IS NULL', null, false);
                if ($byDepartments) {
                    $this->db->where('department IN (SELECT departmentid FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'staff_departments WHERE departmentid IN (' . implode(',', $departments_ids) . ') AND staffid="' . get_staff_user_id() . '")');
                }
                $chart['datasets'][0]['data'][$i] = $this->db->count_all_results(db_prefix() . ($playground ? 'playground_' : '') . 'tickets');
                $i++;
            }
        }
        return $chart;
    }

    public function get_tickets_assignes_disctinct($playground = false) {
        return $this->db->query('SELECT DISTINCT(assigned) as assigned FROM ' . db_prefix() . ($playground ? 'playground_' : '') . 'tickets WHERE assigned != 0 AND merged_ticket_id IS NULL')->result_array();
    }

    /**
     * Check for previous tickets opened by this email/contact and link to the contact
     * @param  string $email      email to check for
     * @param  mixed $contact_id the contact id to transfer the tickets
     * @return boolean
     */
    public function transfer_email_tickets_to_contact($email, $contact_id, $playground = false) {
        // Some users don't want to fill the email
        if (empty($email)) {
            return false;
        }
        $this->load->model('clients_model');
        $customer_id = $this->clients_model->get_user_id_by_contact_id($contact_id, $playground);
        $this->db->where('userid', 0)->where('contactid', 0)->where('admin IS NULL')->where('email', $email);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'tickets', ['email' => null, 'name' => null, 'userid' => $customer_id, 'contactid' => $contact_id, ]);
        $this->db->where('userid', 0)->where('contactid', 0)->where('admin IS NULL')->where('email', $email);
        $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'ticket_replies', ['email' => null, 'name' => null, 'userid' => $customer_id, 'contactid' => $contact_id, ]);
        return true;
    }

    /**
     * Check whether the given ticketid is already merged into another primary ticket
     *
     * @param  int  $id
     *
     * @return boolean
     */
    public function is_merged($id, $playground = false) {
        return total_rows(($playground ? 'playground_' : '') . 'tickets', "ticketid={$id} and merged_ticket_id IS NOT NULL") > 0;
    }

    /**
     * @param $primary_ticket_id
     * @param $status
     * @param  array  $ids
     *
     * @return bool
     */
    public function merge($primary_ticket_id, $status, array $ids, $playground = false) {
        if ($this->is_merged($primary_ticket_id, $playground)) {
            return false;
        }
        if (($index = array_search($primary_ticket_id, $ids)) !== false) {
            unset($ids[$index]);
        }
        if (count($ids) == 0) {
            return false;
        }
        return (new MergeTickets($primary_ticket_id, $ids))->markPrimaryTicketAs($status)->merge();
    }

    /**
     * @param array $tickets id's of tickets to check
     * @return array
     */
    public function get_already_merged_tickets($tickets, $playground = false) {
        if (count($tickets) === 0) {
            return [];
        }
        $alreadyMerged = [];
        foreach ($tickets as $ticketId) {
            if ($this->is_merged((int)$ticketId, $playground)) {
                $alreadyMerged[] = $ticketId;
            }
        }
        return $alreadyMerged;
    }

    /**
     * @param $primaryTicketId
     * @return array
     */
    public function get_merged_tickets_by_primary_id($primaryTicketId, $playground = false) {
        return $this->db->where('merged_ticket_id', $primaryTicketId)->get(db_prefix() . ($playground ? 'playground_' : '') . 'tickets')->result_array();
    }

    public function update_staff_replying($ticketId, $userId = '', $playground = false) {
        $ticket = $this->get($ticketId, [], $playground);
        if ($userId === '') {
            return $this->db->where('ticketid', $ticketId)->set('staff_id_replying', null)->update(db_prefix() . ($playground ? 'playground_' : '') . 'tickets');
        }
        if ($ticket->staff_id_replying !== $userId && !is_null($ticket->staff_id_replying)) {
            return false;
        }
        if ($ticket->staff_id_replying === $userId) {
            return true;
        }
        return $this->db->where('ticketid', $ticketId)->set('staff_id_replying', $userId)->update(db_prefix() . ($playground ? 'playground_' : '') . 'tickets');
    }

    public function get_staff_replying($ticketId, $playground = false) {
        $this->db->select('ticketid,staff_id_replying');
        $this->db->where('ticketid', $ticketId);
        return $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'tickets')->row();
    }

    private function getStaffMembersForTicketNotification($department, $assignedStaff = 0, $playground = false) {
        $this->load->model('departments_model');
        $this->load->model('staff_model');
        $staffToNotify = [];
        if ($assignedStaff != 0 && get_option('staff_related_ticket_notification_to_assignee_only') == 1) {
            $member = $this->staff_model->get($assignedStaff, ['active' => 1], $playground);
            if ($member) {
                $staffToNotify[] = (array)$member;
            }
        } else {
            $staff = $this->staff_model->get('', ['active' => 1], $playground);
            foreach ($staff as $member) {
                if (get_option('access_tickets_to_none_staff_members') == 0 && !is_staff_member($member['staffid'])) {
                    continue;
                }
                $staff_departments = $this->departments_model->get_staff_departments($member['staffid'], true, $playground);
                if (in_array($department, $staff_departments)) {
                    $staffToNotify[] = $member;
                }
            }
        }
        return $staffToNotify;
    }

    /**
     * Check for ticket attachment after inserting ticket to database
     * @param  mixed $ticketid
     * @return mixed           false if no attachment || array uploaded attachments
     */
    public function handle_ticket_attachments($ticketid, $index_name = 'attachments', $playground = false) {
        $hookData = hooks()->apply_filters('before_handle_ticket_attachment', [
            'ticket_id' => $ticketid,
            'index_name' => $index_name,
            'uploaded_files' => [], 
            'handled_externally' => false, // e.g. module upload to s3
            'files' => $_FILES
        ]);
        if ($hookData['handled_externally']) {
            return count($hookData['uploaded_files']) > 0 ? $hookData['uploaded_files'] : false;
        }
        $this->load->model('misc_model');
        $path = $this->misc_model->get_upload_path_by_type('ticket', $playground) . $ticketid . '/';
        $uploaded_files = [];
        if (isset($_FILES[$index_name])) {
            _file_attachments_index_fix($index_name);
            for ($i = 0; $i < count($_FILES[$index_name]['name']); $i++) {
                hooks()->do_action('before_upload_ticket_attachment', $ticketid);
                if ($i <= get_option('maximum_allowed_ticket_attachments')) {
                    // Get the temp file path
                    $tmpFilePath = $_FILES[$index_name]['tmp_name'][$i];
                    // Make sure we have a filepath
                    if (!empty($tmpFilePath) && $tmpFilePath != '') {
                        // Getting file extension
                        $extension = strtolower(pathinfo($_FILES[$index_name]['name'][$i], PATHINFO_EXTENSION));
                        $allowed_extensions = explode(',', get_option('ticket_attachments_file_extensions'));
                        $allowed_extensions = array_map('trim', $allowed_extensions);
                        // Check for all cases if this extension is allowed
                        if (!in_array('.' . $extension, $allowed_extensions)) {
                            continue;
                        }
                        _maybe_create_upload_path($path);
                        $filename = unique_filename($path, $_FILES[$index_name]['name'][$i]);
                        $newFilePath = $path . $filename;
                        // Upload the file into the temp dir
                        if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                            array_push($uploaded_files, ['file_name' => $filename, 'filetype' => $_FILES[$index_name]['type'][$i], ]);
                        }
                    }
                }
            }
        }
        if (count($uploaded_files) > 0) {
            return $uploaded_files;
        }
        return false;
    }
}
