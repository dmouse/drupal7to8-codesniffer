<?php
/**
 * Drupal7to8_Sniffs_User_UserProfileFormAlterSniff.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * hook_form_user_profile_form_alter() was renamed.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class Drupal7to8_Sniffs_User_UserProfileFormAlterSniff extends Drupal7to8_Base_HookImplementationChangeSniff {

  protected $hook = 'form_user_profile_form_alter';

  protected $message = 'hook_form_user_profile_form_alter() was renamed: https://drupal.org/node/1734556';

  protected $code = 'HookUserProfileFormAlter';

  protected $is_fixable = TRUE;

  protected $renamed_hook = 'form_user_form_alter';

  /**
   * {@inheritdoc}
   */
  protected function fix(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $module, $hook) {
    $customMessage = 'hook_form_user_profile_form_alter() was renamed, but neither $form["#user_category"] nor $form_state["user"] exist anymore. See https://drupal.org/node/1734556.';
    Drupal7to8_Utility_InsertContent::insertFixMeComment($phpcsFile, $stackPtr +2, $customMessage);
  }

}
