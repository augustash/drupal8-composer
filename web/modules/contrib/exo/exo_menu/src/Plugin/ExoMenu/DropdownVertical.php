<?php

namespace Drupal\exo_menu\Plugin\ExoMenu;

use Drupal\exo_menu\Plugin\ExoMenuDropdownBase;

/**
 * Plugin implementation of the 'dropdown_vertical' eXo menu.
 *
 * @ExoMenu(
 *   id = "dropdown_vertical",
 *   label = @Translation("Dropdown Vertical"),
 * )
 */
class DropdownVertical extends ExoMenuDropdownBase {

  /**
   * {@inheritdoc}
   */
  public function prepareBuild(array $build) {
    $build = parent::prepareBuild($build);
    $build['#attributes']['class'][] = 'exo-menu-dropdown-vertical';
    $build['#attached']['library'][] = 'exo_menu/dropdown.vertical';
    return $build;
  }

}
