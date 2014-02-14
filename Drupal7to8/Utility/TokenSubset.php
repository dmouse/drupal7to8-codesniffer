<?php
/**
 * Drupal7to8_Sniffs_Utility_TokenSubset.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

class Drupal7to8_Utility_TokenSubset {

  /**
   * The token subset.
   *
   * @var array
   */
  protected $tokens = NULL;

  /**
   * The index of the start token.
   *
   * @var int
   */
  protected $start = 0;

  /**
   * The index of the end token.
   *
   * @var int
   */
  protected $end = 0;

  /**
   * Generates a tokens subset object for a given range of tokens.
   *
   * @param array $tokens
   *   Array of tokens as returned by PHP_CodeSniffer_File::getTokens()
   * @param int $start
   * @param int $end
   */
  public function __construct($tokens, $start, $end) {
    $this->start = $start;
    $this->end = $end;
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
    return $this->start;
  }

  /**
   * Returns the end position of this token subset.
   *
   * @return int
   */
  public function getEnd() {
    return $this->end;
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
