<?php

// This is the main page template for the screens.

// Load the screen header, which includes the references to the scripts, CSS,
// and other things.
$this->load->view('includes/screen_header');

// Print out the "Loading" box
print '<div id="loading-box">Loading</div>';

// Generate empty columns
for($c = 1; $c <= $numcols; $c++){
  print "<div class=\"col\" id=\"col-$c\"></div>";
}

print '<div id="results"></div>';

// Load the footer template to close out the page.
$this->load->view('includes/screen_footer');

?>

