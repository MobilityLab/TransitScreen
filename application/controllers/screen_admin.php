<?php

class Screen_admin extends CI_Controller {

  public function __construct() {

    parent::__construct();
    
    $this->is_logged_in();

  }

  public function index($msg = '') {
    
    $this->load->model('screen_model');
    $this->load->helper('render_admin_helper');

    $data['rows'] = $this->screen_model->get_screens_by_user_id();    
    $data['main_content'] = 'screen_listing';
    $data['msg'] = get_verbose_status($msg);

    $this->load->view('includes/template', $data);
  }

  public function is_logged_in() {
    $is_logged_in = $this->session->userdata('is_logged_in');

    if(!isset($is_logged_in) || $is_logged_in !== true ){
      echo 'You do not have permission to view this page.';
      die();
    }
  }

  public function edit($id = 0){
    $this->load->helper('render_admin');
    $this->load->model('screen_model');

    //if(!isset($id)){  // Setting the id to 0 will signal the system that we intend to create a new record
    // $id = 0;
    //}

    $data['rows'] = $this->screen_model->get_screen_values($id);
    
    $data['main_content'] = 'screen_editor';

    $this->load->view('includes/template', $data);
  }

  public function save($id = 0) {

    $this->load->model('screen_model');
    
    $updatevals = new Screen_model();
    $updatevals->id = $id;

    $postvars = $this->input->post();
    unset($postvars->submit);
    
    foreach($postvars as $key => $value){
      $updatevals->$key = $value;      
    }

    $updatevals->save_screen_values($id);

  }
 
  
}


?>
