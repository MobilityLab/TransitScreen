<?php

/**
 * Class: Screen_model
 *
 * This is the model used to represent a screen, including its wake and sleep times,
 * id, name, zoom value, and all the various associated blocks.
 *
 */

class Screen_model extends CI_Model {

  //  These variables correspond to the fields in screens table
  var $id = 0;
  var $name = '';
  var $MoTh_op = '00:00:00';
  var $MoTh_cl = '24:00:00';
  var $Fr_op = '00:00:00';
  var $Fr_cl = '24:00:00';
  var $Sa_op = '00:00:00';
  var $Sa_cl = '24:00:00';
  var $Su_op = '00:00:00';
  var $Su_cl = '24:00:00';
  var $screen_version = 0;
  var $zoom = 1;
  var $lat = 0;
  var $lon = 0;
  var $wmata_key = '';
  var $user_id = 0;
  
  // These correspond to the related records in the blocks and agency_stop tables
  var $stop_ids = array();
  var $pair_ids = array();
  var $stop_names = array();
  var $stop_columns = array();
  var $stop_positions = array();
  var $stop_custom_bodies = array();
  var $stop_limits = array();
  var $new_stop_ids = array();
  var $new_stop_names = array();
  var $new_stop_columns = array();
  var $new_stop_positions = array();
  var $new_stop_custom_bodies = array();
  var $new_stop_limits = array();

  /**
   * Generic constructor
   */
  public function __construct(){
    parent::__construct();
  }

  /**
   * Function: load_model
   *
   * @param int $id - the id of the screen (record)
   *
   * This function loads all the values from the database into the model.  Remember
   * that it's calling from 3 different data tables to assemble this model.
   *
   * Since the data are stored in the model's properties, nothing is actually
   * returned.
   *
   */
  public function load_model($id){
    $this->id = $id;
    //Query the screen data
    $this->db->select('id, name, MoTh_op, MoTh_cl, Fr_op, Fr_cl, Sa_op, Sa_cl, Su_op, Su_cl, screen_version, zoom, lat, lon, wmata_key');
    $q = $this->db->get_where('screens',array('id' => $id));

    if ($q->num_rows() > 0) {
      //Place the screen data into the object
      $result = $q->result();
      foreach($result[0] as $key => $value) {
        $this->$key = $value;
      }
    }

    //Query the block data
    $this->db->select('id, stop, custom_name, column, position, custom_body, limit');

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
        $newlimitrow[$row->id] = $row->limit;

        $this->stop_ids[] = $newidrow[$row->id];
        $this->stop_names[] = $newnamerow[$row->id];
        $this->stop_columns[] = $newcolumnrow[$row->id];
        $this->stop_positions[] = $newpositionrow[$row->id];
        $this->stop_custom_bodies[] = $newcustombodyrow[$row->id];
        $this->stop_limits[] = $newlimitrow[$row->id];
      }
    }
  }

  /**
   * Function: _assemble_stops
   *
   * @param int $parentid - the id of the parent block
   * @param bool $separate_excl - whether the exclusions should be separated
   * @return string
   *
   * This function assembles the stops based on the parent block since each block
   * can have multiple stops associated with it.
   *
   * Note that this is a private function and as such can only be accessed from
   * within this class.
   *
   */
  private function _assemble_stops($parentid, $separate_excl = false){
    $output = array();

    // Get all the agency_stop pairs affiliated with the specified block
    $this->db->select('id,agency,stop_id,exclusions');
    $q = $this->db->get_where('agency_stop', array('block_id' => $parentid));

    // Assemble all these responses into an array
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
    // Return an array containing the agency names, their stops, and any lines that 
    // should be excluded.
    return $output;
  }

  /**
   * Function: get_screens_by_user_id
   * 
   * @param int $id - the user_id of the screen owner
   * @return array
   *
   * Get a listing of all the screens and return them in an array.  Eventually this 
   * function should differentiate between users since different users will have access
   * only to their respective screens.
   *
   */
  public function get_screens_by_user_id($user_id = 0){
    // Get all the screens' names and sort by the name
    $this->db->order_by('name', 'asc');
    $q = $this->db->get('screens');

    if($q->num_rows() > 0) {
      foreach($q->result() as $row) {
        $data[] = $row;
      }

      return $data;
    }
  }

  /**
   * Function: get_screen_values
   *
   * @param int $id - the id of the screen to load
   * @param bool $separate_excl - whether to exclude bus lines mentioned in the exclusion field
   * @return array
   *
   * This function gets all the screen static values and puts them into an array along with
   * the associated blocks and agency-stop pairs. All the data are returned in an array which can
   * be used to populate the screen editor page.
   *
   */
  public function get_screen_values($id, $separate_excl = false) {
    // Get all the screen's configuration values
    $this->db->select('id, MoTh_op, MoTh_cl, Fr_op, Fr_cl, Sa_op, Sa_cl, Su_op, Su_cl, name, screen_version, zoom, lat, lon, wmata_key');
    if($id == 0){
      $q = $this->db->get('screens',1);
    }
    else {
      $q = $this->db->get_where('screens',array('id' => $id));
    }

    // Load the values in to an array that will be used to populate the screen
    // editor screen
    if ($q->num_rows() > 0) {
      foreach($q->result() as $row) {
        if($id == 0){
          foreach($row as $key => $value){
            $blankrow[$key] = '';
          }
          $row = (object)$blankrow;
          $data['settings'][] = $row;
        }
        else {
          $data['settings'][] = $row;
        }
      }

      // Now get the individual block data for this screen.
      $this->db->select('id, stop, custom_name, column, position, custom_body, limit');
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

      // Return an array with all the data that can be used to populate
      // a screen editor.
      return $data;
    }
  }

  /**
   * Function: save_screen_values
   * @param bool $create - true if a screen is being created, otherwise false
   *
   * This function takes all the screen values and then writes them to the
   * relevant database tables.  This function is employed whenever someone
   * updates the screen settings.  Since the purpose of the function is to
   * write settings, no values are returned.
   *
   */
  public function save_screen_values($create) {

    //Load the model setting into an array that will be written to the db
    $data = array(
      'id'              => $this->id,
      'name'            => $this->name,
      'MoTh_op'         => $this->MoTh_op,
      'MoTh_cl'         => $this->MoTh_cl,
      'Fr_op'           => $this->Fr_op,
      'Fr_cl'           => $this->Fr_cl,
      'Sa_op'           => $this->Sa_op,
      'Sa_cl'           => $this->Sa_cl,
      'Su_op'           => $this->Su_op,
      'Su_cl'           => $this->Su_cl,
      'screen_version'  => $this->screen_version,
      'zoom'            => $this->zoom,
      'lat'             => $this->lat,
      'lon'             => $this->lon,
      'wmata_key'       => $this->wmata_key,
      'user_id'         => $this->user_id
    );
    
    if($create){
      $this->db->insert('screens',$data);      
    }
    else {
      $this->db->where('id', $this->id);
      $this->db->update('screens', $data);
    }

    // For each agency-stop pair, you will need to split out agency names,
    // stop ids, and the bus exclusion lines.  All will be written to the
    // agency_stop table.
    // The data will appear in the [agency]:[stop]-[exclusion] format, e.g.
    //    metrobus:6001234  or metrobus:6001234-x2
    foreach($this->stop_ids as $key => $value){
      unset($blockdata);
      $oldpairs = array();
      $newpairs = array();

      $k = explode(',',$this->pair_ids[$key]);

      // Explode the string into an array with the comma as a delimiter
      // between different stop pairs.
      $stop_pairs = explode(';',$value);

      // For each stop pair, see if there is an exclusion value appended with
      // a hyphen.  Write the data to an array of new blocks if this is new or
      // write to an array of existing blocks.  These arrays are just theoretical
      // and will be written to the db a little later down.
      foreach($stop_pairs as $skey => $svalue){
        $as = explode(':',$svalue);
        $se = explode('-',$as[1]);
        if(count($se) == 2) {
          $exclusions = $se[1];
        }
        else {
          $exclusions = null;
        }

        if(isset($k[$skey])){
          $oldpairs[$k[$skey]] = array(
              'agency'    => $as[0],
              'stop_id'   => $se[0],
              'exclusions'  => $exclusions
              //'stop_id' => $as[1]
          );
        }
        else {
          $newpairs[] = array(
              'agency'    => $as[0],
              'stop_id'   => $se[0],
              'exclusions' => $exclusions
              //'stop_id' => $as[1]
          );
        }
      }

      // Write the updated stop pairs and the new stop pairs to the db.  The
      // key refers to the parent block's id.
      $this->_add_stop_pairs($oldpairs,$newpairs,$key);

      // Now time to write the actual block data to the db.  Prepare it in an
      // array.
      $blockdata = array (
        'custom_name' => $this->stop_names[$key],
        'column'      => $this->stop_columns[$key],
        'position'    => $this->stop_positions[$key],
        'custom_body' => $this->stop_custom_bodies[$key],
        'limit'      => $this->stop_limits[$key]
      );

      // If the user saved the block as empty, delete it.
      if(strlen(trim($value)) == 0 && strlen(trim($blockdata['custom_name'])) == 0){        
        $this->db->delete('agency_stop', array('block_id' => $key));
        $this->db->delete('blocks', array('id' => $key));
      }
      else {         // Otherwise update the block with updated settings
        $this->db->where('id', $key);
        $this->db->update('blocks', $blockdata);
      }      
    }

    // For each of the new blocks, create an array with the new blocks' values
    // and insert them into the db.
    foreach($this->new_stop_ids as $key => $value){
      unset($blockdata);
      if(strlen(trim($value)) > 0){
        $blockdata = array (          
          'custom_name' => $this->new_stop_names[$key],
          'screen_id'   => $this->id,
          'column'      => $this->new_stop_columns[$key],
          'position'    => $this->new_stop_positions[$key],
          'custom_body' => $this->new_stop_custom_bodies[$key],
          'limit'       => $this->new_stop_limits[$key]
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

  /**
   * Function: _add_stop_pairs
   *
   * @param array $old - existing agency-stop pairs that were updates
   * @param array $new - new agency-stop pairsto be added
   * @param int $block_id  - the id of the parents block
   *
   * This function receives two arrays, one of existing stop pairs and an array
   * of new stop pairs.  The existing stop pairs just get updated whereas the
   * new stop pairs get inserted and associated with the parent block.  Since
   * this function just updates the db, it does not return anything.
   *
   */
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

  /**
   * Function is_asleep
   *
   * @return bool
   *
   * This function reads the screens sleep and wake times and the current time
   * and day of the week to determine if the screen should be asleep right now
   * (return true) or if it should be awake (return false).
   */
  public function is_asleep(){
    $today = date('D');   // Day of week, e.g. Fri
    $time = (int) date('Gis');  // Time of day, e.g. 8 am (8:00:00) formatted as 80000
    $morningdivide = 50000; // Time in the morning, e.g. 50000 (5 am) when
                            // open-close calculations should switch

    switch($today){
      case 'Sun':
        $hours = array(
          'yesterday_close' => $this->Sa_cl,
          'open'            => $this->Su_op,
          'close'           => $this->Su_cl
        );
        break;
      case 'Tue':
      case 'Wed':
      case 'Thu':
        $hours = array(
          'yesterday_close' => $this->MoTh_cl,
          'open'            => $this->MoTh_op,
          'close'           => $this->MoTh_cl
        );
        break;
      case 'Mon':
        $hours = array(
          'yesterday_close' => $this->Su_cl,
          'open'            => $this->MoTh_op,
          'close'           => $this->MoTh_cl
        );
        break;
      case 'Fri':
        $hours = array(
          'yesterday_close' => $this->MoTh_cl,
          'open'            => $this->Fr_op,
          'close'           => $this->Fr_cl
        );
        break;
      case 'Sat':
        $hours = array(
          'yesterday_close' => $this->Fr_cl,
          'open'            => $this->Sa_op,
          'close'           => $this->Sa_cl
        );
        break;
    }

    foreach($hours as $key => $value){
      $hours[$key] = (int) str_replace(':','',$value);
    }   

    // If yesterday's close is early in this morning and the current
    // time is in the morning just before that time, sleep = false
    if($hours['yesterday_close'] < $morningdivide && $time < $hours['yesterday_close']){      
      return false;      
    }
    
    // If it's after opening time...
    
    if($time >= $hours['open']){    
      
      // If the closing time is early the next morning, sleep = false
      if($hours['close'] < $morningdivide){
        return false;
      }
      
      // If the current time after close, sleep = true
      if($time > $hours['close']){
        return true;
      }
      
      // In all other cases, e.g. most daytime hours, sleep = false      
      return false;      
    }
    else {
      return true;
    }
  }

  /**
   * Function: get_num_columns
   *
   * @return int
   *
   * This function looks for and returns the highest column marked for any
   * block associated with the screen.  This function is used elsewhere to
   * generate the screen page with X number of blank columns.
   *
   */
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
