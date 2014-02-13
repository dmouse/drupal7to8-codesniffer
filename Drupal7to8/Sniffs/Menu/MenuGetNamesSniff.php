<?php
/**
 * Drupal7to8_Sniffs_Menu_MenuGetNamesSniff.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * menu_get_names() was removed.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class Drupal7to8_Sniffs_Menu_MenuGetNamesSniff extends Drupal7to8_Base_FunctionReplacementSniff {

  protected $message = '!function() was removed: https://drupal.org/node/1357900';

  protected $code = 'MenuGetNames';

  protected $forbiddenFunctions = array(
    'menu_get_names' => NULL,
  );

}
