<?php

/**
 * Defines a subset of tokens from a codesniffer file.
 */
class Drupal7to8_Utility_TokenSubset {

  /**
   * The array of token information, keyed by the original token index.
   *
   * @var array
   */
  protected $tokens = array();

  /**
   * The starting token index.
   *
   * @var int
   */
  protected $start = 0;

  /**
   * The ending token index.
   *
   * @var int
   */
  protected $end = 0;

  /**
   * The Code Sniffer file object to which the tokens belong.
   *
   * @var PHP_CodeSniffer_File
   */
  protected $phpcsFile;

  /**
   * A list of function call names to whitelist (allow) during processing.
   *
   * @var array
   */
  protected $whitelist = array();

  /**
   * Generates a tokens subset object for a given range of tokens.
   *
   * @param PHP_CodeSniffer_File $phpcsFile
   *   The code sniffer file.
   * @param int $start
   *   The index of the token to start from.
   * @param int $end
   *   The index of the token to end with.
   */
  public function __construct(PHP_CodeSniffer_File $phpcsFile, $start, $end) {
    $tokens = $phpcsFile->getTokens();
    $this->start = $start;
    $this->end = $end;
    $this->phpcsFile = $phpcsFile;

    // Add 1 to include the final token in the array slice. For example, if the
    // start token is 0 and the end token is 4, the length should be 5.
    $length = $end - $start + 1;
    $this->tokens = array_slice($tokens, $start, $length, TRUE);
  }

  /**
   * Get the stored token range.
   *
   * @return array
   *   All tokens for the subset, indexed by the original index.
   */
  public function getArray() {
    return $this->tokens;
  }

  /**
   * Get the token at the given position.
   *
   * @param int $pos
   *   The position index.
   *
   * @return array
   *   The complete array for the token.
   */
  public function getToken($pos) {
    return $this->tokens[$pos];
  }

  /**
   * Returns the start position of this token subset.
   *
   * @return int
   *   The index of the starting token.
   */
  public function getStart() {
    return $this->start;
  }

  /**
   * Returns the end position of this token subset.
   *
   * @return int
   *   The index of the end token.
   */
  public function getEnd() {
    return $this->end;
  }

  /**
   * Retrieves the content (string representation) for a range of tokens.
   *
   * @return string content
   *   A string of the full range of content for the set.
   */
  public function getContent() {
    $content = '';
    for ($i = $this->getStart(); $i <= $this->getEnd(); $i++) {
      $content .= $this->tokens[$i]['content'];
    }

    return $content;
  }

  /**
   * Sets the function whitelist for function calls to allow during processing.
   *
   * @param array $whitelist
   *   An array of function names to whitelist, e.g. array('t', 'check_plain').
   *
   * @return $this
   */
  public function setWhitelist(array $whitelist) {
    $this->whitelist = $whitelist;
    return $this;
  }

  /**
   * Checks whether the token subset contains logic or function calls.
   *
   * @param array $function_whitelist
   *   (optional) An array of functions to allow.
   *
   * @return bool
   *   Whether the tokens include any logic or function calls.
   */
  public function containsLogic() {
    $tokens = $this->getArray();
    foreach ($tokens as $pos => $token) {
      if (in_array($token, PHP_CodeSniffer_Tokens::$scopeOpeners) ||
        (Drupal7to8_Utility_FunctionCall::isFunctionCall($this->phpcsFile, $this, $pos)) && !in_array($token['content'], $this->whitelist)) {
        return TRUE;
      }
    }
    return FALSE;
  }

}
