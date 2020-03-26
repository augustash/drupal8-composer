<?php

namespace Drupal\exo\Plugin\Field\FieldWidget;

/**
 * Plugin implementation of the 'exo_column_count' widget.
 *
 * @FieldWidget(
 *   id = "exo_column_count",
 *   label = @Translation("Column Count"),
 *   field_types = {
 *     "exo_attribute"
 *   }
 * )
 */
class ExoColumnCountWidget extends ExoAttributeWidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultOptions() {
    return [
      '1' => exo_icon('One')->setIcon('regular-dice-one'),
      '2' => exo_icon('Two')->setIcon('regular-dice-two'),
      '3' => exo_icon('Three')->setIcon('regular-dice-three'),
      '4' => exo_icon('Four')->setIcon('regular-dice-four'),
      '5' => exo_icon('Five')->setIcon('regular-dice-five'),
      '6' => exo_icon('Six')->setIcon('regular-dice-six'),
    ];
  }

}
