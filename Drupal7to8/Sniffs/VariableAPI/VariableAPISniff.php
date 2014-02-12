<?php
/**
 * Drupal7to8_Sniffs_VariableAPI_VariableAPISniff.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Handles variable_get(), variable_set() and variable_del().
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class Drupal7to8_Sniffs_VariableAPI_VariableAPISniff extends Drupal7to8_Base_FunctionReplacementSniff {

  protected $message = '!function() has been replaced by the Configuration API: https://drupal.org/node/2183531';

  protected $code = 'VariableAPI';

  protected $forbiddenFunctions = array(
    'variable_get' => '\Drupal::config()->get()',
    'variable_set' => NULL,
    'variable_del' => NULL,
  );

  protected $dynamicArgumentReplacements = array(
    'variable_get' => array(
      'arguments' => array(1),
    ),
  );

  /**
   * {@inheritdoc}
   */
  protected function addError($phpcsFile, $stackPtr, $function, $pattern = NULL) {
    $fix = FALSE;
    $message = strtr($this->message, array('!function' => $function));

    $phpcsFile->addFixableError($message, $stackPtr, $this->code);
    if ($this->hasFix($function, $pattern)) {
      $fix = $phpcsFile->addFixableError($message, $stackPtr, $this->code);
    }
    elseif ($phpcsFile->fixer->enabled === TRUE) {
      $this->insertFixMeComment($phpcsFile, $stackPtr, $message);
    }
    if ($fix === TRUE && $phpcsFile->fixer->enabled === TRUE) {
      $tokens = $phpcsFile->getTokens();
      // Find the arguments that need to be moved around, remove them, and
      // dynamically build the replacement string for this function call.
      switch ($function) {
        case 'variable_get':
          $result = $this->findNthArgument($phpcsFile, $stackPtr, 0);
          // Return early if there is no variable to get.
          if ($result === FALSE) {
            continue;
          }
          // Find the variable name to get
          list($arg_start, $arg_end, $remove_start, $remove_end) = $result;
          $varname = $this->getContentForTokenRange($tokens, $arg_start, $arg_end);

          // Convert to the new config object name and updated variable name.
          list($config_object_name, $updated_varname) = $this->updateVariableName($varname, $phpcsFile);

          // Update to the new statement.
          $replacement = "\Drupal::config('" . $config_object_name . "')->get('" . $updated_varname . "')";

          // Remove the rest of the function tokens.
          $openParenthesis = $phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($stackPtr + 1), NULL, TRUE);
          $closeParenthesis = $tokens[$openParenthesis]['parenthesis_closer'];
          $this->removeTokenRange($phpcsFile->fixer, $openParenthesis, $closeParenthesis);
          $phpcsFile->fixer->replaceToken($stackPtr, $replacement);
          break;
      }

      $this->insertUseStatement($phpcsFile, $function, $pattern);
    }
    elseif ($fix === FALSE) {
      $phpcsFile->addError($message, $stackPtr, $this->code);
    }
  }

  /**
   * Update the variable name used to conform to the new settings format.
   * @param $varname
   */
  function updateVariableName($varname, $phpcsFile) {
    // replace single quotes
    $varname = str_replace("'", "", $varname);
    $module_name = Drupal7to8_Utility_ModuleProperties::getModuleName($phpcsFile);
    $var_parts = explode('_', $varname);
    if ($var_parts[0] == $module_name) {
      array_shift($var_parts);
    }
    return array($module_name . '.setting', implode('_', $var_parts));
  }
}
