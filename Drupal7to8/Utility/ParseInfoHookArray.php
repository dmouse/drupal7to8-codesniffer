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
   * Determine the module name based on the file being examined.
   *
   * @param PHP_CodeSniffer_File $phpcsFile
   *   The code sniffer file.
   * @return string|null
   *   The module name if it can be determined, NULL if it cannot.
   */
  static public function containsLogic(Drupal7to8_Utility_TokensSubset $subset, PHP_CodeSniffer_File $phpcsFile, $function_whitelist) {
    $tokens = $subset->getArray();
    foreach ($tokens as $pos => $token) {
      if (in_array($token, PHP_CodeSniffer_Tokens::$scopeOpeners) ||
        (Drupal7to8_Utility_FunctionCall::isFunctionCall($phpcsFile, $subset, $pos)) && !in_array($token['content'], $function_whitelist)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  static public function getArray($static_drupal_code, Drupal7to8_Utility_TokensSubset $token_range) {
    return eval($static_drupal_code . $token_range->getContent());
  }
}
