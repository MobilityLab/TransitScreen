<?php

/**
 * Function: clean_time
 *
 * @param mixed $m
 * @return string
 *
 * This function takes in a train arrival time and converts it into something
 * easier to read.
 */
function clean_time($m){
  switch (trim($m)){
    case '':
      return;
    case 1:
      return '1 <span>min </span>';
    case 'ARR':
    case 0:
      return 'arriving';
    case '-1':
      return 'boarding';
    default:
      return $m . ' <span>mins</span>';
  }
}

/**
 * Function: clean_station
 *
 * @param string $s
 * @return string
 *
 * Gets a line destination abbreviation and returns a more human-readable version.
 *
 */
function clean_station($s){
  switch($s) {
    case 'NewCrltn':
      return 'New Carrollton';
    case 'W Fls Ch':
      return 'W Falls Church';
    case 'Frnconia':
      return 'Franconia-Springfield';
    case 'Grosvenor-Strathmore':
      return 'Grosvenor';
    case 'No Passenger':
      return '(Closed train)';
    default:
      return $s;
  }
}

/**
 * Function: clean_destination
 * @param string $s
 * @return string
 *
 * Receives an API-generated destination and fixes it to make it fit better
 * or read better on the screen.  For instance, the WMATA API prints out 'NW'
 * incorrectly as 'Nw'.  This function fixes that.  This is also a good place
 * to add other API corrections as you find them.
 *
 */
function clean_destination($s){
  $s = str_replace('North to ','',$s);
  $s = str_replace('South to ','',$s);
  $s = str_replace('East to ','',$s);
  $s = str_replace('West to ','',$s);

  //$s = str_replace('Station','',$s);
  $s = str_replace('Square','Sq',$s);
  $s = str_replace('Pike','Pk',$s);

  $s = str_replace(', ',' &raquo; ',$s);

  $s = preg_replace('/Nw(\s|$)/','NW', $s);
  $s = preg_replace('/Ne(\s|$)/','NE', $s);
  $s = preg_replace('/Sw(\s|$)/','SW', $s);
  $s = preg_replace('/Se(\s|$)/','SE', $s);

  $s = str_replace('Court House Metro - ','', $s);
  $s = str_replace('Court House Metro to ','', $s);
  $s = str_replace('Columbia Pk/Dinwiddie - ','', $s);
  $s = str_replace('Shirlington Station to ','', $s);

  return $s;
}


function render_bus($bus){
  return '<div class="item agency-' . $bus['agency'] . '">
            <div class="route">' . $bus['route'] . '</div>
            <div class="destination">' . clean_destination($bus['destination']) . '</div>
            <div class="countdown">' . clean_time($bus['prediction']) . '</div>
          </div>';
}
function render_trains($train) {
  return
    '<div class="item mins-away-' . $train['prediction'] . ' line-' . $train['line'] . '">
      <div class="line-disc"></div>
      <div class="destination">' . clean_station($train['destination']) . '</div>
      <div class="countdown">' . clean_time($train['prediction']) . '</div>
     </div>';
}

/**
 * Function: get_rail_predictions
 * @param int $station_id - the WMATA station id
 * @param string $api_key - the WMATA API key
 * @param array $group_names - optional platform side names
 * @param bool $render - whether this function should rending or just return data
 * @return mixed - the returned array (data) or string (rendered)
 *
 * This function gets the rail predictions from the WMATA API, formats the data
 * nicely and returns the data.
 *
 */
function get_rail_predictions($station_id, $api_key, array $group_names, $render = true){
  $trains = array();


  for($gr = 1; $gr <= count($group_names); $gr++) {
    $traingroup[$gr] = '';
  }

  // Load the train prediction XML from the API
  $railxml = simplexml_load_file("http://api.wmata.com/StationPrediction.svc/GetPrediction/$station_id?api_key=$api_key");
  $predictions = $railxml->Trains->AIMPredictionTrainInfo;

  // For each prediction, but the data into an array to return
  for($t = 0; $t < count($predictions); $t++){
  
    $newitem['stop_name'] = (string) $predictions[$t]->LocationName;
    $newitem['agency'] = 'metrorail';
    $newitem['route'] = (string) $predictions[$t]->Line;  
    $newitem['destination'] = (string) $predictions[$t]->DestinationName;
    $newitem['predictions'] = array();

    // Prediction 'ARR' will become 0 and 'BRD' predictions will be omitted
    switch ((string) $predictions[$t]->Min) {
      case 'ARR':
        $newitem['predictions'][] = 0;
        break;
      case 'BRD':
        //$newitem['predictions'][] = 0;
        break;
      default:
        $newitem['predictions'][] = (int) $predictions[$t]->Min;
    }

    if($newitem['destination'] != '' && count($newitem['predictions']) > 0){
      $trains[] = $newitem;
    }
  }

  // Do an array_multisort to sort by prediction time, then color, then destination
  foreach($trains as $key => $row){
    $r[$key] = $row['route'];
    $d[$key] = $row['destination'];
    $p[$key] = $row['predictions'];
  }
  array_multisort($p, SORT_ASC, $r, SORT_ASC, $d, SORT_ASC, $trains);

  // The new screen system does not render the data here, so this is a vestige.
  // Just return the data.
  if($render) {
    foreach($trains as $train){
      $traingroup[$train['group']] .= render_trains($train, $group_names);
    }
    return $traingroup;
  }
  else {
    return $trains;
  }
}

/*
 * Function: assemble_stop_array
 * @param array $stops - an array of the agency-stop pairs for one block
 * @param string $api_key - the API key for the agency (in this case WMATA)
 * @param int $max - listing maximum
 * @return array - the buses sorted by prediction, regardless of agency
 *
 * This function takes an array of bus predictions for a single stop and sorts
 * the predictions by the prediction time, regardless of agency.  The newly sorted
 * array is returned.
 
function assemble_stop_array(array $stops, $api_key, $max = 4){
  $buses = array();
  $out = '';

  // For each agency-stop pair in this block, get the predictions
  foreach($stops as $key => $value){
    $busgroups[] = get_bus_predictions($value, $api_key, $key, false);
  }
  for($g = 0; $g < count($busgroups); $g++) {
    $buses = array_merge($buses,$busgroups[$g]);
  }

  // We have the bus prediction data grouped by agency, but it needs to be sorted
  // by prediction time, regarless of agency.  This loop and array_multisort will
  // handle that.
  foreach($buses as $key => $row){
    $r[$key] = $row['route'];
    $d[$key] = $row['destination'];
    $p[$key] = $row['prediction'];
    $a[$key] = $row['agency'];
    $s[$key] = $row['stop_name'];
  }
  array_multisort($p, SORT_ASC, $r, SORT_DESC, $d, SORT_ASC, $buses);

  $limit = min(count($buses),$max);

  for($b = 0; $b < $limit; $b++){
    $out .= render_bus($buses[$b]);
  }
  return $out;
}
*/

/**
 * Function: combine_agencies
 *
 * @param array $busgroups
 * @param int $max
 * @return array
 *
 * Take the groups of bus predictions from different agencies (but for one stop),
 * merge them together and sort them by prediction regardless of agency.  Return
 * the newly sorted array.
 *
 */
function combine_agencies(array $busgroups, $max = 99) {
  $combined = array();
  for($g = 0; $g < count($busgroups); $g++) {
    $combined = array_merge($combined,$busgroups[$g]);
  }

  // Sort by prediction, then route, then destination
  foreach($combined as $key => $row){
    $r[$key] = $row['route'];
    $d[$key] = $row['destination'];
    $p[$key] = $row['prediction'];
    $a[$key] = $row['agency'];
    $s[$key] = $row['stop_name'];
  }
  array_multisort($p, SORT_ASC, $r, SORT_DESC, $d, SORT_ASC, $combined);

  return $combined;
}

/**
 * Function: get_bus_predictions
 *
 * @param mixed $stop_id - the stop id
 * @param string $api_key - the API key for the agency
 * @param string $agency - the agency id
 * @param bool $render - whether to render the data or just return the data
 * @return mixed - array of data (unrendered) or a string (rendered)
 *
 *
 */
function get_bus_predictions($stop_id,$api_key,$agency,$render = true) {
  $out = '';

  // Call the different API function based on the agency name.
  switch ($agency) {
    case 'wmata':
    case 'metrobus':
      $buses = get_metrobus_predictions($stop_id, $api_key);      
      break;
    case 'dc-circulator':
    case 'circulator':      
      $buses = get_nextbus_predictions($stop_id, 'dc-circulator');      
      break;
	case 'pgc':      
      $buses = get_nextbus_predictions($stop_id, 'pgc');      
      break;
    case 'art':
      $buses = get_connexionz_predictions($stop_id, 'art');
      break;
  }  

  return $buses;  
}

/**
 * Function: get_metrobus_predictions
 *
 * @param int $stop_id - the stop id
 * @param string $api_key - the WMATA API key
 * @return array - the Metrobus prediction data for this stop
 *
 * This function gets the Metrobus arrival predictions for a given Metrbus stop
 * and returns the predictions in an array.
 *
 */
function get_metrobus_predictions($stop_id,$api_key){
  $out = '';
  // Call the API
  if(!($busxml = simplexml_load_file("http://api.wmata.com/NextBusService.svc/Predictions?StopID=$stop_id&api_key=" . $api_key))){
    return false;
  }
  $stop_name = (string) $busxml->StopName;
  $predictions = $busxml->Predictions->NextBusPrediction;

  $limit = min(count($predictions), 4);

  // Add the predictions into an array
  for($b = 0; $b < $limit; $b++){
    $newitem['stop_name'] = $stop_name;
    $newitem['agency'] = 'Metrobus';
    $newitem['route'] = (string) $predictions[$b]->RouteID;
    $newitem['destination'] = (string) $predictions[$b]->DirectionText;
    $newitem['prediction'] = (int) $predictions[$b]->Minutes;
    $out[] = $newitem;
  }

  // Return the array of predictions.
  return $out;
}

/**
 * Function get_nextbus_predictions
 *
 * @param int $stop_id
 * @param string $agency_tag
 * @return array
 *
 * Get the NextBus predictions for this bus stop and return the data in an array.
 * This is what we will use for the DC Circulator and Prince George's County's TheBus.
 *
 */
function get_nextbus_predictions($stop_id,$agency_tag){

  if($agency_tag == 'dc-circulator'){
    $agency = 'Circulator';
  }
  elseif($agency_tag == 'pgc'){
	$agency = 'pgc';
  }
  
  // Load the XML from the API
  if($agency_tag == 'dc-circulator'){
    $busxml = simplexml_load_file("http://webservices.nextbus.com/service/publicXMLFeed?command=predictions&a=$agency_tag&stopId=$stop_id");
  }
  elseif($agency_tag == 'pgc'){
	$busxml = simplexml_load_file("http://webservices.nextbus.com/service/publicXMLFeed?command=predictions&a=$agency_tag&$stop_id");
  }  

  //foreach predictions
  foreach($busxml->predictions as $pred){  
    $stopname = (string) $pred->attributes()->stopTitle;
    $routename = (string) $pred->attributes()->routeTitle;
    //foreach direction
    foreach($pred->direction as $dir){
      $destination = (string) $dir->attributes()->title;
      //foreach prediction
      foreach($dir->prediction as $p){        
        unset($newitem);
        $newitem['stop_name'] = $stopname;
        $newitem['agency'] = $agency;
        $newitem['route'] = $routename;
        $newitem['destination'] = $routename . ' (' . $destination . ')';
        $newitem['prediction'] = (int) $p['minutes'];
        $out[] = $newitem;
      }
    }
  }

  return $out;
}

/**
 * Function: get_connexionz_predictions
 *
 * @param mixed $stop_id - the stop id
 * @param string $agency - the agency name
 * @return array - an array of bus predictions from Connexionz
 *
 * This function collects the bus arrival predictions from ART's Connexionz API
 * and returns the data in an array.
 *
 */
function get_connexionz_predictions($stop_id,$agency) {
  if($agency == 'art'){
    // Call the XML from the API
    $busxml = simplexml_load_file("http://realtime.commuterpage.com/RTT/Public/Utility/File.aspx?ContentType=SQLXML&Name=RoutePositionET.xml&PlatformTag=$stop_id");
    $agency_name = 'ART';
  }

  // Put the predictions into an array
  $predictions = $busxml->Platform;
  $stop_name = (string) $busxml->Platform['Name'];
  foreach($predictions->Route as $route){ //For each route
    foreach($route->Destination->Trip as $trip){
      $newitem['stop_name'] = $stop_name;
      $newitem['agency'] = $agency_name;
      $newitem['route'] = (string) $route['RouteNo'];
      $newitem['destination'] = (string) $route['Name'];
      $newitem['prediction'] = (int) $trip['ETA'];
      $out[] = $newitem;      
    }
  }

  // Use array_multisort to sort the predictions by time, then route, then
  // destination, then agency
  foreach($out as $key => $row){
    $a[$key] = $row['agency'];
    $r[$key] = $row['route'];
    $d[$key] = $row['destination'];
    $p[$key] = $row['prediction'];
  }
  array_multisort($p, SORT_ASC, $r, SORT_DESC, $d, SORT_ASC, $a, SORT_ASC, $out);

  return $out;
}

/**
 * Function: get_cabi_status
 *
 * @param int $station_id - the id of the CaBi station
 * @return array - an array of the station data
 *
 * Given a CaBi station id, get the station data and return an array with the
 * station status, e.g. number of bikes, number of docks, and the station name.
 * 
 */
function get_cabi_status($station_id){
  // Load the XML file for the entire system.
  $cabixml = simplexml_load_file("http://www.capitalbikeshare.com/stations/bikeStations.xml");

  // Find the station with the parameter id and get the data for it.
  $stations = $cabixml->station;
  foreach($stations as $station){
    if((int) $station->id == $station_id) {
      $cabi['stop_name'] = (string) $station->name;
      $cabi['bikes'] = (int) $station->nbBikes;
      $cabi['docks'] = (int) $station->nbEmptyDocks;
      break;
    }
  }

  // Return an array with the cabi station data.
  return $cabi;
}

?>
