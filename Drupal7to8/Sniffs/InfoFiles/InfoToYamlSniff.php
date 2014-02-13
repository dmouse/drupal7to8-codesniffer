<?php
/**
 * Drupal7to8_Sniffs_InfoFiles_InfoToYamlSniff.
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

require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * Warns that .info files are now .info.yml files, and attempts to rename them.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class Drupal7to8_Sniffs_InfoFiles_InfoToYamlSniff implements PHP_CodeSniffer_Sniff {

  /**
   * {@inheritdoc}
   */
  public function register() {
    // Fire on text outside of PHP.
    return array(T_INLINE_HTML);
  }

  /**
   * {@inheritdoc}
   */
  public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {

    // Only process the rename on .info files.
    $needRename = FALSE;
    $fileExtension = strtolower(substr($phpcsFile->getFilename(), -4));
    if ($fileExtension == 'info') {
      $needRename = TRUE;

      // If .info.yml file already exists, our work here is done. The parsing
      // will run on the info.yml file as well separately.
      if (file_exists($phpcsFile->getFilename() . '.yml')) {
        return;
      }
    }
    else {
      // Only process YAML fixes on .info.yml files.
      $fileExtension = strtolower(substr($phpcsFile->getFilename(), -8));
      if ($fileExtension !== 'info.yml') {
        // Not an info or info.yml file.
        return;
      }
    }

    // Only run once per file.
    $tokens = $phpcsFile->getTokens();
    if ($tokens[$stackPtr]['line'] !== 1) {
      return;
    }

    // Figure out if we are dealing with an .info or YAML format.
    $info = array();
    try {
      $info = Yaml::parse($phpcsFile->getFilename());
    }
    catch (ParseException $e) {
      // Original .info format. Read it into an array for later use.
      $file = file_get_contents($phpcsFile->getFilename());
      $info = $this->drupalParseInfoFormat($file);

      if (!$needRename) {
        $fix = $phpcsFile->addFixableError('.info.yml file did not parse as valid YAML: https://drupal.org/node/1935708', $stackPtr, 'YamlVerify');
      }
      else {
        $fix = $phpcsFile->addFixableError('.info files are now .info.yml files: https://drupal.org/node/1935708', $stackPtr, 'InfoToYaml');
      }
    }

    // Now we have an array of info. Check for required/extraneous properties.

    // New "type: module" key required.
    if (!array_key_exists('type', $info)) {
      $fix = $phpcsFile->addFixableError('Missing required "type" property: https://drupal.org/node/1935708', $stackPtr, 'YamlVerify');
      if ($fix === true && $phpcsFile->fixer->enabled === true) {
        // Add it.
        // @todo: If we start fixing themes and profiles, we can't just do this.
        $info['type'] = 'module';
      }
    }

    // Change core to 8.x.
    if ($info['core'] == '7.x') {
      $fix = $phpcsFile->addFixableError('The "core" property must change to "8.x": https://drupal.org/node/1935708', $stackPtr, 'YamlVerify');
      if ($fix === true && $phpcsFile->fixer->enabled === true) {
        // Fix it.
        $info['core'] = '8.x';
      }
    }

    // Files array is no more.
    if (array_key_exists('files', $info)) {
      $phpcsFile->addFixableError('Drupal 8 now uses PSR class loading; remove "files" entries from .info.yml file.: https://drupal.org/node/1320394', $stackPtr, 'YamlVerify');
      if ($fix === true && $phpcsFile->fixer->enabled === true) {
        // Ditch it.
        unset($info['files']);
      }
    }

    // Styles and scripts arrays are no longer allowed.
    if (array_key_exists('stylesheets', $info) || array_key_exists('scripts', $info)) {
      // Don't think we can fix this one.
      $phpcsFile->addError('Modules can no longer add stylesheets/scripts via their .info.yml file: https://drupal.org/node/1876152', $stackPtr, 'YamlVerify');
    }

    // Remove dependencies that are no longer needed in 8.x.
    if (array_key_exists('dependencies', $info)) {
      $phpcsFile->addFixableError('Lots of modules moved into core, so you no longer need to declare them as dependencies!: https://drupal.org/node/1320394', $stackPtr, 'YamlVerify');
      if ($fix === true && $phpcsFile->fixer->enabled === true) {
        // Ditch 'em.
        foreach ($info['dependencies'] as $key => $module) {
          switch ($module) {
            // Modules that were (largely) moved into core.
            case 'ctools':

            // Modules that were absorbed into other core modules.
            case 'list':
              unset($info['dependencies'][$key]);
              break;
            default:
              break;
          }
        }
      }
    }

    // All done with our checks; write the YAML out again.
    if ($phpcsFile->fixer->enabled === true) {
      Drupal7to8_Utility_CreateFile::writeYaml($phpcsFile->getFilename() . ($needRename ? '.yml' : ''), $info);

      // @todo Leave a @todo in the original .info file to remove it.
      //$contents = "; @todo: Remove this file once your module is ported.\n" . $contents;
    }
  }

  /**
   * Parses a Drupal info file. Copied from Drupal core drupal_parse_info_format().
   *
   * @param string $data The contents of the info file to parse
   *
   * @return array The info array.
   */
  public static function drupalParseInfoFormat($data) {
    $info = array();
    $constants = get_defined_constants();

    if (preg_match_all('
      @^\s*                           # Start at the beginning of a line, ignoring leading whitespace
      ((?:
        [^=;\[\]]|                    # Key names cannot contain equal signs, semi-colons or square brackets,
        \[[^\[\]]*\]                  # unless they are balanced and not nested
      )+?)
      \s*=\s*                         # Key/value pairs are separated by equal signs (ignoring white-space)
      (?:
        ("(?:[^"]|(?<=\\\\)")*")|     # Double-quoted string, which may contain slash-escaped quotes/slashes
        (\'(?:[^\']|(?<=\\\\)\')*\')| # Single-quoted string, which may contain slash-escaped quotes/slashes
        ([^\r\n]*?)                   # Non-quoted string
      )\s*$                           # Stop at the next end of a line, ignoring trailing whitespace
      @msx', $data, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
          // Fetch the key and value string.
          $i = 0;
          foreach (array('key', 'value1', 'value2', 'value3') as $var) {
            $$var = isset($match[++$i]) ? $match[$i] : '';
          }
          $value = stripslashes(substr($value1, 1, -1)) . stripslashes(substr($value2, 1, -1)) . $value3;

          // Parse array syntax.
          $keys = preg_split('/\]?\[/', rtrim($key, ']'));
          $last = array_pop($keys);
          $parent = &$info;

          // Create nested arrays.
          foreach ($keys as $key) {
            if ($key == '') {
              $key = count($parent);
            }
            if (!isset($parent[$key]) || !is_array($parent[$key])) {
              $parent[$key] = array();
            }
            $parent = &$parent[$key];
          }

          // Handle PHP constants.
          if (isset($constants[$value])) {
            $value = $constants[$value];
          }

          // Insert actual value.
          if ($last == '') {
            $last = count($parent);
          }
          $parent[$last] = $value;
        }
    }
    return $info;

  }//end drupalParseInfoFormat()

}
