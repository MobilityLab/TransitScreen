<html>
  <head>
    <title>Transit arrival screen</title>
    <link rel="stylesheet" href="<?php echo base_url(); ?>css/screen.css" type="text/css" media="screen" />
    <script src="<?php echo base_url(); ?>scripts/jquery-1.7.1.min.js"></script>
    <script src="<?php echo base_url(); ?>scripts/underscore-min.js"></script>
    <script src="<?php echo base_url(); ?>scripts/backbone-min.js"></script>

    <!-- Models -->
    <script src="<?php echo base_url(); ?>scripts/stop.js"></script>
    <script src="<?php echo base_url(); ?>scripts/bus.js"></script>

    <script>
      
      
      stop123 = new Stop({
        name:   'Nearby stop'
      });

      bus123 = new Bus({
        route:        '38B',
        destination:  'Ballston',
        prediction:   3
      });
      
      document.write(bus123.get('destination'));
      
    </script>
  </head>
  <body>