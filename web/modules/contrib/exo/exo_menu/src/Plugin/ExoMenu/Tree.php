<?php

namespace Drupal\exo_menu\Plugin\ExoMenu;

use Drupal\exo_menu\Plugin\ExoMenuBase;

/**
 * Plugin implementation of the 'tree' eXo menu.
 *
 * @ExoMenu(
 *   id = "tree",
 *   label = @Translation("Tree"),
 * )
 */
class Tree extends ExoMenuBase {

  /**
   * {@inheritdoc}
   */
  public function prepareBuild(array $build) {
    $build = parent::prepareBuild($build);
    $build['#attributes']['class'][] = 'exo-menu-tree';
    return $build;
  }

}
