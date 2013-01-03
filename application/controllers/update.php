<?php
/**
 * Class: Update
 *
 * This class is really more of a behind-the-scenes class that just works to
 * produce JSON data for the screens to read.  The screens will check in for
 * two reason: they need to get updated arrival data or they need to check if
 * they need to do a page refresh.
 *
 * Since this function's purpose is to return JSON data and since the json_encode()
 * php function does this easily, this controller class does not need to call
 * views the way that other controller classes need to.
 *
 */
class Update extends CI_Controller {

  /**
   * Generic constructor
   */
  public function __construct() {
    parent::__construct();
  }

  /**
   * Function: index
   * @param int $id - This vestigial function may be removed.  However, you
   * may find it helpful for testing purposes in the future.
   */
  public function index($id = 0) {
    /*$this->load->model('screen_model');
    $this->load->helper('render_admin_helper');

    $data['rows'] = $this->screen_model->get_screens_by_user_id();
    //$data['main_content'] = 'screen_listing';

    print $id;

    $this->load->view('includes/template', $data);*/
  }

  /**
   * Function: version
   *
   * @param int $screen_id - Id of the screen whose version is to be checked
   *
   * Each screen calls this function for the sole purpose of determining
   * whether it needs to refresh itself.  The function takes numerous
   * configuration variable and hashes them together.  It then passes that hash
   * back to the screen.  If this new hash differs from the screens current hash,
   * then it knows it needs to refresh since the screen configuration has changed.
   *
   * It is important only to hash static configuration data, as including any
   * dynamic data in the hash would result in screens refreshing everytime a
   * bus prediction updated.
   */
  public function version($screen_id){
    // Load the screen model and fill it with its values
    $this->load->model('screen_model');
    $screen = new Screen_model();

    $screendata = $this->screen_model->get_screen_values($screen_id);

    // Remove the following variables from the hash: the id, the sleep and wake
    // times, the name, and the last checkin time.  This information does not
    // relate to the actual screen layout and should be excluded.  Note that the
    // sleep and wake status is determined by the server and not the screen
    // itself.  This would require that each screen have the accurate time set,
    // which is something we should not assume.
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

    // This is an interesting hashing method:  essentially, print out all of the
    // relevant configuration functions as one big string and hash that string.
    $hash = md5(print_r($screendata,true));

    // Return that hash value as JSON
    print json_encode($hash);

    // Remember that there is no view necessary here since the json_encode() php
    // function does the job.
  }

  /**
   * Function: json
   * @param int $screen_id - The id of the screen whose updates you need to load
   *
   * This is one of the biggest and most complicated functions in the application.
   * It generates the JSON data update for every screen.  More specifically, it
   * loads a screen, ensures that it should be awake at this moment, and gets all
   * the blocks.  For each block it finds the agency-stop pairs and calls the
   * appropriate APIs to get the real-time transit data.  It assembles all this
   * information, including the custom block data and the CaBi status and prints
   * it all out as one JSON response.
   */
  public function json($screen_id) {
    // Load the Update model and the screen model
    $this->load->model('update_model');
    $update = new Update_model();
    
    $this->load->model('screen_model');
    $screen = new Screen_model();

    // Fill this variable with the screen values.
    $screendata = $this->screen_model->get_screen_values($screen_id, true);

    //Load variable of screen model type
    $screen->load_model($screen_id);

    // We will collect all the data to publish via JSON in the $update variable,
    // so set a few of its static properties based on the screendata variable.
    $update->screen_name = $screendata['settings'][0]->name;
    $update->screen_version = $screendata['settings'][0]->screen_version;

    $wmata_key = $screendata['settings'][0]->wmata_key;

    // Update the last_checkin value for this screen.  This allows us to ensure
    // that our screens are regularly calling for updates.
    $this->_update_timestamp($screen_id);

    // If the screen should be asleep right now, print that in JSON and do not
    // bother to load any real-time data.
    if($screen->is_asleep()) {
      $update->sleep = true;
      print json_encode($update);
    }
    else {
      //Gather all the necessary data into the $update variable
      //and then output the variable as JSON

      // This helper contains the fuctions that call the various agency APIs
      $this->load->helper('transit_functions');

      // Obviously this screen should be awake.
      $update->sleep = false;

      $stopname = '';

      // For every block...  (remember that one block can contain more than one
      // stop or CaBi station!)
      foreach($screendata['blocks'] as $block){
        $stops = $block->stop;

        //Set up (or clear) variables to handle the various data
        $vehicles = array();
        unset($bike);
        $bikes = array();

        unset($override);        

        // For each of the agency-stop pairs for this block...
        foreach($stops as $stop){
          // ... get the arrival predictions for each agency.

          // Collected the line exclusions for this block
          $exclusions = array();
          if(isset($stop['exclusions'])){
            $exclusions = explode(',', $stop['exclusions']);
          }          

          // For this agency-stop pair, check to see what mode it is and then
          // call the approriate API function.
          switch($this->_get_agency_type($stop['agency'])){
            case 'bus':
              $newset = array();

              // Get the bus prediction data back.  This get_bus_predictions
              // function covers ART, WMATA, DC Circulator and Prince George's TheBus
              $set = get_bus_predictions($stop['stop_id'],$wmata_key,$stop['agency'],false);              
              if(isset($set[0])){
                // Loop through the results.  If the bus line is not in the
                // exclusions array, add it to a new set.  We will abandon the
                // excluded lines.
                foreach($set as $b){
                  if(!in_array(strtoupper($b['route']),$exclusions)){
                    $newset[] = $b;
                  }
                }                
                $vehicles[] = $newset;
              }              
              break;
            case 'subway':              
              // Get predictions from WMATA for rail station with id 
              // $stop['stop_id').  The second parameter to over ride platform-
              // side labelling.  The third parameter tells the function just
              // to return unrendered data.
              $vehicles[] = get_rail_predictions($stop['stop_id'],$wmata_key, array (1 => '', 2 => ''), false);
              break;
            case 'cabi':
              // For each bike station, get the status.  Notice that the data
              // will be put into the $bikes array since each block may have
              // multiple CaBi stations.
              $bikes[] = get_cabi_status($stop['stop_id']);              
              break;
            case 'custom':
              // This is where the custom block data goes.  There is no clean up.
              $override = $block->custom_body;
              break;
          }
        }
        
        // Combine the different agency predictions for this stop
        // into a single array and sort by time.  Make sure you have actual
        // predictions first!
        if(count($vehicles) > 0){
          // Combine multi-agency data for buses only.
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

          // Set the stop name to the API stop name that comes first.  WMATA and
          // ART will have different descriptions for the same stop, but we will
          // just use the first name instead.  You can override this with a
          // custom stop name in the backend, of course.
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

        // If we're working with bikes, put all the relevant block data into
        // an array.  Otherwise, do the same for a bus or Metro block.
        if(isset($bikes) && count($bikes) > 0){
          $stopdata = array(
              'id'        => $block->id,
              'name'      => clean_destination($stopname),
              'type'      => $this->_get_agency_type($stop['agency']),
              'column'    => (int) $block->column,
              'order'     => (int) $block->position,
              'stations'  => $bikes              
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

        // If this block is a custom block, put in the data here.
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

        // Add all the stop data for this block to the stops array in the $update
        // variable.
        $update->stops[] = $stopdata;

      }
      // Print out the entire $update variable encoded as JSON
      print json_encode($update);
    }

    
  }

  /**
   * Function: _combine_duplicates
   * @param array $predictions
   * @return array
   *
   * That the word 'private' precedes 'function' means that this is a private
   * function only available within this class.  It is also common in php to
   * prefix an underscore to the name of a private function.
   *
   * This function combines arrival predictions by route.  For instence, there
   * is no need to have three lines devoted to X2 buses when we can have one
   * line with the next bus prediction highlighted and the subsequent arrivals
   * in small text.  This requires us to combine these predictions by line number
   * and destination.
   *
   * The return is an array with the predictions grouped into their respective
   * lines.
   *
   */
  private function _combine_duplicates($predictions){
    // This array will hold the returned data.
    $newout = array();    

    for($p = 0; $p < count($predictions); $p++){
      // Hash together the route and the destination.  Since some buses have the
      // same route number, but different destinations, we will have to treat
      // such buses as separate lines.
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

    // Return the new, clean array.
    return $io;
  }

  /**
   * Function: _get_agency_type
   * @param string $agency - the string representing the agency name
   * @return string - the mode
   *
   * This private funciton takes the agency name and returns the mode name.
   * This helps associate different agencies that are using the same mode.   *
   */
  private function _get_agency_type($agency) {
    
    switch(strtolower($agency)){
      case 'metrobus':
      case 'art':
	  case 'pgc':
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

  /**
   * Function: _update_timestamp
   * @param int $id - the id of the screen whose timestamp needs updating
   *
   * This funciton updates the "last_checkin" timestamp for the screen.  This
   * allows an administrator to see the last time the screen checked in for
   * updated data.
   *
   * The database functions here are from CodeIgniter' version of Active Record.
   * 
   */
  private function _update_timestamp($id){

    $this->db->where('id', $id);
    //Postgres format must be ISO 8601, e.g. 2012-01-16 14:43:55
    $this->db->update('screens', array(
        'last_checkin'  =>  date('c')
    ));
  }


}

?>
