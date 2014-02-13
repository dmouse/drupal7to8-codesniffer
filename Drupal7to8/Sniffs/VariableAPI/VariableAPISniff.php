<?php
/**
 * Drupal7to8_Sniffs_VariableAPI_VariableAPISniff.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Dumper;

/**
 * Handles variable_get(), variable_set() and variable_del().
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class Drupal7to8_Sniffs_VariableAPI_VariableAPISniff extends Drupal7to8_Base_FunctionReplacementSniff {

  protected $message = '!function() has been replaced by the Configuration API: https://drupal.org/node/2183531';

  protected $code = 'VariableAPI';

  protected $forbiddenFunctions = array(
    'variable_get' => '\Drupal::config()->get()',
    'variable_set' => '\Drupal::config()->set()',
    'variable_del' => '\Drupal::config()->clear()',
  );

  /**
   * {@inheritdoc}
   */
  protected function addError($phpcsFile, $stackPtr, $function, $pattern = NULL) {
    $fix = FALSE;
    $message = strtr($this->message, array('!function' => $function));

    $phpcsFile->addFixableError($message, $stackPtr, $this->code);
    if ($this->hasFix($function, $pattern)) {
      $fix = $phpcsFile->addFixableError($message, $stackPtr, $this->code);
    }
    elseif ($phpcsFile->fixer->enabled === TRUE) {
      $this->insertFixMeComment($phpcsFile, $stackPtr, $message);
    }
    if ($fix === TRUE && $phpcsFile->fixer->enabled === TRUE) {
      $tokens = $phpcsFile->getTokens();
      // Find the arguments that need to be moved around, remove them, and
      // dynamically build the replacement string for this function call.
      switch ($function) {
        case 'variable_get':
          // Convert to the new config object name and updated variable name.
          $result = $this->getUpdatedVariableName($phpcsFile, $stackPtr, $tokens);
          if ($result === FALSE) {
            return;
          }
          list($config_object_name, $updated_varname) = $result;

          // Find any default values if available
          $default_value = $this->getArgument($phpcsFile, $stackPtr, 1);
          if (!empty($default_value)) {
            $this->addDefaultValue($phpcsFile, $config_object_name, $updated_varname, $default_value);
          }

          // Update to the new statement.
          $replacement = "\Drupal::config('$config_object_name')->get($updated_varname)";

          // Remove the original function arguments and add replacement.
          $this->updateFunctionSignature($phpcsFile, $stackPtr, $tokens, $replacement);
          break;

        case 'variable_set':
          // Get the variable argument
          $result = $this->getUpdatedVariableName($phpcsFile, $stackPtr, $tokens);
          if ($result === FALSE) {
            return;
          }
          list($config_object_name, $updated_varname) = $result;

          // Find the updated value
          if (!$new_value = $this->getArgument($phpcsFile, $stackPtr, 1)) {
            return FALSE;
          }

          // Update to the new statement.
          $replacement = "\Drupal::config('$config_object_name')->set($updated_varname, $new_value)->save()";

          // Remove the original function arguments and add replacement.
          $this->updateFunctionSignature($phpcsFile, $stackPtr, $tokens, $replacement);
          break;

        case 'variable_del':
          // Convert to the new config object name and updated variable name.
          $result = $this->getUpdatedVariableName($phpcsFile, $stackPtr, $tokens);
          if ($result === FALSE) {
            return;
          }
          list($config_object_name, $updated_varname) = $result;

          // Update to the new statement.
          $replacement = "\Drupal::config('$config_object_name')->clear($updated_varname)->save()";

          // Remove the original function arguments and add replacement.
          $this->updateFunctionSignature($phpcsFile, $stackPtr, $tokens, $replacement);
          break;
      }
    }
    elseif ($fix === FALSE) {
      $phpcsFile->addError($message, $stackPtr, $this->code);
    }
  }

  /**
   * Get the original variable name and convert it to conform to the new format.
   *
   * @param PHP_CodeSniffer_File $phpcsFile
   *   The file object being updated.
   * @param $stackPtr
   *   The token index for the variable function.
   * @param $tokens
   *   An array of parsed tokens within the file.
   */
  function getUpdatedVariableName(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $tokens) {
    if (!$varname = $this->getArgument($phpcsFile, $stackPtr, 0)) {
      return FALSE;
    }

    // Convert to the new config object name and updated variable name.
    $cleaned_varname = str_replace("'", "", $varname);
    $module_name = Drupal7to8_Utility_ModuleProperties::getModuleName($phpcsFile);
    $var_parts = explode('_', $cleaned_varname);
    if ($var_parts[0] == $module_name) {
      array_shift($var_parts);
    }
    $updated_varname = implode('_', $var_parts);
    if (strlen($cleaned_varname) !== strlen($varname)) {
      // If the original name was a string literal, then return the same.
      $updated_varname = "'" . $updated_varname . "'";
    }
    return array($module_name . '.settings', $updated_varname);
  }

  /**
   * Return a specific argument from a function call.
   * @param PHP_CodeSniffer_File $phpcsFile
   *   The file to search.
   * @param $stackPtr
   *   The index of the function string in the tokens
   * @param $n
   *   The index of the argument to retrieve
   * @return bool|string
   *   The value of the argument or false if no argument is found.
   */
  function getArgument(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $n) {
    $tokens = $phpcsFile->getTokens();
    $result = $this->findNthArgument($phpcsFile, $stackPtr, $n);
    // Return early if there is no variable to get.
    if ($result === FALSE) {
      return FALSE;
    }
    // Find the variable name to get
    list($arg_start, $arg_end, $remove_start, $remove_end) = $result;
    $value = Drupal7to8_Utility_TokenRange::getContent($tokens, $arg_start, $arg_end);
    return $value;
  }

  /**
   * Update the entire function signature.
   *
   * Remove the original argument tokens and add the new function signature.
   *
   * @param PHP_CodeSniffer_File $phpcsFile
   *   The file object being updated.
   * @param $stackPtr
   *   The token index for the variable function.
   * @param $tokens
   *   An array of parsed tokens within the file.
   * @param $replacement
   *   The new function string containing the function name and arguments.
   */
  function updateFunctionSignature(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $tokens, $replacement) {
    $openParenthesis = $phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($stackPtr + 1), NULL, TRUE);
    $closeParenthesis = $tokens[$openParenthesis]['parenthesis_closer'];
    Drupal7to8_Utility_TokenRange::remove($phpcsFile->fixer, $openParenthesis, $closeParenthesis);
    $phpcsFile->fixer->replaceToken($stackPtr, $replacement);
  }

  /**
   * Adds a default value to the settings configuration file.
   *
   * @param PHP_CodeSniffer_File $phpcsFile
   *   The file that is currently being traversed.
   * @param string $config_object_name
   *   The configuration object name (i.e., modulename.setting)
   * @param string $variable_name
   *   The name of the variable within the configuration object.
   * @param $default_value
   *   The default value to store
   *
   * @todo Is there some way to save this up until the end rather than
   * reading/writing the file so much????
   * Maybe the file writing utility class is the place for this logic as it will
   * need to keep a reference to the variables to write for all files to write
   * at the end of the process.
   */
  function addDefaultValue(PHP_CodeSniffer_File $phpcsFile, $config_object_name, $variable_name, $default_value) {
    if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
      require_once __DIR__ . '/../../vendor/autoload.php';
    }
    else {
      print('HEY! You have to install Composer in order to get Symfony in order to parse YAML because yeah. https://getcomposer.org/download/');
      return array();
    }

    // Determine the settings file.
    $module_properties = Drupal7to8_Utility_ModuleProperties::getModuleNameAndPath($phpcsFile);
    $settings_file = $module_properties['module_path'] . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $config_object_name . '.yml';

    // Parse the YAML to see if the variable already exists
    $config = array();
    if (file_exists($settings_file)) {
      $yaml = file_get_contents($settings_file);
      $config = Yaml::parse($yaml);
    }
    else {
      mkdir($module_properties['module_path'] . DIRECTORY_SEPARATOR . 'config');
    }

    // If the variable exists with a different value - provide some notification???
    if (isset($config[$config_object_name]) && $config[$config_object_name] != $default_value) {
      // @todo: No idea how to inject stdout stuff during fixing.
    }

    // Add the default value to the file.
    $config[$config_object_name][$variable_name] = $default_value;

    // Write out YAML File.
    Drupal7to8_Utility_CreateFile::writeYaml($settings_file, $config);
  }
}
