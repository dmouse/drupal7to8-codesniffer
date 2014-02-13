<?php
/**
 * Drupal7to8_Sniffs_Utility_ModuleProperties.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Provides utility functions for working with Drupal module files.
 */
class Drupal7to8_Utility_ModuleProperties {

  /**
   * Determine the module name for the file being examined.
   *
   * @param PHP_CodeSniffer_File $phpcsFile
   *   The code sniffer file.
   *
   * @return string|null
   *   The module name if it can be determined, NULL if it cannot.
   */
  static public function getModuleName(PHP_CodeSniffer_File $phpcsFile) {
    $module_properties = static::getModuleNameAndPath($phpcsFile);
    return $module_properties['module_name'];
  }

  /**
   * Determine the module directory path for the file being examined.
   *
   * @param PHP_CodeSniffer_File $phpcsFile
   *   The code sniffer file.
   *
   * @return string|null
   *   The absolute path to the module if it can be determined, or NULL.
   */
  static public function getModulePath(PHP_CodeSniffer_File $phpcsFile) {
    $module_properties = static::getModuleNameAndPath($phpcsFile);
    return $module_properties['module_path'];
  }

  /**
   * Determine the module name and module directory path for the file.
   *
   * @param PHP_CodeSniffer_File $phpcsFile
   *   The code sniffer file.
   *
   * @return array
   *   An array containing:
   *   - module_name: The name of the module, or NULL.
   *   - module_path: The absolute path to the module directory, or NULL.
   */
  static public function getModuleNameAndPath(PHP_CodeSniffer_File $phpcsFile) {
    // Initialize the return array.
    $module_properties = array(
      'module_path' => NULL,
      'module_name' => NULL,
    );

    $file_parts = explode(DIRECTORY_SEPARATOR, $phpcsFile->getFilename());
    // Ignore the filename as we are traversing directories.
    array_pop($file_parts);

    // Check each directory path for the base .module file.
    while (count($file_parts) > 0) {
      $path = implode(DIRECTORY_SEPARATOR, $file_parts);
      $files = glob($path . DIRECTORY_SEPARATOR . '*.module');
      if (count($files) == 0) {
        // No module found, so search the parent directory.
        array_pop($file_parts);
        continue;
      }
      $module_properties['module_name'] = basename($files[0], '.module');
      $module_properties['module_path'] = implode(DIRECTORY_SEPARATOR, $file_parts);
      break;
    }

    return $module_properties;
  }

  /**
   * Returns the absolute PSR-0 or PSR-4 path for the module and namespace.
   *
   * @param PHP_CodeSniffer_File $phpcsFile
   *   The code sniffer file.
   * @param string $namespace
   *   (optional) The sub-namespace for the class relative to the root
   *   namespace for the module. For example, for the namespace
   *   \Drupal\node\Form, pass 'Form'. Pass nothing to use the module's root
   *   namespace.
   * @param bool $psr4
   *    (optional) Whether to return a PSR-4 path instead of PSR-0. Defaults to
   *    FALSE (so that a PSR-0 path is returned).
   *
   * @return string|null
   *   The absolute path to the PSR-0 or PSR-4 directory, or NULL.
   */
  public static function getPsrPath(PHP_CodeSniffer_File $phpcsFile, $namespace = '', $psr4 = FALSE) {
    $path_parts = array();
    $path_parts[] = static::getModulePath($phpcsFile);
    $path_parts[] = 'lib';
    if (!$psr4) {
      $path_parts[] = 'Drupal';
      $path_parts[] = static::getModuleName($phpcsFile);
    }
    if (!empty($namespace)) {
      $path_parts[] = str_replace('\\', DIRECTORY_SEPARATOR, $namespace);
    }
    return implode(DIRECTORY_SEPARATOR, $path_parts);
  }

}
