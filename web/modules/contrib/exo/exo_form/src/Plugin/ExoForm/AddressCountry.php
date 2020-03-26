<?php

namespace Drupal\exo_form\Plugin\ExoForm;

use Drupal\exo_form\Plugin\ExoFormBase;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "address_country",
 *   label = @Translation("Address Country"),
 *   element_types = {
 *     "address_country",
 *   }
 * )
 */
class AddressCountry extends ExoFormBase {

  /**
   * {@inheritdoc}
   */
  public function preRender($element) {
    $element = parent::preRender($element);
    $variables['attributes'] = [
      'class' => ['exo-form-element', 'exo-form-element-js'],
    ];
    return $element;
  }

}
