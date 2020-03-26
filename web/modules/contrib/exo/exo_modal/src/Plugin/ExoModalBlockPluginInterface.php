<?php

namespace Drupal\exo_modal\Plugin;

use Drupal\Core\Block\BlockPluginInterface;

/**
 * Defines the required interface for all eXo modal block plugins.
 */
interface ExoModalBlockPluginInterface extends BlockPluginInterface {

  /**
   * Builds and returns the renderable modal array for this block plugin.
   *
   * @return array
   *   A renderable array representing the content of the modal.
   */
  public function buildModal();

}
