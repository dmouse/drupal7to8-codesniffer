<?php

class Drupal7to8_Utility_ParseInfoHookArray {

  /**
   * Retrieves the token subset for the contents of a hook invocation.
   *
   * @param string $hook_name
   *   The name of the hook to match, for example 'hook_menu'.
   * @param PHP_CodeSniffer_File $phpcsFile
   *   The code sniffer file.
   * @param int $stackPtr
   *   The index of the T_FUNCTION token to check.
   *
   * @return bool
   *   TRUE if the token declares the invocation of the given hook for the
   *   current module, or FALSE otherwise.
   */
  public static function isHookImplementation($hook_name, PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
    $tokens = $phpcsFile->getTokens();
    $module_name = Drupal7to8_Utility_ModuleProperties::getModuleName($phpcsFile);
    if ($tokens[$stackPtr]['type'] != 'T_FUNCTION') {
      throw new \Exception("Token pointer $stackPtr must be a T_FUNCTION token.");
    }
    if (substr($hook_name, 0, 5) !== 'hook_') {
      throw new \Exception("$hook_name must begin with 'hook_'");
    }

    if ($phpcsFile->getDeclarationName($stackPtr) == $module_name . '_' . substr($hook_name, 5)) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Gets the token subset for the contents of a function definition.
   *
   * This will return the tokens for the contents of the function only, so that
   * they can be parsed and evaluated. So, for example, if the function is:
   * @code
   * function mymodule_foo() {
   *   $foo = 'mystring';
   *   return $foo;
   * }
   * @endcode
   * This method will return:
   * @code
   *   $foo = 'mystring';
   *   return $foo;
   * @endcode
   *
   * @param PHP_CodeSniffer_File $phpcsFile
   *   The code sniffer file.
   * @param int $stackPtr
   *   The index of the T_FUNCTION token to retrieve contents.
   *
   * @return Drupal7to8_Utility_TokenSubset $subset
   *   A token subset object for the full contents of the function definition.
   */
  public static function getFunctionContentTokens(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
    $tokens = $phpcsFile->getTokens();
    if ($tokens[$stackPtr]['type'] != 'T_FUNCTION') {
      throw new \Exception("Token pointer $stackPtr must be a T_FUNCTION token.");
    }
    // Return the token subset between, but not including, the { and }.
    return new Drupal7to8_Utility_TokenSubset($phpcsFile, $tokens[$stackPtr]['scope_opener'] + 1, $tokens[$stackPtr]['scope_closer'] - 1);
  }

  /**
   * Checks whether the info hook contains logic or function calls.
   *
   * @param Drupal7to8_Utility_TokenSubset $subset
   *   The token subset object for the info hook.
   * @param PHP_CodeSniffer_File $phpcsFile
   *   The code sniffer file.
   * @param array $function_whitelist
   *   (optional) An array of functions to allow. You should ensure these
   *   functions are available to getHookReturnArray() in the
   *   $static_drupal_code parameter.
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
  static public function getHookReturnArray($static_drupal_code, Drupal7to8_Utility_TokenSubset $token_range) {
    return eval($static_drupal_code . $token_range->getContent());
  }
}
