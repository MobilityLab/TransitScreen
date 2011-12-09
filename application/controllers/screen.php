<?php

class Screen extends CI_Controller {

  public function __construct() {

    parent::__construct();
 
  }

  public function index($screenid) {
    
    //If no parameter, redirect somewhere else
    if(!isset($screenid)){
      redirect('/');
    }

    $this->load->model('screen_model');

    $screen = new Screen_model();

    //Load variable of screen model type
    $screen->load_model($screenid);

    // Check for sleep mode to determine the view
    if($screen->is_asleep()) {     
      $data['template'] = 'three_col';
    }
    else {
      //Collect blocks

      $blocks = array();
      $newitem = array();
      $pairs = array();

      $stopids = $screen->stop_ids;
      $stopnames = $screen->stop_names;
      $stopcolumns = $screen->stop_columns;
      
      // For each stop block...
      for($s = 0; $s < count($screen->stop_ids); $s++){
        unset($newitem);

        $ids = explode(';', $stopids[$s]);
        unset($pairs);
        
        foreach($ids as $idpair){
          unset($newitem);
          

          $agencyandid = explode(':', $idpair);
          $newitem = array(
            'agency' => $agencyandid[0],
            'stopid' => $agencyandid[1],            
          );
          //print_r($newitem);
          $pairs[] = $newitem;
        }

        
        if(isset($stopnames[$s])){
          $blockname = $stopnames[$s];
        }
        else {
          $blockname = '';
        }
        $newblock = array(
          'name'    => $blockname,
          'column'  => $stopcolumns[$s],
          'ids'     => $pairs          
        );
        $blocks[] = $newblock;
      }

      $data['template'] = 'three_col';
      $data['data'] = $blocks;

    }
    $this->load->view('includes/screen_template', $data);
  }

  

 
  
}


?>
