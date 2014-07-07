<?php

/**
 * Defines a token subset for the contents of an info hook implementation.
 */
class Drupal7to8_Utility_InfoHook extends Drupal7to8_Utility_FunctionDeclaration {

  /**
   * Constants replacements to subsitute for constant tokens in processing.
   *
   * Mixed replacement values for constants, keyed by the constant name, so
   * that eval() can be used in getContent() without the constants defined
   * locally.
   *
   * @var array
   */

  public $replacements = array();

  /**
   * Boolean flag indicating whether unrecognized constants should be escaped.
   *
   * If set, constants not replaced by $replacements will be escaped to a
   * string placeholder so that eval() can be used without the constants
   * defined.
   *
   * @var bool
   */
  protected $escapeConstants = FALSE;

  /**
   * A list of constants that were escaped to strings during processing.
   *
   * @var array
   */
  protected $escapedConstants = array();

  /**
   * A list of function call names to whitelist (allow) during processing.
   *
   * @var array
   */
  protected $whitelist = array('t');

  /**
   * Configures the InfoHoOk to escape constants during processing.
   *
   * @return $this
   */
  public function enableEscapedConstants() {
    $this->escapeConstants = TRUE;
    return $this;
  }

  /**
   * Configures the InfoHoOk to not escape constants during processing.
   *
   * @return $this
   */
  public function disableEscapedConstants() {
    $this->escapeConstants = FALSE;
    return $this;
  }

  /**
   * Retrieves the content (string representation) for a range of tokens.
   *
   * Constants that are not whitelisted or replaced with specific values are
   * escaped to strings in in getContent() if $this->escapeConstants is TRUE.
   *   (optional) Defaults to FALSE.
   *
   * @return string content
   *   A string of the full range of content for the set.
   *
   * @todo Refactor the FunctionReplacementSniff so we can safely replace
   *   complete function calls as well?
   */
  public function getContent() {
    $content = '';
    foreach ($this->tokens as $i => $token) {
      $content .= $this->getTokenContent($i);
    }

    return $content;
  }

  /**
   * Gets the content of a token in the set, with any escaping or replacements.
   *
   * @param int $token_index
   *   The index of the token in the set.
   *
   * @return string
   *   The content to subsitute for the token.
   */
  public function getTokenContent($token_index) {
    $token = $this->tokens[$token_index];
    $token_content = $token['content'];

    // Replace function and constant names.
    if ($token['type'] == 'T_STRING') {
      if (!empty($this->replacements[$token['content']])) {
        $token_content = $this->replacements[$token['content']];
      }
      elseif ($this->escapeConstants && !in_array($token['content'], $this->whitelist)) {
        $token_content = "'" . $token['content'] . "'";
        $this->escapedConstants = $token['content'];
      }
    }

    return $token_content;
  }

  /**
   * Evaluates tokens for the info hook and returns a PHP array.
   *
   * @param string $static_drupal_code
   *   A string of Drupal code to append at the beginning of the eval()
   *   statement. You can use this to provide constants, function definitions,
   *   etc. to the info hook. A substitution for t() is provided by default.
   *
   * @return array
   *   The return value of the info hook.
   */
  public function getHookReturnArray($static_drupal_code = '') {
    // Convert t() to a plain string by default.
    $static_drupal_code .= "\n" .
      'if (!function_exists("t")) {
        function t($string) {
          return $string;
        }
      }'
      ;

    $eval = "\n" . $static_drupal_code . "\n" . $this->getContent();
    return eval($eval);
  }

}
