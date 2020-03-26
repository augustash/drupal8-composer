<?php

namespace Drupal\exo_form\Plugin\ExoForm;

use Drupal\exo_form\Plugin\ExoFormBase;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "commerce_price",
 *   label = @Translation("Commerce Price"),
 *   element_types = {
 *     "commerce_price",
 *   }
 * )
 */
class CommercePrice extends ExoFormBase {

  /**
   * {@inheritdoc}
   */
  public function preRender($element) {
    $element = parent::preRender($element);
    if (isset($element['currency_code']['#type']) && $element['currency_code']['#type'] == 'hidden' && isset($element['currency_code']['#value']) && $element['currency_code']['#value'] == 'USD') {
      $element['number']['#field_prefix'] = '$';
    }
    return $element;
  }

}
