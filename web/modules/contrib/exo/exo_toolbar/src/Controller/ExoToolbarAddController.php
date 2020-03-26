<?php

namespace Drupal\exo_toolbar\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\exo_toolbar\Entity\ExoToolbarInterface;

/**
 * Controller for building the eXo toolbar item instance add form.
 */
class ExoToolbarAddController extends ControllerBase {

  /**
   * Build the toolbar item instance add form.
   *
   * @param \Drupal\exo_toolbar\Entity\ExoToolbarInterface $exo_toolbar
   *   The toolbar to list items for.
   * @param mixed $exo_toolbar_region
   *   The toolbar region list items for.
   * @param string $plugin_id
   *   The plugin ID for the block instance.
   *
   * @return array
   *   The item instance edit form.
   */
  public function addForm(ExoToolbarInterface $exo_toolbar, $exo_toolbar_region, $plugin_id) {

    // Create an item entity.
    $entity = $this->entityTypeManager()->getStorage('exo_toolbar_item')->create([
      'toolbar' => $exo_toolbar->id(),
      'region' => $exo_toolbar_region,
      'plugin' => $plugin_id,
    ]);

    return $this->entityFormBuilder()->getForm($entity);
  }

}
