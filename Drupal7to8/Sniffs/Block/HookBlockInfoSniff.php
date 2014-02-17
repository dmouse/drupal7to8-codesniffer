<?php

/**
 * Warns that .info files are now .info.yml files.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class Drupal7to8_Sniffs_Block_HookBlockInfoSniff implements PHP_CodeSniffer_Sniff {

  /**
   * The status message linking the Block plugin change record.
   *
   * @var string
   */
  public static $changeRecordMessage = 'hook_block_info() has been replaced by the Block Plugin API: https://drupal.org/node/1880620';

  /**
   * An associative array of metadata for the blocks the hook defines.
   *
   * The array is keyed by the old block name.
   *
   * @var array
   */
  public $blockDefinitions = array();

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
    // Only process a valid InfoHook.
    try {
      $function = new Drupal7to8_Utility_InfoHook($phpcsFile, $stackPtr);
    }
    catch (Drupal7to8_Exception_InvalidSubsetException $e) {
      return;
    }

    // Only act on hook_block_info() and hook_block_view().
    // @todo Support hook_block_view() later.
    if (!$function->isHookImplementation('hook_block_info')) {
      return;
    }

    // If there is any logic, do not try to convert the hook.
    if ($function->containsLogic(array('t'))) {
      // Add a non-fixable error and return early.
      $phpcsFile->addError($this::$changeRecordMessage, $stackPtr, 'HookBlockInfo');
      return;
    }

      $function->enableEscapedConstants();

      $this->blockDefinitions = $function->getHookReturnArray();

      // Create plugins from the block definitions.
      $block_files = array();
      foreach ($this->blockDefinitions as $machine_name => $definition) {
        $class_name = Drupal7to8_Utility_CreateFile::camelUnderscores($machine_name) . 'Block';
        $replacements = array(
          '__MODULE_NAME__' => $function->getModuleName(),
          '__BLOCK_NAME__' => $class_name,
          '__BLOCK_ID__' => $machine_name,
          '__BLOCK_LABEL__' => !empty($definition['info']) ? $definition['info'] : '',
        );

        // Add the block caching setting.
        if (!empty($definition['cache'])) {
          // BlockBase provides DRUPAL_NO_CACHE by default.
          if ($definition['cache'] == 'DRUPAL_NO_CACHE') {
            $replacements['// __BLOCK_CACHING__'] = '';
          }
          else {
            $replacements['// __BLOCK_CACHING__'] = '$configuration[\'cache\'] = ' . $definition['cache'];
          }
        }

        // Create a plugin definition file.
        $filepath = Drupal7to8_Utility_ModuleProperties::getPsrPath($phpcsFile, 'Plugin\Block') . DIRECTORY_SEPARATOR . $class_name . '.php';
        $block_files[$filepath] = Drupal7to8_Utility_CreateFile::replaceTokens(__DIR__ . DIRECTORY_SEPARATOR . 'BlockPluginTemplate.php', $replacements);
      }

    // Otherwise, it is safe to evaluate the hook to get its return value.
    $fix = $phpcsFile->addFixableError($this::$changeRecordMessage, $stackPtr, 'HookBlockInfo');
    if ($phpcsFile->fixer->enabled === TRUE) {
      foreach ($block_files as $filepath => $file_data) {
        Drupal7to8_Utility_CreateFile::writeFile($filepath, $file_data);
      }

      // @todo Add a fixme to the old hook_block_info() as well.
    }
  }

}
