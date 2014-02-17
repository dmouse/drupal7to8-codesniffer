<?php
/**
 * Drupal7to8_Sniffs_Misc_DefineToConstSniff.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Use const keyword to define constants instead of define().
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class Drupal7to8_Sniffs_Misc_DefineToConstSniff extends Drupal7to8_Base_FunctionReplacementSniff {

  protected $message = 'Use const keyword to define constants instead of define(): https://drupal.org/node/1362360';

  protected $code = 'DefineToConst';

  protected $forbiddenFunctions = array(
    'define' => NULL,
  );

  /**
   * {@inheritdoc}
   */
  protected function addError($phpcsFile, $stackPtr, $function, $pattern = NULL) {
    $fix = $phpcsFile->addFixableError($this->message, $stackPtr, $this->code);

    if ($fix === TRUE && $phpcsFile->fixer->enabled === TRUE) {
      $tokens = $phpcsFile->getTokens();

      $close_parenthesis = $tokens[$stackPtr + 1]['parenthesis_closer'];

      // Parse the constant name.
      $constant_name_pos = $this->findNthArgument($phpcsFile, $stackPtr, 0);
      $constant_name = Drupal7to8_Utility_TokenRange::getContent($tokens, $constant_name_pos[0], $constant_name_pos[1]);
      $constant_name = substr($constant_name, 1, -1);

      // Parse the constant value.
      $constant_value_pos = $this->findNthArgument($phpcsFile, $stackPtr, 1);

      // Replace the define() call with a constant declaration.
      $phpcsFile->fixer->replaceToken($stackPtr, 'const ' . $constant_name . ' = ');
      // Delete everything until the semicolon except for the constant's value.
      Drupal7to8_Utility_TokenRange::remove($phpcsFile->fixer, $stackPtr + 1, $constant_value_pos[0] - 1);
      Drupal7to8_Utility_TokenRange::remove($phpcsFile->fixer, $constant_value_pos[1] + 1, $close_parenthesis);
    }
  }

}
