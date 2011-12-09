<?php

/**
 * Screen
 *
 * This is the screen model
 *
 */

class Screen_model extends CI_Model {
  
  var $id = '';
  var $MoTh_op = '';
  var $MoTh_cl = '';
  var $Fr_op = '';
  var $Fr_cl = '';
  var $Sa_op = '';
  var $Sa_cl = '';
  var $Su_op = '';
  var $Su_cl = '';
  var $name = '';
  var $stop_ids = array();
  var $stop_names = array();
  var $stop_columns = array();
  var $new_stop_ids = array();
  var $new_stop_names = array();
  var $new_stop_columns = array();

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
    $this->db->select('id, stop, custom_name, column');
    //print $this->id; die;
    $q = $this->db->get_where('blocks',array('screen_id' => $this->id));

    //Place the data into the arrays of this object
    if ($q->num_rows() > 0) {
      foreach($q->result() as $row){
        $newidrow[$row->id] = $row->stop;
        $newnamerow[$row->id] = $row->custom_name;
        $newcolumnrow[$row->id] = $row->column;

        $this->stop_ids[] = $newidrow[$row->id];
        $this->stop_names[] = $newnamerow[$row->id];
        $this->stop_columns[] = $newcolumnrow[$row->id];
      }
    }
  }

  // For now this function will get all screens
  public function get_screens_by_user_id($id = 0){
    $this->db->order_by('name', 'asc');
    $q = $this->db->get('screens');

    if($q->num_rows() > 0) {
      foreach($q->result() as $row) {
        $data[] = $row;
      }

      return $data;
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
      $this->db->select('id, stop, custom_name, column');
      $this->db->order_by('column', 'asc');
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

  public function save_screen_values($id) {

    $msg = '';

    $data = array(
      'MoTh_op' => $this->MoTh_op,
      'MoTh_cl' => $this->MoTh_cl,
      'Fr_op' => $this->Fr_op,
      'Fr_cl' => $this->Fr_cl,
      'Sa_op' => $this->Sa_op,
      'Sa_cl' => $this->Sa_cl,
      'Su_op' => $this->Su_op,
      'Su_cl' => $this->Su_cl,
      'name' => $this->name
    );

    if($id > 0){ // If updating, instead of inserting anew...
      $this->db->where('id', $id);
      $this->db->update('screens', $data);
      $msg = 'success';
    }
    else {
      $this->db->insert('screens',$data);
      $msg = 'created';
    }
    
    // Block updates:
    foreach($this->stop_ids as $key => $value){      
      unset($blockdata);
      $blockdata = array (
        'stop'        => $value,
        'custom_name' => $this->stop_names[$key],
        'column'     => $this->stop_columns[$key]
      );
      
      //print $blockdata['stop'] . ' | ' . $blockdata['custom_name'] . '<br/>';

      if(strlen(trim($blockdata['stop'])) == 0 && strlen(trim($blockdata['custom_name'])) == 0){
        //print "Delete $key: " . $blockdata['stop'] . ' | ' . $blockdata['custom_name'] . '<br/>';
        $this->db->delete('blocks', array('id' => $key));
      }
      else {        
        //print "Update $key: " . $blockdata['stop'] . ' | ' . $blockdata['custom_name'] . '<br/>';
        $this->db->where('id', $key);
        $this->db->update('blocks', $blockdata);
      }      
    }

    foreach($this->new_stop_ids as $key => $value){
      unset($blockdata);
      if(strlen(trim($value)) > 0){
        $blockdata = array (
          'stop'        => $value,
          'custom_name' => $this->new_stop_names[$key],
          'screen_id'   => $id,
          'column'      => $this->new_stop_columns[$key]
        );
        $this->db->insert('blocks', $blockdata);
      }
    }
        
    redirect("screen_admin/index/$msg");   
  }

  public function is_asleep(){
    $today = date('D');   // Day of week, e.g. Fri
    $time = (int) date('Gis');  // Time of day, e.g. 8 am (8:00:00) formatted as 80000

    switch($today){
      case 'Fri':
        if(($time < (int) str_replace(':','',$this->Fr_op)) || $time > (int) str_replace(':','',$this->Fr_cl)) {
          return true;
        }
        return false;
      case 'Sat':
        if(($time < (int) str_replace(':','',$this->Sa_op)) || $time > (int) str_replace(':','',$this->Sa_cl)) {
          return true;
        }
        return false;
        break;
      case 'Sun':
        if(($time < (int) str_replace(':','',$this->Su_op)) || $time > (int) str_replace(':','',$this->Su_cl)) {
          return true;
        }
        return false;
        break;
      default:        
        if(($time < (int) str_replace(':','',$this->MoTh_op)) || $time > (int) str_replace(':','',$this->MoTh_cl)) {
          return true;
        }
        return false;
    }

  }


}

?>
