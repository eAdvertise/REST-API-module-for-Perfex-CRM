<?php
defined('BASEPATH') or exit('No direct script access allowed');

require __DIR__ . '/REST_Controller.php';

class Subscriptions extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @api {get} api/subscriptions/ Request all Subscriptions
     * @apiName Request Subscriptions
     * @apiGroup Subscriptions
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     * @apiSuccess {Object} Data Information
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     [
     *         {
     *          'name' => varchar 'New  subscription'
     *          'description' => text 'This is a detailed description of subscription'
     *          'description_in_item' => tinyint '1'
     *          'clientid' => int '123'
     *          'date' => date '2024-01-31'
     *          'terms' => text 'subscription payment is due'
     *          'currency ' => int '4'
     *          'tax_id ' => int '456'
     *          'stripe_tax_id_2' => varchar 'tax-789' 
     *          'stripe_plan_id' => text 'subscription_ABC'
     *          'stripe_subscription_id' => text 'subscription_ABC'
     *          'tax_id_2': int '12',
     *          'stripe_subscription_id' => text 'sub_123456789'
     *          'next_billing_cycle' => bigint '1643808000'
     *          'ends_at' => bigint '1646486400'
     *          'status' => varchar 'active'
     *          'quantity' => int '5'
     *          'project_id' => int '789'
     *          'hash' => varchar 'a1b2c3' 
     *          'created' => datetime '2024-01-31 12:34:56'
     *          'created_from' => int '1' 
     *          'date_subscribed' => datetime '2024-01-31 10:00:00'
     *          'in_test_environment' => int '1' 
     *          'last_sent_at' => datetime '2024-01-31 14:45:00'
     *         }
     *     ]
     *
     * @apiError DataNotFound The id of the data was not found.
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *         "status": false,
     *         "message": "No data were found"
     *     }
     */
	 
    /**
     * @api {get} api/subscriptions/:id Request Subscription Information
     * @apiName Request Subscription Information
     * @apiGroup Subscriptions
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     * @apiParam {id} id Data id ID.
     *
     * @apiSuccess {Object} Data Information
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     [
     *         {
     *          'name' => varchar 'New  subscription'
     *          'description' => text 'This is a detailed description of subscription'
     *          'description_in_item' => tinyint '1'
     *          'clientid' => int '123'
     *          'date' => date '2024-01-31'
     *          'terms' => text 'subscription payment is due'
     *          'currency ' => int '4'
     *          'tax_id ' => int '456'
     *          'stripe_tax_id_2' => varchar 'tax-789' 
     *          'stripe_plan_id' => text 'subscription_ABC'
     *          'stripe_subscription_id' => text 'subscription_ABC'
     *          'tax_id_2': int '12',
     *          'stripe_subscription_id' => text 'sub_123456789'
     *          'next_billing_cycle' => bigint '1643808000'
     *          'ends_at' => bigint '1646486400'
     *          'status' => varchar 'active'
     *          'quantity' => int '5'
     *          'project_id' => int '789'
     *          'hash' => varchar 'a1b2c3' 
     *          'created' => datetime '2024-01-31 12:34:56'
     *          'created_from' => int '1' 
     *          'date_subscribed' => datetime '2024-01-31 10:00:00'
     *          'in_test_environment' => int '1' 
     *          'last_sent_at' => datetime '2024-01-31 14:45:00'
     *         }
     *     ]
     *
     * @apiError DataNotFound The id of the data was not found.
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *         "status": false,
     *         "message": "No data were found"
     *     }
     */

    public function data_get($id = '')
    {
        $data = $this->Api_model->get_table('subscriptions', $id);
 
        if ($data) {
            $this->response($data, REST_Controller::HTTP_OK);
        } else {
            $this->response(['status' => FALSE, 'message' => 'No data were found'], REST_Controller::HTTP_NOT_FOUND);
        }
    }

/**
 * @api {post} api/subscriptions/ Add New Subscription
 * @apiName AddNewSubscription
 * @apiGroup Subscriptions
 * @apiHeader {String} authtoken Authentication token, generated from admin area
 * @apiParam {String} name New subscription name.
 * @apiParam {Text} description Detailed description of the subscription.
 * @apiParam {TinyInt} description_in_item Indicates if the description is included in the item (1 or 0).
 * @apiParam {Int} clientid Client ID.
 * @apiParam {Date} date Subscription start date (YYYY-MM-DD).
 * @apiParam {Text} terms Subscription terms.
 * @apiParam {Int} currency Currency ID.
 * @apiParam {Int} tax_id Tax ID.
 * @apiParam {Varchar} stripe_tax_id_2 Stripe tax ID.
 * @apiParam {Text} stripe_plan_id Stripe plan ID.
 * @apiParam {Text} stripe_subscription_id Stripe Subscription ID.
 * @apiParam {Int} tax_id_2 Second tax ID.
 * @apiParam {Varchar} stripe_subscription_id Stripe subscription ID.
 * @apiParam {BigInt} next_billing_cycle Next billing cycle timestamp.
 * @apiParam {BigInt} ends_at Subscription end timestamp.
 * @apiParam {Varchar} status Subscription status (e.g., active).
 * @apiParam {Int} quantity Subscription quantity.
 * @apiParam {Int} project_id Associated project ID.
 * @apiParam {Varchar} hash Unique hash identifier.
 * @apiParam {DateTime} created Creation timestamp (YYYY-MM-DD HH:MM:SS).
 * @apiParam {Int} created_from ID of the creator.
 * @apiParam {DateTime} date_subscribed Subscription date (YYYY-MM-DD HH:MM:SS).
 * @apiParam {Int} in_test_environment Indicates if the subscription is in a test environment (1 or 0).
 * @apiParam {DateTime} last_sent_at Last sent timestamp (YYYY-MM-DD HH:MM:SS).
 *
 * @apiParamExample {multipart/form-data} Request Example:
 *  {
 *      "name": "New subscription",
 *      "description": "This is a detailed description of subscription",
 *      "description_in_item": 1,
 *      "clientid": 123,
 *      "date": "2024-01-31",
 *      "terms": "subscription payment is due",
 *      "currency": 4,
 *      "tax_id": 456,
 *      "stripe_tax_id_2": "tax-789",
 *      "stripe_plan_id": "subscription_ABC",
 *      "stripe_subscription_id": "subscription_ABC",
 *      "tax_id_2": 12,
 *      "stripe_subscription_id": "sub_123456789",
 *      "next_billing_cycle": 1643808000,
 *      "ends_at": 1646486400,
 *      "status": "active",
 *      "quantity": 5,
 *      "project_id": 789,
 *      "hash": "a1b2c3",
 *      "created": "2024-01-31 12:34:56",
 *      "created_from": 1,
 *      "date_subscribed": "2024-01-31 10:00:00",
 *      "in_test_environment": 1,
 *      "last_sent_at": "2024-01-31 14:45:00"
 *  }
 *
 * @apiSuccess {Boolean} status Request status.
 * @apiSuccess {String} message Success message.
 *
 * @apiSuccessExample {json} Success-Response:
 *  HTTP/1.1 200 OK
 *  {
 *      "status": true,
 *      "message": "Data Added Successfully"
 *  }
 *
 * @apiError DataNotAdded Data could not be added.
 *
 * @apiErrorExample {json} Error-Response:
 *  HTTP/1.1 400 Bad Request
 *  {
 *      "status": false,
 *      "error": "Data not Added"
 *  }
 */

    public function data_post()
    {

        $data = $this->input->post();
    
        $this->form_validation->set_rules('name', 'Subscription Name', 'trim|required');
        $this->form_validation->set_rules('quantity', 'Quantity', 'trim|required');
        $this->form_validation->set_rules('next_billing_cycle', ' Billing Plan', 'required');
        $this->form_validation->set_rules('currency', 'Currency', 'trim|required');
        $this->form_validation->set_rules('clientid', 'clientid', 'trim|required');
    
        if ($this->form_validation->run() == FALSE) {
            $message = array('status' => FALSE, 'error' => $this->form_validation->error_array(), 'message' => validation_errors());
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        } else {
            $id = $this->Api_model->subscription($data);
            
            if ($id > 0 && !empty($id)) {
                $message = array('status' => TRUE, 'message' => 'Data Added Successfully');
                $this->response($message, REST_Controller::HTTP_OK);
            } else {
                $message = array('status' => FALSE, 'message' => 'Data Add Fail');
                $this->response($message, REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    /**
     * @api {put} api/subscriptions/:id Update a Subscription
     * @apiName Update a Subscription
     * @apiParam {id} id ID for update data.
     * @apiGroup Subscriptions
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     * @apiParamExample {json} Request-Example:
     *  {
     *     'name' => varchar 'New  subscription updated'
     *     'description' => text 'This is a detailed description of subscription'
     *     'description_in_item' => tinyint '1'
     *     'clientid' => int '123'
     *     'date' => date '2024-01-31'
     *     'terms' => text 'subscription payment is due'
     *     'currency ' => int '4'
     *     'tax_id ' => int '456'
     *     'stripe_tax_id_2' => varchar 'tax-789' 
     *     'stripe_plan_id' => text 'subscription_ABC'
     *     'stripe_subscription_id' => text 'subscription_ABC'
     *     "tax_id_2": int '12',
     *     'stripe_subscription_id' => text 'sub_123456789'
     *     'next_billing_cycle' => bigint '1643808000'
     *     'ends_at' => bigint '1646486400'
     *     'status' => varchar 'active'
     *     'quantity' => int '5'
     *     'project_id' => int '789'
     *     'hash' => varchar 'a1b2c3' 
     *     'created' => datetime '2024-01-31 12:34:56'
     *     'created_from' => int '1' 
     *     'date_subscribed' => datetime '2024-01-31 10:00:00'
     *     'in_test_environment' => int '1' 
     *     'last_sent_at' => datetime '2024-01-31 14:45:00'
     *  }
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": true,
     *       "message": "Data Update Successful."
     *     }
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *  {
     *       "status": false,
     *       "message": "Data Not Acceptable OR Not Provided"
     *  }
     *
     * {
     *    "status": false,
     *    "message": "Data Update Fail."
     * }
     */
    public function data_put($id = '')
    {
        $_POST = json_decode($this->security->xss_clean(file_get_contents("php://input")), true);
       
        if (empty($_POST) || !isset($_POST)) {
            $message = array('status' => FALSE, 'message' => 'Data Not Acceptable OR Not Provided');
            $this->response($message, REST_Controller::HTTP_NOT_ACCEPTABLE);
        }
        $this->form_validation->set_data($_POST);
        if (empty($id) && !is_numeric($id)) {
            $message = array('status' => FALSE, 'message' => 'Invalid data or missing Send ID. please provide updated data ID.');
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        } else {
            $_POST['id'] = $id;
            $update_data = $this->input->post();
            $data = $_POST;
            $output = $this->Api_model->subscriptions($data);
            if ($output > 0 && !empty($output)) {
                $message = array('status' => TRUE, 'message' => 'Data Update Successful.');
                $this->response($message, REST_Controller::HTTP_OK);
            } else {
                $message = array('status' => FALSE, 'message' => 'Data Update Fail.');
                $this->response($message, REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    /**
     * @api {delete} api/subscriptions/:id Delete a Subscription
     * @apiName Delete a Subscription
     * @apiGroup Subscriptions
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     * @apiParam {id} id ID for data Deletion.
     *
     * @apiSuccessExample Success-Response:
     * HTTP/1.1 200 OK
     *  {
     *      "status": true,
     *      "message": "Delete Successful."
     *  }
     *
     * @apiError DataNotAdded.
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *  {
     *      "status": false,
     *      "message": "Delete Fail."
     *  }
     */
    public function data_delete($id = '')
    {
        $id = $this->security->xss_clean($id);

        if (empty($id) && !is_numeric($id)) {
            $message = array('status' => FALSE, 'message' => 'Invalid ID');
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        } else {
            $this->load->model('api_model');
            $output = $this->api_model->delete_subscription($id);
            
            if ($output === TRUE) {
                $message = array('status' => TRUE, 'message' => 'Delete Successful.');
                $this->response($message, REST_Controller::HTTP_OK);
            } else {
                $message = array('status' => FALSE, 'message' => 'Delete Fail.');
                $this->response($message, REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }
}