<?php
/**
 * @file
 * Contains Drupal\Upgrade\Command\UpgradeCommand.
 *
 * Console controller for the drupal:upgrade command.
 */

namespace Drupal\Upgrade\Command;

use Drupal\Upgrade\Upgrader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpgradeCommand extends Command {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('drupal:upgrade')
      ->setDescription('Upgrade your Drupal.')
      ->addArgument(
        'path', InputArgument::REQUIRED, 'Path to the Drupal file to upgrade.'
      )
      ->addOption(
        'standard', 's', InputOption::VALUE_OPTIONAL, 'The code sniffer standard name.', 'Drupal7to8'
      );
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $path = $input->getArgument('path');
    if (!file_exists(realpath($path))) {
      throw new \InvalidArgumentException(sprintf('Path "%s" does not exist:', $path));
    }

    $standard = $input->getOption('standard');
    if (!$standard) {
      $standard = 'Drupal7to8';
    }

    $verbosity = $output->getVerbosity();

    $upgrader = new Upgrader();
    $upgrader->upgrade($path, $standard, $verbosity);
  }

}
