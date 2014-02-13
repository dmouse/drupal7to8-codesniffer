<?php

/**
 * @file
 * Contains \Drupal\__MODULE_NAME__\Plugin\Block\__BLOCK_NAME__.
 */

namespace Drupal\__MODULE_NAME__\Plugin\Block;

use Drupal\block\BlockBase;

/**
 * Provides the __BLOCK_NAME__ block.
 *
 * @Block(
 *   id = "__BLOCK_ID__",
 *   admin_label = @Translation("__BLOCK_LABEL__")
 * )
 */
class __BLOCK_NAME__ extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = array();
    // @todo Add the hook_block_view() business here.
    return $build;
  }

}
