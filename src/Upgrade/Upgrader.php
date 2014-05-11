<?php

/**
 * @file
 * Contains Drupal\Upgrade\Upgrader.
 *
 * Performs code audits and upgrades.
 */

namespace Drupal\Upgrade;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;

class Upgrader {

  /**
   * Perform an upgrade.
   *
   * @param \Symfony\Component\Console\Input\InputInterface $input
   *   Input object for the console.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   Output object for the console.
   */
  public function upgrade(InputInterface $input, OutputInterface $output) {
    $path = $input->getArgument('path');
    $standard = $input->getOption('standard');

    // @todo: map OutputInterface's verbosity level to phpcs -v.
    $command = './bin/phpcs --standard=' . $standard . ' --extensions=php,module,inc,install,test,profile,theme,css,js,txt,info,yml';

    $out = array();
    $status = -1;
    $return = exec("$command $path", $out, $status);
    $output->write($out, TRUE);
  }

}
