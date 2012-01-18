<?php

//Blocks may need specific distribution

$this->load->view('includes/screen_header');

print '<div id="loading-box">Loading</div>';


// Generate empty columns
for($c = 1; $c <= $numcols; $c++){
  print "<div class=\"col\" id=\"col-$c\"></div>";
}

print '<div id="results"></div>';

$this->load->view('includes/screen_footer');

?>

