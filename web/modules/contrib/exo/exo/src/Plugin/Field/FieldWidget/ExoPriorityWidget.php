<?php

namespace Drupal\exo\Plugin\Field\FieldWidget;

/**
 * Plugin implementation of the 'exo_priority' widget.
 *
 * @FieldWidget(
 *   id = "exo_priority",
 *   label = @Translation("Priority"),
 *   field_types = {
 *     "exo_attribute"
 *   }
 * )
 */
class ExoPriorityWidget extends ExoAttributeWidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultOptions() {
    return [
      'primary' => exo_icon('Primary')->setIcon('regular-link'),
      'secondary' => exo_icon('Secondary')->setIcon('regular-link'),
    ];
  }

}
