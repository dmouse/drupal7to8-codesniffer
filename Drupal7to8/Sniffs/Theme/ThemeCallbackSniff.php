<?php
/**
 * Drupal7to8_Sniffs_Theme_ThemeCallbackSniff.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * theme() is deprecated.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class Drupal7to8_Sniffs_Theme_ThemeCallbackSniff implements PHP_CodeSniffer_Sniff {

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_FUNCTION);
    }//end register()

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        if (preg_match('!^theme_.+$!', $tokens[$stackPtr+2]['content'])) {
            $fix = $phpcsFile->addFixableError('Theme functions should be converted to Twig templates: https://drupal.org/node/1831138', $stackPtr, 'ThemeCallback');
            if ($fix === true && $phpcsFile->fixer->enabled === true) {
                //$this->insertFixMeComment($phpcsFile, $stackPtr, $customMessage, $this->forbiddenFunctions[$function]);
            }
        }
    }
}
