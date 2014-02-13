<?php
/**
 * Drupal7to8_Sniffs_Utility_TokensSubset.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

class Drupal7to8_Utility_TokensSubset {

  /**
   * @var array
   */
  protected $tokens = NULL;

  /**
   * Generates a tokens subset object for a given range of tokens.
   *
   * @param array $tokens
   *   Array of tokens as returned by PHP_CodeSniffer_File::getTokens()
   * @param int $start
   * @param int $end
   */
  public function __construct($tokens, $start, $end) {
    $this->tokens = array_slice($tokens, $start, $end, TRUE);
  }

  /**
   * Get the stored token range.
   *
   * @return array
   */
  public function getArray() {
    return $this->tokens;
  }

  /**
   * Get the token at the given position.
   *
   * @param int $pos
   *
   * @return array
   */
  public function getToken($pos) {
    return $this->tokens[$pos];
  }

  /**
   * Returns the start position of this token subset.
   *
   * @return int
   */
  public function getStart() {
    $token_keys = array_keys($this->tokens);
    return array_shift($token_keys);
  }

  /**
   * Returns the end position of this token subset.
   *
   * @return int
   */
  public function getEnd() {
    $token_keys = array_keys($this->tokens);
    return array_pop($token_keys);
  }

  /**
   * Retrieves the content (string representation) for a range of tokens.
   *
   * @param array $tokens
   *   Array of tokens as returned by PHP_CodeSniffer_File::getTokens()
   * @param int $start
   * @param int $end
   */
  public function getContent() {
    $content = '';
    for ($i = $this->getStart(); $i <= $this->getEnd(); $i++) {
      $content .= $this->tokens[$i]['content'];
    }

    return $content;
  }

}
