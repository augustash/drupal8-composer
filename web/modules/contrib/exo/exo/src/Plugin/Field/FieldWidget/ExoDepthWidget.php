<?php

namespace Drupal\exo\Plugin\Field\FieldWidget;

/**
 * Plugin implementation of the 'exo_depth' widget.
 *
 * @FieldWidget(
 *   id = "exo_depth",
 *   label = @Translation("Depth"),
 *   field_types = {
 *     "exo_attribute"
 *   }
 * )
 */
class ExoDepthWidget extends ExoAttributeWidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultOptions() {
    return [
      'above' => exo_icon('Above')->setIcon('regular-bring-front'),
      'behind' => exo_icon('Behind')->setIcon('regular-send-back'),
    ];
  }

}
