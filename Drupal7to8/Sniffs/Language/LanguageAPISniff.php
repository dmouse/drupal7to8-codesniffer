<?php
/**
 * Drupal7to8_Sniffs_Language_LanguageAPISniff.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Update to the new language API.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class Drupal7to8_Sniffs_Language_LanguageAPISniff extends Drupal7to8_Base_FunctionReplacementSniff {

    protected $message = '!function() has been removed in the new language API: https://drupal.org/node/1766152';

    protected $code = 'LanguageAPI';

    protected $forbiddenFunctions = array(
        // Functions with easy replacements on the language manager. Although
        // some places may have local instances of the language manager, that is
        // impossible to tell here.
        'language' => '\Drupal::languageManager()->getCurrentLanguage',
        'language_list' => '\Drupal::languageManager()->getLanguages',
        'language_load' => '\Drupal::languageManager()->getLanguage',
        'language_default' => '\Drupal::languageManager()->getDefaultLanguage',

        // Misc language API functions without direct replacements (or requiring
        // instances of negotiators.
        'language_negotiation_get_switch_links' => NULL,
        'language_types_info' => NULL,
        'language_types_get_configurable' => NULL,
        'language_types_disable' => NULL,
        'language_update_locked_weights' => NULL,
        'language_types_get_all' => NULL,
        'language_types_set' => NULL,
        'language_types_initialize' => NULL,
        'language_negotiation_method_get_first' => NULL,
        'language_negotiation_method_enabled' => NULL,
        'language_negotiation_purge' => NULL,
        'language_negotiation_set' => NULL,
        'language_negotiation_info' => NULL,
        'language_negotiation_method_invoke' => NULL,
        'language_url_split_prefix' => NULL,

        // Data derivatives that require instances of language negotiators.
        'language_from_selected' => NULL,
        'language_from_browser' => NULL,
        'language_from_user' => NULL,
        'language_from_user_admin' => NULL,
        'language_from_session' => NULL,
        'language_from_url' => NULL,
        'language_url_fallback' => NULL,
        'language_switcher_session' => NULL,
        'language_switcher_url' => NULL,
        'language_url_rewrite_session' => NULL,
    );

    /**
     * {@inheritdoc}
     */
    protected function addError($phpcsFile, $stackPtr, $function, $pattern = NULL) {
        if ($function == 'language_list') {
            // Find the token range representing the nth argument.
            $result = $this->findNthArgument($phpcsFile, $stackPtr, 0);
            // Fall back on parent behavior if there is no nth argument.
            if ($result === FALSE) {
                parent::addError($phpcsFile, $stackPtr, $function, $pattern);
                return;
            }
            $customMessage = 'The argument for the replacement of language_list(), languageManager()->getLanguages() does not take field names anymore. It takes language state. Review Language::STATE_* constants.';
            $fix = $phpcsFile->addFixableError($customMessage, $stackPtr, $this->code);
            if ($fix === TRUE && $phpcsFile->fixer->enabled === TRUE) {
                $this->insertFixMeComment($phpcsFile, $stackPtr, $customMessage, $this->forbiddenFunctions[$function]);
            }
        }
    }

}
