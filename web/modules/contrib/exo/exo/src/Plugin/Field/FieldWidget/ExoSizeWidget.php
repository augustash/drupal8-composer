<?php

namespace Drupal\exo\Plugin\Field\FieldWidget;

/**
 * Plugin implementation of the 'exo_size' widget.
 *
 * @FieldWidget(
 *   id = "exo_size",
 *   label = @Translation("Size"),
 *   field_types = {
 *     "exo_attribute"
 *   }
 * )
 */
class ExoSizeWidget extends ExoAttributeWidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultOptions() {
    return [
      'small' => exo_icon('Small')->setIcon('regular-battery-quarter'),
      'medium' => exo_icon('Medium')->setIcon('regular-battery-half'),
      'large' => exo_icon('Large')->setIcon('regular-battery-full'),
    ];
  }

}
