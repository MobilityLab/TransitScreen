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
    echo '  <div class="instructions">Format: [agency id]:[stop id], e.g. <em>metrobus:6000123</em>.
              <p>If several agencies serve a single stop, separate each agency-stop combination with semicolons.</p>
              <p>Agency codes:</p>
              <ul>
                <li>Metrorail: metrorail</li>
                <li>Metrobus: metrobus</li>
                <li>ART: art</li>
                <li>Circulator: dc-circulator</li>
                <li>Capital Bikeshare: cabi</li>
              </ul>
            </div>';
    $agencies = array(
        'metrobus'      =>  'Metrobus (WMATA)',
        'metrorail'     =>  'Metrorail (WMATA)',
        'dc-circulator' =>  'Circulator',
        'art'           =>  'ART'
    );
    echo '<ol>';
    echo '<div class="column-headers"><span>Stop IDs</span><span>Custom stop name (optional)</span><span class="header-column">Column</span><span class="header-column">Position</span></div>';
    for($r = 0; $r < 9; $r++) {
      
      $coloptions = array(
                  1 => '1',
                  2 => '2',
                  3 => '3'
                );
      $positionoptions = array(
                  1 => '1',
                  2 => '2',
                  3 => '3',
                  4 => '4'
                );

      $serialstring = '';
      $pairids = array();
      
      //print_r($rows['blocks'][$r]->stop);
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
      }
      else{
        echo form_input("new_stop_ids[$r]",'');
        echo form_input("new_stop_names[$r]",'');
        echo form_dropdown("new_stop_columns[$r]",$coloptions);
        echo form_dropdown("new_stop_positions[$r]",$positionoptions);
      }
      echo '</li>';
    }
    echo '</ol>';
    echo form_fieldset_close();
  
    echo form_submit('submit', 'Save');
  ?>
  
</div>