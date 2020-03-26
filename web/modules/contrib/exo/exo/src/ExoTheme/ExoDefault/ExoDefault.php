<?php

namespace Drupal\exo\ExoTheme\ExoDefault;

use Drupal\exo\ExoTheme\ExoThemeBase;

/**
 * Plugin implementation of the '{{ plugin_id }}' eXo theme.
 *
 * @ExoTheme(
 *   id = "default",
 *   label = @Translation("Default"),
 *   colors = {
 *     "base" = "#373a3c",
 *     "offset" = "#f1f1f1",
 *     "primary" = "#2780e3",
 *     "secondary" = "#b6bf3d",
 *   },
 * )
 */
class ExoDefault extends ExoThemeBase {

  /**
   * {@inheritdoc}
   */
  public function getScssPath() {
    return $this->pluginDefinition['providerPath'] . '/src/scss';
  }

}
