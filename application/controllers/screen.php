<?php
/**
 * Class: Screen
 *
 * This class represents a screen and orchestrates all the page construction
 * features for every screen.
 *
 */
class Screen extends CI_Controller {

  /**
   * Basic constructor
   */
  public function __construct() {
    parent::__construct(); 
  }

  /**
   * Function: index
   * @param int $screenid - This is the id of the screen you wish to load   *
   *
   * This calls the screen_wrapper view and passes it data, including the id
   * of the screen to load.
   */
  public function index($screenid) { 
    
    $data['id'] = $screenid;    
    $this->load->view('includes/screen_wrapper',$data);
    
  }

  /**
   * Function: inner
   * @param int $screenid - The id of the screen you wish to load
   *
   * This function differs from the index function in that this function, that is
   * /screen/inner is called by the inner IFRAME.
   */
  public function inner($screenid) {
    
    //If no parameter, redirect somewhere else
    if(!isset($screenid)){
      redirect('/');
    }

    // Load the screen model
    $this->load->model('screen_model');

    $screen = new Screen_model();

    //Load variables of screen model type
    $screen->load_model($screenid);

    $data['id'] = $screenid;
    
    // Check for sleep mode to determine the view.  If the screen is asleep,
    // just use the default three_col.  If the screen should be awake, set up
    // a $data variable and set the numcols to the number of columns and the
    // zoom level to the custom zoom level.
    if($screen->is_asleep()) {     
      $data['template'] = 'three_col';
    }
    else {
      $data['numcols'] = $screen->get_num_columns();
      $data['zoom'] = $screen->zoom;
    }

    // Call the screen_template view and pass the $data variable.  Each
    // element of the $data array will become a variable, i.e. $data['id'] will
    // become $id in the views
    $this->load->view('includes/screen_template', $data);
  }

}


?>
