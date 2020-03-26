<?php

namespace Drupal\exo_toolbar\Plugin\ExoToolbarItem;

use Drupal\exo_toolbar\Plugin\ExoToolbarItemBase;
use Drupal\exo_toolbar\ExoToolbarElement;

/**
 * Plugin implementation of the 'admin_escape' eXo toolbar item.
 *
 * @ExoToolbarItem(
 *   id = "admin_escape",
 *   admin_label = @Translation("Admin Escape"),
 *   category = @Translation("Common"),
 * )
 */
class AdminEscape extends ExoToolbarItemBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'title' => 'Return to Site',
      'icon' => 'regular-arrow-alt-circle-left',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function build($preview = FALSE) {
    $build = parent::build($preview);
    $build['#attached']['library'][] = 'exo_toolbar/adminEscape';
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function elementBuild() {

    $route = \Drupal::routeMatch()->getRouteObject();
    if (\Drupal::service('router.admin_context')->isAdminRoute($route) === TRUE) {
      return ExoToolbarElement::create([
        'title' => $this->label(),
        'icon' => $this->getIcon(),
        'attributes' => [
          'class' => ['exo-toolbar-admin-escape', 'exo-toolbar-element-hidden'],
        ],
      ])->setUrl('internal:/')->setAsLink();
    }
    return NULL;
  }

}
