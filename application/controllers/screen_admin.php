<?php
/**
 * Class: Screen_admin
 *
 * This controller is used only for the screen administration pages that an
 * administrator sees.
 *
 */
class Screen_admin extends CI_Controller {

  /**
   * This is the contructor and differs from most other constructors.  This one
   * checks to make sure that a user trying to load any screen_admin page is
   * logged in.  This ensures that unauthenticated users can't access the admin
   * section.
   */
  public function __construct() {

    parent::__construct();
    
    $this->is_logged_in();

  }

  /**
   * Function: index
   * @param string $msg - A status message, e.g. "success"  (optional)
   *
   * This function is called after a user successfully logs in.  It's like the
   * "homepage" for the screen_admin section.     *
   *
   */
  public function index($msg = '') {

    // Load the screen model
    $this->load->model('screen_model');
    // Load the render_admin helper.  This helper provides a few functions
    // to clean up code names and replace them with human readable names.
    $this->load->helper('render_admin_helper');

    // Establish a $data variable for passing to a view.  Include the screens in 'rows',
    // the template to load in 'main_content', and any sort of status message in 'msg'.
    $data['rows'] = $this->screen_model->get_screens_by_user_id();    
    $data['main_content'] = 'screen_listing';
    $data['msg'] = get_verbose_status($msg);

    // Load the admin template and pass all the data to it to render.
    $this->load->view('includes/template', $data);
  }

  /**
   * Function: is_logged_in
   *
   * Check the session data for this user to ensure they're logged in.  If not,
   * redirect the user to the home page, which is a blank url from the root, i.e.
   * ''
   */
  public function is_logged_in() {
    $is_logged_in = $this->session->userdata('is_logged_in');

    if(!isset($is_logged_in) || $is_logged_in !== true ){
      redirect('');      
    }
  }

  /**
   * Function: edit
   *
   * This function builds the page that allows an admin to edit a specific
   * screen.  It creates a $data variable with the necessary information and
   * passes it to the view, which loads the screen_editor template.
   *
   * @param int $id - the id of the screen to edit
   */
  public function edit($id = 0){
    // Load this helper.  (Helpers are in /application/helpers)
    $this->load->helper('render_admin');
    // Load the screen model
    $this->load->model('screen_model');

    // Get all the configuration and set up values for this screen and store
    // the values in $data['rows']
    $data['rows'] = $this->screen_model->get_screen_values($id);

    // Tell the admin page template to load the screen_editor template in the
    // main content section.
    $data['main_content'] = 'screen_editor';

    // Load the view and pass the data.
    $this->load->view('includes/template', $data);
  }

  /**
   * Function: save
   *
   * @param int $id - the id of the screen for which data should be saved
   *
   * This function saves configuration changes for screens.  Most of it is
   * straightforward.  Since the blocks and agency-stop pairs are stored in
   * different tables, the function pulls those data out and saves them in their
   * respective tables.
   *
   */
  public function save($id = 0) {

    // Load the screen model
    $this->load->model('screen_model');

    // Create a placehold screen that will be filled with variables and then saved.
    $updatevals = new Screen_model();
    $updatevals->id = $id;

    // Collect all the posted variables from the HTML form into one variable for
    // easier access.  Delete the submit "variable", which is really just the
    // submit button.
    $postvars = $this->input->post();
    unset($postvars->submit);

    // For each of the variables, check to see if the variable name ends with _op
    // or _cl.  If so, these variables are the sleep and wake times for the screen.
    // We only want to write them if they have values.  Beware that Code Igniter
    // may treat blank submission as an empty string, which cannot be written
    // to a timestamp type in PostgreSQL.
    foreach($postvars as $key => $value){
      if(substr($key, strlen($key)-3) == '_op' || substr($key, strlen($key)-3) == '_cl'){
        if(strlen($value) > 0){
          $updatevals->$key = $value;
        }
      }
      else {
        $updatevals->$key = $value;
      }
    }

    // Call a function that now writes these values to the database.
    $updatevals->save_screen_values($id);

    // Redirect the user back to the edit screen to see the changes he just
    // made.  If he created a new screen, he may be directed back to the
    // screen listing page.
    if($id > 0){
      redirect("screen_admin/edit/$id");
    }
    else {
      redirect('screen_admin');
    }

  } 
  
}

?>
