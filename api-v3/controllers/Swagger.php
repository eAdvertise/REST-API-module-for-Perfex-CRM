<?php

require __DIR__ . '/REST_Controller.php';

defined('BASEPATH') or exit('No direct script access allowed');

class Swagger extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->library('app_modules');
    }

    public function index() {
        $data['title'] = 'Api Guide';
        $this->load->view('playground', $data);
    }

    public function json()
    {
        // v3: REST_Controller::get_swagger_file() never existed (fatal when
        // called) - serve the dynamic OpenAPI 3.1 spec instead.
        redirect(site_url('api/openapi.json'));
    }
}