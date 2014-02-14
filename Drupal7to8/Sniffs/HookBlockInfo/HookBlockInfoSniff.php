<?php

/**
 * Drupal7to8_Sniffs_HookBlockInfo_HookBlockInfo.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Warns that .info files are now .info.yml files.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class Drupal7to8_Sniffs_HookBlockInfo_HookBlockInfoSniff implements PHP_CodeSniffer_Sniff {

  /**
   * The status message linking the Block plugin change record.
   *
   * @var string
   */
  public static $changeRecordMessage = 'hook_block_info() has been replaced by the Block Plugin API: https://drupal.org/node/1880620';

  /**
   * The tokens of hook_block_info().
   *
   * @var array
   */
  public $hookBlockInfoTokens = array();

  /**
   * An associative array of metadata for the blocks the hook defines.
   *
   * The array is keyed by the old block name.
   *
   * @var array
   */
  public $blockDefinitions = array();

  /**
   * The index of the first return statement for the hook declaration.
   *
   * @var int
   */
  public $returnIndex;

  /**
   * The index of the first array() after the return statement token.
   *
   * @var int
   */
  public $returnArrayIndex;

  /**
   * The index of the return array opening parenthesis.
   *
   * @var int
   */
  public $returnArrayOpenIndex;

  /**
   * The top-level nesting for the return array.
   *
   * @var array
   */
  public $returnArrayNesting;

  /**
   * The name of the returned variable.
   *
   * @var string
   */
  public $returnVariableName;

  /**
   * Returns an array of tokens this test wants to listen for.
   *
   * @return array
   */
  public function register() {
    return array(T_FUNCTION);
  }

  /**
   * Processes this test, when one of its tokens is encountered.
   *
   * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
   * @param int                  $stackPtr  The position of the current token
   *                                        in the stack passed in $tokens.
   *
   * @return void
   */
  public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
    $tokens = $phpcsFile->getTokens();
    $module_name = Drupal7to8_Utility_ModuleProperties::getModuleName($phpcsFile);
    $hook_block_info = $module_name . '_block_info';
    $hook_block_view = $module_name . '_block_view';
    // Only act on hook_block_info() and hook_block_view().
    // @todo Support hook_block_view() later.
    // if (!in_array($this->getDeclarationName($stackPtr), array($hook_block_info, $hook_block_view)) {
    if (!in_array($phpcsFile->getDeclarationName($stackPtr), array($hook_block_info))) {
      return;
    }

    // Store all the tokens for the function definition.
    for ($i = $tokens[$stackPtr]['scope_opener']; $i <= $tokens[$stackPtr]['scope_closer']; $i++) {
      $this->hookBlockInfoTokens[$i] = $tokens[$i];

      // Store the location of the return token.
      if ($tokens[$i]['type'] == 'T_RETURN') {
        $this->returnIndex = $i;
      }
      // Otherwise, if the return token is already set, look for what is
      // being returned.
      elseif ($this->returnIndex && $i > $this->returnIndex) {
        // If a variable is being returned, store the variable name. E.g.:
        // return $blocks;
        // @todo don't try to handle return $blocks + $foo;
        if ($tokens[$i]['type'] == 'T_VARIABLE') {
          $this->returnVariableName = $tokens[$i]['content'];
        }

        // If an array is being returned inline, store its token index.
        // return array(
        //          'blockname' => array(...),
        //        );
        elseif ($tokens[$i]['type'] == 'T_ARRAY' && !($this->returnArrayIndex)) {
          $this->returnArrayIndex = $i;
        }
        // Next find the opening parenthesis for that array.
        elseif ($this->returnArrayIndex && $tokens[$i]['type'] == 'T_OPEN_PARENTHESIS') {
          if (!empty($tokens[$i]['parenthesis_owner']) && $tokens[$i]['parenthesis_owner'] == $this->returnArrayIndex) {
            $this->returnArrayOpenIndex = $i;
            // The very next token, if not a closing token, will have the
            // expected nesting of the top-level keys of the array.
            if ($tokens[$i + 1]['type'] != 'T_CLOSE_PARENTHESIS') {
              $this->returnArrayNesting = $tokens[$i + 1]['nested_parenthesis'];
            }
          }
        }
      }
    }

    foreach ($this->hookBlockInfoTokens as $token) {
      // If there is any logic, do not try to convert the hook.
      if (in_array($token['type'], PHP_CodeSniffer_Tokens::$scopeOpeners)) {
        // Add a non-fixable error and return early.
        $phpcsFile->addError($this::$changeRecordMessage, $stackPtr, 'HookBlockInfo');
        return;
      }

      // If an array is being returned inline, collect the top-level keys.
      if (!empty($this->returnArrayNesting)) {
        if ($token['type'] == 'T_CONSTANT_ENCAPSED_STRING' && $token['nested_parenthesis'] == $this->returnArrayNesting) {
          $this->blockDefinitions[str_replace("'", '', $token['content'])]['class_name'] = Drupal7to8_Utility_CreateFile::camelUnderscores($token['content']) . 'Block';
        }
      }
    }

    $block_files = array();
    foreach ($this->blockDefinitions as $machine_name => $definition) {
      $replacements = array(
        '__MODULE_NAME__' => $module_name,
        '__BLOCK_NAME__' => $definition['class_name'],
        '__BLOCK_ID__' => $machine_name,
      );
      $filepath = Drupal7to8_Utility_ModuleProperties::getPsrPath($phpcsFile, 'Plugin\Block') . DIRECTORY_SEPARATOR . $definition['class_name'] . '.php';
      $block_files[$filepath] = Drupal7to8_Utility_CreateFile::replaceTokens(__DIR__ . DIRECTORY_SEPARATOR . 'BlockPluginTemplate.php', $replacements);
    }

    // Try to convert the hook.
    $fix = $phpcsFile->addFixableError($this::$changeRecordMessage, $stackPtr, 'HookBlockInfo');
    if ($phpcsFile->fixer->enabled === TRUE) {
      foreach ($block_files as $filepath => $file_data) {
        Drupal7to8_Utility_CreateFile::writeFile($filepath, $file_data);
      }
    }
  }

}
