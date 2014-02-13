/* Wrapper function for drupal_get_path for menus */
if(function_exists('drupal_get_path')) {
  function drupal_get_path($type, $name) {
    return '.';
  }
}
/* Wrapper function to return t() */
if (function_exists('t')) {
  function t($string, array $args = array(), array $options = array()) {
    return $string;
  }
}
// end !defined() check.