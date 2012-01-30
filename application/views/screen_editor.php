<?php
  //print_r($rows['settings']); die
  if(isset($rows['settings'][0]->id)){
    $id = $rows['settings'][0]->id;
    unset($rows['settings'][0]->id);
  }
  else {
    $id = 0;
    unset($rows['settings'][0]['id']);
  }
  

  if(isset($rows['settings'][0]->name)){
    $title = $rows['settings'][0]->name;
  }
  else {
    $title = 'Create a new screen';
  }


?>
<div id="screen-fields">
  <h2><?php print $title; ?></h2>
  <h3>Screen Settings</h3>

  <?php

    echo form_open("screen_admin/save/$id");

    echo form_fieldset('Operating settings');

    foreach($rows['settings'][0] as $key => $value){
      echo '<div class="edit-field">';
      echo form_label(get_field_alias($key), $key);
      echo form_input($key, trim($value));
      echo '</div>';
    }

    echo form_fieldset_close();
    
    echo form_fieldset('Stops');
    
    $agencies = array(
        'metrobus'      =>  'Metrobus (WMATA)',
        'metrorail'     =>  'Metrorail (WMATA)',
        'dc-circulator' =>  'Circulator',
        'art'           =>  'ART'
    );
    echo '<ol>';
    echo '<div class="column-headers"><span>Stop IDs</span><span>Custom stop name (opt.)</span><span class="header-column">Column</span><span class="header-column">Position</span><span class="header-column-ct">Custom text</span></div>';
    for($r = 0; $r < 9; $r++) {

      for($c = 1; $c < 4; $c++){
        $coloptions[$c] = $c;
      }
      for($p = 1; $p < 10; $p++){
        $positionoptions[$p] = $p;
      }

      $limitoptions[0] = 'none';
      for($l = 1; $l < 20; $l++){
        $limitoptions[$l] = $l;
      }

      $serialstring = '';
      $pairids = array();      
      
      if(isset($rows['blocks'][$r]->stop)){
        foreach($rows['blocks'][$r]->stop as $key => $value){
          $serialstring .= $value['agency'] . ':' . $value['stop_id'] . ';';
          $pairids[] = $key;
        }
      }

      if(strlen($serialstring) > 0){
        $serialstring = substr($serialstring, 0, strlen($serialstring) - 1);
      }

      echo '<li class="stop-row">';
      if(isset($rows['blocks'][$r])){        
        //echo form_input('stop_ids[' . $rows['blocks'][$r]->id . ']', $rows['blocks'][$r]->stop);
        echo form_input('stop_ids[' . $rows['blocks'][$r]->id . ']', $serialstring);
        echo form_hidden('pair_ids[' . $rows['blocks'][$r]->id . ']', implode(',', $pairids));
        echo form_input('stop_names[' . $rows['blocks'][$r]->id . ']', $rows['blocks'][$r]->custom_name);
        echo form_dropdown('stop_columns[' . $rows['blocks'][$r]->id . ']',$coloptions,$rows['blocks'][$r]->column);
        echo form_dropdown('stop_positions[' . $rows['blocks'][$r]->id . ']',$positionoptions,$rows['blocks'][$r]->position);
        echo form_input('stop_custom_bodies[' . $rows['blocks'][$r]->id . ']', $rows['blocks'][$r]->custom_body);
        echo form_dropdown('stop_limits[' . $rows['blocks'][$r]->id . ']',$limitoptions,$rows['blocks'][$r]->limit);
      }
      else{
        echo form_input("new_stop_ids[$r]",'');
        echo form_input("new_stop_names[$r]",'');
        echo form_dropdown("new_stop_columns[$r]",$coloptions);
        echo form_dropdown("new_stop_positions[$r]",$positionoptions);
        echo form_input("new_stop_custom_bodies[$r]",'');
        echo form_dropdown("new_stop_limits[$r]",$limitoptions);
      }
      echo '</li>';
    }
    echo '</ol>';

    echo '  <div class="instructions">Format: [agency id]:[stop id], e.g. <em>metrobus:6000123</em>.
              <p>If several agencies serve a single stop, separate each agency-stop combination with semicolons.</p>
              <p>Agency codes:</p>
              <ul>
                <li>Metrorail: metrorail</li>
                <li>Metrobus: metrobus</li>
                <li>ART: art</li>
                <li>Circulator: dc-circulator</li>
                <li>Capital Bikeshare: cabi</li>
                <li>Custom text block: custom</li>
              </ul>             

              <p><strong>WMATA</strong>: Use the form below to help find nearby Metrobus stop IDs based on latitude and longitude coordinates.</p>';
 
    echo form_input(array(
        'id'    =>  'lat',
        'value' =>  '38.898732'
    ));
    echo form_input(array(
        'id'    =>  'lon',
        'value' => '-77.036605'
    ));
    echo form_button('finder', 'Find', 'onClick="find_stops()"');

    echo '    <p>View the <a href="http://api.wmata.com/Rail.svc/Stations?api_key=' . WMATAKEY . '" target="_blank">rail station codes</a>.</p>
              <p><strong>ART</strong>: Download and unzip the <a href="http://www.arlingtontransit.com/pages/rider-tools/tools-for-developers/" target="_blank">ART GTFS file</a>.  Open the stops.txt file, which is really a CSV file, in Excel or something similar.  Find the <em>stop_id</em> for the stop you want.</p>
              <p><strong>DC Circulator</strong>: Go to <a href="http://circulator.dc.gov/" target="_blank">circulator.dc.gov</a>, select the line and stop you want.  The stop id will appear as the number at the end of the URL in your browser address bar.</p>
              <p><strong>CaBi</strong>: Go to <a href="http://cabitracker.com" target="_blank">cabitracker.com</a>, and click the station you want. Click the <em>more data</em> link.  The station id will appear in the URL address bar of your browser.</p>';

    echo form_fieldset_close();
  
    echo form_submit('submit', 'Save');
    echo form_close();
  ?>
  
</div>