<?php

namespace Drupal\exo;

/**
 * Defines the interface for eXo settings.
 */
interface ExoSettingsPluginInterface {

  /**
   * Return the plugin manager.
   *
   * @return \Drupal\Component\Plugin\PluginManagerInterface
   *   The plugin manager.
   */
  public function getPluginManager();

}
