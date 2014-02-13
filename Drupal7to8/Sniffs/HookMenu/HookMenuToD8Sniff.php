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

  protected $functionStart = 0;
  protected $functionStop = 0;
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
  public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
  {
      $tokens = $phpcsFile->getTokens();
      $module = Drupal7to8_Utility_ModuleProperties::getModuleName($phpcsFile);

      if ($tokens[$stackPtr]['type'] == 'T_FUNCTION' &&
         ($tokens[$stackPtr+2]['content'] == $module . '_menu' || $tokens[$stackPtr+2]['content'] == 'hook_menu')) {

        $this->functionStart  = $tokens[$stackPtr]['scope_opener'];
        $this->functionStop = $tokens[$stackPtr]['scope_closer'];
        $function_tokens = new Drupal7to8_Utility_TokenSubset($tokens, $this->functionStart + 1, ($this->functionStop - $this->functionStart - 1));
        if (Drupal7to8_Utility_ParseInfoHookArray::containsLogic($function_tokens, $phpcsFile, $this->menu_function_whitelist)) {
          $fix = $phpcsFile->addError('Routing functionality of hook_menu() has been replaced by new routing system, conditionals found, cannot change automatically: https://drupal.org/node/1800686', $stackPtr, 'HookMenuToD8');
          // Reset functionStart to 0 to stop the parser from further processing.
          $this->functionStart = $this->functionStop = 0;
          return;
        }

        // If we've gotten this far, eval the function
        $menu_array = Drupal7to8_Utility_ParseInfoHookArray::getHookReturnArray(file_get_contents(__DIR__ . '/drupal_menu_bootstrap.php.inc'), $function_tokens);

        // We're in hook_menu, throw this fixable error (to create YML files
        $fix = $phpcsFile->addFixableError('Routing functionality of hook_menu() has been replaced by new routing system: https://drupal.org/node/1800686', $stackPtr, 'HookMenuToD8');
        if ($fix === true && $phpcsFile->fixer->enabled === true) {
          // Remove the old file.
          // @todo This is not only dangerous, it also causes an error when the file
          // it was checking suddenly vanishes. ;)
          $yaml_route = array();
          foreach($menu_array AS $path => $item) {
            $item = new Drupal7to8_Sniffs_HookMenu_MenuItem($module, $path, $item);
            if($route = $item->getRouteYAML()) {
              $yaml_route += $route;
            }
          }
          Drupal7to8_Utility_CreateFile::writeYaml($module . '.routing.yml', $yaml_route);
        }
      }
  }//end process()
}
