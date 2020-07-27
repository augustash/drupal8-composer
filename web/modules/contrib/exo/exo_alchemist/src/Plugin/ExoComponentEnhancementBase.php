<?php

namespace Drupal\exo_alchemist\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\exo_alchemist\ExoComponentContextTrait;
use Drupal\exo_icon\ExoIconTranslationTrait;

/**
 * Base class for eXo Component Enhancement plugins.
 */
abstract class ExoComponentEnhancementBase extends PluginBase implements ExoComponentEnhancementInterface {

  use ExoIconTranslationTrait;
  use ExoComponentContextTrait;

  /**
   * {@inheritdoc}
   */
  public function getEnhancementDefinition() {
    return $this->configuration['enhancementDefinition'];
  }

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function view(array $contexts) {
    return [];
  }

  public function getLibrary() {
    return $this->pluginDefinition['library'];
  }

}
