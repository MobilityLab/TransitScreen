<?php
/**
 * Class: Login
 *
 * This is the login class and is used exclusively for logging in admin users.
 * If you wish to handle more sophisticated tasks like password recovery or
 * assinging different users to different access priviledges, add the code in
 * here.
 *
 */
class Login extends CI_Controller {

  /**
   * Function: index()
   *
   * When a user tries to go to /login/, the MVC model for Code Igniter directs
   * the user to the index function of the login class.  In this case, the
   * function directs the user to the login template.
   */
  public function index() {        

    $data['main_content'] = 'login_form'; // Tells the system which form to load
    $this->load->view('includes/template', $data); // Loads this template
  }

  /**
   * Function: validate_credentials()
   *
   * Load the user model and attempt to log the user in.  If the credentials
   * validate, redirect the user to the /screen_admin/, otherwise, send them
   * back to the login page.
   */
  public function validate_credentials(){
    // Load the user model
    $this->load->model('user_model');
    $query = $this->user_model->validate();

    // If the user quety is successful...
    if($query) {
      $data = array(
          'username' => $this->input->post('username'),
          'is_logged_in' => true
      );

      // Create the user session.
      $this->session->set_userdata($data);
      // Redirect the user to the screen admin page.
      redirect('screen_admin');
    }
    else {
      // Otherwise, force the user to the login screen.
      $this->index();
    }
  }
  
}


?>
