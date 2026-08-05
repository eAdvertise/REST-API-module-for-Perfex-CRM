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
class Invoices extends REST_Controller {
    function __construct() {
        // Construct the parent class
        parent::__construct();
    }

    /**
     * Normalize custom_fields payload for invoices.
     *
     * Expected (typical) shape:
     *   { "invoice": { "<custom_field_id>": "<value>", ... } }
     *
     * Also accepts a flat shape and wraps it:
     *   { "<custom_field_id>": "<value>", ... }
     */
    private function normalize_invoice_custom_fields($custom_fields) {
        if (is_string($custom_fields)) {
            $decoded = json_decode($custom_fields, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $custom_fields = $decoded;
            }
        }

        if (!is_array($custom_fields) || empty($custom_fields)) {
            return [];
        }

        if (isset($custom_fields['invoice']) && is_array($custom_fields['invoice'])) {
            return $custom_fields;
        }

        $looks_flat = true;
        foreach ($custom_fields as $value) {
            if (is_array($value)) {
                $looks_flat = false;
                break;
            }
        }

        if ($looks_flat) {
            return ['invoice' => $custom_fields];
        }

        return $custom_fields;
    }

    /**
     * @api {get} api/invoices/:id Request invoice information
     * @apiVersion 0.1.0
     * @apiName GetInvoice
     * @apiGroup Invoices
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     * @apiError {Boolean} status Request status.
     * @apiError {String} message No data were found.
     *
     * @apiParam {Number} id Contact unique ID
     *
     * @apiSuccess {Object} Invoice Invoice information.
     *
     * @apiSuccessExample Success-Response:
     *   HTTP/1.1 200 OK
     *   {
     *       "id": "2",
     *       "sent": "0",
     *       "datesend": null,
     *       "clientid": "1",
     *       "deleted_customer_name": null,
     *       "number": "2",
     *       "prefix": "INV-",
     *       "number_format": "1",
     *       "datecreated": "2020-05-26 19:53:11",
     *       "date": "2020-05-26",
     *       "duedate": "2020-06-25",
     *       "currency": "1",
     *       "subtotal": "5.00",
     *       "total_tax": "0.00",
     *       "total": "5.00",
     *       "adjustment": "0.00",
     *       "addedfrom": "0",
     *       "hash": "7bfac86da004df5364407574d4d1dbf2",
     *       "status": "1",
     *       "clientnote": null,
     *       "adminnote": null,
     *       "last_overdue_reminder": null,
     *       "cancel_overdue_reminders": "0",
     *       "allowed_payment_modes": "['1']",
     *       "token": null,
     *       "discount_percent": "0.00",
     *       "discount_total": "0.00",
     *       "discount_type": "",
     *       "recurring": "0",
     *       "recurring_type": null,
     *       "custom_recurring": "0",
     *       "cycles": "0",
     *       "total_cycles": "0",
     *       "is_recurring_from": null,
     *       "last_recurring_date": null,
     *       "terms": null,
     *       "sale_agent": "0",
     *       "billing_street": "",
     *       "billing_city": "",
     *       "billing_state": "",
     *       "billing_zip": "",
     *       "billing_country": null,
     *       "shipping_street": null,
     *       "shipping_city": null,
     *       "shipping_state": null,
     *       "shipping_zip": null,
     *       "shipping_country": null,
     *       "include_shipping": "0",
     *       "show_shipping_on_invoice": "1",
     *       "show_quantity_as": "1",
     *       "project_id": "0",
     *       "subscription_id": "0",
     *       "symbol": "$",
     *       "name": "USD",
     *       "decimal_separator": ".",
     *       "thousand_separator": ",",
     *       "placement": "before",
     *       "isdefault": "1",
     *       "currencyid": "1",
     *       "currency_name": "USD",
     *       "total_left_to_pay": "5.00",
     *       "items": [
     *        {
     *           "id": "2",
     *           "rel_id": "2",
     *           "rel_type": "invoice",
     *           "description": "12MP Dual Camera with cover",
     *           "long_description": "The JBL Cinema SB110 is a hassle-free soundbar",
     *           "qty": "1.00",
     *           "rate": "5.00",
     *           "unit": "",
     *           "item_order": "1"
     *           }
     *       ],
     *       "attachments": [],
     *       "visible_attachments_to_customer_found": false,
     *       "client": {
     *       "userid": "1",
     *       "company": "trueline",
     *       "vat": "",
     *       "phonenumber": "",
     *       "country": "0",
     *       "city": "",
     *       "zip": "",
     *       "state": "",
     *       "address": "",
     *       "website": "",
     *       "datecreated": "2020-05-19 20:07:49",
     *       "active": "1",
     *       "leadid": null,
     *       "billing_street": "",
     *       "billing_city": "",
     *       "billing_state": "",
     *       "billing_zip": "",
     *       "billing_country": "0",
     *       "shipping_street": "",
     *       "shipping_city": "",
     *       "shipping_state": "",
     *       "shipping_zip": "",
     *       "shipping_country": "0",
     *       "longitude": null,
     *       "latitude": null,
     *       "default_language": "english",
     *       "default_currency": "0",
     *       "show_primary_contact": "0",
     *       "stripe_id": null,
     *       "registration_confirmed": "1",
     *       "addedfrom": "1"
     *   },
     *   "payments": [],
     *   "scheduled_email": null
     * }
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status": false,
     *       "message": "No data were found"
     *     }
     */
	public function data_get($id = '') {
		// Fetch invoices without specifying order
		$data = $this->Api_model->get_table('invoices', $id);
		
		// Check if the data store contains any invoices
		if ($data) {
			// Only sort if fetching multiple invoices (no ID provided)
			if (empty($id) && is_array($data)) {
				// Sort $data array by 'id' in ascending order
				usort($data, function($a, $b) {
					$a_id = is_array($a) ? $a['id'] : (isset($a->id) ? $a->id : 0);
					$b_id = is_array($b) ? $b['id'] : (isset($b->id) ? $b->id : 0);
					return $a_id - $b_id;
				});
			}
			
			// Optionally, apply additional custom data formatting if needed
			$data = $this->Api_model->get_api_custom_data($data, "invoice", $id);
			
			// Apply pagination if retrieving all invoices (no specific id)
			if (empty($id) && is_array($data)) {
				$data = $this->apply_pagination($data);
			}
			
			// Set the response and exit
			$this->response($data, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
		} else {
			// Set the response and exit with a not found message
			$this->response(['status' => FALSE, 'message' => 'No data were found'], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
		}
	}

    /**
     * @api {get} api/invoices/search/:keysearch Search invoice information
     * @apiVersion 0.1.0
     * @apiName GetInvoiceSearch
     * @apiGroup Invoices
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     * @apiParam {String} keysearch Search Keywords.
     *
     * @apiSuccess {Object} Invoice Information.
     *
     * @apiSuccessExample Success-Response:
     *  HTTP/1.1 200 OK
     *    {
     *        "id": "19",
     *        "sent": "0",
     *        "datesend": null,
     *        "clientid": "3",
     *        "deleted_customer_name": null,
     *        "number": "19",
     *        "prefix": "INV-",
     *        "number_format": "1",
     *        "datecreated": "2020-08-18 21:19:51",
     *        "date": "2020-07-04",
     *        "duedate": "2020-08-03",
     *        "currency": "1",
     *        "subtotal": "20.00",
     *        "total_tax": "1.80",
     *        "total": "21.80",
     *        "adjustment": "0.00",
     *        "addedfrom": "1",
     *        "hash": "809c0e4c9efba2a3bedfdb5871dc6240",
     *        "status": "2",
     *        "clientnote": "",
     *        "adminnote": "",
     *        "last_overdue_reminder": null,
     *        "cancel_overdue_reminders": "0",
     *        "allowed_payment_modes": "['1']",
     *        "token": null,
     *        "discount_percent": "0.00",
     *        "discount_total": "0.00",
     *        "discount_type": "",
     *        "recurring": "0",
     *        "recurring_type": null,
     *        "custom_recurring": "0",
     *        "cycles": "0",
     *        "total_cycles": "0",
     *        "is_recurring_from": null,
     *        "last_recurring_date": null,
     *        "terms": "",
     *        "sale_agent": "0",
     *        "billing_street": "",
     *        "billing_city": "",
     *        "billing_state": "",
     *        "billing_zip": "",
     *        "billing_country": "0",
     *        "shipping_street": "",
     *        "shipping_city": "",
     *        "shipping_state": "",
     *        "shipping_zip": "",
     *        "shipping_country": "0",
     *        "include_shipping": "0",
     *        "show_shipping_on_invoice": "1",
     *        "show_quantity_as": "1",
     *        "project_id": "0",
     *        "subscription_id": "0",
     *        "userid": "3",
     *        "company": "xyz",
     *        "vat": "",
     *        "phonenumber": "",
     *        "country": "0",
     *        "city": "",
     *        "zip": "",
     *        "state": "",
     *        "address": "",
     *        "website": "",
     *        "active": "1",
     *        "leadid": null,
     *        "longitude": null,
     *        "latitude": null,
     *        "default_language": "",
     *        "default_currency": "0",
     *        "show_primary_contact": "0",
     *        "stripe_id": null,
     *        "registration_confirmed": "1",
     *        "invoiceid": "19"
     *    }
     *
     * @apiError {Boolean} status Request status.
     * @apiError {String} message No data were found.
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status": false,
     *       "message": "No Data Were Found"
     *     }
     */
    public function data_search_get($key = '') {
        // Support both URL path parameter and query parameter for search term
        // This allows: /api/invoices/search/term OR /api/invoices/search?q=term
        // Query parameter is recommended for multi-word searches to avoid Apache mod_rewrite issues
        if (empty($key)) {
            $key = $this->get('q');
            if (empty($key)) {
                $key = $this->get('query');
            }
        }
        
        if (empty($key)) {
            $this->response(['status' => FALSE, 'message' => 'Search term is required. Use /api/invoices/search/{term} or /api/invoices/search?q={term}'], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }
        
        $data = $this->Api_model->search('invoices', $key);
        // Check if the data store contains
        if ($data) {
            $data = $this->Api_model->get_api_custom_data($data, "invoice");
            // Set the response and exit
            $this->response($data, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            
        } else {
            // Set the response and exit
            $this->response(['status' => FALSE, 'message' => 'No data were found'], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
            
        }
    }

    /**
     * @api {post} api/invoices Add New invoice
     * @apiVersion 0.1.0
     * @apiName PostInvoice
     * @apiGroup Invoices
     *
     *  @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     *  @apiParam {Number}  clientid                       Mandatory. Customer id
     *	@apiParam {Number}  number                         Mandatory. Invoice Number
     *	@apiParam {Date}    date                           Mandatory. Invoice Date
     *	@apiParam {Number}  currency                       Mandatory. currency field
     *  @apiParam {Array}   newitems               	 	   Mandatory. New Items to be added
     *  @apiParam {Decimal}   subtotal               	   Mandatory. calculation based on item Qty, Rate and Tax
     *  @apiParam {Decimal}   total               	 	   Mandatory. calculation based on subtotal, Discount and Adjustment
     *	@apiParam {String}  billing_street                 Mandatory. Street Address
     *  @apiParam {Array} 	allowed_payment_modes          Mandatory. Payment modes
     *	@apiParam {String}  [billing_city]                 Optional. City Name for billing
     *	@apiParam {String}  [billing_state]                Optional. Name of state for billing
     *	@apiParam {Number}  [billing_zip]                  Optional. Zip code
     *	@apiParam {Number}  [billing_country]              Optional. Country code
     *	@apiParam {boolean} [include_shipping="no"]        Optional. set yes if you want add Shipping Address
     *	@apiParam {boolean} [show_shipping_on_invoice]     Optional. Shows shipping details in invoice.
     *	@apiParam {String}  [shipping_street]              Optional. Address of shipping
     *	@apiParam {String}  [shipping_city]                Optional. City name for shipping
     *	@apiParam {String}  [shipping_state]               Optional. Name of state for shipping
     *	@apiParam {Number}  [shipping_zip]                 Optional. Zip code for shipping
     *	@apiParam {Number}  [shipping_country]             Optional. Country code
     *	@apiParam {Date}    [duedate]                      Optional. Due date for Invoice
     *	@apiParam {boolean} [cancel_overdue_reminders] 	   Optional. Prevent sending overdue remainders for invoice
     *	@apiParam {String}  [tags]                         Optional. TAGS comma separated
     *	@apiParam {Number}  [sale_agent]                   Optional. Sale Agent name
     *	@apiParam {String}  [recurring]                    Optional. recurring 1 to 12 or custom
     *	@apiParam {String}  [discount_type]                Optional. before_tax / after_tax discount type
     *	@apiParam {Number}  [repeat_every_custom]          Optional. if recurring is custom set number gap
     *	@apiParam {String}  [repeat_type_custom]           Optional. if recurring is custom set gap option day/week/month/year
     *	@apiParam {Number}  [cycles]                       Optional. number of cycles 0 for infinite
     *	@apiParam {String}  [adminnote]                    Optional. notes by admin
     *	@apiParam {Array}   [removed_items]				   Optional. Items to be removed
     *	@apiParam {String} 	[clientnote]       			   Optional. client notes
     *	@apiParam {String} 	[terms]                   	   Optional. Terms
     *
     *  @apiParamExample {Multipart Form} Request-Example:
     *   [
     *		"clientid"=>1,
     *		"number"=>"00001",
     *		"date"=>"2020-09-07",
     *		"currency"=>1,
     *		"newitems[0][description]"=>"item 1 description",
     *		"newitems[0][long_description]"=>"item 1 long description",
     *		"newitems[0][qty]"=>1,
     *		"newitems[0][rate]"=>100,
     *		"newitems[0][order]"=>1,
     * 		"newitems[0][taxname][]"=>CGST|9.00,
     * 		"newitems[0][taxname][]"=>SGST|9.00,
     * 		"newitems[0][unit]"=>"",
     * 		"newitems[1][description]"=>"item 2 description",
     *		"newitems[1][long_description]"=>"item 2 long description",
     *		"newitems[1][qty]"=>1,
     *		"newitems[1][rate]"=>100,
     *		"newitems[1][order]"=>1,
     * 		"newitems[1][taxname][]"=>CGST|9.00,
     * 		"newitems[1][taxname][]"=>SGST|9.00,
     * 		"newitems[1][unit]"=>"",
     *		"subtotal"=>236.00,
     *		"total"=>236.00,
     *		"billing_street"=>"billing address",
     * 		"allowed_payment_modes[0]"=>1,
     * 		"allowed_payment_modes[1]"=>2,
     * 		....
     *	]
     *
     *
     * @apiSuccess {Boolean} status Request status.
     * @apiSuccess {String} message Invoice Added Successfully
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": true,
     *       "message": "Invoice Added Successfully"
     *     }
     *
     * @apiError {Boolean} status Request status.
     * @apiError {String} message Invoice add fail
     * @apiError {String} newitems[] The Items field is required
     * @apiError {String} number The Invoice number is already in use
     * @apiError {String} allowed_payment_modes[] The Allow Payment Mode field is required
     * @apiError {String} billing_street The Billing Street field is required
     * @apiError {String} subtotal The Sub Total field is required
     * @apiError {String} total The Total field is required
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status": false,
     *       "message": "Invoice Add Fail"
     *     }
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 409 Conflict
     *     {
     *       "status": false,
     *       "error": {
     *			"number":"The Invoice number is already in use"
     *		},
     * 		"message": "The Invoice number is already in use"
     *     }
     *
     * @apiErrorExample Error-Response:
     *   HTTP/1.1 404 Not Found
     *    {
     *	    "status": false,
     *	    "error": {
     *	        "allowed_payment_modes[]": "The Allow Payment Mode field is required."
     *	    },
     *	    "message": "<p>The Allow Payment Mode field is required.</p>\n"
     *	}
     *
     * @apiErrorExample Error-Response:
     *   HTTP/1.1 404 Not Found
     *    {
     *	    "status": false,
     *	    "error": {
     *	        "billing_street": "The Billing Street field is required"
     *	    },
     *	    "message": "<p>The Billing Street field is required</p>\n"
     *	}
     *
     * @apiErrorExample Error-Response:
     *   HTTP/1.1 404 Not Found
     *    {
     *	    "status": false,
     *	    "error": {
     *	        "newitems[]": "The Items field is required"
     *	    },
     *	    "message": "<p>The Items field is required</p>\n"
     *	}
     *
     * @apiErrorExample Error-Response:
     *   HTTP/1.1 404 Not Found
     *    {
     *	    "status": false,
     *	    "error": {
     *	        "subtotal": "The Sub Total field is required"
     *	    },
     *	    "message": "<p>The Sub Total field is required</p>\n"
     *	}
     *
     *  @apiErrorExample Error-Response:
     *   HTTP/1.1 404 Not Found
     *    {
     *	    "status": false,
     *	    "error": {
     *	        "total": "The Total field is required"
     *	    },
     *	    "message": "<p>The Total field is required</p>\n"
     *	}
     *
     */
    public function data_post() {
        \modules\api\core\Apiinit::the_da_vinci_code('api');

		error_reporting(0);
        // v3: accept GET-shaped items[] and auto-calculate subtotal/total server-side
        $this->api_normalize_line_items();
        $this->api_autocalculate_totals();
        $data = $this->input->post();
        $this->form_validation->set_rules('clientid', 'Customer', 'trim|required|max_length[255]');
        $this->form_validation->set_rules('number', 'Invoice number', 'trim|required|max_length[255]|callback_validate_invoice_number[0]');
        $this->form_validation->set_rules('date', 'Invoice date', 'trim|required|max_length[255]');
        $this->form_validation->set_rules('currency', 'Currency', 'trim|required|max_length[255]');
        $this->form_validation->set_rules('newitems[]', 'Items', 'required');
        $this->form_validation->set_rules('allowed_payment_modes[]', 'Allow Payment Mode', 'trim|required|max_length[255]');
        $this->form_validation->set_rules('billing_street', 'Billing Street', 'trim|required|max_length[255]');
        $this->form_validation->set_rules('subtotal', 'Sub Total', 'trim|required|decimal|greater_than[0]');
        $this->form_validation->set_rules('total', 'Total', 'trim|required|decimal|greater_than[0]');
        if ($this->form_validation->run() == FALSE) {
            $message = array('status' => FALSE, 'error' => $this->form_validation->error_array(), 'message' => validation_errors());
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        } else {
            $this->load->model('invoices_model');
            $id = $this->invoices_model->add($data);
            if ($id > 0 && !empty($id)) {
				
                // success
                $message = array('status' => TRUE, 'message' => 'Invoice Added Successfully', 'record_id' => $id);
                $this->response($message, REST_Controller::HTTP_OK);
            } else {
                // error
                $message = array('status' => FALSE, 'message' => 'Invoice Add Fail');
                $this->response($message, REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    /**
     * @api {delete} api/invoices/:id Delete invoice
     * @apiVersion 0.1.0
     * @apiName DeleteInvoice
     * @apiGroup Invoices
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     * @apiSuccess {Boolean} status Request status.
     * @apiSuccess {String} message Invoice Deleted Successfully
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": true,
     *       "message": "Invoice Deleted Successfully"
     *     }
     *
     * @apiError {Boolean} status Request status.
     * @apiError {String} message Invoice Delete Fail
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status": false,
     *       "message": "Invoice Delete Fail"
     *     }
     */
    public function data_delete($id = '') {
        $id = $this->security->xss_clean($id);
        if (empty($id) && !is_numeric($id)) {
            $message = array('status' => FALSE, 'message' => 'Invalid Invoice ID');
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        } else {
            $this->load->model('invoices_model');
            $is_exist = $this->invoices_model->get($id);
            if (is_object($is_exist)) {
                $output = $this->invoices_model->delete($id);
                if ($output === TRUE) {
                    // success
                    $message = array('status' => TRUE, 'message' => 'Invoice Deleted Successfully');
                    $this->response($message, REST_Controller::HTTP_OK);
                } else {
                    // error
                    $message = array('status' => FALSE, 'message' => 'Invoice Delete Fail');
                    $this->response($message, REST_Controller::HTTP_NOT_FOUND);
                }
            } else {
                $message = array('status' => FALSE, 'message' => 'Invalid Invoice ID');
                $this->response($message, REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }
	
    /**
     * @api {post} api/invoices/:id/send Send invoice by email
     * @apiVersion 3.0.2
     * @apiName SendInvoice
     * @apiGroup Invoices
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     * @apiDescription Emails the invoice to the customer using Perfex's own invoice email
     * template, PDF attachment and SMTP configuration, and marks the invoice as sent.
     * Requires the Invoices "Send email" permission. Send an `Idempotency-Key` header to
     * make retries safe. The customer must have at least one contact flagged to receive
     * invoice emails, and Setup -> Email (SMTP) must be configured or nothing is sent.
     *
     * @apiParam {String} [cc] Optional comma-separated CC email addresses.
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": true,
     *       "invoice_id": "847",
     *       "invoice_number": "INV-000832",
     *       "sent": true,
     *       "datesend": "2026-07-30 14:05:11",
     *       "message": "Invoice sent to the customer."
     *     }
     *
     * @apiErrorExample {json} Not-Sent:
     *     HTTP/1.1 500 Internal Server Error
     *     {
     *       "status": false,
     *       "invoice_id": "847",
     *       "sent": false,
     *       "message": "Invoice could not be sent. Check SMTP settings and that the customer has a contact set to receive invoice emails."
     *     }
     */
    public function send_post($id = '') {
        \modules\api\core\Apiinit::the_da_vinci_code('api');

        if (empty($id) || !is_numeric($id)) {
            $this->response(['status' => FALSE, 'message' => 'Invalid Invoice ID'], REST_Controller::HTTP_BAD_REQUEST);
        }

        $this->load->model('invoices_model');
        $invoice = $this->invoices_model->get($id);
        if (!$invoice) {
            $this->response(['status' => FALSE, 'message' => 'Invoice not found'], REST_Controller::HTTP_NOT_FOUND);
        }

        $cc   = (string) ($this->input->post('cc') ?: '');
        $sent = $this->invoices_model->send_invoice_to_client($id, '', true, $cc, true);

        if ($sent) {
            $updated = $this->invoices_model->get($id);
            $this->response([
                'status'         => TRUE,
                'invoice_id'     => (string) $id,
                'invoice_number' => format_invoice_number($id),
                'sent'           => TRUE,
                'datesend'       => ($updated && !empty($updated->datesend)) ? $updated->datesend : date('Y-m-d H:i:s'),
                'message'        => 'Invoice sent to the customer.',
            ], REST_Controller::HTTP_OK);
        }

        $this->response([
            'status'     => FALSE,
            'invoice_id' => (string) $id,
            'sent'       => FALSE,
            'message'    => 'Invoice could not be sent. Check SMTP settings (Setup -> Email) and that the customer has a contact set to receive invoice emails.',
        ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @api {put} api/invoices/:id Update invoice
     * @apiVersion 0.1.0
     * @apiName PutInvoice
     * @apiGroup Invoices
     *
     * @apiHeader {String} authtoken Authentication token, generated from admin area
     *
     *	@apiParam {Number}  clientid                     Mandatory Customer id.
     *
     *  @apiParam {Number}  clientid                       Mandatory. Customer id
     *	@apiParam {Number}  number                         Mandatory. Invoice Number
     *	@apiParam {Date}    date                           Mandatory. Invoice Date
     *	@apiParam {Number}  currency                       Mandatory. currency field
     *  @apiParam {Array}   newitems               	 	   Mandatory. New Items to be added
     *  @apiParam {Decimal}   subtotal               	   Mandatory. calculation based on item Qty, Rate and Tax
     *  @apiParam {Decimal}   total               	 	   Mandatory. calculation based on subtotal, Discount and Adjustment
     *	@apiParam {String}  billing_street                 Mandatory. Street Address
     *  @apiParam {Array} 	allowed_payment_modes          Mandatory. Payment modes
     *	@apiParam {String}  [billing_city]                 Optional. City Name for billing
     *	@apiParam {String}  [billing_state]                Optional. Name of state for billing
     *	@apiParam {Number}  [billing_zip]                  Optional. Zip code
     *	@apiParam {Number}  [billing_country]              Optional. Country code
     *	@apiParam {boolean} [include_shipping="no"]        Optional. set yes if you want add Shipping Address
     *	@apiParam {boolean} [show_shipping_on_invoice]     Optional. Shows shipping details in invoice.
     *	@apiParam {String}  [shipping_street]              Optional. Address of shipping
     *	@apiParam {String}  [shipping_city]                Optional. City name for shipping
     *	@apiParam {String}  [shipping_state]               Optional. Name of state for shipping
     *	@apiParam {Number}  [shipping_zip]                 Optional. Zip code for shipping
     *	@apiParam {Number}  [shipping_country]             Optional. Country code
     *	@apiParam {Date}    [duedate]                      Optional. Due date for Invoice
     *	@apiParam {boolean} [cancel_overdue_reminders] 	   Optional. Prevent sending overdue remainders for invoice
     *	@apiParam {String}  [tags]                         Optional. TAGS comma separated
     *	@apiParam {Number}  [sale_agent]                   Optional. Sale Agent name
     *	@apiParam {String}  [recurring]                    Optional. recurring 1 to 12 or custom
     *	@apiParam {String}  [discount_type]                Optional. before_tax / after_tax discount type
     *	@apiParam {Number}  [repeat_every_custom]          Optional. if recurring is custom set number gap
     *	@apiParam {String}  [repeat_type_custom]           Optional. if recurring is custom set gap option day/week/month/year
     *	@apiParam {Number}  [cycles]                       Optional. number of cycles 0 for infinite
     *	@apiParam {String}  [adminnote]                    Optional. notes by admin
     *	@apiParam {Array}   [items]                        Optional. Existing items with Id
     *	@apiParam {Array}   [removed_items]				   Optional. Items to be removed
     *	@apiParam {String} 	[clientnote]       			   Optional. client notes
     *	@apiParam {String} 	[terms]                   	   Optional. Terms
     *
     *  @apiParamExample {json} Request-Example:
     *  {
     *	    "clientid": "1",
     *	    "billing_street": "billing address",
     *	    "billing_city": "billing city name",
     *	    "billing_state": "billing state name",
     *	    "billing_zip": "billing zip code",
     *	    "billing_country": "",
     *	    "include_shipping": "on",
     *	    "show_shipping_on_invoice": "on",
     *	    "shipping_street": "shipping address",
     *	    "shipping_city": "city name",
     *	    "shipping_state": "state name",
     *	    "shipping_zip": "zip code",
     *	    "shipping_country": "",
     *	    "number": "000001",
     *	    "date": "2020-08-28",
     *	    "duedate": "2020-09-27",
     *	    "cancel_overdue_reminders": "on",
     *	    "tags": "TAG 1,TAG 2",
     *	    "allowed_payment_modes": [
     *	        "1","2"
     *	    ],
     *	    "currency": "1",
     *	    "sale_agent": "1",
     *	    "recurring": "custom",
     *	    "discount_type": "before_tax",
     *	    "repeat_every_custom": "7",
     *	    "repeat_type_custom": "day",
     *	    "cycles": "0",
     *	    "adminnote": "TEST",
     *	    "show_quantity_as": "1",
     *	    "items": {
     *	        "1": {
     *	            "itemid": "1",
     *	            "order": "1",
     *	            "description": "item description",
     *	            "long_description": "item long description",
     *	            "qty": "1",
     *	            "unit": "1",
     *	            "rate": "10.00"
     *	        }
     *	    },
     *	    "removed_items": [
     *	        "2",
     *	        "3"
     *	    ],
     *	    "newitems": {
     *	        "2": {
     *	            "order": "2",
     *	            "description": "item 2 description",
     *	            "long_description": "item 2 logn description",
     *	            "qty": "1",
     *	            "unit": "",
     *	            "rate": "100.00"
     *	        }
     *	    },
     *	    "subtotal": "10.00",
     *	    "discount_percent": "10",
     *	    "discount_total": "1.00",
     *	    "adjustment": "1",
     *	    "total": "10.00",
     *	    "clientnote": "client note",
     *	    "terms": "terms"
     *	}
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": false,
     *       "message": "Invoice Updated Successfully"
     *     }
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status": false,
     *       "message": "Invoice Update Fail"
     *     }
     *
     *  @apiError {String} number The Invoice number is already in use
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 409 Conflict
     *     {
     *       "status": false,
     *       "error": {
     *			"number":"The Invoice number is already in use"
     *		},
     * 		"message": "The Invoice number is already in use"
     *     }
     *
     */
    public function data_put($id = '') {
        // v3: accept GET-shaped items[] and auto-calculate totals on update too
        $this->api_normalize_line_items();
        $this->api_autocalculate_totals();
        // JSON data is now automatically parsed in REST_Controller
        if (empty($_POST) || !isset($_POST)) {
            $message = array('status' => FALSE, 'message' => 'Data Not Acceptable OR Not Provided');
            $this->response($message, REST_Controller::HTTP_NOT_ACCEPTABLE);
        }
        if (empty($id) && !is_numeric($id)) {
            $message = array('status' => FALSE, 'message' => 'Invalid Invoice ID');
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        } else {
            $raw_data = $this->input->post();
            $custom_fields = $this->input->post('custom_fields', TRUE);
            if (!empty($custom_fields)) {
                $custom_fields = $this->normalize_invoice_custom_fields($custom_fields);
            } else {
                $custom_fields = [];
            }
            $core_data = $raw_data;
            unset($core_data['custom_fields']);

            // Support safe custom-fields-only updates, same as Items behavior.
            if (empty($core_data) && !empty($custom_fields)) {
                $this->load->model('invoices_model');
                $is_exist = $this->invoices_model->get($id);
                if (!is_object($is_exist)) {
                    $message = array('status' => FALSE, 'message' => 'Invoice ID Doesn\'t Not Exist.');
                    $this->response($message, REST_Controller::HTTP_CONFLICT);
                }
                $this->load->model('custom_fields_model');
                $this->custom_fields_model->handle_custom_fields_post($id, $custom_fields, false, false);
                $message = array('status' => TRUE, 'message' => "Invoice Updated Successfully",);
                $this->response($message, REST_Controller::HTTP_OK);
            }

            $this->form_validation->set_rules('number', 'Invoice number', 'trim|required|max_length[255]|callback_validate_invoice_number[' . $id . ']');
            $this->form_validation->set_rules('date', 'Invoice date', 'trim|required|max_length[255]');
            $this->form_validation->set_rules('currency', 'Currency', 'trim|required|max_length[255]');
            $this->form_validation->set_rules('items[]', 'Items', 'required');
            $this->form_validation->set_rules('allowed_payment_modes[]', 'Allow Payment Mode', 'trim|required|max_length[255]');
            $this->form_validation->set_rules('billing_street', 'Billing Street', 'trim|required|max_length[255]');
            $this->form_validation->set_rules('subtotal', 'Sub Total', 'trim|required|decimal|greater_than[0]');
            $this->form_validation->set_rules('total', 'Total', 'trim|required|decimal|greater_than[0]');
            if ($this->form_validation->run() == FALSE) {
                $message = array('status' => FALSE, 'error' => $this->form_validation->error_array(), 'message' => validation_errors());
                $this->response($message, REST_Controller::HTTP_CONFLICT);
            } else {
                $this->load->model('invoices_model');
                $is_exist = $this->invoices_model->get($id);
                if (!is_object($is_exist)) {
                    $message = array('status' => FALSE, 'message' => 'Invoice ID Doesn\'t Not Exist.');
                    $this->response($message, REST_Controller::HTTP_CONFLICT);
                }
                if (is_object($is_exist)) {
                    $data = $this->input->post();
                    $data['isedit'] = "";
                    $success = $this->invoices_model->update($data, $id);
                    if ($success == true) {
                        $message = array('status' => TRUE, 'message' => "Invoice Updated Successfully",);
                        $this->response($message, REST_Controller::HTTP_OK);
                    } else {
                        // error
                        $message = array('status' => FALSE, 'message' => 'Invoice Update Fail');
                        $this->response($message, REST_Controller::HTTP_NOT_FOUND);
                    }
                } else {
                    $message = array('status' => FALSE, 'message' => 'Invalid Invoice ID');
                    $this->response($message, REST_Controller::HTTP_NOT_FOUND);
                }
            }
        }
    }

    public function validate_invoice_number($number, $invoiceid) {
        $isedit = 'false';
        if (!empty($invoiceid)) {
            $isedit = 'true';
        }
        $this->form_validation->set_message('validate_invoice_number', 'The {field} is already in use');
        $original_number = null;
        $date = $this->input->post('date');
        if (!empty($invoiceid)) {
            $data = $this->Api_model->get_table('invoices', $invoiceid);
            $original_number = $data->number;
            if (empty($date)) {
                $date = $data->date;
            }
        }
        $number = trim($number);
        $number = ltrim($number, '0');
        if ($isedit == 'true') {
            if ($number == $original_number) {
                return TRUE;
            }
        }
        if (total_rows(db_prefix() . 'invoices', ['YEAR(date)' => date('Y', strtotime(to_sql_date($date))), 'number' => $number, ]) > 0) {
            return FALSE;
        } else {
            return TRUE;
        }
    }
}
