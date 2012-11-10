<?php

/**
 * Class: Update_model
 *
 * This is the model that represents updates.  It is called by the update
 * controller, which is itself invoked whenever a screen looks for an update.
 *
 */

class Update_model extends CI_Model {

  // These are properties set to defaults.
  var $screen_name = '';
  var $screen_version = 1323896592;
  var $sleep = false;
  var $stops = array();

  /**
   * Default constructor
   */
  public function __construct(){
    parent::__construct();
  }

  /**
   * Function load_model
   * @param int $id - the ide of the screen to load
   *
   * This function loads the modele's properties with screen values from the
   * database.
   *
   */
  public function load_model($id){
    $this->id = $id;
    //Query the screen data
    $this->db->select('MoTh_op, MoTh_cl, Fr_op, Fr_cl, Sa_op, Sa_cl, Su_op, Su_cl, name');
    $q = $this->db->get_where('screens',array('id' => $id));

    if ($q->num_rows() > 0) {
      //Place the screen data into the object
      $result = $q->result();
      foreach($result[0] as $key => $value) {
        $this->$key = $value;
      }
    }

    //Query the block data
    $this->db->select('id, stop, custom_name, column, position');    
    $q = $this->db->get_where('blocks',array('screen_id' => $this->id));

    //Place the data into the arrays of this object
    if ($q->num_rows() > 0) {
      
    }
  }

  /**
   * Function: get_screen_values
   * @param int $id - the id of the screen
   * @return array - return an array with the screen settings
   *
   * This function gets all the screen configuration values and puts them
   * into an array.  The array is then returned.
   *
   * MSC we should probably not have this duplicate the get_screen_values in screen_model.php
   */
  public function get_screen_values($id) {
    // Get all the screen's configuration values
    $this->db->select('id, MoTh_op, MoTh_cl, Fr_op, Fr_cl, Sa_op, Sa_cl, Su_op, Su_cl, name');
    if($id == 0){
      $q = $this->db->get('screens',1);
    }
    else {
      $q = $this->db->get_where('screens',array('id' => $id));
    }

    // Load the values in to an array that will be used 

    if ($q->num_rows() > 0) {
      foreach($q->result() as $row) {
        if($id == 0){
          foreach($row as $key => $value){
            $blankrow[$key] = '';
          }
          $row = $blankrow;
          $data['settings'][] = $row;
        }
        else {
          $data['settings'][] = $row;
        }
      }

      // Now get the individual block data for this screen.
      $this->db->select('id, stop, custom_name, column, position');
      $this->db->order_by('column', 'desc');
      $this->db->order_by('position', 'desc');
      $q = $this->db->get_where('blocks',array('screen_id' => $id));

      if($q->num_rows() > 0){
        foreach($q->result() as $row){
          $data['blocks'][] = $row;
        }
      }

      return $data;
    }
  }  

  


}

?>
