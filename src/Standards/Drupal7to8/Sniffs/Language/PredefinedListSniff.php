<?php

/**
 * _locale_get_predefined_list() is now part of the language manager.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class Drupal7to8_Sniffs_Language_PredefinedListSniff extends Drupal7to8_Base_FunctionReplacementSniff {

  protected $message = '!function() has been converted to a swappable service: https://drupal.org/node/2019329';

  protected $code = 'PredefinedList';

  protected $forbiddenFunctions = array(
    '_locale_get_predefined_list' => 'LanguageManager::getStandardLanguageList',
  );

  protected $useStatements = array(
    '_locale_get_predefined_list' => 'Drupal\Core\Language\LanguageManager',
  );

}
