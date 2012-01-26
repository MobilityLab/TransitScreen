<?php

/**
 * Screen
 *
 * This is the screen model
 *
 */

class Screen_model extends CI_Model {
  
  var $id = '';
  var $MoTh_op = '00:00:00';
  var $MoTh_cl = '24:00:00';
  var $Fr_op = '00:00:00';
  var $Fr_cl = '24:00:00';
  var $Sa_op = '00:00:00';
  var $Sa_cl = '24:00:00';
  var $Su_op = '00:00:00';
  var $Su_cl = '24:00:00';
  var $name = '';
  var $screen_version = 0;
  var $zoom = 1;
  var $stop_ids = array();
  var $pair_ids = array();
  var $stop_names = array();
  var $stop_columns = array();
  var $stop_positions = array();
  var $stop_custom_bodies = array();
  var $new_stop_ids = array();
  var $new_stop_names = array();
  var $new_stop_columns = array();
  var $new_stop_positions = array();
  var $new_stop_custom_bodies = array();

  public function __construct(){
    parent::__construct();
  }

  public function load_model($id){
    $this->id = $id;
    //Query the screen data
    $this->db->select('MoTh_op, MoTh_cl, Fr_op, Fr_cl, Sa_op, Sa_cl, Su_op, Su_cl, screen_version, name, zoom');
    $q = $this->db->get_where('screens',array('id' => $id));

    if ($q->num_rows() > 0) {
      //Place the screen data into the object
      $result = $q->result();
      foreach($result[0] as $key => $value) {
        $this->$key = $value;
      }
    }

    //Query the block data
    $this->db->select('id, stop, custom_name, column, position, custom_body');

    $q = $this->db->get_where('blocks',array('screen_id' => $this->id));

    //Place the data into the arrays of this object
    if ($q->num_rows() > 0) {
      foreach($q->result() as $row){                
        $serialstops = $this->_assemble_stops($row->id);

        $newidrow[$row->id] = $row->stop;
        $newnamerow[$row->id] = $row->custom_name;
        $newcolumnrow[$row->id] = $row->column;
        $newpositionrow[$row->id] = $row->position;
        $newcustombodyrow[$row->id] = $row->custom_body;

        $this->stop_ids[] = $newidrow[$row->id];
        $this->stop_names[] = $newnamerow[$row->id];
        $this->stop_columns[] = $newcolumnrow[$row->id];
        $this->stop_positions[] = $newpositionrow[$row->id];
        $this->stop_custom_bodies[] = $newcustombodyrow[$row->id];
      }
    }
  }

  private function _assemble_stops($parentid, $separate_excl = false){
    $output = array();

    $this->db->select('id, agency,stop_id, exclusions');
    $q = $this->db->get_where('agency_stop', array('block_id' => $parentid));

    if($q->num_rows() > 0) {
      foreach($q->result() as $row){
        $rowarray['agency']   = $row->agency;
        $rowarray['stop_id']  = $row->stop_id;
        if(strlen($row->exclusions) > 0){
          if($separate_excl){
            $rowarray['exclusions'] = strtoupper($row->exclusions);
          }
          else {
            $rowarray['stop_id'] .= '-' . $row->exclusions;
          }
        }
        $output[$row->id] = $rowarray;
      }
    }
    return $output;
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

  public function get_screen_values($id, $separate_excl = false) {
    $this->db->select('id, MoTh_op, MoTh_cl, Fr_op, Fr_cl, Sa_op, Sa_cl, Su_op, Su_cl, name, screen_version, zoom');
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
      $this->db->select('id, stop, custom_name, column, position, custom_body');
      $this->db->order_by('column', 'asc');
      $this->db->order_by('position', 'asc');
      $q = $this->db->get_where('blocks',array('screen_id' => $id));

      if($q->num_rows() > 0){
        foreach($q->result() as $row){
          $stopstring = '';
          $stoppairs = $this->_assemble_stops($row->id, $separate_excl);
          foreach($stoppairs as $pairing){
            $stopstring .= implode(':',$pairing) . ';';
          }          

          $row->stop = $stoppairs;
          $data['blocks'][] = $row;
        }
      }

      return $data;
    }
  }

  public function save_screen_values($id) {

    $msg = '';

    $data = array(
      'MoTh_op'         => $this->MoTh_op,
      'MoTh_cl'         => $this->MoTh_cl,
      'Fr_op'           => $this->Fr_op,
      'Fr_cl'           => $this->Fr_cl,
      'Sa_op'           => $this->Sa_op,
      'Sa_cl'           => $this->Sa_cl,
      'Su_op'           => $this->Su_op,
      'Su_cl'           => $this->Su_cl,
      'name'            => $this->name,
      'screen_version'  => $this->screen_version,
      'zoom'            => $this->zoom
    );

    
    if($id > 0){ // If updating, instead of inserting anew...
      $this->db->where('id', $id);
      $this->db->update('screens', $data);
      $msg = 'success';
    }
    else {
      $this->db->insert('screens',$data);
      $id = $this->db->insert_id();
      $msg = 'created';
    }

    foreach($this->stop_ids as $key => $value){
      unset($blockdata);
      $oldpairs = array();
      $newpairs = array();

      $k = explode(',',$this->pair_ids[$key]);
      $stop_pairs = explode(';',$value);
      foreach($stop_pairs as $skey => $svalue){
        $as = explode(':',$svalue);
        $se = explode('-',$as[1]);

        if(isset($k[$skey])){
          $oldpairs[$k[$skey]] = array(
              'agency'    => $as[0],
              'stop_id'   => $se[0],
              'exclusions'  => $se[1]
              //'stop_id' => $as[1]
          );
        }
        else {
          $newpairs[] = array(
              'agency'    => $as[0],
              'stop_id'   => $se[0],
              'exclusions' => $se[1]
              //'stop_id' => $as[1]
          );
        }
      }

      $this->_add_stop_pairs($oldpairs,$newpairs,$key);

      
      $blockdata = array (
        'custom_name' => $this->stop_names[$key],
        'column'      => $this->stop_columns[$key],
        'position'    => $this->stop_positions[$key],
        'custom_body' => $this->stop_custom_bodies[$key]
      );
      
      if(strlen(trim($value)) == 0 && strlen(trim($blockdata['custom_name'])) == 0){        
        $this->db->delete('agency_stop', array('block_id' => $key));
        $this->db->delete('blocks', array('id' => $key));
      }
      else {                
        $this->db->where('id', $key);
        $this->db->update('blocks', $blockdata);
      }      
    }

    foreach($this->new_stop_ids as $key => $value){
      unset($blockdata);
      if(strlen(trim($value)) > 0){
        $blockdata = array (
          //'stop'        => $value,
          'custom_name' => $this->new_stop_names[$key],
          'screen_id'   => $id,
          'column'      => $this->new_stop_columns[$key],
          'position'    => $this->new_stop_positions[$key],
          'custom_body' => $this->new_stop_custom_bodies[$key]
        );
        
        $this->db->insert('blocks', $blockdata);
        $newid = $this->db->insert_id();        

        $stops = explode(';',$value);
        foreach($stops as $stop){
          $pairings = explode(':',$stop);
          $newstop = array(
              'agency'    => $pairings[0],
              'stop_id'   => $pairings[1],
              'block_id'  => $newid
          );
          $this->db->insert('agency_stop',$newstop);
        }
      }
    }        
  }

  private function _add_stop_pairs($old, $new, $block_id){
    foreach($old as $key => $value){
      if($key > 0){
        $this->db->where('id', $key);
        $this->db->update('agency_stop', $old[$key]);
      }
    }
    foreach($new as $pair){
      $pair['block_id'] = $block_id;
      $this->db->insert('agency_stop',$pair);
    }
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

  public function get_num_columns() {
    $max = 0;

    $this->db->select('column');
    $q = $this->db->get_where('blocks',array('screen_id' => $this->id));

    if($q->num_rows() > 0){
      foreach($q->result() as $row){
        $thiscol = $row->column;
        if($thiscol > $max){
          $max = $thiscol;
        }
      }
    }
    return $max;
  }
}

?>
