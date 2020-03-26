<?php

namespace Drupal\exo_alchemist\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Defines an interface for Component Property plugins.
 */
interface ExoComponentPropertyInterface extends PluginInspectionInterface, PluginFormInterface {

  /**
   * Return a default value.
   *
   * @return mixed
   *   The default value.
   */
  public function getDefault();

  /**
   * Return as value ready for inclusing in attributes array.
   *
   * @return array
   *   An array of attributes.
   */
  public function asAttributeArray();

}
