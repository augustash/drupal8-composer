<?php

namespace Drupal\exo_toolbar\Plugin;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Component\Plugin\DerivativeInspectionInterface;
use Drupal\exo_toolbar\ExoToolbarElementInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\exo_toolbar\Entity\ExoToolbarItemInterface;

/**
 * Defines an interface for eXo toolbar dialog type plugins.
 */
interface ExoToolbarDialogTypePluginInterface extends ConfigurableInterface, PluginFormInterface, PluginInspectionInterface, DerivativeInspectionInterface, CacheableDependencyInterface {

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
   *   The element to prepare.
   */
  public function elementPrepare(ExoToolbarElementInterface $element);

  /**
   * Get the dialog response.
   *
   * @param \Drupal\exo_toolbar\Entity\ExoToolbarItemInterface $exo_toolbar_item
   *   A eXo toolbar item.
   * @param string $arg
   *   An optional argument that can be passed through do alter the response.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The response.
   */
  public function dialogResponse(ExoToolbarItemInterface $exo_toolbar_item, $arg = NULL);

}
