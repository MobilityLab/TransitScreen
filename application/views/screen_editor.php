<?php
  // This is the screen editor view.  It is called from the edit method of the
  // screen_admin controller.  That is where the $rows array is created.

  if($rows['settings'][0]->id != 0){
    // Screen exists, get id and title
    $id = $rows['settings'][0]->id;
    $title = $rows['settings'][0]->name;
    $create = false;
  }
  else {
    // Create a random ID
    // MSC need to check that ID doesn't exist yet
    $id = rand(0,999999);
    $rows['settings'][0]->id = $id;
    $title = 'Create a new screen';
    $create = true;
  }
?>

<div id="screen-fields">
  <h2><?php print $title; ?></h2>
  <h3>Screen Settings</h3>

  <?php
    // Open the form and set the action attribute
    echo form_open("screen_admin/save/$create");

    // Create a field set for the screen properties and print them out with
    // labels
    echo form_fieldset('Operating settings');
    foreach($rows['settings'][0] as $key => $value){
      echo '<div class="edit-field">';
      echo form_label(get_field_alias($key), $key);
      echo form_input($key, trim($value));
      echo '</div>';
    }
    echo form_fieldset_close();
    
    // Create a field set for the stops
    echo form_fieldset('Stops');
    
    $agencies = array(
        'metrobus'      =>  'Metrobus (WMATA)',
        'metrorail'     =>  'Metrorail (WMATA)',
        'dc-circulator' =>  'Circulator',
        'art'           =>  'ART'
    );

    // Print out block section with space for 9 blocks
    echo '<ol>';
    echo '<div class="column-headers"><span>Stop IDs</span><span>Custom stop name (opt.)</span><span class="header-column">Column</span><span class="header-column">Position</span><span class="header-column-ct">Custom text</span><span>Limit</span></div>';
    for($r = 0; $r < 9; $r++) {

      // Set the options for the columns, positions, and item limits
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

      // Write the existing agency-stop pairs to the block boxes
      if(isset($rows['blocks'][$r]->stop)){
        foreach($rows['blocks'][$r]->stop as $key => $value){
          $serialstring .= $value['agency'] . ':' . $value['stop_id'] . ';';
          $pairids[] = $key;
        }
      }

      if(strlen($serialstring) > 0){
        $serialstring = substr($serialstring, 0, strlen($serialstring) - 1);
      }

      // Write out the lines for each of the blocks.  Existing blocks are written
      // out first and empty blocks are written out second (in the else{})
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
    
    echo form_fieldset_close();

    echo form_submit('submit', 'Save');
    echo form_close();
  ?>
  
  <?php
    $this->load->view('includes/screen_admin_instructions');
  ?>
  
</div>