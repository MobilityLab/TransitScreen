<html>
  <head>
    <title></title>
    <link rel="stylesheet" href="<?php echo base_url(); ?><?php echo PUBLICDIR; ?>css/style.css" type="text/css" media="screen" />

    <script type="text/javascript">
      function find_stops(){
        var lat = document.getElementById('lat').value;
        var lon = document.getElementById('lon').value;
        var key = '<?php print WMATAKEY; ?>';
        var radius = 500;

        var url = 'http://api.wmata.com/Bus.svc/Stops?radius=' + radius + '&api_key=' + key + '&lat=' + lat + '&lon=' + lon;

        window.open(url);
      }
    </script>

  </head>
  <body>