<?php

/**
 * Provides handling for function call tokens.
 */
class Drupal7to8_Utility_FunctionCall {

  /**
   * Determines whether a token is a function call.
   *
   * @param PHP_CodeSniffer_File $phpcsFile
   *   The code sniffer file.
   * @param Drupal7to8_Utility_TokenSubset $subset
   *   A token subset containing a potential function name
   * @param int $stackPtr
   *   The index of the token within $subset that may be a function name.
   *
   * @return bool
   *   Whether or not the token represents a function call.
   */
  static public function isFunctionCall(PHP_CodeSniffer_File $phpcsFile, Drupal7to8_Utility_TokenSubset $subset, $stackPtr) {

    $token_info = $subset->getToken($stackPtr);
    if ($token_info['type'] !== "T_STRING") {
      return FALSE;
    }
    $ignore = array(
               T_DOUBLE_COLON,
               T_OBJECT_OPERATOR,
               T_FUNCTION,
               T_CONST,
               T_PUBLIC,
               T_PRIVATE,
               T_PROTECTED,
               T_AS,
               T_NEW,
               T_INSTEADOF,
               T_NS_SEPARATOR,
               T_IMPLEMENTS,
              );

    $prevToken = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
    $token_info = $subset->getToken($prevToken);
    if (in_array($token_info['code'], $ignore) === true) {
        // Not a call to a PHP function.
      return FALSE;
    }

    $nextToken = $phpcsFile->findNext(T_OPEN_PARENTHESIS, ($stackPtr + 1));
    if ($nextToken <= $subset->getEnd()) {
      $token_info = $subset->getToken($nextToken - 1);
      $backptr = ($token_info['type'] == T_WHITESPACE) ? 2 : 1;
      if ($nextToken - $backptr == $stackPtr) {
        return TRUE;
      }
    }
    return FALSE;
  }

}
