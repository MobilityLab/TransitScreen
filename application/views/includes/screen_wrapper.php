<?php
  
  $callurl = base_url() . 'index.php/screen/inner/'   . $id;
  $pollurl = base_url() . 'index.php/update/version/' . $id;

?><html>
  <head>
    <title>Transit Screen</title>
    <script type="text/javascript" src="<?php print base_url(); ?>/public/scripts/jquery-1.7.1.min.js"></script>
    <script type="text/javascript" src="<?php print base_url(); ?>/public/scripts/jquery.timers-1.2.js"></script>
    
    <script type="text/javascript">
      var now = Math.round(new Date().getTime() / 1000);  
      var latestv = ''; 
      var frameclass = '';
      
      $(document).ready(function(){
        //Call the update function
        get_update();
      });
      
      // Poll the server to find the latest version number;
      $(document).everyTime(25000, function(){
        get_update();      
      });
      
      function get_update() {
        // Poll the server for the latest version number
        
        $.getJSON('<?php print $pollurl; ?>',function(versionval){
          // If that version number differs from the current version number,
          // create a new hidden iframe and append it to the body.  ID = version num
          if(versionval != latestv){
            
            //If the element already exists, remove it and replace it with a new version
            if($('#frame-' + versionval).length > 0){
              $('#frame-' + versionval).remove();  
            }
            
            $('<iframe />', {
              id:     'frame-' + versionval,
              class:  frameclass,
              src:    '<?php print $callurl; ?>?' + now
            }).appendTo('body');
            
            frameclass = 'hidden';    
          
            // Wait 15 seconds and call another function to check the status
            // of the new iframe
            setTimeout('switch_frames(' + versionval + ');',15000);
          }         
          
        })
        .error(function() {
            
        }); 
      }
      
      function switch_frames(ver) {        
        var newname = '#frame-' + ver;
        
        //console.log('blocks in ' + newname + ': ' + $(newname).contents().find('.block').length);
        
        // If the new iframe has populated with .blocks, remove the old iframe
        // and show the new one                
        if($(newname).contents().find('.block').length > 0) {
          // For each iframe, if the id doesn't equal newname, remove it
          $.each($('iframe'), function(i, frame) {
            //console.log('frame.id = ' + frame.id + '; compare to: frame' + ver);
            if(frame.id != 'frame-' + ver){
              $('#' + frame.id).remove();
            }
            
            // And show the new iframe by removing the .hidden class
            $(newname).attr('class', '');            
            // Set the latest version variable to the new version
            latestv = ver;
            
            //console.log(frame.id);
          });
          
        }      
        // Else, remove the new, hidden iframe
        else {
          $(newname).remove();
          //console.log('Removed ' + newname);
        }
      }
      
      
      
    </script>
    
    <style type="text/css">
      body {
        margin: 0;
        background-color: #000;
      }
      iframe {
        border: 0;
        width: 100%;
        height: 100%;
      }
      .hidden {
        display: none;
      }
    </style>
  
  </head>
  
  <body>
    
  </body>
</html>