<?php

require __DIR__ . '/REST_Controller.php';

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @OA\Tag(
 *     name="Check",
 *     description="Common API endpoints"
 * )
 */
class Check extends REST_Controller
{
    /**
     * @api {get} api/common/{type} Get Common Data
     * @apiVersion 0.3.0
     * @apiName GetCommonData
     * @apiGroup Common
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     * @apiParam {String} type Data type. Must be one of: expense_category, payment_mode, tax_data
     *
     * @apiDescription Retrieves common system data based on the specified type. This endpoint is an alias for the Common controller's data_get method.
     *
     * @apiSuccess {Array} Array of common data records (format depends on type)
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     [
     *       {
     *         "id": "1",
     *         "name": "expense category name",
     *         "description": "description"
     *       }
     *     ]
     *
     * @apiError {Boolean} status Request status
     * @apiError {String} message Error message
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status": false,
     *       "message": "Not valid data"
     *     }
     *
     * @apiNote This controller appears to be a duplicate/stub of Common.php. The implementation should match Common::data_get() or this controller should be removed.
     */
    public function data_get($type = "")
    {
        // NOTE: This method is not fully implemented
        // This controller appears to be a stub/duplicate of Common.php
        // Consider implementing the same logic as Common::data_get() or removing this controller
        $allowed_type = ["expense_category", "payment_mode", "tax_data"];
        if (empty($type) || !in_array($type, $allowed_type)) {
            $this->response([
                'status' => FALSE,
                'message' => 'Not valid data'
            ], REST_Controller::HTTP_NOT_FOUND);
            return;
        }
        
        // Delegate to appropriate method (would need to be implemented)
        $this->response([
            'status' => FALSE,
            'message' => 'Method not implemented. Use /api/common/{type} instead.'
        ], REST_Controller::HTTP_NOT_IMPLEMENTED);
    }
}