<?php

/**
 * Users
 *
 * This is the user account model
 *
 */

class User_model extends CI_Model {

  public $id = '';
  public $username = '';
  public $password = '';
  public $account_type = '';

  public function __construct(){
    parent::__construct();

    //$this->table = 'users';

    // codeigniter profiler //
    $this->output->enable_profiler(TRUE);

    // error reporting //
    error_reporting(E_ALL);


  }

  public function create_user($data){

    $data->password = md5($data->password);

    if($this->db->insert($this->table, $data)) {
      $id = $this->db->insert_id();
    }
    return NULL;
  }

  public function update_user($data){

  }
  
  public function get_user_by_id($user_id) {    
    $this->db->where('id',$user_id);    
    $query = $this->db->get($this->table);
    
    if($query->num_rows() == 1){
      return $query->row();      
    }
    else {      
      return NULL;
    }    
  }
  
  public function get_user_by_username($username){
    $this->db->where('email',$username);
    $query = $this->db->get($this->table);

    if($query->num_rows() == 1){
      return $query->row();
    }
    else {
      return NULL;
    }
  }

  public function validate() {
    $this->db->where('email', $this->input->post('username'));
    $this->db->where('password', md5($this->input->post('password')));
    $query = $this->db->get('users');

    if($query->num_rows() == 1){
      $row = $query->row();
      
      $user = new User_model();
      $user->id = $row->id;
      $user->username = $row->username;

      return $user;
    }

  }


}

?>
