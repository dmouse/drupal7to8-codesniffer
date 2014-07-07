<?php

/**
 * Handles drupal_add_js(), drupal_add_css() and drupal_add_library().
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class Drupal7to8_Sniffs_Assets_DrupalAddSniff extends Drupal7to8_Base_FunctionReplacementSniff {

    protected $message = '!function() was removed. Use #attached in your render array: https://drupal.org/node/2169605';

    protected $code = 'DrupalAdd';

    protected $forbiddenFunctions = array(
        'drupal_add_css' => NULL,
        'drupal_add_js' => NULL,
        'drupal_add_library' => NULL,
    );

}
