<?php

namespace Drupal\exo\ExoThemeProvider;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Base class for eXo theme provider plugins.
 */
abstract class ExoThemeProviderBase extends PluginBase implements ExoThemeProviderPluginInterface {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getLibrary() {
    return $this->pluginDefinition['library'];
  }

  /**
   * {@inheritdoc}
   */
  public function getPath($from_root = FALSE) {
    return ($from_root ? '/' . $this->pluginDefinition['providerPath'] . '/' : '') . $this->pluginDefinition['path'];
  }

  /**
   * {@inheritdoc}
   */
  public function getPathname($from_root = FALSE) {
    return $this->getPath($from_root) . '/' . $this->pluginDefinition['filename'];
  }

}
