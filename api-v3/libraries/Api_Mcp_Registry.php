<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MCP tool registry (v3)
 *
 * Builds the Model Context Protocol tool list from a declarative resource
 * config and executes tool calls against the same Perfex models / tables the
 * REST controllers use. Tools are filtered by the API token's permissions
 * (feature = resource, capability = get/post/put/delete) so AI agents only
 * ever see operations their token is allowed to perform.
 */
class Api_Mcp_Registry
{
    /**
     * Declarative resource map.
     *
     * feature      permission feature slug (matches the REST controllers)
     * table        table without prefix (read path + direct writes)
     * pk           primary key column
     * date_col     column used for date filtering / ordering
     * search       columns searched by {resource}_search
     * label        human label for descriptions
     * model        [name, add, update(update_style: data_id|merged, update_id_key), delete]
     *              omitted method => that write goes direct-to-db (whitelisted)
     */
    public static function resources()
    {
        return [
            'customers' => [
                'feature' => 'customers', 'table' => 'clients', 'pk' => 'userid',
                'date_col' => 'datecreated', 'search' => ['company', 'vat', 'phonenumber'],
                'label' => 'customer',
                'model' => ['name' => 'clients_model', 'add' => 'add', 'update' => 'update', 'delete' => 'delete'],
            ],
            'contacts' => [
                'feature' => 'contacts', 'table' => 'contacts', 'pk' => 'id',
                'date_col' => 'datecreated', 'search' => ['firstname', 'lastname', 'email'],
                'label' => 'contact',
                'model' => ['name' => 'clients_model', 'update' => 'update_contact', 'delete' => 'delete_contact'],
            ],
            'leads' => [
                'feature' => 'leads', 'table' => 'leads', 'pk' => 'id',
                'date_col' => 'dateadded', 'search' => ['name', 'email', 'company'],
                'label' => 'lead',
                'model' => ['name' => 'leads_model', 'add' => 'add', 'update' => 'update', 'delete' => 'delete'],
            ],
            'invoices' => [
                'feature' => 'invoices', 'table' => 'invoices', 'pk' => 'id',
                'date_col' => 'date', 'search' => ['clientnote', 'adminnote'],
                'label' => 'invoice',
                'model' => ['name' => 'invoices_model', 'add' => 'add', 'update' => 'update', 'delete' => 'delete'],
            ],
            'estimates' => [
                'feature' => 'estimates', 'table' => 'estimates', 'pk' => 'id',
                'date_col' => 'date', 'search' => ['clientnote', 'adminnote'],
                'label' => 'estimate',
                'model' => ['name' => 'estimates_model', 'add' => 'add', 'update' => 'update', 'delete' => 'delete'],
            ],
            'proposals' => [
                'feature' => 'proposals', 'table' => 'proposals', 'pk' => 'id',
                'date_col' => 'datecreated', 'search' => ['subject'],
                'label' => 'proposal',
                'model' => ['name' => 'proposals_model', 'add' => 'add', 'update' => 'update', 'delete' => 'delete'],
            ],
            'credit_notes' => [
                'feature' => 'credit_notes', 'table' => 'creditnotes', 'pk' => 'id',
                'date_col' => 'date', 'search' => ['clientnote', 'adminnote'],
                'label' => 'credit note',
                'model' => ['name' => 'credit_notes_model', 'add' => 'add', 'update' => 'update', 'delete' => 'delete'],
            ],
            'payments' => [
                'feature' => 'payments', 'table' => 'invoicepaymentrecords', 'pk' => 'id',
                'date_col' => 'date', 'search' => ['transactionid', 'note'],
                'label' => 'payment',
                'model' => ['name' => 'payments_model', 'update' => 'update', 'delete' => 'delete'],
            ],
            'projects' => [
                'feature' => 'projects', 'table' => 'projects', 'pk' => 'id',
                'date_col' => 'project_created', 'search' => ['name'],
                'label' => 'project',
                'model' => ['name' => 'projects_model', 'add' => 'add', 'update' => 'update', 'delete' => 'delete'],
            ],
            'tasks' => [
                'feature' => 'tasks', 'table' => 'tasks', 'pk' => 'id',
                'date_col' => 'dateadded', 'search' => ['name'],
                'label' => 'task',
                'model' => ['name' => 'tasks_model', 'add' => 'add', 'update' => 'update', 'delete' => 'delete_task'],
            ],
            'milestones' => [
                'feature' => 'milestones', 'table' => 'milestones', 'pk' => 'id',
                'date_col' => 'due_date', 'search' => ['name'],
                'label' => 'milestone',
                'model' => ['name' => 'projects_model', 'add' => 'add_milestone', 'update' => 'update_milestone', 'delete' => 'delete_milestone'],
            ],
            'tickets' => [
                'feature' => 'tickets', 'table' => 'tickets', 'pk' => 'ticketid',
                'date_col' => 'date', 'search' => ['subject'],
                'label' => 'ticket',
                'model' => ['name' => 'tickets_model', 'add' => 'add', 'update' => 'update_single_ticket_settings', 'update_style' => 'merged', 'update_id_key' => 'ticketid', 'delete' => 'delete'],
            ],
            'contracts' => [
                'feature' => 'contracts', 'table' => 'contracts', 'pk' => 'id',
                'date_col' => 'dateadded', 'search' => ['subject', 'description'],
                'label' => 'contract',
                'model' => ['name' => 'contracts_model', 'add' => 'add', 'update' => 'update', 'delete' => 'delete'],
            ],
            'expenses' => [
                'feature' => 'expenses', 'table' => 'expenses', 'pk' => 'id',
                'date_col' => 'date', 'search' => ['note', 'expense_name'],
                'label' => 'expense',
                'model' => ['name' => 'expenses_model', 'add' => 'add', 'update' => 'update', 'delete' => 'delete'],
            ],
            'items' => [
                'feature' => 'items', 'table' => 'items', 'pk' => 'id',
                'date_col' => NULL, 'search' => ['description', 'long_description'],
                'label' => 'item',
                'model' => ['name' => 'invoice_items_model', 'add' => 'add', 'update' => 'edit', 'update_style' => 'merged', 'update_id_key' => 'itemid', 'delete' => 'delete'],
            ],
            'staffs' => [
                'feature' => 'staffs', 'table' => 'staff', 'pk' => 'staffid',
                'date_col' => 'datecreated', 'search' => ['firstname', 'lastname', 'email'],
                'label' => 'staff member',
                'model' => ['name' => 'staff_model', 'add' => 'add', 'update' => 'update'],
            ],
            'subscriptions' => [
                'feature' => 'subscriptions', 'table' => 'subscriptions', 'pk' => 'id',
                'date_col' => 'created', 'search' => ['name', 'description'],
                'label' => 'subscription',
            ],
            'timesheets' => [
                'feature' => 'timesheets', 'table' => 'taskstimers', 'pk' => 'id',
                'date_col' => NULL, 'search' => ['note'],
                'label' => 'timesheet',
            ],
            'calendar_events' => [
                'feature' => 'calendar', 'table' => 'events', 'pk' => 'eventid',
                'date_col' => 'start', 'search' => ['title', 'description'],
                'label' => 'calendar event',
            ],
            'notes' => [
                'feature' => 'notes', 'table' => 'notes', 'pk' => 'id',
                'date_col' => 'dateadded', 'search' => ['description'],
                'label' => 'note',
            ],
            'kb_articles' => [
                'feature' => 'knowledge_base', 'table' => 'knowledge_base', 'pk' => 'articleid',
                'date_col' => 'datecreated', 'search' => ['subject', 'description'],
                'label' => 'knowledge base article',
            ],
            'kb_groups' => [
                'feature' => 'knowledge_base', 'table' => 'knowledge_base_groups', 'pk' => 'groupid',
                'date_col' => NULL, 'search' => ['name'],
                'label' => 'knowledge base group',
            ],
            'webhooks' => [
                'feature' => 'webhooks', 'table' => 'api_webhooks', 'pk' => 'id',
                'date_col' => 'date_created', 'search' => ['name', 'url'],
                'label' => 'webhook',
            ],
        ];
    }

    /**
     * Lookup (read-only) tools: name => [table, description, order_col].
     */
    public static function lookups()
    {
        return [
            'list_countries'          => ['countries', 'List all countries with their ids', 'short_name'],
            'list_currencies'         => ['currencies', 'List all currencies configured in the CRM', 'name'],
            'list_taxes'              => ['taxes', 'List all tax rates configured in the CRM', 'name'],
            'list_payment_modes'      => ['payment_modes', 'List all payment modes', 'name'],
            'list_expense_categories' => ['expenses_categories', 'List all expense categories', 'name'],
            'list_lead_sources'       => ['leads_sources', 'List all lead sources', 'name'],
            'list_lead_statuses'      => ['leads_status', 'List all lead statuses', 'statusorder'],
            'list_departments'        => ['departments', 'List all support departments', 'name'],
        ];
    }

    /**
     * Build the MCP tool definitions, filtered by token permissions.
     *
     * @param array|NULL $granted NULL = full access; else feature => [capability,...]
     * @return array
     */
    public static function buildTools($granted = null)
    {
        $tools = [];

        foreach (self::resources() as $key => $cfg) {
            $feature = $cfg['feature'];
            $label   = $cfg['label'];

            if (self::allowed($granted, $feature, 'get')) {
                $tools[] = self::tool($key . '_list', 'List ' . $label . 's. Supports limit/offset and optional date range.', [
                    'limit'  => ['type' => 'integer', 'description' => 'Max rows (default 25, max 100)'],
                    'offset' => ['type' => 'integer', 'description' => 'Rows to skip'],
                    'created_after'  => ['type' => 'string', 'description' => 'ISO date lower bound'],
                    'created_before' => ['type' => 'string', 'description' => 'ISO date upper bound'],
                ]);
                $tools[] = self::tool($key . '_get', 'Get a single ' . $label . ' by id.', [
                    'id' => ['type' => 'integer', 'description' => 'Primary key'],
                ], ['id']);
                if (!empty($cfg['search'])) {
                    $tools[] = self::tool($key . '_search', 'Search ' . $label . 's by keyword (matches ' . implode(', ', $cfg['search']) . ').', [
                        'q'     => ['type' => 'string', 'description' => 'Search keyword'],
                        'limit' => ['type' => 'integer', 'description' => 'Max rows (default 25, max 100)'],
                    ], ['q']);
                }
            }

            if (self::allowed($granted, $feature, 'post')) {
                $tools[] = self::tool($key . '_create', 'Create a ' . $label . '. Pass the record fields as the "data" object (same field names as the REST API).', [
                    'data' => ['type' => 'object', 'description' => 'Field map for the new record'],
                ], ['data']);
            }
            if (self::allowed($granted, $feature, 'put')) {
                $tools[] = self::tool($key . '_update', 'Update a ' . $label . ' by id. Only the provided fields change.', [
                    'id'   => ['type' => 'integer', 'description' => 'Primary key'],
                    'data' => ['type' => 'object', 'description' => 'Fields to change'],
                ], ['id', 'data']);
            }
            if (self::allowed($granted, $feature, 'delete')) {
                $tools[] = self::tool($key . '_delete', 'Delete a ' . $label . ' by id.', [
                    'id' => ['type' => 'integer', 'description' => 'Primary key'],
                ], ['id']);
            }
        }

        // Read-only lookups (available to every authenticated token)
        foreach (self::lookups() as $name => $spec) {
            $tools[] = self::tool($name, $spec[1], [
                'limit' => ['type' => 'integer', 'description' => 'Max rows (default 100)'],
            ]);
        }

        // Utility tools
        $tools[] = self::tool('get_current_datetime', 'Get the CRM server date/time plus useful periods (today, current month/quarter, previous quarter, year-to-date).', []);
        $tools[] = self::tool('get_server_info', 'Get API module name/version and MCP capability summary.', []);

        return $tools;
    }

    /**
     * Execute a tool call. Returns a JSON-serializable result.
     * Throws Exception with a human message on any failure.
     */
    public static function execute($name, array $args, $CI, $granted = null)
    {
        // Utility tools first
        if ($name === 'get_current_datetime') {
            return self::currentDatetime();
        }
        if ($name === 'get_server_info') {
            return [
                'name'    => 'Perfex CRM REST API - MCP Server',
                'version' => defined('API_MODULE_VERSION') ? API_MODULE_VERSION : '3.0.0',
                'tools'   => count(self::buildTools($granted)),
            ];
        }

        $lookups = self::lookups();
        if (isset($lookups[$name])) {
            $limit = isset($args['limit']) ? max(1, min(500, (int)$args['limit'])) : 100;
            return $CI->db->order_by($lookups[$name][2], 'ASC')->limit($limit)
                ->get(db_prefix() . $lookups[$name][0])->result_array();
        }

        // Resource tools: {resource}_{action}
        foreach (self::resources() as $key => $cfg) {
            $prefix = $key . '_';
            if (strpos($name, $prefix) !== 0) {
                continue;
            }
            $action = substr($name, strlen($prefix));
            if (!in_array($action, ['list', 'get', 'search', 'create', 'update', 'delete'], true)) {
                continue;
            }

            self::assertAllowed($granted, $cfg['feature'], $action);
            return self::executeResource($cfg, $action, $args, $CI);
        }

        throw new Exception('Unknown tool: ' . $name);
    }

    // ------------------------------------------------------------------
    // Internals
    // ------------------------------------------------------------------

    private static function executeResource($cfg, $action, array $args, $CI)
    {
        $table = db_prefix() . $cfg['table'];
        $pk    = $cfg['pk'];

        switch ($action) {
            case 'list':
                $limit  = isset($args['limit']) ? max(1, min(100, (int)$args['limit'])) : 25;
                $offset = isset($args['offset']) ? max(0, (int)$args['offset']) : 0;
                if (!empty($cfg['date_col'])) {
                    if (!empty($args['created_after'])) {
                        $CI->db->where($cfg['date_col'] . ' >=', date('Y-m-d H:i:s', strtotime($args['created_after'])));
                    }
                    if (!empty($args['created_before'])) {
                        $CI->db->where($cfg['date_col'] . ' <=', date('Y-m-d H:i:s', strtotime($args['created_before'])));
                    }
                    $CI->db->order_by($cfg['date_col'], 'DESC');
                } else {
                    $CI->db->order_by($pk, 'DESC');
                }
                return $CI->db->limit($limit, $offset)->get($table)->result_array();

            case 'get':
                $row = $CI->db->where($pk, (int)self::arg($args, 'id'))->get($table)->row_array();
                if (!$row) {
                    throw new Exception(ucfirst($cfg['label']) . ' not found');
                }
                return $row;

            case 'search':
                $q     = trim((string)self::arg($args, 'q'));
                $limit = isset($args['limit']) ? max(1, min(100, (int)$args['limit'])) : 25;
                if ($q === '') {
                    throw new Exception('Search keyword q is required');
                }
                $CI->db->group_start();
                foreach ($cfg['search'] as $i => $col) {
                    if ($i === 0) {
                        $CI->db->like($col, $q);
                    } else {
                        $CI->db->or_like($col, $q);
                    }
                }
                $CI->db->group_end();
                return $CI->db->limit($limit)->get($table)->result_array();

            case 'create':
                $data = self::arg($args, 'data');
                if (!is_array($data) || empty($data)) {
                    throw new Exception('data object is required');
                }
                if (!empty($cfg['model']['add'])) {
                    $model = self::loadModel($CI, $cfg['model']['name'], $cfg['model']['add']);
                    $id    = $model->{$cfg['model']['add']}($data);
                } else {
                    $data = self::whitelist($CI, $cfg['table'], $data);
                    if (empty($data)) {
                        throw new Exception('No valid fields provided');
                    }
                    $CI->db->insert($table, $data);
                    $id = $CI->db->insert_id();
                }
                if (!$id) {
                    throw new Exception(ucfirst($cfg['label']) . ' could not be created');
                }
                return ['status' => true, 'record_id' => is_numeric($id) ? (int)$id : $id];

            case 'update':
                $id   = (int)self::arg($args, 'id');
                $data = self::arg($args, 'data');
                if (!is_array($data) || empty($data)) {
                    throw new Exception('data object is required');
                }
                $exists = $CI->db->where($pk, $id)->get($table)->row_array();
                if (!$exists) {
                    throw new Exception(ucfirst($cfg['label']) . ' not found');
                }
                if (!empty($cfg['model']['update'])) {
                    $model  = self::loadModel($CI, $cfg['model']['name'], $cfg['model']['update']);
                    $method = $cfg['model']['update'];
                    if (isset($cfg['model']['update_style']) && $cfg['model']['update_style'] === 'merged') {
                        $idKey        = isset($cfg['model']['update_id_key']) ? $cfg['model']['update_id_key'] : $pk;
                        $data[$idKey] = $id;
                        $model->{$method}($data);
                    } else {
                        $model->{$method}($data, $id);
                    }
                } else {
                    $data = self::whitelist($CI, $cfg['table'], $data);
                    unset($data[$pk]);
                    if (empty($data)) {
                        throw new Exception('No valid fields to update');
                    }
                    $CI->db->where($pk, $id)->update($table, $data);
                }
                return ['status' => true, 'record_id' => $id];

            case 'delete':
                $id     = (int)self::arg($args, 'id');
                $exists = $CI->db->where($pk, $id)->get($table)->row_array();
                if (!$exists) {
                    throw new Exception(ucfirst($cfg['label']) . ' not found');
                }
                if (!empty($cfg['model']['delete'])) {
                    $model = self::loadModel($CI, $cfg['model']['name'], $cfg['model']['delete']);
                    $model->{$cfg['model']['delete']}($id);
                } else {
                    $CI->db->where($pk, $id)->delete($table);
                }
                return ['status' => true, 'record_id' => $id];
        }

        throw new Exception('Unsupported action');
    }

    private static function loadModel($CI, $name, $method)
    {
        $CI->load->model($name);
        $instance = $CI->{$name};
        if (!method_exists($instance, $method)) {
            throw new Exception('Operation not supported on this Perfex version (' . $name . '::' . $method . ')');
        }
        return $instance;
    }

    /** Keep only real table columns to avoid SQL errors from unknown fields */
    private static function whitelist($CI, $table, array $data)
    {
        try {
            $columns = $CI->db->list_fields(db_prefix() . $table);
        } catch (Exception $e) {
            return $data;
        }
        if (empty($columns)) {
            return $data;
        }
        return array_intersect_key($data, array_flip($columns));
    }

    private static function arg(array $args, $key)
    {
        if (!array_key_exists($key, $args)) {
            throw new Exception('Missing required argument: ' . $key);
        }
        return $args[$key];
    }

    private static function tool($name, $description, array $props, array $required = [])
    {
        $schema = [
            'type'       => 'object',
            'properties' => empty($props) ? new stdClass() : $props,
        ];
        if (!empty($required)) {
            $schema['required'] = $required;
        }
        return [
            'name'        => $name,
            'description' => $description,
            'inputSchema' => $schema,
        ];
    }

    /** capability mapping: list/get/search => get, create => post, update => put, delete => delete */
    public static function capabilityFor($action)
    {
        switch ($action) {
            case 'create': return 'post';
            case 'update': return 'put';
            case 'delete': return 'delete';
            default:       return 'get';
        }
    }

    private static function allowed($granted, $feature, $capability)
    {
        if ($granted === null) {
            return true; // full-access token
        }
        return isset($granted[$feature]) && in_array($capability, $granted[$feature], true);
    }

    private static function assertAllowed($granted, $feature, $action)
    {
        if (!self::allowed($granted, $feature, self::capabilityFor($action))) {
            throw new Exception('This token has no permission for ' . $feature . ' (' . self::capabilityFor($action) . ')');
        }
    }

    private static function currentDatetime()
    {
        $now     = time();
        $quarter = (int)ceil(date('n', $now) / 3);
        $qStart  = mktime(0, 0, 0, ($quarter - 1) * 3 + 1, 1, (int)date('Y', $now));
        $pqYear  = $quarter === 1 ? (int)date('Y', $now) - 1 : (int)date('Y', $now);
        $pq      = $quarter === 1 ? 4 : $quarter - 1;
        $pqStart = mktime(0, 0, 0, ($pq - 1) * 3 + 1, 1, $pqYear);
        $pqEnd   = strtotime(date('Y-m-d', $qStart) . ' -1 day');

        return [
            'datetime'  => date('Y-m-d H:i:s', $now),
            'date'      => date('Y-m-d', $now),
            'timezone'  => date_default_timezone_get(),
            'today'     => date('Y-m-d', $now),
            'yesterday' => date('Y-m-d', $now - 86400),
            'current_month'    => ['start' => date('Y-m-01', $now), 'end' => date('Y-m-t', $now)],
            'current_quarter'  => ['quarter' => $quarter, 'start' => date('Y-m-d', $qStart), 'end' => date('Y-m-d', strtotime(date('Y-m-d', $qStart) . ' +3 months -1 day'))],
            'previous_quarter' => ['quarter' => $pq, 'start' => date('Y-m-d', $pqStart), 'end' => date('Y-m-d', $pqEnd)],
            'year_to_date'     => ['start' => date('Y-01-01', $now), 'end' => date('Y-m-d', $now)],
        ];
    }
}
