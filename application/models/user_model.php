<?php

/**
 * Class: User_model
 *
 * This simple model represents a logged in user to the system.  Since Phase 1
 * development only accounts for an administrative user and not for the public
 * we the class's functions are limited to some basic function.
 *
 */

class User_model extends CI_Model {

  // Class properties.  Account type is for future use.
  public $id = '';
  public $username = '';
  public $password = '';
  public $admin = 0;

  /*
   * Generic constructor
   */
  public function __construct(){
    parent::__construct();

    // This is a Code Igniter feature.
    $this->output->enable_profiler(TRUE);

    // error reporting //
    error_reporting(E_ALL);


  }


  /**
   * Function: create_user
   *
   * @param array $data - an array of data used to create a user
   * @return NULL
   *
   * This is a function not in use right now, but can be used later when adding
   * functionality to create new users.
   *
   */
  public function create_user($data){

    $data->password = md5($data->password);

    if($this->db->insert($this->table, $data)) {
      $id = $this->db->insert_id();
    }
    return NULL;
  }

  /**
   * Function: get_user_by_id
   *
   * @param int $user_id - the user id of the user whose settings yo want to load
   * @return array or null
   *
   * This function returns user information from the db based on the id it has
   * been passed.  This may not be in actual use yet.
   *
   */
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

  /**
   * Function: get_user_by_username
   *
   * @param string $username
   * @return array or null
   *
   * This function returns user data based on the user name instead of the
   * user id.  This function may not be in use at this time.
   *
   */
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

  /**
   * Function: validate
   *
   * @return User_model
   *
   * This function validates the user credentials in the class properties
   * against what's stored in the db.  If the user name and password match a
   * record, then the user object is returned with the user name and id as
   * object properties.  This function is necessary to log users, including
   * the administrator, in properly.
   *
   */
  public function validate() {
    $this->db->where('email', $this->input->post('username'));
    $this->db->where('password', md5($this->input->post('password')));
    $query = $this->db->get('users');

    if($query->num_rows() == 1) {
      $row = $query->row();
      
      $user = new User_model();
      $user->id = $row->id;
      $user->admin = $row->admin; // 1 for admin, 0 for regular user
      $user->username = $row->email; // current db schema uses email

      return $user;
    }

  }


}

?>
