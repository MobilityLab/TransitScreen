<?php

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

function get_rail_predictions($station_id, array $group_names, $render = true){
  $trains = array();

  for($gr = 1; $gr <= count($group_names); $gr++) {
    $traingroup[$gr] = '';
  }

  $key = WMATAKEY;
  $railxml = simplexml_load_file("http://api.wmata.com/StationPrediction.svc/GetPrediction/$station_id?api_key=$key");
  $predictions = $railxml->Trains->AIMPredictionTrainInfo;

  for($t = 0; $t < count($predictions); $t++){
    //$newitem['group'] = (int) $predictions[$t]->Group;
    $newitem['stop_name'] = (string) $predictions[$t]->LocationName;
    $newitem['agency'] = 'metrorail';
    $newitem['route'] = (string) $predictions[$t]->Line;
    //$newitem['cars'] = (int) $predictions[$t]->Car;
    $newitem['destination'] = (string) $predictions[$t]->DestinationName;

    $newitem['predictions'] = array();

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

  

  foreach($trains as $key => $row){
    //$g[$key] = $row['group'];
    //$l[$key] = $row['line'];
    $r[$key] = $row['route'];
    //$c[$key] = $row['cars'];
    $d[$key] = $row['destination'];
    $p[$key] = $row['predictions'];
  }

  array_multisort($p, SORT_ASC, $r, SORT_ASC, $d, SORT_ASC, $trains);

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

function assemble_stop(array $stops, $max = 4){
  $buses = array();
  $out = '';

  foreach($stops as $key => $value){
    $busgroups[] = get_bus_predictions($value, $key, false);
  }
  for($g = 0; $g < count($busgroups); $g++) {
    $buses = array_merge($buses,$busgroups[$g]);
  }

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

function combine_agencies(array $busgroups, $max = 99) {
  $combined = array();
  for($g = 0; $g < count($busgroups); $g++) {
    $combined = array_merge($combined,$busgroups[$g]);
  }

  foreach($combined as $key => $row){
    $r[$key] = $row['route'];
    $d[$key] = $row['destination'];
    $p[$key] = $row['prediction'];
    $a[$key] = $row['agency'];
    $s[$key] = $row['stop_name'];
  }
  array_multisort($p, SORT_ASC, $r, SORT_DESC, $d, SORT_ASC, $combined);

  //$limit = min(count($combined),$max);

  //for($b = 0; $b < $limit; $b++){
  //  $out .= render_bus($buses[$b]);
  //}

  return $combined;
}

function get_bus_predictions($stop_id,$agency,$render = true) {
  $out = '';

  switch ($agency) {
    case 'wmata':
    case 'metrobus':
      $buses = get_metrobus_predictions($stop_id);
      //print_r($buses);
      break;
    case 'dc-circulator':
    case 'circulator':      
      $buses = get_nextbus_predictions($stop_id, 'dc-circulator');
      //print_r($buses); die;
      break;
    case 'art':
      $buses = get_connexionz_predictions($stop_id, 'art');
      break;
  }  

  return $buses;  
}

function get_metrobus_predictions($stop_id){
  $out = '';
  if(!($busxml = simplexml_load_file("http://api.wmata.com/NextBusService.svc/Predictions?StopID=$stop_id&api_key=" . WMATAKEY))){
    return false;
  }
  $stop_name = (string) $busxml->StopName;
  $predictions = $busxml->Predictions->NextBusPrediction;

  $limit = min(count($predictions), 4);

  for($b = 0; $b < $limit; $b++){
    $newitem['stop_name'] = $stop_name;
    $newitem['agency'] = 'Metrobus';
    $newitem['route'] = (string) $predictions[$b]->RouteID;
    $newitem['destination'] = (string) $predictions[$b]->DirectionText;
    $newitem['prediction'] = (int) $predictions[$b]->Minutes;
    $out[] = $newitem;
  }
  //print_r($out);
  return $out;
}

function get_nextbus_predictions($stop_id,$agency_tag){

  if($agency_tag == 'dc-circulator'){
    $agency = 'Circulator';
  }

  $busxml = simplexml_load_file("http://webservices.nextbus.com/service/publicXMLFeed?command=predictions&a=$agency_tag&stopId=$stop_id");
  //print_r($busxml);die;

  $routename = $busxml->predictions->attributes()->routeTitle;
  if($routename == 'Dupont-Rosslyn') {
    $routename = 'Circulator';
  }
  $stop_name = (string) $busxml->predictions->attributes()->stopTitle;

  if($stop_id == '0136' && $agency_tag == 'dc-circulator'){
    $destination = "Dupont via Georgetown";
  }
  else {    
    $destination = (string) $busxml->predictions->direction[0]->attributes()->title;
  }


  $predictions = $busxml->predictions->direction[0];
  foreach($predictions->prediction as $prediction) {
    $newitem['stop_name'] = $stop_name;
    $newitem['agency'] = $agency;
    $newitem['route'] = $routename;
    $newitem['destination'] = $destination;
    $newitem['prediction'] = (int) $prediction['minutes'];
    $out[] = $newitem;
  }

  //$out = array_splice($out, 0, 99);
  //print_r($out); die;
  return $out;
}

function get_connexionz_predictions($stop_id,$agency) {
  if($agency == 'art'){
    $busxml = simplexml_load_file("http://realtime.commuterpage.com/RTT/Public/Utility/File.aspx?ContentType=SQLXML&Name=RoutePositionET.xml&PlatformTag=$stop_id");
    $agency_name = 'ART';
  }

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
      //print $route['RouteNo'] . ' - ' . $route['Name'] . ': ' . $trip['ETA'] . '<br/>';
    }
  }

  foreach($out as $key => $row){
    $a[$key] = $row['agency'];
    $r[$key] = $row['route'];
    $d[$key] = $row['destination'];
    $p[$key] = $row['prediction'];
  }
  array_multisort($p, SORT_ASC, $r, SORT_DESC, $d, SORT_ASC, $a, SORT_ASC, $out);

  //print_r($out);

  return $out;
}

function get_cabi_status($station_id){
  $cabixml = simplexml_load_file("http://www.capitalbikeshare.com/stations/bikeStations.xml");

  $stations = $cabixml->station;
  foreach($stations as $station){
    if((int) $station->id == $station_id) {
      $cabi['stop_name'] = (string) $station->name;
      $cabi['bikes'] = (int) $station->nbBikes;
      $cabi['docks'] = (int) $station->nbEmptyDocks;
      break;
    }
  }

  return $cabi;
}

?>
