<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * GTSSolution verify
 */
class Gtsverify extends AdminController{
    public function __construct(){
        parent::__construct();
    }

    /**
     * index 
     * @return void
     */
    public function index(){
        show_404();
    }

    /**
     * activate
     * @return json
     */
    public function activate(){
        echo json_encode([
            'status' => true,
            'message' => 'License verification disabled for this fork.',
            'original_url' => $this->input->post('original_url'),
        ]);
    }    
}