<?php

/**
 * country_get_list() was removed.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class Drupal7to8_Sniffs_Misc_CountryGetListSniff extends Drupal7to8_Base_FunctionReplacementSniff {

  protected $message = '!function() has been converted to a swappable service: https://drupal.org/node/2019329';

  protected $code = 'CountryGetList';

  protected $forbiddenFunctions = array(
    'country_get_list' => '\Drupal::service(\'country_manager\')->getList',
  );

}
