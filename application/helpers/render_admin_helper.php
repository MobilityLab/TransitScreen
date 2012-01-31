<?php


  /**
   * Function: get_field_alias
   *
   * @param string $input
   * @return string
   *
   * This function replaces the machine formated database field names with
   * human-readable names.
   *
   */
  function get_field_alias($input){

    $input = str_replace('MoTh_', 'Monday - Thursday ', $input);
    $input = str_replace('Fr_', 'Friday ', $input);
    $input = str_replace('Sa_', 'Saturday ', $input);
    $input = str_replace('Su_', 'Sunday ', $input);

    $input = preg_replace('/\sop$/', ' wakeup time', $input);
    $input = preg_replace('/\scl$/', ' sleep time', $input);
    $input = preg_replace('/^name$/', 'Internal name', $input);

    return $input;
   
  }

  /**
   * Function: get_verbose_status
   *
   * @param string $input
   * @return string
   *
   * This returns a more descriptive status message to the user.  It may not be
   * in use anymore.
   *
   */
  function get_verbose_status($input) {
    switch($input){
      case 'success':
         return 'Changes saved.';
      case 'success':
         return 'Screen created.';
      default:
         return '';
    }
  }

