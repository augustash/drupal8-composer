<?php

namespace Drupal\exo\Plugin\Field\FieldWidget;

/**
 * Plugin implementation of the 'exo_alignment_sizing' widget.
 *
 * @FieldWidget(
 *   id = "exo_alignment_sizing",
 *   label = @Translation("Alignment: Sizing"),
 *   weight = -79,
 *   field_types = {
 *     "exo_attribute"
 *   }
 * )
 */
class ExoAlignmentSizingWidget extends ExoAttributeWidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultOptions() {
    return [
      'cover' => exo_icon('Cover')->setIcon('regular-expand-arrows'),
      'contain' => exo_icon('Contain')->setIcon('regular-expand'),
      'auto' => exo_icon('Original')->setIcon('regular-expand-alt'),
    ];
  }

}
