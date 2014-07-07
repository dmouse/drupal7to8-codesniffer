<?php

class Drupal7to8_Utility_InsertContent {

    /**
     * Inserts a "@fixme" comment before a function call.
     *
     * Also prefixes the function name with fixme_ to prevent an endless loop.
     */
    static public function insertFixMeComment(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $message, $newName = NULL) {
        if (!isset($newName)) {
            $phpcsFile->fixer->addContentBefore($stackPtr, 'fixme_');
        }
        else {
            $phpcsFile->fixer->replaceToken($stackPtr, $newName);
        }

        // Prefix the "@fixme" comment with as much whitespace as exists before the
        // function declaration.
        $tokens = $phpcsFile->getTokens();
        $this_line = $tokens[$stackPtr]['line'];
        $first_token_on_this_line = $stackPtr;
        while ($tokens[$first_token_on_this_line]['line'] == $this_line) {
            $first_token_on_this_line--;
        }
        $first_token_on_this_line++;

        $whitespace = '';
        if ($tokens[$first_token_on_this_line]['type'] === 'T_WHITESPACE') {
            $first_non_whitespace_token_on_this_line = $phpcsFile->findNext(T_WHITESPACE, $first_token_on_this_line + 1, NULL, TRUE);
            $whitespace = Drupal7to8_Utility_TokenRange::getContent($tokens, $first_token_on_this_line, $first_non_whitespace_token_on_this_line - 1); //$stackPtr - 1);
        }

        $phpcsFile->fixer->addContentBefore($first_token_on_this_line, $whitespace . '/** @fixme '. $message . " */\n");
    }

}
