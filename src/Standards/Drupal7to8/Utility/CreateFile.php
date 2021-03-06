<?php

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
   * Writes a file, recursively creating directories as needed.
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
    $path_parts = pathinfo($filename);
    if (!is_dir($path_parts['dirname'])) {
      mkdir($path_parts['dirname'], 0777, TRUE);
    }
    return file_put_contents($filename, $contents);
  }

  /**
   * Writes a YAML file.
   *
   * @param string $filename
   *   The file to write.
   * @param array $data
   *   An associative array to convert to YAML.
   * @param string $prepend_comment
   *   A comment which should be prepended to the YAML file, e.g. a to-do.
   *
   * @return bool
   *   Whether the file was written successfully.
   */
  public static function writeYaml($filename, array $data, $prepend_comment = '') {
    $dumper  = new Dumper();
    $dumper->setIndentation(2);

    $yaml = $dumper->dump($data, PHP_INT_MAX);

    if (!empty($prepend_comment)) {
      $yaml = "# $prepend_comment\n" . $yaml;
    }

    self::writeFile($filename, $yaml);
  }

  /**
   * Reads and updates a YAML file.
   *
   * @param string $filename
   *   The file to write.
   * @param array $data
   *   An associative array to convert to YAML.
   * @param string $prepend_comment
   *   A comment which should be prepended to the YAML file, e.g. a to-do.
   *
   * @return bool
   *   Whether the file was written successfully.
   */
  public static function readAndWriteYaml($filename, array $data, $prepend_comment = '') {
    $dumper = new Dumper();
    $dumper->setIndentation(2);
    $original_yaml = array();
    if (file_exists($filename)) {
      $contents = file_get_contents($filename);
      $original_yaml = Yaml::parse($contents);
    }
    // @todo we need something that will work for both deepply nested arrays
    // but also not double-nest un-nested arrays. :P
    // In other words, this works fine for config, but will fail on menu arrays.
    $merged_data = array_merge($original_yaml, $data);
    $yaml = $dumper->dump($data, PHP_INT_MAX);

    self::writeYaml($filename, $merged_data, $prepend_comment);
  }
}
