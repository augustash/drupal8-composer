<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentProperty;

/**
 * A 'exo_theme_color' adapter for exo components.
 *
 * @ExoComponentProperty(
 *   id = "exo_theme_color",
 *   label = @Translation("Theme Color"),
 * )
 */
class ExoThemeColor extends ClassAttribute {

  /**
   * {@inheritdoc}
   */
  public function getOptions() {
    $options = [
      '_none' => t('Auto'),
    ];
    foreach ([
      'white' => [
        'label' => t('White'),
        'hex' => '#fff',
      ],
      'black' => [
        'label' => t('Black'),
        'hex' => '#000',
      ],
    ] + exo_theme_colors() as $key => $color) {
      $options[$key] = '<div class="exo-icon exo-swatch" style="background-color:' . $color['hex'] . '"></div><span class="exo-icon-label">' . $color['label'] . '</span>';
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  protected function getHtmlClassName($name, $value) {
    return 'exo-modifier--' . $name . '-theme-' . $value;
  }

}
