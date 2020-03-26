<?php

namespace Drupal\exo_toolbar\Plugin;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Component\Plugin\DerivativeInspectionInterface;
use Drupal\exo_toolbar\ExoToolbarElementInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;

/**
 * Defines an interface for eXo toolbar badge type plugins.
 */
interface ExoToolbarBadgeTypePluginInterface extends ConfigurableInterface, PluginFormInterface, PluginInspectionInterface, DerivativeInspectionInterface, CacheableDependencyInterface {

  /**
   * Gets the plugin provider.
   *
   * The provider is the name of the module that provides the plugin.
   *
   * @return string
   *   The provider.
   */
  public function getProvider();

  /**
   * Prepare element for rendering.
   *
   * @param \Drupal\exo_toolbar\ExoToolbarElementInterface $element
   *   The element being acted on.
   * @param string $key
   *   The unique key of the element being acted on.
   * @param \Drupal\exo_toolbar\Plugin\ExoToolbarItemPluginInterface $item
   *   The parent toolbar item plugin.
   */
  public function elementPrepare(ExoToolbarElementInterface $element, $key, ExoToolbarItemPluginInterface $item);

}
