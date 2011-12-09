<?php

class Login extends CI_Controller {
  
  public function index() {        

    $data['main_content'] = 'login_form';
    $this->load->view('includes/template', $data);
  }

  public function validate_credentials(){
    $this->load->model('user_model');
    $query = $this->user_model->validate();

    if($query) {
      $data = array(
          'username' => $this->input->post('username'),
          'is_logged_in' => true
      );

      $this->session->set_userdata($data);
      redirect('screen_admin');
    }
    else {
      $this->index();
    }
  }
  
}


?>
