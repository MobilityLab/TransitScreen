<?php

class Blog extends CI_Controller {

  public function  __construct() {
    parent::__construct();
    //default values, etc
  }

  public function index() {
    $user = $this->load->model('user_model');

    $this->load->helper('form');


    $data['title'] = 'Moo';
    $this->load->view('blogview', $data);
    $this->load->view('stopview');



  }

  public function comments() {
    echo 'Look at this';
  }

  private function _secrets() {
    echo 'You called it!';
  }
  
}

?>