<?php
defined('BASEPATH') or exit('No direct script access allowed');

require __DIR__ . '/REST_Controller.php';

/**
 * @api {get} api/notes/:rel_type/:rel_id List Notes of an Entity
 * @apiVersion 3.0.0
 * @apiName GetNotesByRelation
 * @apiGroup Notes
 * @apiHeader {String} authtoken Authentication token, generated from admin area
 * @apiParam {String} rel_type One of: customer, lead, contract, ticket, invoice, estimate, credit_note, staff, expense, proposal, project, task.
 * @apiParam {Number} rel_id The related record id.
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     [
 *         {
 *             "id": "142",
 *             "description": "Called the client to confirm the renewal date. They will send the signed contract by Friday.",
 *             "date_contacted": null,
 *             "dateadded": "2026-03-14 15:42:07",
 *             "addedfrom": "3",
 *             "rel_id": "18",
 *             "rel_type": "customer"
 *         },
 *         {
 *             "id": "128",
 *             "description": "Client requested a follow-up call regarding the outstanding invoice.",
 *             "date_contacted": null,
 *             "dateadded": "2026-03-02 09:18:55",
 *             "addedfrom": "1",
 *             "rel_id": "18",
 *             "rel_type": "customer"
 *         }
 *     ]
 *
 * @apiErrorExample {json} Not-Found:
 *     HTTP/1.1 404 Not Found
 *     {
 *         "status": false,
 *         "message": "No data were found"
 *     }
 */

/**
 * @api {post} api/notes Add New Note
 * @apiVersion 3.0.0
 * @apiName PostNote
 * @apiGroup Notes
 * @apiHeader {String} authtoken Authentication token, generated from admin area
 * @apiParam {String} rel_type Entity type the note attaches to (12 supported types).
 * @apiParam {Number} rel_id The related record id.
 * @apiParam {String} description Note text.
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *         "status": true,
 *         "message": "Note added successfully",
 *         "record_id": 143
 *     }
 *
 * @apiErrorExample {json} Validation-Error:
 *     HTTP/1.1 422 Unprocessable Entity
 *     {
 *         "status": false,
 *         "error": "validation_failed",
 *         "message": "Note validation failed",
 *         "errors": {
 *             "rel_type": "rel_type must be one of: customer, lead, contract, ticket, invoice, estimate, credit_note, staff, expense, proposal, project, task",
 *             "description": "The description field is required."
 *         }
 *     }
 */

/**
 * @api {get} api/notes/:id Request Note Information
 * @apiVersion 3.0.0
 * @apiName GetNote
 * @apiGroup Notes
 * @apiHeader {String} authtoken Authentication token, generated from admin area
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *         "id": "142",
 *         "description": "Called the client to confirm the renewal date. They will send the signed contract by Friday.",
 *         "date_contacted": null,
 *         "dateadded": "2026-03-14 15:42:07",
 *         "addedfrom": "3",
 *         "rel_id": "18",
 *         "rel_type": "customer"
 *     }
 *
 * @apiErrorExample {json} Not-Found:
 *     HTTP/1.1 404 Not Found
 *     {
 *         "status": false,
 *         "message": "Note not found"
 *     }
 */

/**
 * @api {put} api/notes/:id Update a Note
 * @apiVersion 3.0.0
 * @apiName PutNote
 * @apiGroup Notes
 * @apiHeader {String} authtoken Authentication token, generated from admin area
 * @apiParam {String} description New note text.
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *         "status": true,
 *         "message": "Note updated successfully"
 *     }
 *
 * @apiErrorExample {json} Validation-Error:
 *     HTTP/1.1 422 Unprocessable Entity
 *     {
 *         "status": false,
 *         "error": "validation_failed",
 *         "message": "Note validation failed",
 *         "errors": {
 *             "description": "The description field is required."
 *         }
 *     }
 */

/**
 * @api {delete} api/notes/:id Delete a Note
 * @apiVersion 3.0.0
 * @apiName DeleteNote
 * @apiGroup Notes
 * @apiHeader {String} authtoken Authentication token, generated from admin area
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *         "status": true,
 *         "message": "Note deleted successfully"
 *     }
 *
 * @apiErrorExample {json} Not-Found:
 *     HTTP/1.1 404 Not Found
 *     {
 *         "status": false,
 *         "message": "Note not found"
 *     }
 */
class Notes extends REST_Controller
{
    /** Entity types notes can attach to (Perfex core rel_type values) */
    const REL_TYPES = [
        'customer', 'lead', 'contract', 'ticket', 'invoice', 'estimate',
        'credit_note', 'staff', 'expense', 'proposal', 'project', 'task',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->load->model('misc_model');
    }

    public function data_get($first = '', $second = '')
    {
        // api/notes/:id (numeric single) vs api/notes/:rel_type/:rel_id
        if ($first !== '' && $second === '' && is_numeric($first)) {
            $row = $this->db->where('id', (int)$first)->get(db_prefix() . 'notes')->row_array();
            if (!$row) {
                $this->response(['status' => FALSE, 'message' => 'Note not found'], REST_Controller::HTTP_NOT_FOUND);
            }
            $this->response($this->api_apply_fields($row), REST_Controller::HTTP_OK);
        }

        $relType = strtolower((string)$first);
        if (!in_array($relType, self::REL_TYPES, true) || !is_numeric($second)) {
            $this->api_validation_error([
                'rel_type' => 'Use api/notes/{rel_type}/{rel_id} with rel_type one of: ' . implode(', ', self::REL_TYPES),
            ], 'Invalid notes request');
        }

        $rows = $this->db
            ->where('rel_type', $relType)
            ->where('rel_id', (int)$second)
            ->order_by('dateadded', 'DESC')
            ->get(db_prefix() . 'notes')
            ->result_array();

        if (!$rows) {
            $this->response(['status' => FALSE, 'message' => 'No data were found'], REST_Controller::HTTP_NOT_FOUND);
        }
        $this->api_list_response($rows, ['date_field' => 'dateadded']);
    }

    public function data_post()
    {
        $relType     = strtolower(trim((string)$this->input->post('rel_type')));
        $relId       = $this->input->post('rel_id');
        $description = trim((string)$this->input->post('description'));

        $errors = [];
        if (!in_array($relType, self::REL_TYPES, true)) {
            $errors['rel_type'] = 'rel_type must be one of: ' . implode(', ', self::REL_TYPES);
        }
        if (!is_numeric($relId) || (int)$relId <= 0) {
            $errors['rel_id'] = 'A valid rel_id is required.';
        }
        if ($description === '') {
            $errors['description'] = 'The description field is required.';
        }
        if (!empty($errors)) {
            $this->api_validation_error($errors, 'Note validation failed');
        }

        // Misc_model::add_note fires the note_created core hook,
        // which the webhook bridge converts into the note_created event.
        $id = $this->misc_model->add_note(['description' => $description], $relType, (int)$relId);

        if (!$id) {
            $this->response(['status' => FALSE, 'message' => 'Note could not be created'], REST_Controller::HTTP_BAD_REQUEST);
        }

        $this->response([
            'status'    => TRUE,
            'message'   => 'Note added successfully',
            'record_id' => $id,
        ], REST_Controller::HTTP_OK);
    }

    public function data_put($id = '')
    {
        $existing    = $this->note_or_404($id);
        $description = trim((string)$this->input->post('description'));
        if ($description === '') {
            $this->api_validation_error(['description' => 'The description field is required.'], 'Note validation failed');
        }

        // Misc_model::edit_note fires the note-update core hooks
        if (method_exists($this->misc_model, 'edit_note')) {
            $this->misc_model->edit_note(['description' => $description], $existing['id']);
        } else {
            $this->db->where('id', $existing['id'])
                ->update(db_prefix() . 'notes', ['description' => nl2br($description)]);
            $this->api_fire_event('note_updated', ['id' => (int)$existing['id']]);
        }

        $this->response(['status' => TRUE, 'message' => 'Note updated successfully'], REST_Controller::HTTP_OK);
    }

    public function data_delete($id = '')
    {
        $existing = $this->note_or_404($id);

        // Misc_model::delete_note fires the note_deleted core hook
        if (method_exists($this->misc_model, 'delete_note')) {
            $this->misc_model->delete_note($existing['id']);
        } else {
            $this->db->where('id', $existing['id'])->delete(db_prefix() . 'notes');
            $this->api_fire_event('note_deleted', ['id' => (int)$existing['id']]);
        }

        $this->response(['status' => TRUE, 'message' => 'Note deleted successfully'], REST_Controller::HTTP_OK);
    }

    private function note_or_404($id)
    {
        if (empty($id) || !is_numeric($id)) {
            $this->response(['status' => FALSE, 'message' => 'Invalid note ID'], REST_Controller::HTTP_NOT_FOUND);
        }
        $row = $this->db->where('id', (int)$id)->get(db_prefix() . 'notes')->row_array();
        if (!$row) {
            $this->response(['status' => FALSE, 'message' => 'Note not found'], REST_Controller::HTTP_NOT_FOUND);
        }
        return $row;
    }
}
