<?php

namespace Drupal\exo_menu\Plugin\ExoMenu;

use Drupal\exo_menu\Plugin\ExoMenuDropdownBase;

/**
 * Plugin implementation of the 'dropdown_horizontal' eXo menu.
 *
 * @ExoMenu(
 *   id = "dropdown_horizontal",
 *   label = @Translation("Dropdown Horizontal"),
 * )
 */
class DropdownHorizontal extends ExoMenuDropdownBase {

  /**
   * {@inheritdoc}
   */
  public function prepareBuild(array $build) {
    $build = parent::prepareBuild($build);
    $build['#attributes']['class'][] = 'exo-menu-dropdown-horizontal';
    $build['#attached']['library'][] = 'exo_menu/dropdown.horizontal';
    return $build;
  }

}
