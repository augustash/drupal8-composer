<?php

namespace Drupal\exo\Plugin\Field\FieldWidget;

/**
 * Plugin implementation of the 'exo_theme_color' widget.
 *
 * @FieldWidget(
 *   id = "exo_theme_color",
 *   label = @Translation("Theme color"),
 *   weight = -90,
 *   field_types = {
 *     "exo_attribute"
 *   }
 * )
 */
class ExoThemeColorWidget extends ExoAttributeWidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultOptions() {
    $options = [];
    foreach (exo_theme_colors() as $key => $color) {
      $options[$key] = '<div class="exo-icon exo-swatch" style="background-color:' . $color['hex'] . '"></div><span class="exo-icon-label">' . $color['label'] . '</span>';
    }
    return $options;
  }

}
