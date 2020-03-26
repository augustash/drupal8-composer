<?php

namespace Drupal\exo\Plugin\Field\FieldWidget;

/**
 * Plugin implementation of the 'exo_alignment_x' widget.
 *
 * @FieldWidget(
 *   id = "exo_alignment_x",
 *   label = @Translation("Alignment: Horizontal"),
 *   weight = -80,
 *   field_types = {
 *     "exo_attribute"
 *   }
 * )
 */
class ExoAlignmentHorizontalWidget extends ExoAttributeWidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultOptions() {
    return [
      'left' => exo_icon('Left')->setIcon('regular-arrow-to-left'),
      'center' => exo_icon('Center')->setIcon('regular-arrows-h'),
      'right' => exo_icon('Right')->setIcon('regular-arrow-to-right'),
    ];
  }

}
