<?php

class Screen extends CI_Controller {

  public function __construct() {
    parent::__construct(); 
  }

  public function index($screenid) {
    
    //If no parameter, redirect somewhere else
    if(!isset($screenid)){
      redirect('/');
    }

    $this->load->model('screen_model');

    $screen = new Screen_model();

    //Load variable of screen model type
    $screen->load_model($screenid);

    $data['id'] = $screenid;
    
    // Check for sleep mode to determine the view
    if($screen->is_asleep()) {     
      $data['template'] = 'three_col';
    }
    else {
      $data['numcols'] = $screen->get_num_columns();
      $data['zoom'] = $screen->zoom;

    
    }
    $this->load->view('includes/screen_template', $data);
  }

}


?>
