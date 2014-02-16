<?php

/**
 * Drupal7to8_Sniffs_Utility_InfoHook.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Defines a token subset for the contents of an info hook implementation.
 */
class Drupal7to8_Utility_InfoHook extends Drupal7to8_Utility_FunctionDeclaration {

  /**
   * Evaluates tokens for the info hook and returns a PHP array.
   *
   * @param string $static_drupal_code
   *   A string of Drupal code to append at the beginning of the eval()
   *   statement. You can use this to provide constants, function definitions,
   *   etc. to the info hook.
   *
   * @return array
   *   The return value of the info hook.
   */
  public function getHookReturnArray($static_drupal_code) {
    return eval($static_drupal_code . $this->getContent());
  }

}
