<?php

namespace Drupal\exo\Plugin\Field\FieldWidget;

/**
 * Plugin implementation of the 'exo_containment' widget.
 *
 * @FieldWidget(
 *   id = "exo_containment",
 *   label = @Translation("Containment"),
 *   weight = -100,
 *   field_types = {
 *     "exo_attribute"
 *   }
 * )
 */
class ExoContainmentWidget extends ExoAttributeWidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultOptions() {
    return [
      'contain' => exo_icon('Contain within Container')->setIcon('regular-border-inner'),
      'expand' => exo_icon('Expand beyond Container')->setIcon('regular-border-outer'),
    ];
  }

}
