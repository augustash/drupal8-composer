<?php

namespace Drupal\exo_toolbar\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\exo_toolbar\Plugin\ExoToolbarRegionPluginInterface;

/**
 * Provides an interface for defining eXo Toolbar Item entities.
 */
interface ExoToolbarItemInterface extends ConfigEntityInterface {

  /**
   * Get the toolbar ID.
   *
   * @return string
   *   The toolbar id this item is placed within.
   */
  public function getToolbarId();

  /**
   * Get the toolbar entity object.
   *
   * @return \Drupal\exo_toolbar\Entity\ExoToolbarInterface
   *   The toolbar entity this item is placed within.
   */
  public function getToolbar();

  /**
   * Returns the region id this item is placed in.
   *
   * @return string
   *   The region id this item is placed in.
   */
  public function getRegionId();

  /**
   * Get the region plugin instance.
   *
   * @return \Drupal\exo_toolbar\Plugin\ExoTOolbarRegionPluginInterface
   *   The toolbar region plugin instance.
   */
  public function getRegion();

  /**
   * Provides an array of information to build a list of operation links.
   *
   * @return array
   *   An associative array of operation link data for this list, keyed by
   *   operation name, containing the following key-value pairs:
   *   - title: The localized title of the operation.
   *   - url: An instance of \Drupal\Core\Url for the operation URL.
   *   - weight: The weight of this operation.
   */
  public function getOperations();

  /**
   * Allows the region render array to be altered.
   *
   * @param array $settings
   *   The settings array passed to drupalSettings.
   * @param \Drupal\exo_toolbar\Plugin\ExoToolbarRegionPluginInterface $region
   *   A region plugin.
   */
  public function alterRegionJsSettings(array &$settings, ExoToolbarRegionPluginInterface $region);

  /**
   * Allows the region render array to be altered.
   *
   * @param array $element
   *   A render array.
   * @param \Drupal\exo_toolbar\Plugin\ExoToolbarRegionPluginInterface $region
   *   A region plugin.
   */
  public function alterRegionElement(array &$element, ExoToolbarRegionPluginInterface $region);

  /**
   * Allows the section render array to be altered.
   *
   * @param array $element
   *   A render array.
   * @param array $context
   *   An array of contextual information including items, toolbar and section.
   */
  public function alterSectionElement(array &$element, array $context);

  /**
   * Gets the plugin_id of the plugin instance.
   *
   * @return string
   *   The plugin_id of the plugin instance.
   */
  public function getPluginId();

  /**
   * Get the plugin instance.
   *
   * @return \Drupal\exo_toolbar\Plugin\ExoToolbarItemPluginInterface
   *   The item plugin.
   */
  public function getPlugin();

}
