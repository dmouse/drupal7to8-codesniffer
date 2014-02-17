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
    /** @fixme Add the content from your hook_block_view() here. **/
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $configuration = array();
    // BlockBase provides DRUPAL_NO_CACHE by default.
    // __BLOCK_CACHING__
    return $configuration;
  }

}
