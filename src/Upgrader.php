<?php

/**
 * @file
 * Contains Drupal\Upgrade\Upgrader.
 *
 * Performs code audits and upgrades.
 */

namespace Drupal\Upgrade;

use Symfony\Component\Console\Output\OutputInterface;

class Upgrader {

  public function upgrade($path, $standard, $verbosity) {
    $params = '';

    // If is Drupal standar
    if ($standard == "Drupal7to8") {
      $params .= '--standard=';
      $params .= __DIR__ . '/Standards/Drupal7to8/ruleset.xml ';
    }

    // Extensions
    $params .= '--extensions=php,module,inc,install,test,profile,theme,css,js,txt,info,yml ';

    switch ($verbosity) {
      case OutputInterface::VERBOSITY_VERBOSE:
        $params .= '-v';
        break;
      case OutputInterface::VERBOSITY_VERY_VERBOSE:
      case OutputInterface::VERBOSITY_DEBUG:
        $params .= '-vv';
        break;
    }

    $command = './bin/phpcs ' . $params;
    system("$command $path");
  }

}
