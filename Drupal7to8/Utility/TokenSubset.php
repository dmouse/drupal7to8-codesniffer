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

}
