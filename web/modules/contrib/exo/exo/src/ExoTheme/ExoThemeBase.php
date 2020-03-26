<?php

namespace Drupal\exo\ExoTheme;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Base class for eXo theme plugins.
 */
abstract class ExoThemeBase extends PluginBase implements ExoThemePluginInterface {
  use StringTranslationTrait;

  /**
   * Get the HEX colors of this theme.
   *
   * @param bool $with_black_white
   *   Add black and white colors in.
   *
   * @return string
   *   The hex value.
   */
  public function getColors($with_black_white = FALSE) {
    $definition = $this->getPluginDefinition();
    $colors = $definition['colors'];
    if ($with_black_white) {
      $colors += [
        'white' => '#ffffff',
        'black' => '#1a1a1a',
      ];
    }
    return $colors;
  }

  /**
   * Get a HEX color by id.
   *
   * @param string $color_id
   *   The color id.
   *
   * @return string
   *   The hex value.
   */
  public function getColor($color_id) {
    switch ($color_id) {
      case 'white':
        return '#ffffff';

      case 'black':
        return '#1a1a1a';

      default:
        $colors = $this->getColors();
        $definition = $this->getPluginDefinition();
        if (isset($colors[$color_id])) {
          return $colors[$color_id];
        }
        return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getPath($from_root = FALSE) {
    return ($from_root ? '/' . $this->getProviderPath() . '/' : '') . $this->pluginDefinition['path'];
  }

  /**
   * {@inheritdoc}
   */
  public function getProviderPath() {
    return $this->pluginDefinition['providerPath'];
  }

  /**
   * {@inheritdoc}
   */
  public function getScssPath() {
    return $this->pluginDefinition['scssPath'];
  }

  /**
   * {@inheritdoc}
   */
  public function getProviderPathname($provider_plugin_id) {
    $directory = str_replace(' ', '', ucwords(str_replace('_', ' ', $this->getPluginId())));
    return $this->getPath(TRUE) . '/' . $directory . '/' . str_replace('_', '-', $provider_plugin_id) . '.css';
  }

}
