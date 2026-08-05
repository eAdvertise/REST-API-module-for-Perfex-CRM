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
class Tasks extends REST_Controller {
    function __construct()
    {
        // Construct the parent class
        parent::__construct();
    }

    /**
     * @api {get} api/tasks/:id Request Task information
     * @apiName GetTask
     * @apiGroup Tasks
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     * @apiParam {Number} id Task unique ID.
     *
     * @apiSuccess {Object} Tasks information.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "id": "10",
     *         "name": "This is a task",
     *         "description": "",
     *         "priority": "2",
     *         "dateadded": "2019-02-25 12:26:37",
     *         "startdate": "2019-01-02 00:00:00",
     *         "duedate": "2019-01-04 00:00:00",
     *         "datefinished": null,
     *         "addedfrom": "9",
     *         "is_added_from_contact": "0",
     *         "status": "4",
     *         "recurring_type": null,
     *         "repeat_every": "0",
     *         "recurring": "0",
     *         "is_recurring_from": null,
     *         ...
     *     }
     *
     * @apiError {Boolean} status Request status.
     * @apiError {String} message No data were found.
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
        // If the id parameter doesn't exist return all tasks with pagination
        $data = $this->Api_model->get_table('tasks', $id);

        // Check if the data store contains results
        if ($data)
        {
            $data = $this->Api_model->get_api_custom_data($data, "tasks", $id);

            // Apply pagination if retrieving all tasks (no specific id)
            if (empty($id) && is_array($data)) {
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
     * @api {get} api/tasks/search/:keysearch Search Tasks Information
     * @apiName GetTaskSearch
     * @apiGroup Tasks
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     * @apiParam {String} keysearch Search Keywords.
     *
     * @apiSuccess {Object} Tasks information.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "id": "10",
     *         "name": "This is a task",
     *         "description": "",
     *         "priority": "2",
     *         "dateadded": "2019-02-25 12:26:37",
     *         "startdate": "2019-01-02 00:00:00",
     *         "duedate": "2019-01-04 00:00:00",
     *         "datefinished": null,
     *         "addedfrom": "9",
     *         "is_added_from_contact": "0",
     *         "status": "4",
     *         "recurring_type": null,
     *         "repeat_every": "0",
     *         "recurring": "0",
     *         "is_recurring_from": null,
     *         ...
     *     }
     *
     * @apiError {Boolean} status Request status.
     * @apiError {String} message No data were found.
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
        // This allows: /api/tasks/search/term OR /api/tasks/search?q=term
        // Query parameter is recommended for multi-word searches to avoid Apache mod_rewrite issues
        if (empty($key)) {
            $key = $this->get('q');
            if (empty($key)) {
                $key = $this->get('query');
            }
        }
        
        if (empty($key)) {
            $this->response(['status' => FALSE, 'message' => 'Search term is required. Use /api/tasks/search/{term} or /api/tasks/search?q={term}'], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }
        
        $data = $this->Api_model->search('tasks', $key);

        // Check if the data store contains
        if ($data)
        {
			usort($data, function($a, $b) {
				return $a['id'] - $b['id'];
			});
            $data = $this->Api_model->get_api_custom_data($data,"tasks");

            // Set the response and exit
            $this->response($data, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        } else {
            // Set the response and exit
            $this->response([
                'status' => FALSE,
                'message' => 'No data were found'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
    }

    /**
     * @api {post} api/tasks Add New Task
     * @apiName PostTask
     * @apiGroup Tasks
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     * @apiParam {String} name              Mandatory Task Name.
     * @apiParam {Date} startdate           Mandatory Task Start Date.
     * @apiParam {String} [is_public]       Optional Task public.
     * @apiParam {String} [billable]        Optional Task billable.
     * @apiParam {String} [hourly_rate]     Optional Task hourly rate.
     * @apiParam {String} [milestone]       Optional Task milestone.
     * @apiParam {Date} [duedate]           Optional Task deadline.
     * @apiParam {String} [priority]        Optional Task priority.
     * @apiParam {String} [repeat_every]    Optional Task repeat every.
     * @apiParam {Number} [repeat_every_custom]     Optional Task repeat every custom.
     * @apiParam {String} [repeat_type_custom]      Optional Task repeat type custom.
     * @apiParam {Number} [cycles]                  Optional cycles.
     * @apiParam {string="lead","customer","invoice", "project", "quotation", "contract", "annex", "ticket", "expense", "proposal"} rel_type Mandatory Task Related.
     * @apiParam {Number} rel_id            Optional Related ID.
     * @apiParam {String} [tags]            Optional Task tags.
     * @apiParam {String} [description]     Optional Task description.
     * @apiParam {Mixed} [assignees]        Optional Task assignees. Can be: array of staff IDs, comma-separated string "1,2,3", or JSON array "[1,2,3]".
     *
     *
     * @apiParamExample {Multipart Form} Request-Example:
     *     array (size=15)
     *     'is_public' => string 'on' (length=2)
     *     'billable' => string 'on' (length=2)
     *     'name' => string 'Task 12' (length=7)
     *     'hourly_rate' => string '0' (length=1)
     *     'milestone' => string '' (length=0)
     *     'startdate' => string '17/07/2019' (length=10)
     *     'duedate' => string '31/07/2019 11:07' (length=16)
     *     'priority' => string '2' (length=1)
     *     'repeat_every' => string '' (length=0)
     *     'repeat_every_custom' => string '1' (length=1)
     *     'repeat_type_custom' => string 'day' (length=3)
     *     'rel_type' => string 'customer' (length=8)
     *     'rel_id' => string '9' (length=1)
     *     'tags' => string '' (length=0)
     *     'description' => string '<span>Task Description</span>' (length=29)
     *
     *
     * @apiSuccess {String} status Request status.
     * @apiSuccess {String} message Task add successful.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": true,
     *       "message": "Task add successful."
     *     }
     *
     * @apiError {String} status Request status.
     * @apiError {String} message Task add fail.
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status": false,
     *       "message": "Task add fail."
     *     }
     * 
     */
    public function data_post()
    {
        \modules\api\core\Apiinit::the_da_vinci_code('api');
        
        // form validation
        $this->form_validation->set_rules('name', 'Task Name', 'trim|required|max_length[600]', array('is_unique' => 'This %s already exists please enter another Task Name'));
        $this->form_validation->set_rules('startdate', 'Task Start Date', 'trim|required', array('is_unique' => 'This %s already exists please enter another Task Start Date'));
        $this->form_validation->set_rules('is_public', 'Publicly available task', 'trim', array('is_unique' => 'Public state can be 1. Skip it completely to set it at non-public'));
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
                'name' => $this->input->post('name', TRUE),
                'startdate' => $this->input->post('startdate', TRUE),
                'is_public' => $this->input->post('is_public', TRUE),
                'billable' => $this->Api_model->value($this->input->post('billable', TRUE)),
                'hourly_rate' => $this->Api_model->value($this->input->post('hourly_rate', TRUE)),
                'milestone' => $this->Api_model->value($this->input->post('milestone', TRUE)),
                'duedate' => $this->Api_model->value($this->input->post('duedate', TRUE)),
                'priority' => $this->Api_model->value($this->input->post('priority', TRUE)),
                'repeat_every' => $this->Api_model->value($this->input->post('repeat_every', TRUE)),
                'repeat_every_custom' => $this->Api_model->value($this->input->post('repeat_every_custom', TRUE)),
                'repeat_type_custom' => $this->Api_model->value($this->input->post('repeat_type_custom', TRUE)),
                'cycles' => $this->Api_model->value($this->input->post('cycles', TRUE)),
                'rel_type' => $this->Api_model->value($this->input->post('rel_type', TRUE)),
                'rel_id' => $this->Api_model->value($this->input->post('rel_id', TRUE)),
                'tags' => $this->Api_model->value($this->input->post('tags', TRUE)),
                'description' => $this->Api_model->value($this->input->post('description', TRUE))
            ];
            
            // Handle assignees - can be array or comma-separated string
            $assignees_input = $this->input->post('assignees', TRUE);
            if (!empty($assignees_input)) {
                if (is_string($assignees_input)) {
                    // Handle comma-separated string or JSON string
                    if (strpos($assignees_input, ',') !== false) {
                        $insert_data['assignees'] = array_map('trim', explode(',', $assignees_input));
                    } elseif (json_decode($assignees_input)) {
                        $insert_data['assignees'] = json_decode($assignees_input, true);
                    } else {
                        $insert_data['assignees'] = [$assignees_input];
                    }
                } elseif (is_array($assignees_input)) {
                    $insert_data['assignees'] = $assignees_input;
                }
            }
               
            if (!empty($this->input->post('custom_fields', TRUE))) {
                $insert_data['custom_fields'] = $this->Api_model->value($this->input->post('custom_fields', TRUE));
            }
            // insert data
			$this->load->model('tasks_model');
			$output = $this->tasks_model->add($insert_data);
			if ($output > 0 && !empty($output)) {
				// success
				$this->handle_task_attachments_array($output);
				$message = array(
					'status' => TRUE,
					'message' => 'Task add successful.',
					'record_id' => $output  // επιστρέφουμε το ID του νέου task
				);
				$this->response($message, REST_Controller::HTTP_OK);
			}

            else {
                // error
                $message = array(
                    'status' => FALSE,
                    'message' => 'Task add failed.'
                );
                $this->response($message, REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    /**
     * @api {delete} api/delete/tasks/:id Delete a Task
     * @apiName DeleteTask
     * @apiGroup Tasks
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     * @apiParam {Number} id Task unique ID.
     *
     * @apiSuccess {String} status Request status.
     * @apiSuccess {String} message Task Delete Successful.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": true,
     *       "message": "Task Delete Successful."
     *     }
     *
     * @apiError {String} status Request status.
     * @apiError {String} message Task Delete Fail.
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status": false,
     *       "message": "Task Delete Fail."
     *     }
     */
    public function data_delete($id = '')
    {
        $id = $this->security->xss_clean($id);
        if (empty($id) && !is_numeric($id)) {
            $message = array(
                'status' => FALSE,
                'message' => 'Invalid Task ID'
            );
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        } else {
            // delete data
            $this->load->model('tasks_model');
            $output = $this->tasks_model->delete_task($id);
            if ($output === TRUE) {
                // success
                $message = array(
                    'status' => TRUE,
                    'message' => 'Task Delete Successful.'
                );
                $this->response($message, REST_Controller::HTTP_OK);
            } else {
                // error
                $message = array(
                    'status' => FALSE,
                    'message' => 'Task Delete Fail.'
                );
                $this->response($message, REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    /**
     * @api {put} api/tasks/:id Update a task
     * @apiName PutTask
     * @apiGroup Tasks
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     * @apiParam {String} [name]            Optional Task Name.
     * @apiParam {Date} [startdate]         Optional Task Start Date.
     * @apiParam {Number} [status]          Optional Task status (0-5).
     * @apiParam {String} [is_public]       Optional Task public.
     * @apiParam {String} [billable]        Optional Task billable.
     * @apiParam {String} [hourly_rate]     Optional Task hourly rate.
     * @apiParam {String} [milestone]       Optional Task milestone.
     * @apiParam {Date} [duedate]           Optional Task deadline.
     * @apiParam {String} [priority]        Optional Task priority.
     * @apiParam {String} [repeat_every]    Optional Task repeat every.
     * @apiParam {Number} [repeat_every_custom]     Optional Task repeat every custom.
     * @apiParam {String} [repeat_type_custom]      Optional Task repeat type custom.
     * @apiParam {Number} [cycles]                  Optional cycles.
     * @apiParam {string="lead","customer","invoice", "project", "quotation", "contract", "annex", "ticket", "expense", "proposal"} rel_type Optional Task Related.
     * @apiParam {Number} rel_id            Optional Related ID.
     * @apiParam {String} [tags]            Optional Task tags.
     * @apiParam {String} [description]     Optional Task description.
     * @apiParam {Mixed} [assignees]        Optional Task assignees. Can be: array of staff IDs, comma-separated string "1,2,3", or JSON array "[1,2,3]".
     *
     *
     * @apiParamExample {json} Request-Example:
     *  {
     *      "billable": "1", 
     *      "is_public": "1",
     *      "name": "Task 1",
     *      "hourly_rate": "0.00",
     *      "milestone": "0",
     *      "startdate": "27/08/2019",
     *      "duedate": null,
     *      "priority": "0",
     *      "repeat_every": "",
     *      "repeat_every_custom": "1",
     *      "repeat_type_custom": "day",
     *      "cycles": "0",
     *      "rel_type": "lead",
     *      "rel_id": "11",
     *      "tags": "",
     *      "description": ""
     *   }
     *
     * @apiSuccess {String} status Request status.
     * @apiSuccess {String} message Task Update Successful.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": true,
     *       "message": "Task Update Successful."
     *     }
     *
     * @apiError {String} status Request status.
     * @apiError {String} message Task Update Fail.
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status": false,
     *       "message": "Task Update Fail."
     *     }
     */
    public function data_put($id = '')
    {
        // Validate task ID
        if (empty($id) || !is_numeric($id)) {
            $message = array('status' => FALSE, 'message' => 'Invalid Task ID');
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        }
        
        // Handle different content types
        $content_type = $this->input->server('CONTENT_TYPE');
        
        if (strpos($content_type, 'application/json') !== false) {
            // JSON data - use REST_Controller's put() method
            $update_data = $this->put();
        } else {
            // Form data or other - parse the input stream
            $this->load->library('Parse_Input_Stream');
            $stream = new Parse_Input_Stream();
            $parsed = $stream->parse_parameters();
            
            // Extract parameters from nested structure
            $update_data = isset($parsed['parameters']) ? $parsed['parameters'] : $parsed;
            
            // If still empty, try regular post
            if (empty($update_data)) {
                $update_data = $this->input->post();
            }
        }
        
        // Validate we have data
        if (empty($update_data)) {
            $message = array('status' => FALSE, 'message' => 'No data provided for update');
            $this->response($message, REST_Controller::HTTP_NOT_ACCEPTABLE);
        }
        
        // Load model and get existing task data
        $this->load->model('tasks_model');
        $existing_task = $this->tasks_model->get($id);

        if (!$existing_task) {
            $message = array('status' => FALSE, 'message' => 'Task not found');
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        }

        // v3: ignore unknown payload fields instead of failing with SQL errors
        $update_data = $this->api_sanitize_update_payload('tasks', $update_data, ['custom_fields', 'assignees', 'followers', 'tags', 'checklist_items']);
        
        // Provide defaults for required fields if not present
        if (!isset($update_data['startdate'])) {
            $update_data['startdate'] = $existing_task->startdate;
        }
        if (!isset($update_data['duedate'])) {
            $update_data['duedate'] = $existing_task->duedate;
        }
        // Bug Fix 1: Don't copy repeat_every from DB (wrong format), set empty if not provided
        if (!isset($update_data['repeat_every'])) {
            $update_data['repeat_every'] = '';
        }
        // Bug Fix 3: Provide name from existing task if not in update data (required for logging)
        if (!isset($update_data['name'])) {
            $update_data['name'] = $existing_task->name;
        }
        
        // Handle file separately
        $update_file = isset($update_data['file']) ? $update_data['file'] : null;
        unset($update_data['file']);
        
        // Handle assignees separately (like in add method)
        $assignees = null;
        if (isset($update_data['assignees']) && !empty($update_data['assignees'])) {
            $assignees_input = $update_data['assignees'];
            if (is_string($assignees_input)) {
                // Handle comma-separated string or JSON string
                if (strpos($assignees_input, ',') !== false) {
                    $assignees = array_map('trim', explode(',', $assignees_input));
                } elseif (json_decode($assignees_input)) {
                    $assignees = json_decode($assignees_input, true);
                } else {
                    $assignees = [$assignees_input];
                }
            } else {
                $assignees = $assignees_input;
            }
            // Remove assignees from update_data as it's not a table column
            unset($update_data['assignees']);
        }

        // Update task data
        $output = $this->tasks_model->update($update_data, $id);
        
        // Track if we made any changes (for success determination)
        $assignees_updated = false;
        
        // Handle assignees if provided
        if ($assignees !== null) {
            // Remove existing assignees
            $this->db->where('taskid', $id);
            $this->db->delete(db_prefix() . 'task_assigned');
            
            // Add new assignees
            foreach ($assignees as $staff_id) {
                // Ensure staff_id is numeric (handle both string and int inputs)
                if (is_string($staff_id)) {
                    $staff_id = (int)trim($staff_id);
                } else {
                    $staff_id = (int)$staff_id;
                }
                
                // Validate staff exists before adding
                if ($staff_id > 0) {
                    $this->load->model('staff_model');
                    $staff = $this->staff_model->get($staff_id);
                    
                    if ($staff) {
                        $this->tasks_model->add_task_assignees([
                            'taskid' => $id,
                            'assignee' => $staff_id
                        ], false, false);
                        $assignees_updated = true;
                    }
                }
            }
        }
        
        // Bug Fix 3: Consider update successful if task or assignees were updated
        $update_successful = ($output > 0 && !empty($output)) || $assignees_updated;

        if ($update_successful) {
            // Bug Fix 2: Only delete and re-add attachments if new files are being uploaded
            if (!empty($_FILES['file']['name'])) {
                $attachments = $this->tasks_model->get_task_attachments($id);
                foreach ($attachments as $attachment) {
                    $this->tasks_model->remove_task_attachment($attachment['id']);
                }
                $this->handle_task_attachments_array($id);
            }
            
            $message = array(
                'status' => TRUE,
                'message' => 'Task Update Successful.'
            );
            $this->response($message, REST_Controller::HTTP_OK);
        } else {
            $message = array(
                'status' => FALSE,
                'message' => 'Task Update Fail.'
            );
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        }
    }

    /**
     * @api {get} api/tasks/:id/checklist Get Task Checklist Items
     * @apiName GetTaskChecklist
     * @apiGroup Tasks
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     * @apiParam {Number} id Task unique ID.
     *
     * @apiSuccess {Object[]} checklist Array of checklist items.
     * @apiSuccess {Number} checklist.id Checklist item ID.
     * @apiSuccess {Number} checklist.taskid Task ID.
     * @apiSuccess {String} checklist.description Checklist item description.
     * @apiSuccess {Number} checklist.finished 0=unchecked, 1=checked.
     * @apiSuccess {DateTime} checklist.dateadded Date when item was added.
     * @apiSuccess {Number} checklist.addedfrom Staff ID who added the item.
     * @apiSuccess {Number} checklist.finished_from Staff ID who checked the item.
     * @apiSuccess {Number} checklist.list_order Sort order.
     * @apiSuccess {Number} checklist.assigned Staff ID assigned to this item.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     [
     *       {
     *         "id": "1",
     *         "taskid": "10",
     *         "description": "Review documentation",
     *         "finished": "0",
     *         "dateadded": "2025-01-23 10:30:00",
     *         "addedfrom": "1",
     *         "finished_from": "0",
     *         "list_order": "1",
     *         "assigned": "2"
     *       }
     *     ]
     *
     * @apiError {Boolean} status Request status.
     * @apiError {String} message Task not found or no checklist items.
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status": false,
     *       "message": "Task not found or no checklist items"
     *     }
     */
    public function checklist_get($id = '')
    {
        if (empty($id) || !is_numeric($id)) {
            $this->response([
                'status' => FALSE,
                'message' => 'Invalid Task ID'
            ], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        // Verify task exists
        $task = $this->db->where('id', $id)->get(db_prefix().'tasks')->row();
        if (!$task) {
            $this->response([
                'status' => FALSE,
                'message' => 'Task not found'
            ], REST_Controller::HTTP_NOT_FOUND);
            return;
        }

        // Get checklist items
        $checklist = $this->db->where('taskid', $id)
                              ->order_by('list_order', 'ASC')
                              ->get(db_prefix().'task_checklist_items')
                              ->result_array();

        $this->response($checklist, REST_Controller::HTTP_OK);
    }

    /**
     * @api {post} api/tasks/:id/checklist Add Checklist Item to Task
     * @apiName PostTaskChecklist
     * @apiGroup Tasks
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     * @apiParam {Number} id Task unique ID.
     * @apiParam {String} description Mandatory Checklist item description.
     * @apiParam {Number} [assigned] Optional Staff ID to assign this item to.
     * @apiParam {Number} [list_order] Optional Sort order (defaults to highest + 1).
     *
     * @apiParamExample {Multipart Form} Request-Example:
     *     {
     *       "description": "Review and approve design",
     *       "assigned": "2",
     *       "list_order": "1"
     *     }
     *
     * @apiSuccess {Boolean} status Request status.
     * @apiSuccess {String} message Success message.
     * @apiSuccess {Number} checklist_item_id ID of created checklist item.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": true,
     *       "message": "Checklist item added successfully",
     *       "checklist_item_id": 15
     *     }
     *
     * @apiError {Boolean} status Request status.
     * @apiError {String} message Error message.
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *       "status": false,
     *       "message": "Description is required"
     *     }
     */
    public function checklist_post($id = '')
    {
        if (empty($id) || !is_numeric($id)) {
            $this->response([
                'status' => FALSE,
                'message' => 'Invalid Task ID'
            ], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        // Verify task exists
        $task = $this->db->where('id', $id)->get(db_prefix().'tasks')->row();
        if (!$task) {
            $this->response([
                'status' => FALSE,
                'message' => 'Task not found'
            ], REST_Controller::HTTP_NOT_FOUND);
            return;
        }

        // Validate description
        $description = $this->input->post('description', TRUE);
        if (empty($description)) {
            $this->response([
                'status' => FALSE,
                'message' => 'Description is required'
            ], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        // Get list_order (default to max + 1)
        $list_order = $this->input->post('list_order', TRUE);
        if (empty($list_order)) {
            $max_order = $this->db->select_max('list_order')
                                  ->where('taskid', $id)
                                  ->get(db_prefix().'task_checklist_items')
                                  ->row();
            $list_order = ($max_order && $max_order->list_order) ? $max_order->list_order + 1 : 1;
        }

        // Get current staff ID (from API authentication)
        $staff_id = get_staff_user_id();

        // Prepare data
        $data = [
            'taskid' => $id,
            'description' => $description,
            'finished' => 0,
            'dateadded' => date('Y-m-d H:i:s'),
            'addedfrom' => $staff_id,
            'finished_from' => 0,
            'list_order' => $list_order,
            'assigned' => $this->input->post('assigned', TRUE) ?: NULL
        ];

        // Insert
        $this->db->insert(db_prefix().'task_checklist_items', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            $this->response([
                'status' => TRUE,
                'message' => 'Checklist item added successfully',
                'checklist_item_id' => $insert_id
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'status' => FALSE,
                'message' => 'Failed to add checklist item'
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @api {put} api/tasks/:task_id/checklist/:item_id Update Checklist Item
     * @apiName PutTaskChecklist
     * @apiGroup Tasks
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     * @apiParam {Number} task_id Task unique ID.
     * @apiParam {Number} item_id Checklist item ID.
     * @apiParam {String} [description] Optional Updated description.
     * @apiParam {Number} [finished] Optional 0=unchecked, 1=checked.
     * @apiParam {Number} [assigned] Optional Staff ID to assign.
     * @apiParam {Number} [list_order] Optional Sort order.
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *       "finished": "1",
     *       "description": "Updated task description"
     *     }
     *
     * @apiSuccess {Boolean} status Request status.
     * @apiSuccess {String} message Success message.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": true,
     *       "message": "Checklist item updated successfully"
     *     }
     *
     * @apiError {Boolean} status Request status.
     * @apiError {String} message Error message.
     */
    public function checklist_put($task_id = '', $item_id = '')
    {
        // JSON data is now automatically parsed in REST_Controller
        if (empty($_POST) || !isset($_POST)) {
            $this->load->library('Parse_Input_Stream');
            $_POST = $this->parse_input_stream->parse_parameters();
            if (empty($_POST) || !isset($_POST)) {
                $this->response([
                    'status' => FALSE,
                    'message' => 'Data Not Acceptable OR Not Provided'
                ], REST_Controller::HTTP_NOT_ACCEPTABLE);
                return;
            }
        }

        if (empty($task_id) || !is_numeric($task_id) || empty($item_id) || !is_numeric($item_id)) {
            $this->response([
                'status' => FALSE,
                'message' => 'Invalid Task ID or Checklist Item ID'
            ], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        // Verify item exists and belongs to task
        $item = $this->db->where('id', $item_id)
                         ->where('taskid', $task_id)
                         ->get(db_prefix().'task_checklist_items')
                         ->row();
        
        if (!$item) {
            $this->response([
                'status' => FALSE,
                'message' => 'Checklist item not found'
            ], REST_Controller::HTTP_NOT_FOUND);
            return;
        }

        // Prepare update data
        $update_data = [];
        
        if ($this->input->post('description') !== NULL) {
            $update_data['description'] = $this->input->post('description', TRUE);
        }
        
        if ($this->input->post('finished') !== NULL) {
            $finished = $this->input->post('finished', TRUE);
            $update_data['finished'] = $finished;
            
            // If marking as finished, record who finished it
            if ($finished == 1 && $item->finished == 0) {
                $update_data['finished_from'] = get_staff_user_id();
            }
        }
        
        if ($this->input->post('assigned') !== NULL) {
            $update_data['assigned'] = $this->input->post('assigned', TRUE) ?: NULL;
        }
        
        if ($this->input->post('list_order') !== NULL) {
            $update_data['list_order'] = $this->input->post('list_order', TRUE);
        }

        if (empty($update_data)) {
            $this->response([
                'status' => FALSE,
                'message' => 'No data to update'
            ], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        // Update
        $this->db->where('id', $item_id);
        $this->db->update(db_prefix().'task_checklist_items', $update_data);

        if ($this->db->affected_rows() >= 0) {
            $this->response([
                'status' => TRUE,
                'message' => 'Checklist item updated successfully'
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'status' => FALSE,
                'message' => 'Failed to update checklist item'
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @api {delete} api/tasks/:task_id/checklist/:item_id Delete Checklist Item
     * @apiName DeleteTaskChecklist
     * @apiGroup Tasks
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     * @apiParam {Number} task_id Task unique ID.
     * @apiParam {Number} item_id Checklist item ID.
     *
     * @apiSuccess {Boolean} status Request status.
     * @apiSuccess {String} message Success message.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": true,
     *       "message": "Checklist item deleted successfully"
     *     }
     *
     * @apiError {Boolean} status Request status.
     * @apiError {String} message Error message.
     */
    public function checklist_delete($task_id = '', $item_id = '')
    {
        if (empty($task_id) || !is_numeric($task_id) || empty($item_id) || !is_numeric($item_id)) {
            $this->response([
                'status' => FALSE,
                'message' => 'Invalid Task ID or Checklist Item ID'
            ], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        // Verify item exists and belongs to task
        $item = $this->db->where('id', $item_id)
                         ->where('taskid', $task_id)
                         ->get(db_prefix().'task_checklist_items')
                         ->row();
        
        if (!$item) {
            $this->response([
                'status' => FALSE,
                'message' => 'Checklist item not found'
            ], REST_Controller::HTTP_NOT_FOUND);
            return;
        }

        // Delete
        $this->db->where('id', $item_id);
        $this->db->delete(db_prefix().'task_checklist_items');

        if ($this->db->affected_rows() > 0) {
            $this->response([
                'status' => TRUE,
                'message' => 'Checklist item deleted successfully'
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'status' => FALSE,
                'message' => 'Failed to delete checklist item'
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    function handle_task_attachments_array($task_id, $index_name = 'file') {
        $path = get_upload_path_by_type('task') . $task_id . '/';
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
                        $CI->load->model('tasks_model');
                        $data = [];
                        $data[] = ['file_name' => $filename, 'filetype' => $_FILES[$index_name]['type'][$i], ];
                        $CI->tasks_model->add_attachment_to_database($task_id, $data, false);
                    }
                }
            }
        }
        return true;
    }

    /**
     * @api {get} api/tasks/:id/comments Get Task Comments
     * @apiVersion 3.0.3
     * @apiName GetTaskComments
     * @apiGroup Tasks
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     * @apiParam {Number} id Task unique ID (URL segment).
     *
     * @apiDescription Returns the comment thread of a task, oldest first. Requires the
     * Tasks "get" permission.
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     [
     *       {
     *         "id": "42",
     *         "content": "Client approved the quote.",
     *         "taskid": "18",
     *         "staffid": "3",
     *         "contact_id": "0",
     *         "dateadded": "2026-07-31 10:14:52",
     *         "file_id": "0"
     *       }
     *     ]
     *
     * @apiErrorExample {json} Task-Not-Found:
     *     HTTP/1.1 404 Not Found
     *     { "status": false, "message": "Task not found" }
     */
    public function comments_get($id = '')
    {
        if (empty($id) || !is_numeric($id)) {
            $this->response(['status' => FALSE, 'message' => 'Invalid Task ID'], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }
        $task = $this->db->where('id', $id)->get(db_prefix() . 'tasks')->row();
        if (!$task) {
            $this->response(['status' => FALSE, 'message' => 'Task not found'], REST_Controller::HTTP_NOT_FOUND);
            return;
        }
        $comments = $this->db->where('taskid', $id)
                             ->order_by('dateadded', 'ASC')
                             ->get(db_prefix() . 'task_comments')
                             ->result_array();
        $this->response($comments, REST_Controller::HTTP_OK);
    }

    /**
     * @api {post} api/tasks/:id/comments Add a Task Comment
     * @apiVersion 3.0.3
     * @apiName PostTaskComment
     * @apiGroup Tasks
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     * @apiParam {Number} id Task unique ID (URL segment).
     * @apiParam {String} content Mandatory comment text.
     *
     * @apiDescription Adds a comment to a task, authored by the token's staff member.
     * Requires the Tasks "post" permission. Send an Idempotency-Key header to make
     * retries safe.
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     { "status": true, "message": "Comment added successfully", "comment_id": 42 }
     *
     * @apiErrorExample {json} Validation:
     *     HTTP/1.1 400 Bad Request
     *     { "status": false, "message": "Content is required" }
     */
    public function comments_post($id = '')
    {
        if (empty($id) || !is_numeric($id)) {
            $this->response(['status' => FALSE, 'message' => 'Invalid Task ID'], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }
        $task = $this->db->where('id', $id)->get(db_prefix() . 'tasks')->row();
        if (!$task) {
            $this->response(['status' => FALSE, 'message' => 'Task not found'], REST_Controller::HTTP_NOT_FOUND);
            return;
        }
        $content = $this->input->post('content', TRUE);
        if (empty($content)) {
            $this->response(['status' => FALSE, 'message' => 'Content is required'], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }
        $data = [
            'taskid'     => $id,
            'content'    => $content,
            'staffid'    => get_staff_user_id(),
            'contact_id' => 0,
            'dateadded'  => date('Y-m-d H:i:s'),
        ];
        $this->db->insert(db_prefix() . 'task_comments', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            $this->response(['status' => TRUE, 'message' => 'Comment added successfully', 'comment_id' => $insert_id], REST_Controller::HTTP_OK);
            return;
        }
        $this->response(['status' => FALSE, 'message' => 'Failed to add comment'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @api {put} api/tasks/:task_id/comments/:comment_id Update a Task Comment
     * @apiVersion 3.0.3
     * @apiName PutTaskComment
     * @apiGroup Tasks
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     * @apiParam {Number} task_id Task unique ID (URL segment).
     * @apiParam {Number} comment_id Comment unique ID (URL segment).
     * @apiParam {String} content Mandatory new comment text.
     *
     * @apiDescription Updates the text of an existing task comment. Requires the Tasks
     * "put" permission.
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     { "status": true, "message": "Comment updated successfully" }
     */
    public function comments_id_put($task_id = '', $comment_id = '')
    {
        // Real PUT requests do not populate $_POST; parse the raw body if needed.
        if (empty($_POST)) {
            $this->load->library('Parse_Input_Stream');
            $_POST = $this->parse_input_stream->parse_parameters();
        }
        if (empty($task_id) || !is_numeric($task_id) || empty($comment_id) || !is_numeric($comment_id)) {
            $this->response(['status' => FALSE, 'message' => 'Invalid task or comment ID'], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }
        $comment = $this->db->where('id', $comment_id)->where('taskid', $task_id)->get(db_prefix() . 'task_comments')->row();
        if (!$comment) {
            $this->response(['status' => FALSE, 'message' => 'Comment not found'], REST_Controller::HTTP_NOT_FOUND);
            return;
        }
        $content = $this->input->post('content', TRUE);
        if (empty($content)) {
            $this->response(['status' => FALSE, 'message' => 'Content is required'], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }
        $this->db->where('id', $comment_id)->update(db_prefix() . 'task_comments', ['content' => $content]);
        $this->response(['status' => TRUE, 'message' => 'Comment updated successfully'], REST_Controller::HTTP_OK);
    }

    /**
     * @api {delete} api/tasks/:task_id/comments/:comment_id Delete a Task Comment
     * @apiVersion 3.0.3
     * @apiName DeleteTaskComment
     * @apiGroup Tasks
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     * @apiParam {Number} task_id Task unique ID (URL segment).
     * @apiParam {Number} comment_id Comment unique ID (URL segment).
     *
     * @apiDescription Deletes a task comment. Requires the Tasks "delete" permission.
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     { "status": true, "message": "Comment deleted successfully" }
     */
    public function comments_id_delete($task_id = '', $comment_id = '')
    {
        if (empty($task_id) || !is_numeric($task_id) || empty($comment_id) || !is_numeric($comment_id)) {
            $this->response(['status' => FALSE, 'message' => 'Invalid task or comment ID'], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }
        $comment = $this->db->where('id', $comment_id)->where('taskid', $task_id)->get(db_prefix() . 'task_comments')->row();
        if (!$comment) {
            $this->response(['status' => FALSE, 'message' => 'Comment not found'], REST_Controller::HTTP_NOT_FOUND);
            return;
        }
        $this->db->where('id', $comment_id)->delete(db_prefix() . 'task_comments');
        $this->response(['status' => TRUE, 'message' => 'Comment deleted successfully'], REST_Controller::HTTP_OK);
    }
}