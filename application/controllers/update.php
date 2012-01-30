<?php

class Update extends CI_Controller {

  public function __construct() {
    parent::__construct();
  }

  public function index($id = 0) {
    $this->load->model('screen_model');
    $this->load->helper('render_admin_helper');

    $data['rows'] = $this->screen_model->get_screens_by_user_id();
    //$data['main_content'] = 'screen_listing';

    print $id;

    $this->load->view('includes/template', $data);
  }
  
  public function version($screen_id){
    $this->load->model('screen_model');
    $screen = new Screen_model();

    $screendata = $this->screen_model->get_screen_values($screen_id);
  
    unset($screendata['settings'][0]->id);
    unset($screendata['settings'][0]->MoTh_op);
    unset($screendata['settings'][0]->MoTh_cl);
    unset($screendata['settings'][0]->Fr_op);
    unset($screendata['settings'][0]->Fr_cl);
    unset($screendata['settings'][0]->Sa_op);
    unset($screendata['settings'][0]->Sa_cl);
    unset($screendata['settings'][0]->Su_op);
    unset($screendata['settings'][0]->Su_cl);
    unset($screendata['settings'][0]->name);
    unset($screendata['settings'][0]->last_checkin);
    
    $hash = md5(print_r($screendata,true));

    print json_encode($hash);

  }

  public function json($screen_id) {
    $this->load->model('update_model');
    $update = new Update_model();
    
    $this->load->model('screen_model');
    $screen = new Screen_model();

    $screendata = $this->screen_model->get_screen_values($screen_id, true);
    //print_r($screendata); die;
    
    //Load variable of screen model type
    $screen->load_model($screen_id);

    $update->screen_name = $screendata['settings'][0]->name;
    $update->screen_version = $screendata['settings'][0]->screen_version;

    // Update the last_checkin value for this screen
    $this->_update_timestamp($screen_id);

    if($screen->is_asleep()) {
      $update->sleep = true;
      print json_encode($update);
    }
    else {
      //Gather all the necessary data into the $update variable
      //and then output the variable as JSON
      
      $this->load->helper('transit_functions');

      $update->sleep = false;

      $stopname = '';
      
      foreach($screendata['blocks'] as $block){
        $stops = $block->stop;

        $vehicles = array();
        unset($bike);
        $bikes = array();

        unset($override);        

        // For each of the agency-stop pairs for this block...
        foreach($stops as $stop){
          // ... get the arrival predictions for each agency.
          $exclusions = array();
          if(isset($stop['exclusions'])){
            $exclusions = explode(',', $stop['exclusions']);
          }          

          switch($this->_get_agency_type($stop['agency'])){
            case 'bus':
              $newset = array();
              $set = get_bus_predictions($stop['stop_id'],$stop['agency'],false);              
              if(isset($set[0])){
                //print_r($set); print_r($exclusions);die;
                foreach($set as $b){
                  if(!in_array(strtoupper($b['route']),$exclusions)){
                    $newset[] = $b;
                  }
                }
                //print_r($set); print_r($newset);die;
                $vehicles[] = $newset;
              }
              
              break;
            case 'subway':              
              $vehicles[] = get_rail_predictions($stop['stop_id'], array (1 => '', 2 => ''), false);              
              break;
            case 'cabi':
              $bikes[] = get_cabi_status($stop['stop_id']);
              //$bike = get_cabi_status($stop['stop_id']);
              break;
            case 'custom':
              $override = $block->custom_body;
              break;
          }
        }
        
        // Combine the different agency predictions for this stop
        // into a single array and sort by time. Bus or Metro
        //print_r($vehicles); die;
        if(count($vehicles) > 0){

          //print_r($vehicles);die;
          if($this->_get_agency_type($stop['agency']) == 'bus'){
            $stopdata = combine_agencies($vehicles);
            $stopdata = $this->_combine_duplicates($stopdata);            
          }
          else {
            $stopdata = $vehicles[0];
          }

          // If there is a limit to the number of arrival lines to list
          // at any bus stop, remove the extra vehicles from the array
          if((isset($block->limit) && isset($stopdata)) && (count($stopdata) > $block->limit) && $block->limit > 0){
            array_splice($stopdata,$block->limit);
          }

          if(isset($vehicles[0][0]['stop_name'])){
            $stopname = $vehicles[0][0]['stop_name'];
          }
        }

        // If we're working with CaBi here
        if(isset($bike)){
          $stopdata = $bike;
          $stopname = $bike['stop_name'];
        }

        // Set the stop's custom name
        if(strlen(trim($block->custom_name)) > 0 ) {
          $stopname = $block->custom_name;
        }
        
        if(isset($bikes) && count($bikes) > 0){
          $stopdata = array(
              'id'        => $block->id,
              'name'      => clean_destination($stopname),
              'type'      => $this->_get_agency_type($stop['agency']),
              'column'    => (int) $block->column,
              'order'     => (int) $block->position,
              'stations'  => $bikes
              //'bikes'     => $bike['bikes'],
              //'docks'     => $bike['docks']
            );
        }
        else {
          $stopdata = array(
              'id'        => $block->id,
              'name'      => clean_destination($stopname),
              'type'      => $this->_get_agency_type($stop['agency']),
              'column'    => (int) $block->column,
              'order'     => (int) $block->position,
              'vehicles'  => $stopdata
            );
        }

        if(isset($override)){
          $stopdata = array(
              'id'          => $block->id,
              'name'        => clean_destination($stopname),
              'type'        => $this->_get_agency_type($stop['agency']),
              'column'      => (int) $block->column,
              'order'       => (int) $block->position,
              'custom_body' => $override
            );
        }

        //print_r($stopdata);die;
        
        $update->stops[] = $stopdata;

      }

      print json_encode($update);

    }

    
  }

  private function _combine_duplicates($predictions){
    // This array will hold the returned data.
    $newout = array();    

    for($p = 0; $p < count($predictions); $p++){
      $rdhash = hash('adler32', $predictions[$p]['route'] . $predictions[$p]['destination']);
      // If the route already exists, just add the prediction      
      if(isset($newout[$rdhash])){
        $newout[$rdhash]['predictions'][] = $predictions[$p]['prediction'];
      }
      else {
        $newout[$rdhash] = array(
          'agency'      =>  $predictions[$p]['agency'],
          'route'       =>  $predictions[$p]['route'],
          'destination' =>  $predictions[$p]['destination'],
          'predictions' =>  array(0 => $predictions[$p]['prediction'])
          );
      }
    }
    
    $io = array();
    
    // Replace the hash keys with a normal integer index
    foreach($newout as $item){
      $io[] = $item;
    }
    
    return $io;
  }

  private function _get_agency_type($agency) {
    
    switch(strtolower($agency)){
      case 'metrobus':
      case 'art':
      case 'circulator':
      case 'dc-circulator':
        return 'bus';
      case 'cabi':
        return 'cabi';
      case 'metro':
      case 'metrorail':
        return 'subway';
    }
    return $agency;

  }

  private function _update_timestamp($id){    
    $this->db->where('id', $id);
    //Postgres format must be ISO 8601, e.g. 2012-01-16 14:43:55
    $this->db->update('screens', array(
        'last_checkin'  =>  date('c')
    ));
  }


}

?>
