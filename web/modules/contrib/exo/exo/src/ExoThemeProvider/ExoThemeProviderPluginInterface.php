<?php

namespace Drupal\exo\ExoThemeProvider;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for eXo theme provider plugins.
 */
interface ExoThemeProviderPluginInterface extends PluginInspectionInterface {

  /**
   * Get the library.
   *
   * @return string
   *   The library.
   */
  public function getLibrary();

  /**
   * Get the path of the default CSS file.
   *
   * @param bool $from_root
   *   If true, the path returned will be from Drupal root.
   *
   * @return string
   *   The path.
   */
  public function getPath($from_root = FALSE);

  /**
   * Get the path and filename of the default CSS file.
   *
   * @param bool $from_root
   *   If true, the path returned will be from Drupal root.
   *
   * @return string|null
   *   The path.
   */
  public function getPathname($from_root = FALSE);

}
