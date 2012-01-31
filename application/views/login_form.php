<div id="login-form">
  <h2>Site login</h2>

  <?php
    // This is the login form. It uses the Code Igniter form funcitons to
    // create and print the form.
  
    echo form_open('login/validate_credentials');
    echo form_input('username', set_value('username','Email'));
    echo form_password('password', 'Password');
    echo form_submit('submit', 'Login');

  ?>
</div>