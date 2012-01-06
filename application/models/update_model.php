<?php

/**
 * Update
 *
 * This is the update model
 *
 */

class Update_model extends CI_Model {

  var $screen_name = '';
  var $screen_version = 1323896592;
  var $sleep = false;
  var $stops = array();

  public function __construct(){
    parent::__construct();
  }

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
    //print $this->id; die;
    $q = $this->db->get_where('blocks',array('screen_id' => $this->id));

    //Place the data into the arrays of this object
    if ($q->num_rows() > 0) {
      
    }
  }
  
  public function get_screen_values($id) {
    $this->db->select('id, MoTh_op, MoTh_cl, Fr_op, Fr_cl, Sa_op, Sa_cl, Su_op, Su_cl, name');
    if($id == 0){
      $q = $this->db->get('screens',1);
    }
    else {
      $q = $this->db->get_where('screens',array('id' => $id));
    }

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

      //print_r($data);die;

      return $data;
    }
  }  

  


}

?>
