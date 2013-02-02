<?php
  $appendix = '?' . time(); // Appending this time integer forces the browser to refresh the file instead of using a cached version
?><!DOCTYPE html>
  <html>
  <head>
    <title>Transit arrival screen</title>
    <link rel="stylesheet" href="<?php echo base_url(); ?><?php echo PUBLICDIR; ?>css/screen.css" type="text/css" media="screen">

    <script type="text/javascript">
      var screen_id = '<?php print $id; ?>';
    </script>

    <script type="text/javascript" src="<?php echo base_url(); ?><?php echo PUBLICDIR; ?>scripts/jquery-1.7.1.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?><?php echo PUBLICDIR; ?>scripts/jquery.timers-1.2.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?><?php echo PUBLICDIR; ?>scripts/screen.js<?php print $appendix; ?>"></script>

    <link rel="stylesheet" href="<?php echo base_url(); ?><?php echo PUBLICDIR; ?>css/reset.css" type="text/css" media="screen">
    <link rel="stylesheet" href="<?php echo base_url(); ?><?php echo PUBLICDIR; ?>css/metro.css" type="text/css" media="screen">
    <link rel="stylesheet" href="<?php echo base_url(); ?><?php echo PUBLICDIR; ?>css/cabi.css" type="text/css" media="screen">
    <link rel="stylesheet" href="<?php echo base_url(); ?><?php echo PUBLICDIR; ?>css/bus.css" type="text/css" media="screen">

    <style type="text/css">
      body { background:#000000; margin:20px 20px 0 20px; font-family:"Helvetica Neue",Helvetica,Arial,sans-serif;}

      /* 1 column layout */
      #one-col { width:98%; margin:0 auto 0 auto;}

      /* 2 column layout */
      .total-cols-2 .col {
        width: 48%;
        float: left;
      }
      .total-cols-2 #col-1 {
        margin-right: 32px;
      }

      /* 3 column layout */
      .total-cols-3 .col {
        width: 31%;
        float: left;
      }
      .total-cols-3 #col-1 {
        margin-right: 32px;
      }
      .total-cols-3 #col-2 {
        margin-right: 32px;
      }

      #cabi_table td {
        vertical-align: middle;
        line-height: 1em;
      }

      body {
        zoom: <?php print $zoom; ?>;
      }

    </style>

  </head>
  <body class="total-cols-<?php print $numcols; ?>">
   