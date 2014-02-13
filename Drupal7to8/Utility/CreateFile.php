<?php
/**
 * Drupal7to8_Utility_CreateFile
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
 * Utility class for writing out files.
 *
 * @todo Add methods for creating yaml files, PSR-N class files, etc.
 */
class Drupal7to8_Utility_CreateFile {

  /**
   * Replace a set of tokens in a boilerplate file template.
   *
   * @param string $boilerPath
   *   The path to the boilerplate file.
   * @param array $boilerTokens
   *   An associative array of tokens to replace in the boilerplate file,
   *   with the tokens as the keys and the values as the replacements
   *
   * @return string|null
   *   The PHP code with the tokens replaced, or NULL on failure.
   */
  public static function replaceTokens($boilerPath, array $boilerTokens) {
    if ($boilerplate = file_get_contents($boilerPath)) {
      return str_replace(array_keys($boilerTokens), array_values($boilerTokens), $boilerplate);
    }
  }

  /**
   * Converts an underscore-separated name to CamelCase.
   *
   * @param string $name
   *   An underscore-separated string (machine name, variable name, etc.).
   * @param bool $lowerCamel
   *   Boolean flag to convert to lowerCamel instead of UpperCamel. Defaults to
   *   FALSE.
   *
   * @return
   *   The converted string.
   */
  public static function camelUnderscores($name, $lowerCamel = FALSE) {
    $name = str_replace("'", '', $name);
    $pieces = explode('_', $name);
    $pieces = array_map('ucfirst', array_map('strtolower', $pieces));
    if ($lowerCamel) {
      $pieces[0] = strtolower($pieces[0]);
    }
    return implode('', $pieces);
  }

  /**
   * Writes a file.
   *
   * @param string $filename
   *   The file to write.
   * @param string $contents
   *   The contents to write to the file.
   *
   * @return bool
   *   Whether the file was written successfully.
   *
   * @todo
   *   We should NOT just write out the file; we should perform that operation
   *   once processing is done. Somehow.
   */
  public static function writeFile($filename, $contents) {
    // This is BAD. Fix me!
    return file_put_contents($filename, $contents);
  }

  /**
   * Writes a YAML file.
   *
   * @param string $filename
   *   The file to write.
   * @param array $data
   *   An associative array to convert to YAML.
   *
   * @return bool
   *   Whether the file was written successfully.
   */
  public static function writeYaml($filename, array $data) {
    $yaml = Yaml::dump($data);
    self::writeFile($filename, $yaml);
  }

  /**
   * Reads and updates a YAML file.
   *
   * @param string $filename
   *   The file to write.
   * @param array $data
   *   An associative array to convert to YAML.
   *
   * @return bool
   *   Whether the file was written successfully.
   */
  public static function readAndWriteYaml($filename, array $data) {
    $orignal_yaml = Yaml::parse($filename);
    $merged_data = array_merge_recursive($original_yaml, $data);
    $yaml = Yaml::dump($data);
    self::writeYaml($filename, $yaml);
  }

}
