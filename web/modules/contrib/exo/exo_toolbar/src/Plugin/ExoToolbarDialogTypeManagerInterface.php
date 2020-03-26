<?php

namespace Drupal\exo_toolbar\Plugin;

use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Defines the interface for eXo toolbar dialog type plugin managers.
 */
interface ExoToolbarDialogTypeManagerInterface extends PluginManagerInterface {

  /**
   * Get dialog type labels.
   *
   * @return array
   *   An array of strings keyed by plugin id.
   */
  public function getDialogTypeLabels();

}
