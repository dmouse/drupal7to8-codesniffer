<?php
  /**
   * Drupal7to8_Sniffs_HookMenu_HookMenuToD8.
   *
   * PHP version 5
   *
   * @category PHP
   * @package  PHP_CodeSniffer
   * @link     http://pear.php.net/package/PHP_CodeSniffer
   */

  /**
   * Warns that .info files are now .info.yml files.
   *
   * @category PHP
   * @package  PHP_CodeSniffer
   * @link     http://pear.php.net/package/PHP_CodeSniffer
   */
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Dumper;

class Drupal7to8_Sniffs_HookMenu_HookMenuToD8Sniff implements PHP_CodeSniffer_Sniff {

  protected $array_parent = FALSE;
  protected $return_var = '';
  protected $menu_paths = array();
  protected $menu_function_whitelist = array('drupal_get_path', 't');

  /**
   * Returns an array of tokens this test wants to listen for.
   *
   * @return array
   */
  public function register()
  {
    return array(T_FUNCTION);

  }//end register()

  /**
   * Processes this test, when one of its tokens is encountered.
   *
   * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
   * @param int                  $stackPtr  The position of the current token
   *                                        in the stack passed in $tokens.
   *
   * @return void
   */
  public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
    $function = new  Drupal7to8_Utility_InfoHook($phpcsFile, $stackPtr);

    // Only proceed if this is a hook_menu() declaration.
    if (!$function->isHookImplementation('hook_menu')) {
      return;
    }
    $module = Drupal7to8_Utility_ModuleProperties::getModuleName($phpcsFile);

    // If the function contains logic, add a non-fixable error.
    if ($function->containsLogic($this->menu_function_whitelist)) {
      $fix = $phpcsFile->addError('Routing functionality of hook_menu() has been replaced by new routing system, conditionals found, cannot change automatically: https://drupal.org/node/1800686', $stackPtr, 'HookMenuToD8');
      return;
    }

    // Otherwise, the function is safe to evaluate for its return value.
    include_once 'menu_constants.inc';
    $menu_array = $function->getHookReturnArray(file_get_contents(__DIR__ . '/drupal_menu_bootstrap.php.inc'));
    // We're in hook_menu(). Throw this fixable error (to create YML files).
    $fix = $phpcsFile->addFixableError('Routing functionality of hook_menu() has been replaced by new routing system: https://drupal.org/node/1800686', $stackPtr, 'HookMenuToD8');
    if ($fix === true && $phpcsFile->fixer->enabled === true) {
      $yaml_route = $yaml_local_tasks = array();
      $menu = new Drupal7to8_Sniffs_HookMenu_MenuItems($module, $menu_array);
      if ($routing = $menu->getRouteYAML()) {
        Drupal7to8_Utility_CreateFile::writeYaml(Drupal7to8_Utility_ModuleProperties::getModuleFilePath($phpcsFile, $module . '.routing.yml'), $routing);
      }
      if($local_tasks = $menu->getLocalTasksYAML()) {
        Drupal7to8_Utility_CreateFile::writeYaml(Drupal7to8_Utility_ModuleProperties::getModuleFilePath($phpcsFile, $module . '.local_tasks.yml'), $local_tasks);
      }
    }
  }

}
