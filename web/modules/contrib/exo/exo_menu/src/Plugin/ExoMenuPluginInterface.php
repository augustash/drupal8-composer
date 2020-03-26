<?php

namespace Drupal\exo_menu\Plugin;

/**
 * Defines an interface for eXo menu plugins.
 */
interface ExoMenuPluginInterface {

  /**
   * Prepare settings for attachment.
   *
   * @param array $settings
   *   A render array.
   * @param string $type
   *   A setting type. Either site or local.
   *
   * @return array
   *   A render array.
   */
  public function prepareSettings(array $settings, $type);

  /**
   * Prepare build for rendering.
   *
   * @param array $build
   *   A render array.
   *
   * @return array
   *   A render array.
   */
  public function prepareBuild(array $build);

  /**
   * Display each level as its own menu.
   */
  public function renderAsLevels();

}
