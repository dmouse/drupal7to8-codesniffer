<?php

/**
 * Drupal7to8_Sniffs_Utility_FunctionDeclaration.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Defines a token subset for the contents of a function declaration.
 *
 * This subset will contain the tokens for the contents of the function only,
 * so that they can be parsed and evaluated. So, for example, for the function:
 * @code
 * function mymodule_foo() {
 *   $foo = 'mystring';
 *   return $foo;
 * }
 * @endcode
 * This token subset will contain:
 * @code
 *   $foo = 'mystring';
 *   return $foo;
 * @endcode
 */
class Drupal7to8_Utility_FunctionDeclaration extends Drupal7to8_Utility_TokenSubset {

  /**
   * The index of the T_FUNCTION token that declares this function declaration.
   *
   * @var int
   */
  protected $functionTokenIndex;

  /**
   * Constructs the FunctionDeclaration subset from a T_FUNCTION token index.
   *
   * @param PHP_CodeSniffer_File $phpcsFile
   *   The code sniffer file.
   * @param int $functionTokenIndex
   *   The index of the T_FUNCTION token.
   */
  public function __construct(PHP_CodeSniffer_File $phpcsFile, $functionTokenIndex) {
    // Load the token set from the file.
    $tokens = $phpcsFile->getTokens();

    // Only T_FUNCTION tokens are valid.
    if ($tokens[$functionTokenIndex]['type'] != 'T_FUNCTION') {
      throw new Drupal7to8_Exception_InvalidSubsetException("Token pointer $functionTokenIndex must be a T_FUNCTION token.");
    }
    // Only non-empty function definitions are valid.
    if (empty($tokens[$functionTokenIndex]['scope_opener'])) {
      throw new Drupal7to8_Exception_InvalidSubsetException("Function defined at $functionTokenIndex must not be empty.");
    }
    $this->functionTokenIndex = $functionTokenIndex;

    // Use the token subset between, but not including, the { and }.
    $start = $tokens[$functionTokenIndex]['scope_opener'] + 1;
    $end = $tokens[$functionTokenIndex]['scope_closer'] - 1;
    parent::__construct($phpcsFile, $start, $end);
  }


  /**
   * Returns the function name for the token subset.
   *
   * @return string
   */
  public function getFunctionName() {
    return $this->phpcsFile->getDeclarationName($this->functionTokenIndex);
  }

  /**
   * Returns the module name for the token subset.
   *
   * @return string
   */
  public function getModuleName() {
    return Drupal7to8_Utility_ModuleProperties::getModuleName($this->phpcsFile);
  }

  /**
   * Determines whether this function is a hook implementation.
   *
   * @param string $hook_name
   *   The name of the hook to match, for example 'hook_menu'.
   *
   * @return bool
   *   TRUE if the token declares the invocation of the given hook for the
   *   current module, or FALSE otherwise.
   */
  public function isHookImplementation($hook_name) {
    if (substr($hook_name, 0, 5) !== 'hook_') {
      throw new \Exception("$hook_name must begin with 'hook_'");
    }

    if ($this->getFunctionName() == $this->getModuleName() . '_' . substr($hook_name, 5)) {
      return TRUE;
    }

    return FALSE;
  }

}
