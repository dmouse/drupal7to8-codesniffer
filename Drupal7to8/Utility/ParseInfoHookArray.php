<?php

/**
 * Drupal7to8_Sniffs_Utility_ParseInfoHookArray.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

class Drupal7to8_Utility_ParseInfoHookArray {

  /**
   * Checks whether the info hook contains logic or function calls.
   *
   * @param Drupal7to8_Utility_TokenSubset $subset
   *   The token subset object for the info hook.
   * @param PHP_CodeSniffer_File $phpcsFile
   *   The code sniffer file.
   * @param array $function_whitelist
   *   (optional) An array of functions to allow. You should ensure these
   *   functions are available to getArray() in the $static_drupal_code
   *   parameter.
   *
   * @return string|null
   *   The module name if it can be determined, NULL if it cannot.
   */
  static public function containsLogic(Drupal7to8_Utility_TokenSubset $subset, PHP_CodeSniffer_File $phpcsFile, array $function_whitelist = array()) {
    $tokens = $subset->getArray();
    foreach ($tokens as $pos => $token) {
      if (in_array($token, PHP_CodeSniffer_Tokens::$scopeOpeners) ||
        (Drupal7to8_Utility_FunctionCall::isFunctionCall($phpcsFile, $subset, $pos)) && !in_array($token['content'], $function_whitelist)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Evaluates tokens for the info hook and returns a PHP array.
   *
   * @param string $static_drupal_code
   *   A string of Drupal code to append at the beginning of the eval()
   *   statement. You can use this to provide constants, function definitions,
   *   etc. to the info hook.
   * @param Drupal7to8UtilityTokenSubset $token_range
   *   The token range object for the info hook.
   *
   * @return array
   *   The return value of the info hook.
   */
  static public function getArray($static_drupal_code, Drupal7to8_Utility_TokenSubset $token_range) {
    return eval($static_drupal_code . $token_range->getContent());
  }
}
