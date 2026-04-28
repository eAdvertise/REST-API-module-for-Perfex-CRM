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
 */
class Payments extends REST_Controller {
    function __construct() {
        // Construct the parent class
        parent::__construct();
        $this->load->model('payments_model');
        $this->load->model('Api_model');
    }

    /**
     * @api {get} api/payments/:id List all Payments
     * @apiVersion 0.3.0
     * @apiName GetPayment
     * @apiGroup Payments
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     * @apiParam {Number} payment_id Optional payment unique ID <br/><i>Note : if you don't pass Payment id then it will list all payments records</i>
     *
     * @apiSuccess {Array} Payments List all Payment Records.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   [
     *       {
     *           "id": "3",
     *           "invoiceid": "7",
     *           "amount": "1000.00",
     *           "paymentmode": "3",
     *           "paymentmethod": "",
     *           "date": "2020-06-08",
     *           "daterecorded": "2020-06-08 20:29:54",
     *           "note": "",
     *           "transactionid": "000355795931",
     *           "invoiceid": "UPI",
     *           "description": "",
     *           "show_on_pdf": "0",
     *           "invoices_only": "0",
     *           "expenses_only": "0",
     *           "selected_by_default": "0",
     *           "active": "1",
     *           "paymentid": "1"
     *       },
     *       {
     *           "id": "4",
     *           "invoiceid": "12",
     *           "amount": "-3.00",
     *           "paymentmode": "4",
     *           "paymentmethod": "",
     *           "date": "2020-07-04",
     *           "daterecorded": "2020-07-04 15:32:59",
     *           "note": "",
     *           "transactionid": "P228210122733439",
     *           "invoiceid": "Stripe",
     *           "description": "",
     *           "show_on_pdf": "0",
     *           "invoices_only": "0",
     *           "expenses_only": "0",
     *           "selected_by_default": "0",
     *           "active": "1",
     *           "paymentid": "2"
     *       },
     *       {
     *           "id": "1",
     *           "invoiceid": "14",
     *           "amount": "8.00",
     *           "paymentmode": "1",
     *           "paymentmethod": "",
     *           "date": "2020-07-04",
     *           "daterecorded": "2020-07-04 15:47:30",
     *           "note": "",
     *           "transactionid": "000360166374",
     *           "invoiceid": "Bank",
     *           "description": null,
     *           "show_on_pdf": "0",
     *           "invoices_only": "0",
     *           "expenses_only": "0",
     *           "selected_by_default": "1",
     *           "active": "1",
     *           "paymentid": "3"
     *       },
     *       {
     *           "id": "2",
     *           "invoiceid": "13",
     *           "amount": "3.00",
     *           "paymentmode": "2",
     *           "paymentmethod": "Credit card",
     *           "date": "2020-07-04",
     *           "daterecorded": "2020-07-04 15:49:56",
     *           "note": "",
     *           "transactionid": "0124875873",
     *           "invoiceid": "paypal",
     *           "description": "",
     *           "show_on_pdf": "0",
     *           "invoices_only": "0",
     *           "expenses_only": "0",
     *           "selected_by_default": "0",
     *           "active": "1",
     *           "paymentid": "4"
     *       }
     *   ]
     * @apiError {Boolean} paymentmode Request paymentmode.
     * @apiError {String} message No data were found.
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "paymentmode": false,
     *       "message": "No data were found"
     *     }
     */
    public function data_get($id = '') {
        // If the id parameter doesn't exist return all the
        $data = $this->Api_model->payment_get($id);
        // Check if the data store contains
        if ($data) {
            // Set the response and exit
            $this->response($data, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            
        } else {
            // Set the response and exit
            $this->response(['paymentmode' => FALSE, 'message' => 'No data were found'], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
            
        }
    }

    /**
     * @api {get} api/payments/search/:keysearch Search Payments Information
     * @apiVersion 0.3.0
     * @apiName GetPaymentSearch
     * @apiGroup Payments
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     * @apiParam {String} keysearch Search Keywords
     *
     * @apiSuccess {Array} Payments Payments information
     *
     * @apiSuccessExample Success-Response:
     *   HTTP/1.1 200 OK
     *   [
     *       {
     *           "id": "3",
     *           "invoiceid": "14",
     *           "amount": "8.00",
     *           "paymentmode": "2",
     *           "paymentmethod": "",
     *           "date": "2020-07-04",
     *           "daterecorded": "2020-07-04 15:47:30",
     *           "note": "",
     *           "transactionid": "",
     *           ...
     *       }
     *   ]
     *
     * @apiError {Boolean} paymentmode Request paymentmode
     * @apiError {String} message No data were found
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "paymentmode": false,
     *       "message": "No data were found"
     *     }
     */
    public function data_search_get($key = '') {
        // If the key parameter doesn't exist return all the
        $data = $this->Api_model->search('payments', $key);
        // Check if the data store contains
        if ($data) {
            // Set the response and exit
            $this->response($data, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            
        } else {
            // Set the response and exit
            $this->response(['paymentmode' => FALSE, 'message' => 'No data were found'], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
            
        }
    }
	
	
	



/**
 * @api {post} api/payments Add New Payment
 * @apiName PostPayment
 * @apiGroup Payments
 *
 * @apiHeader {String} authtoken Authentication token, generated from admin area
 *
 * @apiParam {String} invoiceid       Mandatory Invoice ID associated with the payment.
 * @apiParam {String} amount          Mandatory Payment amount.
 * @apiParam {String} paymentmode     Mandatory Payment mode (e.g., cash, credit card, etc.).
 * @apiParam {String} [paymentmethod] Optional Payment method details.
 * @apiParam {String} [note]          Optional Additional payment note.
 * @apiParam {String} [transactionid] Optional Transaction ID.
 * @apiParam {String} [custom_fields] Optional Custom fields data.
 *
 * @apiParamExample {Multipart Form} Request-Example:
 *  array (size=6)
 *     'invoiceid' => string '123' (length=3)
 *     'amount' => string '250.00' (length=6)
 *     'paymentmode' => string '1' (length=1)
 *     'paymentmethod' => string 'Visa' (length=4)
 *     'note' => string 'Payment for Invoice #123' (length=25)
 *     'transactionid' => string 'TXN123456789' (length=12)
 *     'custom_fields' => string '{"field1": "value1", "field2": "value2"}' (JSON format)
 *
 * @apiSuccess {Boolean} paymentmode Status of the request.
 * @apiSuccess {String} message Payment add successful.
 *
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "paymentmode": true,
 *       "message": "Payment add successful."
 *     }
 *
 * @apiError {Boolean} paymentmode Status of the request.
 * @apiError {String} message Payment add fail.
 *
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "paymentmode": false,
 *       "message": "Payment add fail."
 *     }
 */


    public function data_post() {
        \modules\api\core\Apiinit::the_da_vinci_code('api');
        
        // form validation
        $this->form_validation->set_rules('invoiceid', 'Payment Name', 'trim|required|max_length[600]', array('is_unique' => 'This %s already exists please enter another Payment Name'));
        $this->form_validation->set_rules('amount', 'Source', 'trim|required', array('is_unique' => 'This %s already exists please enter another Payment amount'));
        $this->form_validation->set_rules('paymentmode', 'Status', 'trim|required', array('is_unique' => 'This %s already exists please enter another Status'));
        if ($this->form_validation->run() == FALSE) {
            // form validation error
            $message = array('paymentmode' => FALSE, 'error' => $this->form_validation->error_array(), 'message' => validation_errors());
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        } else {
    $insert_data = [
        'invoiceid'      => $this->input->post('invoiceid', TRUE),
        'amount'         => $this->input->post('amount', TRUE),
        'paymentmode'    => $this->input->post('paymentmode', TRUE),
        'paymentmethod'  => $this->input->post('paymentmethod', TRUE),
        'date'           => date('Y-m-d H:i:s'), // Current date and time
        'daterecorded'   => date('Y-m-d H:i:s'), // Current date and time for recording
        'note'           => $this->input->post('note', TRUE), // Optional note
        'transactionid'  => $this->input->post('transactionid', TRUE)
    ];            if (!empty($this->input->post('custom_fields', TRUE))) {
                $insert_data['custom_fields'] = $this->Api_model->value($this->input->post('custom_fields', TRUE));
            }
            // insert data
            $this->load->model('payments_model');
            $output = $this->payments_model->add($insert_data);
			    return $this->db->insert_id(); // Ensure this returns a valid ID
            if ($output > 0 && !empty($output)) {
                // success
                $this->handle_payment_attachments_array($output);
                $message = array('paymentmode' => TRUE, 'message' => 'Payment add successful.');
                $this->response($message, REST_Controller::HTTP_OK);
            } else {
                // error
                $message = array('paymentmode' => FALSE, 'message' => 'Payment add fail.');
                $this->response($message, REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }
	
	
	
	
	
	
	
}














