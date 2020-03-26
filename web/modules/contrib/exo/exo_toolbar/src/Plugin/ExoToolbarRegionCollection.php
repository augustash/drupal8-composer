<?php

namespace Drupal\exo_toolbar\Plugin;

use Drupal\Core\Plugin\DefaultLazyPluginCollection;

/**
 * Provides a collection of eXo toolbar region plugins.
 */
class ExoToolbarRegionCollection extends DefaultLazyPluginCollection {

  /**
   * Provides uasort() callback to sort plugins.
   */
  public function sortHelper($aID, $bID) {
    $a = $this->get($aID);
    $b = $this->get($bID);

    // Sort by weight.
    $weight = $a->getWeight() - $b->getWeight();
    if ($weight) {
      return $weight;
    }

    // Sort by id.
    return strnatcasecmp($a->getPluginId(), $b->getPluginId());
  }

}
