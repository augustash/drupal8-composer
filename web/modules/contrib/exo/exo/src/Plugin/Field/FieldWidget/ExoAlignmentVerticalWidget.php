<?php

namespace Drupal\exo\Plugin\Field\FieldWidget;

/**
 * Plugin implementation of the 'exo_alignment_y' widget.
 *
 * @FieldWidget(
 *   id = "exo_alignment_y",
 *   label = @Translation("Alignment: Vertical"),
 *   weight = -80,
 *   field_types = {
 *     "exo_attribute"
 *   }
 * )
 */
class ExoAlignmentVerticalWidget extends ExoAttributeWidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultOptions() {
    return [
      'top' => exo_icon('Top')->setIcon('regular-arrow-to-top'),
      'center' => exo_icon('Center')->setIcon('regular-arrows-v'),
      'bottom' => exo_icon('Bottom')->setIcon('regular-arrow-to-bottom'),
    ];
  }

}
