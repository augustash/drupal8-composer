<?php

namespace Drupal\exo_toolbar\Plugin;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Component\Plugin\DerivativeInspectionInterface;

/**
 * Defines an interface for eXo toolbar region plugins.
 */
interface ExoToolbarRegionPluginInterface extends ConfigurableInterface, PluginFormInterface, PluginInspectionInterface, DerivativeInspectionInterface {

  /**
   * Get a simple array of sections.
   *
   * @return \Drupal\exo_toolbar\ExoToolbarSectionInterface[]
   *   An array where key is the section ID and value is the label.
   */
  public function getSections();

  /**
   * Get the alignment.
   *
   * @return string
   *   A string that represents the aligment.
   */
  public function getAlignment();

  /**
   * Returns the weight of this region (used for sorting).
   *
   * @return int
   *   The toolbar weight.
   */
  public function getWeight();

  /**
   * Get the edge.
   *
   * @return string
   *   The edge of the region. Either 'top', 'right', 'bottom', 'left'.
   */
  public function getEdge();

  /**
   * Get the size.
   *
   * @return string
   *   The size of the region.
   */
  public function getSize();

  /**
   * Check if region should be rendered when toolbar is rendered.
   *
   * @return bool
   *   Returns TRUE if region should be rendered when toolbar is rendered.
   */
  public function isRenderedOnInit();

  /**
   * Check if set to icon only.
   *
   * @return bool
   *   Returns TRUE if mark-only.
   */
  public function isMarkOnly();

  /**
   * Check if region is toggleable.
   *
   * @return bool
   *   Returns TRUE if toggleable.
   */
  public function isToggleable();

  /**
   * Check if region is expandable.
   *
   * @return bool
   *   Returns TRUE if expandable.
   */
  public function isExpandable();

  /**
   * Check if region should be hidden.
   *
   * @return bool
   *   Returns TRUE if hidden.
   */
  public function isHidden();

  /**
   * Get the theme key.
   *
   * @return string
   *   Returns the theme key.
   */
  public function getTheme();

}
