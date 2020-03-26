<?php

namespace Drupal\exo\Plugin\Field\FieldWidget;

/**
 * Plugin implementation of the 'exo_spacing' widget.
 *
 * @FieldWidget(
 *   id = "exo_spacing",
 *   label = @Translation("Spacing"),
 *   default_cardinality = -1,
 *   field_types = {
 *     "exo_attribute"
 *   }
 * )
 */
class ExoSpacingWidget extends ExoAttributeWidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultOptions() {
    return [
      'top' => exo_icon('Top')->setIcon('regular-arrow-to-top'),
      'bottom' => exo_icon('Bottom')->setIcon('regular-arrow-to-bottom'),
      'left' => exo_icon('Left')->setIcon('regular-arrow-to-left'),
      'right' => exo_icon('Right')->setIcon('regular-arrow-to-right'),
    ];
  }

}
