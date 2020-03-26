<?php

namespace Drupal\exo\ExoTheme;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for eXo theme plugins.
 */
interface ExoThemePluginInterface extends PluginInspectionInterface {

  /**
   * Get the path to the compiled CSS files.
   *
   * @param bool $from_root
   *   If true, the path returned will be from Drupal root.
   *
   * @return string|null
   *   The path.
   */
  public function getPath($from_root = FALSE);

}
