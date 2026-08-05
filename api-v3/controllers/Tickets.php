<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require __DIR__.'/REST_Controller.php';

/**
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array
 *
 * @package         CodeIgniter
 * @subpackage      Rest Server
 * @category        Controller
 * @author          Phil Sturgeon, Chris Kacerguis
 * @license         MIT
 * @link            https://github.com/chriskacerguis/codeigniter-restserver
 */
class Tickets extends REST_Controller {
    function __construct()
    {
        // Construct the parent class
        parent::__construct();
    }

    /**
     * Get Ticket(s) information
     * 
     * GET /api/tickets - Returns all tickets with pagination
     * GET /api/tickets/:id - Returns a single ticket by ID
     * 
     * Supports pagination parameters: ?page=1&per_page=20 (default: 20, max: 100, min: 1)
     * 
     * @api {get} api/tickets/:id Get Ticket(s)
     * @apiName GetTicket
     * @apiGroup Tickets
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     * @apiParam {Number} [id] Optional Ticket unique ID. If not provided, returns all tickets with pagination
     * @apiParam {Number} [page=1] Optional Page number for pagination (when ID is not provided)
     * @apiParam {Number} [per_page=20] Optional Number of items per page (min: 1, max: 100, when ID is not provided)
     *
     * @apiSuccess {Object} Ticket Ticket information (when ID provided)
     * @apiSuccess {Object[]} data Array of tickets (when ID not provided)
     * @apiSuccess {Object} meta Pagination metadata (when ID not provided)
     * @apiSuccess {Number} meta.current_page Current page number
     * @apiSuccess {Number} meta.per_page Items per page
     * @apiSuccess {Number} meta.total Total number of tickets
     * @apiSuccess {Number} meta.last_page Last page number
     *
     * @apiSuccessExample Success-Response (Single Ticket):
     *     HTTP/1.1 200 OK
     *     {
     *         "id": "7",
     *         "ticketid": "7",
     *         "adminreplying": "0",
     *         "userid": "0",
     *         "contactid": "0",
     *         "email": null,
     *         "name": "Trung bình",
     *         "department": "1",
     *         "priority": "2",
     *         "status": "1",
     *         "service": "1",
     *         "ticketkey": "8ef33d61bb0f26cd158d56cc18b71c02",
     *         "subject": "Ticket ER",
     *         "message": "Ticket ER",
     *         "admin": "5",
     *         "date": "2019-04-10 03:08:21",
     *         "project_id": "5",
     *         "lastreply": null,
     *         "clientread": "0",
     *         "adminread": "1",
     *         "assigned": "5",
     *         "line_manager": "8",
     *         "milestone": "27",
     *         ...
     *     }
     *
     * @apiSuccessExample Success-Response (All Tickets with Pagination):
     *     HTTP/1.1 200 OK
     *     {
     *       "data": [
     *         {
     *           "id": "7",
     *           "ticketid": "7",
     *           "subject": "Ticket ER",
     *           ...
     *         }
     *       ],
     *       "meta": {
     *         "current_page": 1,
     *         "per_page": 20,
     *         "total": 137,
     *         "last_page": 7
     *       }
     *     }
     *
     * @apiError {Boolean} status Request status.
     * @apiError {String} message The id of the Ticket was not found.
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status": false,
     *       "message": "No data were found"
     *     }
     */
	public function data_get($id = '')
	{
		// Get ticket(s) - if ID provided get single, otherwise get all
		$data = $this->Api_model->get_table('tickets', $id);
		
		// If single ticket returned as object, wrap in array
		if ($data && is_object($data)) {
			$data = [$data];
		}

		if (!empty($data)) {
			// Normalize ticket IDs
			foreach ($data as &$ticket) {
				// rename ticketid to id for consistency
				if (is_object($ticket)) {
					$ticket->id = $ticket->ticketid;
				} else if (is_array($ticket)) {
					$ticket['id'] = $ticket['ticketid'];
				}
			}

			// If getting all tickets (no specific ID), apply pagination
			if (empty($id) && is_array($data)) {
				$data = $this->apply_pagination($data);
			} else if (!empty($id)) {
				// Return single ticket without pagination wrapper
				$data = $data[0];
			}

			$this->response($data, REST_Controller::HTTP_OK);
		}
		else
		{
			// Set the response and exit with a not found message
			$this->response([
				'status' => FALSE,
				'message' => 'No data were found'
			], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
		}
	}

    /**
     * Search Tickets by keyword
     * Supports pagination parameters: ?page=1&per_page=20
     * 
     * @api {get} api/tickets/search/:keysearch Search Ticket Information
     * @apiName GetTicketSearch
     * @apiGroup Tickets
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     * @apiParam {String} keysearch Search keywords.
     *
     * @apiSuccess {Object} Ticket information.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "data": [
     *         {
     *           "ticketid": "7",
     *           "adminreplying": "0",
     *           "userid": "0",
     *           "contactid": "0",
     *           "email": null,
     *           "name": "Trung bình",
     *           "department": "1",
     *           "priority": "2",
     *           "status": "1",
     *           "service": "1",
     *           "ticketkey": "8ef33d61bb0f26cd158d56cc18b71c02",
     *           "subject": "Ticket ER",
     *           "message": "Ticket ER",
     *           "admin": "5",
     *           "date": "2019-04-10 03:08:21",
     *           "project_id": "5",
     *           "lastreply": null,
     *           "clientread": "0",
     *           "adminread": "1",
     *           "assigned": "5",
     *           "line_manager": "8",
     *           "milestone": "27",
     *           ...
     *         }
     *       ],
     *       "meta": {
     *         "current_page": 1,
     *         "per_page": 20,
     *         "total": 45,
     *         "last_page": 3
     *       }
     *     }
     * @apiError {Boolean} status Request status.
     * @apiError {String} message The id of the Ticket was not found.
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status": false,
     *       "message": "No data were found"
     *     }
     */
    public function data_search_get($key = '')
    {
        // Support both URL path parameter and query parameter for search term
        // This allows: /api/tickets/search/term OR /api/tickets/search?q=term
        // Query parameter is recommended for multi-word searches to avoid Apache mod_rewrite issues
        if (empty($key)) {
            $key = $this->get('q');
            if (empty($key)) {
                $key = $this->get('query');
            }
        }
        
        if (empty($key)) {
            $this->response(['status' => FALSE, 'message' => 'Search term is required. Use /api/tickets/search/{term} or /api/tickets/search?q={term}'], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }
        
        $data = $this->Api_model->search('ticket', $key);

        // Check if the data store contains results
        if ($data)
        {
            $data = $this->Api_model->get_api_custom_data($data,"tickets");

            // Apply pagination to search results
            if (is_array($data)) {
                $data = $this->apply_pagination($data);
            }

            // Set the response and exit
            $this->response($data, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
        else
        {
            // Set the response and exit
            $this->response([
                'status' => FALSE,
                'message' => 'No data were found'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
    }

    /**
     * @api {post} api/tickets Add New Ticket
     * @apiName PostTicket
     * @apiGroup Tickets
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     * @apiParam {String} subject                       Mandatory Ticket name .
     * @apiParam {String} department                    Mandatory Ticket Department.
     * @apiParam {String} contactid                     Mandatory Ticket Contact.
     * @apiParam {String} userid                        Mandatory Ticket user.
     * @apiParam {String} [project_id]                  Optional Ticket Project.
     * @apiParam {String} [message]                     Optional Ticket message.
     * @apiParam {String} [service]                     Optional Ticket Service.
     * @apiParam {String} [assigned]                    Optional Assign ticket.
     * @apiParam {String} [cc]                          Optional Ticket CC.
     * @apiParam {String} [priority]                    Optional Priority.
     * @apiParam {String} [tags]                        Optional ticket tags.
     *
     * @apiParamExample {Multipart Form} Request-Example:
     *    array (size=11)
     *     'subject' => string 'ticket name' (length=11)
     *     'contactid' => string '4' (length=1)
     *     'userid' => string '5' (length=1)
     *     'department' => string '2' (length=1)
     *     'cc' => string '' (length=0)
     *     'tags' => string '' (length=0)
     *     'assigned' => string '8' (length=1)
     *     'priority' => string '2' (length=1)
     *     'service' => string '2' (length=1)
     *     'project_id' => string '' (length=0)
     *     'message' => string '' (length=0)
     *
     *
     * @apiSuccess {Boolean} status Request status.
     * @apiSuccess {String} message Ticket add successful.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": true,
     *       "message": "Ticket add successful."
     *     }
     *
     * @apiError {Boolean} status Request status.
     * @apiError {String} message Ticket add fail.
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status": false,
     *       "message": "Ticket add fail."
     *     }
     * 
     */
    public function data_post()
    {
		error_reporting(0);
        // form validation
        $this->form_validation->set_rules('subject', 'Ticket Name', 'trim|required', array('is_unique' => 'This %s already exists please enter another Ticket Name'));
        $this->form_validation->set_rules('department', 'Department', 'trim|required', array('is_unique' => 'This %s already exists please enter another Ticket Department'));
        $this->form_validation->set_rules('contactid', 'Contact', 'trim|required', array('is_unique' => 'This %s already exists please enter another Ticket Contact'));
        if ($this->form_validation->run() == FALSE)
        {
            // form validation error
            $message = array(
                'status' => FALSE,
                'error' => $this->form_validation->error_array(),
                'message' => validation_errors() 
            );
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        }
        else
        {
            $insert_data = [
                'subject' => $this->input->post('subject', TRUE),
                'department' => $this->input->post('department', TRUE),
                'contactid' => $this->input->post('contactid', TRUE),
                'userid' => $this->input->post('userid', TRUE),

                'cc' => $this->Api_model->value($this->input->post('cc', TRUE)),
                'tags' => $this->Api_model->value($this->input->post('tags', TRUE)),
                'assigned' => $this->Api_model->value($this->input->post('assigned', TRUE)),
                'priority' => $this->Api_model->value($this->input->post('priority', TRUE)),
                'service' => $this->Api_model->value($this->input->post('service', TRUE)),
                'project_id' => $this->Api_model->value($this->input->post('project_id', TRUE)),
                'message' => $this->Api_model->value($this->input->post('message', TRUE))
            ];
            if (!empty($this->input->post('custom_fields', TRUE))) {
                $insert_data['custom_fields'] = $this->Api_model->value($this->input->post('custom_fields', TRUE));
            }
               
            // insert data
			$this->load->model('tickets_model');
			$output = $this->tickets_model->add($insert_data);
			if ($output > 0 && !empty($output)) {
				// success
				$this->handle_ticket_attachments_array($output);
				$message = array(
					'status' => TRUE,
					'message' => 'Ticket add successful.',
					'record_id' => $output  // επιστρέφουμε το ID του νέου ticket
				);
				$this->response($message, REST_Controller::HTTP_OK);
			}
			else {
                // error
                $message = array(
                    'status' => FALSE,
                    'message' => 'Ticket add fail.'
                );
                $this->response($message, REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    /**
     * @api {delete} api/delete/tickets/:id Delete a Ticket
     * @apiName DeleteTicket
     * @apiGroup Tickets
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     * @apiParam {Number} id Ticket unique ID.
     *
     * @apiSuccess {Boolean} status Request status.
     * @apiSuccess {String} message Ticket Delete Successful.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": true,
     *       "message": "Ticket Delete Successful."
     *     }
     *
     * @apiError {Boolean} status Request status.
     * @apiError {String} message Ticket Delete Fail.
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status": false,
     *       "message": "Ticket Delete Fail."
     *     }
     */
    public function data_delete($id = '')
    {
        $id = $this->security->xss_clean($id);
        if (empty($id) && !is_numeric($id))
        {
            $message = array(
                'status' => FALSE,
                'message' => 'Invalid Ticket ID'
            );
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        }
        else
        {
            // delete data
            $this->load->model('tickets_model');
            $output = $this->tickets_model->delete($id);
            if ($output === TRUE) {
                // success
                $message = array(
                    'status' => TRUE,
                    'message' => 'Ticket Delete Successful.'
                );
                $this->response($message, REST_Controller::HTTP_OK);
            } else {
                // error
                $message = array(
                    'status' => FALSE,
                    'message' => 'Ticket Delete Fail.'
                );
                $this->response($message, REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    /**
     * @api {put} api/tickets/:id Update a ticket
     * @apiName PutTicket
     * @apiGroup Tickets
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     * @apiParam {String} subject                       Mandatory Ticket name .
     * @apiParam {String} department                    Mandatory Ticket Department.
     * @apiParam {String} contactid                     Mandatory Ticket Contact.
     * @apiParam {String} userid                        Mandatory Ticket user.
     * @apiParam {String} priority                      Mandatory Priority.
     * @apiParam {String} [project_id]                  Optional Ticket Project.
     * @apiParam {String} [message]                     Optional Ticket message.
     * @apiParam {String} [service]                     Optional Ticket Service.
     * @apiParam {String} [assigned]                    Optional Assign ticket.
     * @apiParam {String} [tags]                        Optional ticket tags.
     *
     *
     * @apiParamExample {json} Request-Example:
     *  {
     *       "subject": "Ticket ER",
     *       "department": "1",
     *       "contactid": "0",
     *       "ticketid": "7",
     *       "userid": "0",
     *       "project_id": "5",
     *       "message": "Ticket ER",
     *       "service": "1",
     *       "assigned": "5",
     *       "priority": "2",
     *       "tags": ""
     *   }
     *
     * @apiSuccess {Boolean} status Request status.
     * @apiSuccess {String} message Ticket Update Successful.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": true,
     *       "message": "Ticket Update Successful."
     *     }
     *
     * @apiError {Boolean} status Request status.
     * @apiError {String} message Ticket Update Fail.
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status": false,
     *       "message": "Ticket Update Fail."
     *     }
     */
    public function data_put($id = '')
    {
        // JSON data is now automatically parsed in REST_Controller
        if (empty($_POST) || !isset($_POST)) {
            $this->load->library('Parse_Input_Stream');
            $_POST = $this->parse_input_stream->parse_parameters();
            $_FILES = $this->parse_input_stream->parse_files();
            if (empty($_POST) || !isset($_POST)) {
                $message = array('status' => FALSE, 'message' => 'Data Not Acceptable OR Not Provided');
                $this->response($message, REST_Controller::HTTP_NOT_ACCEPTABLE);
            }
        }
        if (empty($id) && !is_numeric($id)) {
            $message = array('status' => FALSE, 'message' => 'Invalid Ticket ID');
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        } else {
            $update_data = $this->input->post();
            $update_file = isset($update_data['file']) ? $update_data['file'] : null;
            unset($update_data['file']);
            // v3: ignore unknown payload fields instead of failing with SQL errors
            $update_data = $this->api_sanitize_update_payload('tickets', $update_data, ['custom_fields', 'tags', 'message', 'priority', 'assigned']);
            // update data
            $this->load->model('tickets_model');
            $update_data['ticketid'] = $id;
            $output = $this->tickets_model->update_single_ticket_settings($update_data);
            if (!empty($update_file) && count($update_file)) {
                if ($output <= 0 || empty($output)) {
                    $output = $id;
                }
            }

            if ($output > 0 && !empty($output)) {
                // success
                $attachments = $this->tickets_model->get_ticket_attachments($output);
                foreach ($attachments as $attachment) {
                    $this->tickets_model->delete_ticket_attachment($attachment['id']);
                }
                $this->handle_ticket_attachments_array($output);
                $message = array(
                    'status' => TRUE,
                    'message' => 'Ticket Update Successful.'
                );
                $this->response($message, REST_Controller::HTTP_OK);
            } else {
                // error
                $message = array(
                    'status' => FALSE,
                    'message' => 'Ticket Update Fail.'
                );
                $this->response($message, REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    /**
     * @api {post} api/tickets/reply/:id Add reply to a ticket
     * @apiName PostTicketReply
     * @apiGroup Tickets
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     * @apiParam {Number} id Ticket unique ID.
     * @apiParam {String} message Mandatory Reply message.
     * @apiParam {Number} [admin] Optional Staff ID (if provided, reply is from staff member).
     * @apiParam {Number} [status] Optional Ticket status after reply (default: 1 for customer, 3 for staff).
     * @apiParam {String} [cc] Optional CC email addresses.
     * @apiParam {File} [file] Optional File attachments.
     *
     * @apiParamExample {json} Request-Example:
     *  {
     *       "message": "Thank you for your inquiry. We are looking into this issue.",
     *       "admin": "5",
     *       "status": "3",
     *       "cc": ""
     *   }
     *
     * @apiSuccess {Boolean} status Request status.
     * @apiSuccess {String} message Ticket Reply Added Successful.
     * @apiSuccess {Number} reply_id Reply ID.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": true,
     *       "message": "Ticket Reply Added Successful.",
     *       "reply_id": 123
     *     }
     *
     * @apiError {Boolean} status Request status.
     * @apiError {String} message Ticket Reply Fail or Invalid Ticket ID.
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status": false,
     *       "message": "Ticket Reply Fail."
     *     }
     */
    public function data_reply_post($id = '')
    {
        // JSON data is now automatically parsed in REST_Controller
        if (empty($_POST) || !isset($_POST)) {
            $this->load->library('Parse_Input_Stream');
            $_POST = $this->parse_input_stream->parse_parameters();
            $_FILES = $this->parse_input_stream->parse_files();
            if (empty($_POST) || !isset($_POST)) {
                $message = array('status' => FALSE, 'message' => 'Data Not Acceptable OR Not Provided');
                $this->response($message, REST_Controller::HTTP_NOT_ACCEPTABLE);
            }
        }
        
        if (empty($id) || !is_numeric($id)) {
            $message = array('status' => FALSE, 'message' => 'Invalid Ticket ID');
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        }
        
        // Validate required message field
        $reply_message = $this->input->post('message', TRUE);
        if (empty($reply_message)) {
            $message = array(
                'status' => FALSE,
                'message' => 'Reply message is required'
            );
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        }
        
        // Load tickets model
        $this->load->model('tickets_model');
        
        // Check if ticket exists
        $ticket = $this->tickets_model->get($id);
        if (!$ticket) {
            $message = array(
                'status' => FALSE,
                'message' => 'Ticket not found'
            );
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        }
        
        // Prepare reply data
        $reply_data = [
            'message' => $reply_message
        ];
        
        // Get optional admin/staff ID (if provided, it's a staff reply)
        $admin_id = $this->Api_model->value($this->input->post('admin', TRUE));
        if (!empty($admin_id) && is_numeric($admin_id)) {
            $reply_data['admin'] = $admin_id;
            // Get status for staff reply (default: 3 = Answered)
            $reply_data['status'] = $this->Api_model->value($this->input->post('status', TRUE));
            if (empty($reply_data['status'])) {
                $reply_data['status'] = get_option('default_ticket_reply_status');
                if (!$reply_data['status']) {
                    $reply_data['status'] = 3; // Answered
                }
            }
        } else {
            // Customer reply - no admin ID
            $admin_id = null;
        }
        
        // Get optional CC
        $cc = $this->Api_model->value($this->input->post('cc', TRUE));
        if (!empty($cc)) {
            $reply_data['cc'] = $cc;
        }
        
        // Add reply
        $reply_id = $this->tickets_model->add_reply($reply_data, $id, $admin_id, false, false);
        
        if ($reply_id) {
            // Handle attachments if any
            if (isset($_FILES['file']) && !empty($_FILES['file'])) {
                $this->handle_ticket_reply_attachments($id, $reply_id);
            }
            
            $message = array(
                'status' => TRUE,
                'message' => 'Ticket Reply Added Successful.',
                'reply_id' => $reply_id
            );
            $this->response($message, REST_Controller::HTTP_OK);
        } else {
            $message = array(
                'status' => FALSE,
                'message' => 'Ticket Reply Fail.'
            );
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        }
    }

    function handle_ticket_reply_attachments($ticket_id, $reply_id, $index_name = 'file') {
        $path = get_upload_path_by_type('ticket') . $ticket_id . '/';
        $CI = & get_instance();
        if (isset($_FILES[$index_name]['name']) && ($_FILES[$index_name]['name'] != '' || is_array($_FILES[$index_name]['name']) && count($_FILES[$index_name]['name']) > 0)) {
            if (!is_array($_FILES[$index_name]['name'])) {
                $_FILES[$index_name]['name'] = [$_FILES[$index_name]['name']];
                $_FILES[$index_name]['type'] = [$_FILES[$index_name]['type']];
                $_FILES[$index_name]['tmp_name'] = [$_FILES[$index_name]['tmp_name']];
                $_FILES[$index_name]['error'] = [$_FILES[$index_name]['error']];
                $_FILES[$index_name]['size'] = [$_FILES[$index_name]['size']];
            }
            _file_attachments_index_fix($index_name);
            for ($i = 0; $i < count($_FILES[$index_name]['name']); $i++) {
                $tmpFilePath = $_FILES[$index_name]['tmp_name'][$i];
                if (!empty($tmpFilePath) && $tmpFilePath != '') {
                    if (_perfex_upload_error($_FILES[$index_name]['error'][$i]) || !_upload_extension_allowed($_FILES[$index_name]['name'][$i])) {
                        continue;
                    }
                    _maybe_create_upload_path($path);
                    $filename = unique_filename($path, $_FILES[$index_name]['name'][$i]);
                    $newFilePath = $path . $filename;
                    if (copy($tmpFilePath, $newFilePath)) {
                        unlink($tmpFilePath);
                        $CI = & get_instance();
                        $CI->load->model('tickets_model');
                        $data = [];
                        $data[] = ['file_name' => $filename, 'filetype' => $_FILES[$index_name]['type'][$i], ];
                        $CI->tickets_model->insert_ticket_attachments_to_database($data, $ticket_id, $reply_id);
                    }
                }
            }
        }
        return true;
    }

    function handle_ticket_attachments_array($ticket_id, $index_name = 'file') {
        $path = get_upload_path_by_type('ticket') . $ticket_id . '/';
        $CI = & get_instance();
        if (isset($_FILES[$index_name]['name']) && ($_FILES[$index_name]['name'] != '' || is_array($_FILES[$index_name]['name']) && count($_FILES[$index_name]['name']) > 0)) {
            if (!is_array($_FILES[$index_name]['name'])) {
                $_FILES[$index_name]['name'] = [$_FILES[$index_name]['name']];
                $_FILES[$index_name]['type'] = [$_FILES[$index_name]['type']];
                $_FILES[$index_name]['tmp_name'] = [$_FILES[$index_name]['tmp_name']];
                $_FILES[$index_name]['error'] = [$_FILES[$index_name]['error']];
                $_FILES[$index_name]['size'] = [$_FILES[$index_name]['size']];
            }
            _file_attachments_index_fix($index_name);
            for ($i = 0; $i < count($_FILES[$index_name]['name']); $i++) {
                // Get the temp file path
                $tmpFilePath = $_FILES[$index_name]['tmp_name'][$i];
                // Make sure we have a filepath
                if (!empty($tmpFilePath) && $tmpFilePath != '') {
                    if (_perfex_upload_error($_FILES[$index_name]['error'][$i]) || !_upload_extension_allowed($_FILES[$index_name]['name'][$i])) {
                        continue;
                    }
                    _maybe_create_upload_path($path);
                    $filename = unique_filename($path, $_FILES[$index_name]['name'][$i]);
                    $newFilePath = $path . $filename;
                    // Upload the file into the temp dir
                    if (copy($tmpFilePath, $newFilePath)) {
                        unlink($tmpFilePath);
                        $CI = & get_instance();
                        $CI->load->model('tickets_model');
                        $data = [];
                        $data[] = ['file_name' => $filename, 'filetype' => $_FILES[$index_name]['type'][$i], ];
                        $CI->tickets_model->insert_ticket_attachments_to_database($data, $ticket_id, false);
                    }
                }
            }
        }
        return true;
    }
}
