<?php

namespace Drupal\exo_alchemist\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for eXo Component Enhancement plugins.
 */
interface ExoComponentEnhancementInterface extends PluginInspectionInterface {

  /**
   * Get the enhancement definition.
   *
   * @return \Drupal\exo_alchemist\Definition\ExoComponentDefinitionEnhancement
   *   The enhancement definition.
   */
  public function getEnhancementDefinition();

  /**
   * Return component property info.
   *
   * @return array
   *   An array of property_id => description.
   */
  public function propertyInfo();

  /**
   * Return the values that will be passed to the component for display.
   *
   * @param \Drupal\Core\Plugin\Context\Context[] $contexts
   *   An array of current contexts.
   *
   * @return array
   *   A value that will be sent to twig.
   */
  public function view(array $contexts);

}
