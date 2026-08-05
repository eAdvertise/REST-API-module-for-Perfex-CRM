<?php
defined('BASEPATH') or exit('No direct script access allowed');

require __DIR__ . '/REST_Controller.php';

/**
 * @api {get} api/knowledge_base List Knowledge Base Articles
 * @apiVersion 3.0.0
 * @apiName GetKbArticles
 * @apiGroup Knowledge_Base
 * @apiHeader {String} authtoken Authentication token, generated from admin area
 * @apiParam {Number} [group_id] Filter articles by group.
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "data": [
 *         {
 *           "articleid": "12",
 *           "articlegroup": "3",
 *           "subject": "How to reset your account password",
 *           "slug": "how-to-reset-your-account-password",
 *           "description": "<p>Open Settings &gt; Security and click Reset password.</p>",
 *           "active": "1",
 *           "datecreated": "2026-02-14 09:32:10",
 *           "article_order": "1",
 *           "staff_article": "0"
 *         }
 *       ],
 *       "meta": {
 *         "page": 1,
 *         "per_page": 20,
 *         "total": 8,
 *         "total_pages": 1,
 *         "has_more": false,
 *         "current_page": 1,
 *         "last_page": 1
 *       }
 *     }
 * @apiErrorExample {json} Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "status": false,
 *       "message": "No data were found"
 *     }
 */

/**
 * @api {post} api/knowledge_base Add New Article
 * @apiVersion 3.0.0
 * @apiName PostKbArticle
 * @apiGroup Knowledge_Base
 * @apiHeader {String} authtoken Authentication token, generated from admin area
 * @apiParam {String} subject Article subject (slug auto-generated, unique).
 * @apiParam {Number} articlegroup Existing knowledge base group id.
 * @apiParam {String} [description] Article body (HTML allowed).
 * @apiParam {Number} [active=1] 1 published / 0 hidden.
 * @apiParam {Number} [staff_article=0] 1 = internal (staff only).
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "status": true,
 *       "message": "Article added successfully",
 *       "record_id": 12
 *     }
 * @apiErrorExample {json} Error-Response:
 *     HTTP/1.1 422 Unprocessable Entity
 *     {
 *       "status": false,
 *       "error": "validation_failed",
 *       "message": "Article validation failed",
 *       "errors": {
 *         "subject": "The subject field is required.",
 *         "articlegroup": "A valid articlegroup id is required."
 *       }
 *     }
 */

/**
 * @api {get} api/knowledge_base/:id Request Article Information
 * @apiVersion 3.0.0
 * @apiName GetKbArticle
 * @apiGroup Knowledge_Base
 * @apiHeader {String} authtoken Authentication token, generated from admin area
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "articleid": "12",
 *       "articlegroup": "3",
 *       "subject": "How to reset your account password",
 *       "slug": "how-to-reset-your-account-password",
 *       "description": "<p>Open Settings &gt; Security and click Reset password.</p>",
 *       "active": "1",
 *       "datecreated": "2026-02-14 09:32:10",
 *       "article_order": "1",
 *       "staff_article": "0"
 *     }
 * @apiErrorExample {json} Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "status": false,
 *       "message": "Article not found"
 *     }
 */

/**
 * @api {put} api/knowledge_base/:id Update an Article
 * @apiVersion 3.0.0
 * @apiName PutKbArticle
 * @apiGroup Knowledge_Base
 * @apiHeader {String} authtoken Authentication token, generated from admin area
 * @apiDescription Partial update - unknown fields are ignored.
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "status": true,
 *       "message": "Article updated successfully"
 *     }
 * @apiErrorExample {json} Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "status": false,
 *       "message": "Article not found"
 *     }
 */

/**
 * @api {delete} api/knowledge_base/:id Delete an Article
 * @apiVersion 3.0.0
 * @apiName DeleteKbArticle
 * @apiGroup Knowledge_Base
 * @apiHeader {String} authtoken Authentication token, generated from admin area
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "status": true,
 *       "message": "Article deleted successfully"
 *     }
 * @apiErrorExample {json} Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "status": false,
 *       "message": "Article not found"
 *     }
 */

/**
 * @api {get} api/knowledge_base/groups List Knowledge Base Groups
 * @apiVersion 3.0.0
 * @apiName GetKbGroups
 * @apiGroup Knowledge_Base
 * @apiHeader {String} authtoken Authentication token, generated from admin area
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "data": [
 *         {
 *           "groupid": "3",
 *           "name": "Billing & Payments",
 *           "group_slug": "billing-payments",
 *           "description": "Invoices, subscriptions and refunds",
 *           "active": "1",
 *           "color": "#28B8DA",
 *           "group_order": "2"
 *         }
 *       ],
 *       "meta": {
 *         "page": 1,
 *         "per_page": 20,
 *         "total": 5,
 *         "total_pages": 1,
 *         "has_more": false,
 *         "current_page": 1,
 *         "last_page": 1
 *       }
 *     }
 * @apiErrorExample {json} Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "status": false,
 *       "message": "No data were found"
 *     }
 */

/**
 * @api {post} api/knowledge_base/groups Add New Group
 * @apiVersion 3.0.0
 * @apiName PostKbGroup
 * @apiGroup Knowledge_Base
 * @apiHeader {String} authtoken Authentication token, generated from admin area
 * @apiParam {String} name Group name (slug auto-generated, unique).
 * @apiParam {String} [description] Group description.
 * @apiParam {String} [color=#28B8DA] Group color.
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "status": true,
 *       "message": "Group added successfully",
 *       "record_id": 3
 *     }
 * @apiErrorExample {json} Error-Response:
 *     HTTP/1.1 422 Unprocessable Entity
 *     {
 *       "status": false,
 *       "error": "validation_failed",
 *       "message": "Group validation failed",
 *       "errors": {
 *         "name": "The name field is required."
 *       }
 *     }
 */

/**
 * @api {put} api/knowledge_base/groups/:id Update a Group
 * @apiVersion 3.0.0
 * @apiName PutKbGroup
 * @apiGroup Knowledge_Base
 * @apiHeader {String} authtoken Authentication token, generated from admin area
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "status": true,
 *       "message": "Group updated successfully"
 *     }
 * @apiErrorExample {json} Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "status": false,
 *       "message": "Group not found"
 *     }
 */

/**
 * @api {delete} api/knowledge_base/groups/:id Delete a Group
 * @apiVersion 3.0.0
 * @apiName DeleteKbGroup
 * @apiGroup Knowledge_Base
 * @apiHeader {String} authtoken Authentication token, generated from admin area
 * @apiDescription Returns 409 while articles are still attached to the group.
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "status": true,
 *       "message": "Group deleted successfully"
 *     }
 * @apiErrorExample {json} Error-Response:
 *     HTTP/1.1 409 Conflict
 *     {
 *       "status": false,
 *       "message": "Group has 4 article(s); move or delete them first"
 *     }
 */
class Knowledge_base extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    // ------------------------------------------------------------------
    // Articles
    // ------------------------------------------------------------------

    public function data_get($id = '')
    {
        if (!empty($id)) {
            $row = $this->db->where('articleid', (int)$id)->get(db_prefix() . 'knowledge_base')->row_array();
            if (!$row) {
                $this->response(['status' => FALSE, 'message' => 'Article not found'], REST_Controller::HTTP_NOT_FOUND);
            }
            $this->response($this->api_apply_fields($row), REST_Controller::HTTP_OK);
        }

        $group = $this->get('group_id');
        if ($group !== NULL && $group !== '') {
            $this->db->where('articlegroup', (int)$group);
        }
        $rows = $this->db->order_by('article_order', 'ASC')->get(db_prefix() . 'knowledge_base')->result_array();

        if (!$rows) {
            $this->response(['status' => FALSE, 'message' => 'No data were found'], REST_Controller::HTTP_NOT_FOUND);
        }
        $this->api_list_response($rows, ['date_field' => 'datecreated']);
    }

    public function data_post()
    {
        $subject = trim((string)$this->input->post('subject'));
        $errors  = [];
        if ($subject === '') {
            $errors['subject'] = 'The subject field is required.';
        }
        $group = (int)$this->input->post('articlegroup');
        if ($group <= 0) {
            $errors['articlegroup'] = 'A valid articlegroup id is required.';
        } elseif (!$this->group_exists($group)) {
            $errors['articlegroup'] = 'Knowledge base group not found.';
        }
        if (!empty($errors)) {
            $this->api_validation_error($errors, 'Article validation failed');
        }

        $data = [
            'articlegroup'  => $group,
            'subject'       => $subject,
            'description'   => (string)$this->input->post('description'),
            'slug'          => $this->unique_slug($subject),
            'active'        => $this->input->post('active') !== NULL ? (int)(bool)$this->input->post('active') : 1,
            'datecreated'   => date('Y-m-d H:i:s'),
            'article_order' => (int)$this->input->post('article_order'),
            'staff_article' => $this->input->post('staff_article') !== NULL ? (int)(bool)$this->input->post('staff_article') : 0,
        ];

        $this->db->insert(db_prefix() . 'knowledge_base', $data);
        $id = $this->db->insert_id();
        if (!$id) {
            $this->response(['status' => FALSE, 'message' => 'Article could not be created'], REST_Controller::HTTP_BAD_REQUEST);
        }

        $this->api_fire_event('kb_article_created', ['id' => $id]);
        $this->response(['status' => TRUE, 'message' => 'Article added successfully', 'record_id' => $id], REST_Controller::HTTP_OK);
    }

    public function data_put($id = '')
    {
        $existing = $this->article_or_404($id);

        $payload = $this->api_sanitize_update_payload('knowledge_base', $this->input->post(), []);
        unset($payload['articleid'], $payload['datecreated']);

        if (isset($payload['articlegroup']) && !$this->group_exists((int)$payload['articlegroup'])) {
            $this->api_validation_error(['articlegroup' => 'Knowledge base group not found.'], 'Article validation failed');
        }
        if (isset($payload['subject']) && trim((string)$payload['subject']) === '') {
            $this->api_validation_error(['subject' => 'The subject field cannot be empty.'], 'Article validation failed');
        }
        if (empty($payload)) {
            $this->api_validation_error(['_general' => 'No updatable fields provided'], 'Nothing to update');
        }

        $this->db->where('articleid', $existing['articleid'])->update(db_prefix() . 'knowledge_base', $payload);
        $this->api_fire_event('kb_article_updated', ['id' => (int)$existing['articleid']]);
        $this->response(['status' => TRUE, 'message' => 'Article updated successfully'], REST_Controller::HTTP_OK);
    }

    public function data_delete($id = '')
    {
        $existing = $this->article_or_404($id);
        $this->db->where('articleid', $existing['articleid'])->delete(db_prefix() . 'knowledge_base');
        $this->api_fire_event('kb_article_deleted', ['id' => (int)$existing['articleid']]);
        $this->response(['status' => TRUE, 'message' => 'Article deleted successfully'], REST_Controller::HTTP_OK);
    }

    // ------------------------------------------------------------------
    // Groups
    // ------------------------------------------------------------------

    public function groups_get()
    {
        $rows = $this->db->order_by('group_order', 'ASC')->get(db_prefix() . 'knowledge_base_groups')->result_array();
        if (!$rows) {
            $this->response(['status' => FALSE, 'message' => 'No data were found'], REST_Controller::HTTP_NOT_FOUND);
        }
        $this->api_list_response($rows);
    }

    public function groups_post()
    {
        $name = trim((string)$this->input->post('name'));
        if ($name === '') {
            $this->api_validation_error(['name' => 'The name field is required.'], 'Group validation failed');
        }

        $data = [
            'name'        => $name,
            'group_slug'  => $this->unique_slug($name, 'knowledge_base_groups', 'group_slug'),
            'description' => (string)$this->input->post('description'),
            'active'      => $this->input->post('active') !== NULL ? (int)(bool)$this->input->post('active') : 1,
            'color'       => (string)($this->input->post('color') ?: '#28B8DA'),
            'group_order' => (int)$this->input->post('group_order'),
        ];

        $this->db->insert(db_prefix() . 'knowledge_base_groups', $data);
        $id = $this->db->insert_id();
        if (!$id) {
            $this->response(['status' => FALSE, 'message' => 'Group could not be created'], REST_Controller::HTTP_BAD_REQUEST);
        }

        $this->api_fire_event('kb_group_created', ['id' => $id]);
        $this->response(['status' => TRUE, 'message' => 'Group added successfully', 'record_id' => $id], REST_Controller::HTTP_OK);
    }

    public function groups_put($id = '')
    {
        $existing = $this->group_or_404($id);

        $payload = $this->api_sanitize_update_payload('knowledge_base_groups', $this->input->post(), []);
        unset($payload['groupid']);
        if (isset($payload['name']) && trim((string)$payload['name']) === '') {
            $this->api_validation_error(['name' => 'The name field cannot be empty.'], 'Group validation failed');
        }
        if (empty($payload)) {
            $this->api_validation_error(['_general' => 'No updatable fields provided'], 'Nothing to update');
        }

        $this->db->where('groupid', $existing['groupid'])->update(db_prefix() . 'knowledge_base_groups', $payload);
        $this->api_fire_event('kb_group_updated', ['id' => (int)$existing['groupid']]);
        $this->response(['status' => TRUE, 'message' => 'Group updated successfully'], REST_Controller::HTTP_OK);
    }

    public function groups_delete($id = '')
    {
        $existing = $this->group_or_404($id);

        $inUse = $this->db->where('articlegroup', $existing['groupid'])->count_all_results(db_prefix() . 'knowledge_base');
        if ($inUse > 0) {
            $this->response([
                'status'  => FALSE,
                'message' => 'Group has ' . $inUse . ' article(s); move or delete them first',
            ], REST_Controller::HTTP_CONFLICT);
        }

        $this->db->where('groupid', $existing['groupid'])->delete(db_prefix() . 'knowledge_base_groups');
        $this->api_fire_event('kb_group_deleted', ['id' => (int)$existing['groupid']]);
        $this->response(['status' => TRUE, 'message' => 'Group deleted successfully'], REST_Controller::HTTP_OK);
    }

    // ------------------------------------------------------------------
    // Internals
    // ------------------------------------------------------------------

    private function article_or_404($id)
    {
        if (empty($id) || !is_numeric($id)) {
            $this->response(['status' => FALSE, 'message' => 'Invalid article ID'], REST_Controller::HTTP_NOT_FOUND);
        }
        $row = $this->db->where('articleid', (int)$id)->get(db_prefix() . 'knowledge_base')->row_array();
        if (!$row) {
            $this->response(['status' => FALSE, 'message' => 'Article not found'], REST_Controller::HTTP_NOT_FOUND);
        }
        return $row;
    }

    private function group_or_404($id)
    {
        if (empty($id) || !is_numeric($id)) {
            $this->response(['status' => FALSE, 'message' => 'Invalid group ID'], REST_Controller::HTTP_NOT_FOUND);
        }
        $row = $this->db->where('groupid', (int)$id)->get(db_prefix() . 'knowledge_base_groups')->row_array();
        if (!$row) {
            $this->response(['status' => FALSE, 'message' => 'Group not found'], REST_Controller::HTTP_NOT_FOUND);
        }
        return $row;
    }

    private function group_exists($id)
    {
        return $this->db->where('groupid', (int)$id)->count_all_results(db_prefix() . 'knowledge_base_groups') > 0;
    }

    /**
     * Generate a unique slug for the given table/column.
     */
    private function unique_slug($text, $table = 'knowledge_base', $column = 'slug')
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $text), '-'));
        if ($slug === '') {
            $slug = 'article';
        }
        $candidate = $slug;
        $suffix    = 1;
        while ($this->db->where($column, $candidate)->count_all_results(db_prefix() . $table) > 0) {
            $suffix++;
            $candidate = $slug . '-' . $suffix;
        }
        return $candidate;
    }
}
