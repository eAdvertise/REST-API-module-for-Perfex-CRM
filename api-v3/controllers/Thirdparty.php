<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require __DIR__ . '/REST_Controller.php';

/**
 * Thirdparty Controller
 * 
 * Handles dynamic custom table operations for third-party modules
 * Allows CRUD operations on any custom database table
 */
class Thirdparty extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get all records from a custom table
     * 
     * @api {get} api/thirdparty/customtable/:table_name Get All Records from Custom Table
     * @apiVersion 0.3.0
     * @apiName GetCustomTableRecords
     * @apiGroup Thirdparty
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     * @apiParam {String} table_name Name of the custom database table (exact table name as it exists in the database)
     *
     * @apiDescription Retrieves all records from the specified custom table. The table name should be the exact name as it exists in the database (no prefix will be added).
     *
     * @apiSuccess {Array} Array of records with all columns and values
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     [
     *       {
     *         "id": "1",
     *         "shoenumber": "39",
     *         "winter": "40"
     *       },
     *       {
     *         "id": "2",
     *         "shoenumber": "41",
     *         "winter": "42"
     *       }
     *     ]
     *
     * @apiError {Boolean} status Request status
     * @apiError {String} message Error message
     *
     * @apiErrorExample Error-Response (Table Not Found):
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status": false,
     *       "message": "Table 'giorgos' does not exist"
     *     }
     */
    public function customtable_get($table_name = '')
    {
        if (empty($table_name)) {
            $this->response([
                'status' => FALSE,
                'message' => 'Table name is required'
            ], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        // Sanitize table name to prevent SQL injection
        $table_name = preg_replace('/[^a-zA-Z0-9_]/', '', $table_name);

        // Check if table exists
        if (!$this->_table_exists($table_name)) {
            $this->response([
                'status' => FALSE,
                'message' => "Table '{$table_name}' does not exist"
            ], REST_Controller::HTTP_NOT_FOUND);
            return;
        }

        try {
            // Use raw SQL query to avoid query builder state conflicts
            $query = $this->db->query("SELECT * FROM `{$table_name}`");
            $data = $query->result_array();

            $this->response($data, REST_Controller::HTTP_OK);
        } catch (Exception $e) {
            $this->response([
                'status' => FALSE,
                'message' => 'Error retrieving data: ' . $e->getMessage()
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get a specific record from a custom table by ID
     * 
     * @api {get} api/thirdparty/customtable/:table_name/:id Get Record from Custom Table by ID
     * @apiVersion 0.3.0
     * @apiName GetCustomTableRecordById
     * @apiGroup Thirdparty
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     * @apiParam {String} table_name Name of the custom database table (exact table name as it exists in the database)
     * @apiParam {Number} id Record ID
     *
     * @apiDescription Retrieves a specific record from the custom table by its ID.
     *
     * @apiSuccess {Object} Record with all columns and values
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "id": "1",
     *       "shoenumber": "39",
     *       "winter": "40"
     *     }
     *
     * @apiError {Boolean} status Request status
     * @apiError {String} message Error message
     *
     * @apiErrorExample Error-Response (Record Not Found):
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status": false,
     *       "message": "Record not found"
     *     }
     */
    public function customtable_id_get($table_name = '', $id = '')
    {
        if (empty($table_name) || empty($id)) {
            $this->response([
                'status' => FALSE,
                'message' => 'Table name and ID are required'
            ], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        // Sanitize table name
        $table_name = preg_replace('/[^a-zA-Z0-9_]/', '', $table_name);

        // Check if table exists
        if (!$this->_table_exists($table_name)) {
            $this->response([
                'status' => FALSE,
                'message' => "Table '{$table_name}' does not exist"
            ], REST_Controller::HTTP_NOT_FOUND);
            return;
        }

        try {
            // Use raw SQL query to avoid query builder state conflicts
            $id = (int)$id; // Sanitize ID
            $query = $this->db->query("SELECT * FROM `{$table_name}` WHERE id = ?", [$id]);
            $data = $query->row_array();

            if (empty($data)) {
                $this->response([
                    'status' => FALSE,
                    'message' => 'Record not found'
                ], REST_Controller::HTTP_NOT_FOUND);
                return;
            }

            $this->response($data, REST_Controller::HTTP_OK);
        } catch (Exception $e) {
            $this->response([
                'status' => FALSE,
                'message' => 'Error retrieving data: ' . $e->getMessage()
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Insert a new record into a custom table
     * 
     * @api {post} api/thirdparty/customtable/:table_name Insert Record into Custom Table
     * @apiVersion 0.3.0
     * @apiName PostCustomTableRecord
     * @apiGroup Thirdparty
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     * @apiHeader {String} Content-Type application/json
     *
     * @apiParam {String} table_name Name of the custom database table (exact table name as it exists in the database)
     *
     * @apiParamExample {json} Request-Body:
     *     {
     *       "shoenumber": "40",
     *       "winter": "39"
     *     }
     *
     * @apiDescription Inserts a new record into the specified custom table. All columns in the request body must exist in the table, otherwise an error will be returned.
     *
     * @apiSuccess {Boolean} status Request status
     * @apiSuccess {String} message Success message
     * @apiSuccess {Number} id ID of the newly created record
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 201 Created
     *     {
     *       "status": true,
     *       "message": "Record created successfully",
     *       "id": 5
     *     }
     *
     * @apiError {Boolean} status Request status
     * @apiError {String} message Error message
     *
     * @apiErrorExample Error-Response (Column Not Found):
     *     HTTP/1.1 400 Bad Request
     *     {
     *       "status": false,
     *       "message": "Column 'invalid_column' does not exist in table 'george'"
     *     }
     */
    public function customtable_post($table_name = '')
    {
        if (empty($table_name)) {
            $this->response([
                'status' => FALSE,
                'message' => 'Table name is required'
            ], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        // Sanitize table name
        $table_name = preg_replace('/[^a-zA-Z0-9_]/', '', $table_name);

        // Check if table exists
        if (!$this->_table_exists($table_name)) {
            $this->response([
                'status' => FALSE,
                'message' => "Table '{$table_name}' does not exist"
            ], REST_Controller::HTTP_NOT_FOUND);
            return;
        }

        // Get request data
        $data = $this->input->post();
        if (empty($data)) {
            // Try to get JSON data
            $raw_input = file_get_contents('php://input');
            $data = json_decode($raw_input, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->response([
                    'status' => FALSE,
                    'message' => 'Invalid JSON data'
                ], REST_Controller::HTTP_BAD_REQUEST);
                return;
            }
        }

        if (empty($data)) {
            $this->response([
                'status' => FALSE,
                'message' => 'No data provided'
            ], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        // Get table columns
        $columns = $this->_get_table_columns($table_name);
        if ($columns === false) {
            $this->response([
                'status' => FALSE,
                'message' => 'Error retrieving table structure'
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            return;
        }

        // Validate all provided columns exist
        $invalid_columns = [];
        foreach ($data as $column => $value) {
            if (!in_array($column, $columns)) {
                $invalid_columns[] = $column;
            }
        }

        if (!empty($invalid_columns)) {
            $this->response([
                'status' => FALSE,
                'message' => "Column(s) '" . implode("', '", $invalid_columns) . "' do not exist in table '{$table_name}'"
            ], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        try {
            // Build INSERT query manually to avoid query builder state conflicts
            $columns = array_keys($data);
            $values = array_values($data);
            $placeholders = array_fill(0, count($values), '?');
            
            $columns_str = '`' . implode('`, `', $columns) . '`';
            $placeholders_str = implode(', ', $placeholders);
            
            $sql = "INSERT INTO `{$table_name}` ({$columns_str}) VALUES ({$placeholders_str})";
            $this->db->query($sql, $values);
            $insert_id = $this->db->insert_id();

            $this->response([
                'status' => TRUE,
                'message' => 'Record created successfully',
                'id' => $insert_id
            ], REST_Controller::HTTP_CREATED);
        } catch (Exception $e) {
            $this->response([
                'status' => FALSE,
                'message' => 'Error creating record: ' . $e->getMessage()
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update a record in a custom table
     * 
     * @api {put} api/thirdparty/customtable/:table_name/:id Update Record in Custom Table
     * @apiVersion 0.3.0
     * @apiName PutCustomTableRecord
     * @apiGroup Thirdparty
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     * @apiHeader {String} Content-Type application/json
     *
     * @apiParam {String} table_name Name of the custom database table (exact table name as it exists in the database)
     * @apiParam {Number} id Record ID
     *
     * @apiParamExample {json} Request-Body:
     *     {
     *       "shoenumber": "40"
     *     }
     *
     * @apiDescription Updates an existing record in the specified custom table. All columns in the request body must exist in the table, otherwise an error will be returned.
     *
     * @apiSuccess {Boolean} status Request status
     * @apiSuccess {String} message Success message
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": true,
     *       "message": "Record updated successfully"
     *     }
     *
     * @apiError {Boolean} status Request status
     * @apiError {String} message Error message
     *
     * @apiErrorExample Error-Response (Column Not Found):
     *     HTTP/1.1 400 Bad Request
     *     {
     *       "status": false,
     *       "message": "Column 'invalid_column' does not exist in table 'george'"
     *     }
     */
    public function customtable_id_put($table_name = '', $id = '')
    {
        if (empty($table_name) || empty($id)) {
            $this->response([
                'status' => FALSE,
                'message' => 'Table name and ID are required'
            ], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        // Sanitize table name
        $table_name = preg_replace('/[^a-zA-Z0-9_]/', '', $table_name);

        // Check if table exists
        if (!$this->_table_exists($table_name)) {
            $this->response([
                'status' => FALSE,
                'message' => "Table '{$table_name}' does not exist"
            ], REST_Controller::HTTP_NOT_FOUND);
            return;
        }

        // Get request data
        $data = $this->input->put();
        if (empty($data)) {
            // Try to get JSON data
            $raw_input = file_get_contents('php://input');
            $data = json_decode($raw_input, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->response([
                    'status' => FALSE,
                    'message' => 'Invalid JSON data'
                ], REST_Controller::HTTP_BAD_REQUEST);
                return;
            }
        }

        if (empty($data)) {
            $this->response([
                'status' => FALSE,
                'message' => 'No data provided'
            ], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        // Get table columns
        $columns = $this->_get_table_columns($table_name);
        if ($columns === false) {
            $this->response([
                'status' => FALSE,
                'message' => 'Error retrieving table structure'
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            return;
        }

        // Validate all provided columns exist
        $invalid_columns = [];
        foreach ($data as $column => $value) {
            if (!in_array($column, $columns)) {
                $invalid_columns[] = $column;
            }
        }

        if (!empty($invalid_columns)) {
            $this->response([
                'status' => FALSE,
                'message' => "Column(s) '" . implode("', '", $invalid_columns) . "' do not exist in table '{$table_name}'"
            ], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        // Check if record exists using raw SQL
        $id = (int)$id; // Sanitize ID
        $query = $this->db->query("SELECT id FROM `{$table_name}` WHERE id = ?", [$id]);
        if ($query->num_rows() == 0) {
            $this->response([
                'status' => FALSE,
                'message' => 'Record not found'
            ], REST_Controller::HTTP_NOT_FOUND);
            return;
        }

        try {
            // Build UPDATE query manually to avoid query builder state conflicts
            $set_parts = [];
            $update_values = [];
            foreach ($data as $column => $value) {
                $set_parts[] = "`{$column}` = ?";
                $update_values[] = $value;
            }
            $update_values[] = $id; // Add ID for WHERE clause
            
            $set_str = implode(', ', $set_parts);
            $sql = "UPDATE `{$table_name}` SET {$set_str} WHERE id = ?";
            $this->db->query($sql, $update_values);

            if ($this->db->affected_rows() > 0) {
                $this->response([
                    'status' => TRUE,
                    'message' => 'Record updated successfully'
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => TRUE,
                    'message' => 'No changes made to the record'
                ], REST_Controller::HTTP_OK);
            }
        } catch (Exception $e) {
            $this->response([
                'status' => FALSE,
                'message' => 'Error updating record: ' . $e->getMessage()
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete a record from a custom table
     * 
     * @api {delete} api/thirdparty/customtable/:table_name/:id Delete Record from Custom Table
     * @apiVersion 0.3.0
     * @apiName DeleteCustomTableRecord
     * @apiGroup Thirdparty
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     * @apiParam {String} table_name Name of the custom database table (exact table name as it exists in the database)
     * @apiParam {Number} id Record ID
     *
     * @apiDescription Deletes a record from the specified custom table by its ID.
     *
     * @apiSuccess {Boolean} status Request status
     * @apiSuccess {String} message Success message
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": true,
     *       "message": "Record deleted successfully"
     *     }
     *
     * @apiError {Boolean} status Request status
     * @apiError {String} message Error message
     *
     * @apiErrorExample Error-Response (Record Not Found):
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status": false,
     *       "message": "Record not found"
     *     }
     */
    public function customtable_id_delete($table_name = '', $id = '')
    {
        if (empty($table_name) || empty($id)) {
            $this->response([
                'status' => FALSE,
                'message' => 'Table name and ID are required'
            ], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        // Sanitize table name
        $table_name = preg_replace('/[^a-zA-Z0-9_]/', '', $table_name);

        // Check if table exists
        if (!$this->_table_exists($table_name)) {
            $this->response([
                'status' => FALSE,
                'message' => "Table '{$table_name}' does not exist"
            ], REST_Controller::HTTP_NOT_FOUND);
            return;
        }

        // Check if record exists using raw SQL
        $id = (int)$id; // Sanitize ID
        $query = $this->db->query("SELECT id FROM `{$table_name}` WHERE id = ?", [$id]);
        if ($query->num_rows() == 0) {
            $this->response([
                'status' => FALSE,
                'message' => 'Record not found'
            ], REST_Controller::HTTP_NOT_FOUND);
            return;
        }

        try {
            // Use raw SQL query to avoid query builder state conflicts
            $this->db->query("DELETE FROM `{$table_name}` WHERE id = ?", [$id]);

            if ($this->db->affected_rows() > 0) {
                $this->response([
                    'status' => TRUE,
                    'message' => 'Record deleted successfully'
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => FALSE,
                    'message' => 'Failed to delete record'
                ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (Exception $e) {
            $this->response([
                'status' => FALSE,
                'message' => 'Error deleting record: ' . $e->getMessage()
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Check if a table exists in the database
     * 
     * @param string $table_name Table name (exact as provided, no prefix)
     * @return bool
     */
    private function _table_exists($table_name)
    {
        // Security gate (v3.0.2): a table is reachable only if it is both on the
        // admin allowlist and not on the hardcoded core denylist. Denied/unlisted
        // tables report as "does not exist" so this cannot be used to probe the schema.
        if (!$this->_table_allowed($table_name)) {
            return false;
        }
        // Use raw SQL query to avoid query builder state conflicts
        $query = $this->db->query("SHOW TABLES LIKE ?", [$table_name]);
        return $query->num_rows() > 0;
    }

    /**
     * Whether a table may be reached through the custom-table endpoints.
     *
     * Two layers:
     *  1. A hardcoded denylist of core/credential tables, ALWAYS enforced regardless
     *     of configuration (options, staff, api tokens, roles, sessions, ...).
     *  2. An opt-in allowlist (Setup -> API -> Settings, option
     *     api_thirdparty_allowed_tables), empty by default, so installing a module
     *     never silently exposes its data.
     *
     * @param string $table_name Sanitized table name (may include the db prefix)
     * @return bool
     */
    private function _table_allowed($table_name)
    {
        $table  = strtolower(trim($table_name));
        if ($table === '') {
            return false;
        }
        $prefix = strtolower(db_prefix());
        $base   = ($prefix !== '' && strpos($table, $prefix) === 0) ? substr($table, strlen($prefix)) : $table;

        // 1. Never-reachable core/credential tables (matched by base name, any prefix).
        $denied = [
            'options', 'staff', 'staff_permissions', 'roles',
            'user_api', 'user_api_permissions',
            'api_webhooks', 'api_webhook_queue', 'api_idempotency_keys',
            'migrations', 'sessions', 'consents', 'oauth_authorized_apps',
        ];
        if (in_array($base, $denied, true)) {
            return false;
        }

        // 2. Opt-in allowlist (comma-separated). Empty => nothing is exposed.
        $raw   = function_exists('get_option') ? (string) get_option('api_thirdparty_allowed_tables') : '';
        $allow = array_filter(array_map('trim', explode(',', strtolower($raw))));
        if (empty($allow)) {
            return false;
        }
        return in_array($table, $allow, true) || in_array($base, $allow, true);
    }

    /**
     * Get all column names from a table
     * 
     * @param string $table_name Table name (exact as provided, no prefix)
     * @return array|false Array of column names or false on error
     */
    private function _get_table_columns($table_name)
    {
        try {
            // Use raw SQL query to avoid query builder state conflicts
            $query = $this->db->query("SHOW COLUMNS FROM `{$table_name}`");
            $columns = [];
            foreach ($query->result_array() as $row) {
                $columns[] = $row['Field'];
            }
            return $columns;
        } catch (Exception $e) {
            return false;
        }
    }
}
