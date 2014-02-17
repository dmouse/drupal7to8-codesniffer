/* Wrapper function for drupal_get_path() for menus */
if (!function_exists('drupal_get_path')) {
  function drupal_get_path($type, $name) {
    return '.';
  }
}
