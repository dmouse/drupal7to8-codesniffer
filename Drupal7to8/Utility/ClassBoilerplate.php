<?php
/**
 * Drupal7to8_Utility_ClassBoilerplate
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Utility class for writing out class files based on boilerplate templates.
 */
class Drupal7to8_Utility_ClassBoilerplate {

  /**
   * Replace a set of tokens in a boilerplate class template.
   *
   * @param string $boilerPath
   *   The path to the boilerplate file.
   * @param array $boilerTokens
   *   An associative array of tokens to replace in the boilerplate file,
   *   with the tokens as the keys and the values as the replacements
   *
   * @return string|null
   *   The PHP code with the tokens replaced, or NULL on failure.
   */
  static public function replaceTokens($boilerPath, array $boilerTokens) {
    if ($boilerplate = file_get_contents($boilerPath)) {
      return str_replace(array_keys($boilerTokens), array_values($boilerTokens), $boilerplate);
    }
  }
}
