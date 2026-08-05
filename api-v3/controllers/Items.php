<?php

defined('BASEPATH') OR exit('No direct script access allowed');
// This can be removed if you use __autoload() in config.php OR use Modular Extensions

/** @noinspection PhpIncludeInspection */
require __DIR__ . '/REST_Controller.php';

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
class Items extends REST_Controller {
    function __construct() {
        // Construct the parent class
        parent::__construct();
    }

    /**
     * Normalize custom_fields payload for items to the structure expected by
     * custom_fields_model->handle_custom_fields_post(..., $is_cf_items=true).
     *
     * Expected (typical) shape:
     *   {
     *     "items": { "<custom_field_id>": "<value>", ... }
     *   }
     *
     * We also accept a "flat" shape:
     *   { "<custom_field_id>": "<value>", ... }
     * and wrap it into the expected "items" object.
     */
    private function normalize_items_custom_fields($custom_fields) {
        if (is_string($custom_fields)) {
            // Some clients send custom_fields as a JSON string
            $decoded = json_decode($custom_fields, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $custom_fields = $decoded;
            }
        }

        if (!is_array($custom_fields) || empty($custom_fields)) {
            return [];
        }

        // If already nested under "items", keep as-is.
        if (isset($custom_fields['items']) && is_array($custom_fields['items'])) {
            return $custom_fields;
        }

        // If it looks "flat" (field_id => scalar value), wrap as items.
        $looksFlat = true;
        foreach ($custom_fields as $k => $v) {
            if (is_array($v)) {
                $looksFlat = false;
                break;
            }
        }

        if ($looksFlat) {
            return ['items' => $custom_fields];
        }

        return $custom_fields;
    }

    /**
     * Get Item(s) information
     * Returns a single item if ID is provided, or all items with pagination if no ID
     * Supports pagination parameters: ?page=1&per_page=20
     * 
     * @api {get} api/items/:id Request Item information
     * @apiVersion 0.1.0
     * @apiName GetItem
     * @apiGroup Items
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     * @apiParam {Number} [id] Optional Item unique ID. If not provided, returns all items
     *
     * @apiError {Boolean} status Request status.
     * @apiError {String} message No data were found.
     *
     * @apiSuccess {Object} Item item information.
     * @apiSuccessExample Success-Response (Single Item):
     *     HTTP/1.1 200 OK
     *     {
     *	      "id": "1",
     *        "description": "JBL Soundbar",
     *        "long_description": "The JBL Cinema SB110 is a hassle-free soundbar",
     *        "rate": "100.00",
     *        "tax": 1,
     *        "tax2": 2,
     *        "unit": "pcs",
     *        "group_id": 0
     *     }
     *
     * @apiSuccessExample Success-Response (All Items with Pagination):
     *     HTTP/1.1 200 OK
     *     {
     *       "data": [{...}, {...}],
     *       "meta": {
     *         "current_page": 1,
     *         "per_page": 20,
     *         "total": 137,
     *         "last_page": 7
     *       }
     *     }
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status": false,
     *       "message": "No data were found"
     *     }
     */
    public function data_get($id = '') {
        // If the id parameter doesn't exist return all the items with pagination
        $data = $this->Api_model->get_table('invoice_items', $id);
        
        // Check if the data store contains results
        if ($data) {
            $data = $this->Api_model->get_api_custom_data($data, "items", $id);
            
            // Apply pagination if retrieving all items (no specific id)
            if (empty($id) && is_array($data)) {
                $data = $this->apply_pagination($data);
            }
            
            // Set the response and exit
            $this->response($data, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            
        } else {
            // Set the response and exit
            $this->response(['status' => FALSE, 'message' => 'No data were found'], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
            
        }
    }

    /**
     * Search Items
     * Supports pagination parameters: ?page=1&per_page=20
     * 
     * @api {get} api/items/search/:keysearch Search Items
     * @apiVersion 0.1.0
     * @apiName GetItemSearch
     * @apiGroup Items
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     * @apiParam {String} keysearch Search Keywords
     *
     * @apiSuccess {Object} Item  Item Information
     *
     * @apiSuccessExample Success-Response:
     *	HTTP/1.1 200 OK
     *	{
     *	  "data": [
     *	    {
     *	      "id": "1",
     *	      "description": "JBL Soundbar",
     *	      "rate": "100.00",
     *	      ...
     *	    }
     *	  ],
     *	  "meta": {
     *	    "current_page": 1,
     *	    "per_page": 20,
     *	    "total": 45,
     *	    "last_page": 3
     *	  }
     *	}
     *
     * @apiError {Boolean} status Request status
     * @apiError {String} message No data were found
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status": false,
     *       "message": "No data were found"
     *     }
     */
    public function data_search_get($key = '') {
        // Support both URL path parameter and query parameter for search term
        // This allows: /api/items/search/term OR /api/items/search?q=term
        // Query parameter is recommended for multi-word searches to avoid Apache mod_rewrite issues
        if (empty($key)) {
            $key = $this->get('q');
            if (empty($key)) {
                $key = $this->get('query');
            }
        }
        
        if (empty($key)) {
            $this->response(['status' => FALSE, 'message' => 'Search term is required. Use /api/items/search/{term} or /api/items/search?q={term}'], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }
        
        $data = $this->Api_model->search('invoice_items', $key);
        
        // Check if the data store contains results
        if ($data) {
            $data = $this->Api_model->get_api_custom_data($data, "items");
            
            // Apply pagination to search results
            if (is_array($data)) {
                $data = $this->apply_pagination($data);
            }
            
            // Set the response and exit
            $this->response($data, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            
        } else {
            // Set the response and exit
            $this->response(['status' => FALSE, 'message' => 'No data were found'], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
            
        }
    }

    /**
     * @api {post} api/items Create New Item
     * @apiVersion 0.1.0
     * @apiName PostItem
     * @apiGroup Items
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     * @apiParam {String} description Mandatory item description/name
     * @apiParam {Number} rate Mandatory item rate/price
     * @apiParam {String} [long_description] Optional long description
     * @apiParam {Number} [tax] Optional primary tax ID
     * @apiParam {Number} [tax2] Optional secondary tax ID
     * @apiParam {Number} [group_id] Optional item group ID (default: 0)
     * @apiParam {String} [unit] Optional unit of measurement
     *
     * @apiSuccess {Boolean} status Request status
     * @apiSuccess {String} message Item created successfully
     * @apiSuccess {Number} item_id The ID of the newly created item
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": true,
     *       "message": "Item created successfully",
     *       "item_id": 123
     *     }
     *
     * @apiError {Boolean} status Request status
     * @apiError {String} message Item creation failed
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status": false,
     *       "message": "Item creation failed"
     *     }
     */
    public function data_post() {
        // Form validation
        $this->form_validation->set_rules('description', 'Description', 'trim|required');
        $this->form_validation->set_rules('rate', 'Rate', 'trim|required|numeric');
        
        if ($this->form_validation->run() == FALSE) {
            // Form validation error
            $message = array(
                'status' => FALSE,
                'error' => $this->form_validation->error_array(),
                'message' => validation_errors()
            );
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        } else {
            // Prepare insert data
            $insert_data = [
                'description' => $this->input->post('description', TRUE),
                'rate' => $this->input->post('rate', TRUE),
                'long_description' => $this->Api_model->value($this->input->post('long_description', TRUE)),
                'tax' => $this->Api_model->value($this->input->post('tax', TRUE)),
                'tax2' => $this->Api_model->value($this->input->post('tax2', TRUE)),
                'group_id' => $this->Api_model->value($this->input->post('group_id', TRUE)) ?: 0,
                'unit' => $this->Api_model->value($this->input->post('unit', TRUE))
            ];

            // Optional custom fields (JSON or form-data)
            $custom_fields = $this->input->post('custom_fields', TRUE);
            if (!empty($custom_fields)) {
                $custom_fields = $this->normalize_items_custom_fields($custom_fields);
            } else {
                $custom_fields = [];
            }
            
            // Insert data into database
            $this->db->insert(db_prefix() . 'items', $insert_data);
            $item_id = $this->db->insert_id();
            
            if ($item_id > 0) {
                // Persist custom fields for items (Perfex entity: items_pr)
                if (!empty($custom_fields)) {
                    $this->load->model('custom_fields_model');
                    // $is_cf_items=true => store into fieldto=items_pr
                    $this->custom_fields_model->handle_custom_fields_post($item_id, $custom_fields, true, false);
                }
                // Success
                $message = array(
                    'status' => TRUE,
                    'message' => 'Item created successfully',
                    'item_id' => $item_id,
                    'record_id' => $item_id
                );
                $this->response($message, REST_Controller::HTTP_OK);
            } else {
                // Error
                $message = array(
                    'status' => FALSE,
                    'message' => 'Item creation failed'
                );
                $this->response($message, REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    /**
     * Update an existing Item
     * @api {put} api/items/:id Update an Item
     * @apiVersion 0.1.0
     * @apiName PutItem
     * @apiGroup Items
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     * @apiParam {Number} id Item unique ID (in URL)
     * @apiParam {String} [description] Optional item description/name
     * @apiParam {Number} [rate] Optional item rate/price
     * @apiParam {String} [long_description] Optional long description
     * @apiParam {Number} [tax] Optional primary tax ID
     * @apiParam {Number} [tax2] Optional secondary tax ID
     * @apiParam {Number} [group_id] Optional item group ID
     * @apiParam {String} [unit] Optional unit of measurement
     *
     * @apiSuccess {Boolean} status Request status
     * @apiSuccess {String} message Item updated successfully
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": true,
     *       "message": "Item updated successfully"
     *     }
     *
     * @apiError {Boolean} status Request status
     * @apiError {String} message Item update failed
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status": false,
     *       "message": "Item update failed"
     *     }
     */
    public function data_put($id = '') {
        // Parse PUT data if not already available
        if (empty($_POST) || !isset($_POST)) {
            $this->load->library('Parse_Input_Stream');
            $_POST = $this->parse_input_stream->parse_parameters();
            if (empty($_POST) || !isset($_POST)) {
                $message = array('status' => FALSE, 'message' => 'Data Not Acceptable OR Not Provided');
                $this->response($message, REST_Controller::HTTP_NOT_ACCEPTABLE);
            }
        }
        
        // Validate ID
        if (empty($id) || !is_numeric($id)) {
            $message = array('status' => FALSE, 'message' => 'Invalid Item ID');
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        }
        
        // Check if item exists
        $this->db->where('id', $id);
        $existing_item = $this->db->get(db_prefix() . 'items')->row();
        
        if (!$existing_item) {
            $message = array('status' => FALSE, 'message' => 'Item not found');
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        }
        
        // Prepare update data (only include provided fields)
        $update_data = array();

        // Optional custom fields (JSON or form-data)
        $custom_fields = $this->input->post('custom_fields', TRUE);
        if (!empty($custom_fields)) {
            $custom_fields = $this->normalize_items_custom_fields($custom_fields);
        } else {
            $custom_fields = [];
        }
        
        if ($this->input->post('description') !== NULL) {
            $update_data['description'] = $this->input->post('description', TRUE);
        }
        if ($this->input->post('rate') !== NULL) {
            $update_data['rate'] = $this->input->post('rate', TRUE);
        }
        if ($this->input->post('long_description') !== NULL) {
            $update_data['long_description'] = $this->input->post('long_description', TRUE);
        }
        if ($this->input->post('tax') !== NULL) {
            $update_data['tax'] = $this->input->post('tax', TRUE);
        }
        if ($this->input->post('tax2') !== NULL) {
            $update_data['tax2'] = $this->input->post('tax2', TRUE);
        }
        if ($this->input->post('group_id') !== NULL) {
            $update_data['group_id'] = $this->input->post('group_id', TRUE);
        }
        if ($this->input->post('unit') !== NULL) {
            $update_data['unit'] = $this->input->post('unit', TRUE);
        }
        
        if (empty($update_data) && empty($custom_fields)) {
            $message = array('status' => FALSE, 'message' => 'No data provided for update');
            $this->response($message, REST_Controller::HTTP_NOT_ACCEPTABLE);
        }
        
        $core_updated = false;

        // Update core fields only if provided.
        if (!empty($update_data)) {
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'items', $update_data);
            $core_updated = $this->db->affected_rows() >= 0; // keep legacy behavior
        }

        // Persist custom fields for items (Perfex entity: items_pr)
        if (!empty($custom_fields)) {
            $this->load->model('custom_fields_model');
            $this->custom_fields_model->handle_custom_fields_post($id, $custom_fields, true, false);
        }

        // Success if item exists and we applied either core updates or custom fields.
        if ($core_updated || !empty($custom_fields) || !empty($update_data)) {
            $message = array(
                'status' => TRUE,
                'message' => 'Item updated successfully'
            );
            $this->response($message, REST_Controller::HTTP_OK);
        }

        $message = array(
            'status' => FALSE,
            'message' => 'Item update failed'
        );
        $this->response($message, REST_Controller::HTTP_NOT_FOUND);
    }

    /**
     * @api {delete} api/delete/items/:id Delete an Item
     * @apiVersion 0.1.0
     * @apiName DeleteItem
     * @apiGroup Items
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     * @apiParam {Number} id Item unique ID
     *
     * @apiSuccess {Boolean} status Request status
     * @apiSuccess {String} message Item deleted successfully
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": true,
     *       "message": "Item deleted successfully"
     *     }
     *
     * @apiError {Boolean} status Request status
     * @apiError {String} message Item deletion failed
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status": false,
     *       "message": "Item deletion failed"
     *     }
     */
    public function data_delete($id = '') {
        // Sanitize ID
        $id = $this->security->xss_clean($id);
        
        if (empty($id) || !is_numeric($id)) {
            $message = array(
                'status' => FALSE,
                'message' => 'Invalid Item ID'
            );
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        }
        
        // Check if item exists
        $this->db->where('id', $id);
        $existing_item = $this->db->get(db_prefix() . 'items')->row();
        
        if (!$existing_item) {
            $message = array(
                'status' => FALSE,
                'message' => 'Item not found'
            );
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        }
        
        // Delete the item
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'items');
        
        if ($this->db->affected_rows() > 0) {
            // Success
            $message = array(
                'status' => TRUE,
                'message' => 'Item deleted successfully'
            );
            $this->response($message, REST_Controller::HTTP_OK);
        } else {
            // Error
            $message = array(
                'status' => FALSE,
                'message' => 'Item deletion failed'
            );
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        }
    }
}