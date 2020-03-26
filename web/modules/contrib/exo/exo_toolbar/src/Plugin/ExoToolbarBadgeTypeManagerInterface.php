<?php

namespace Drupal\exo_toolbar\Plugin;

use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Defines the interface for eXo toolbar badge type plugin managers.
 */
interface ExoToolbarBadgeTypeManagerInterface extends PluginManagerInterface {

  /**
   * Get badge type labels.
   *
   * @return array
   *   An array of strings keyed by plugin id.
   */
  public function getBadgeTypeLabels();

}
