<?php

namespace Drupal\exo_form\Plugin\ExoForm;

use Drupal\exo_form\Plugin\ExoFormBase;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "address",
 *   label = @Translation("Address"),
 *   element_types = {
 *     "address",
 *   }
 * )
 */
class Address extends ExoFormBase {

  /**
   * {@inheritdoc}
   */
  public function preRender($element) {
    $element = parent::preRender($element);
    if (isset($element['#wrapper_id']) && !empty($element['#prefix'])) {
      // Special treatment for the AJAX functionality in the address field. We
      // need our element wrapper to make sure things are spaced correctly.
      $wrapper_id = $element['#wrapper_id'];
      $element['#prefix'] = '<div id="' . $wrapper_id . '" class="exo-form-address form-wrapper exo-form-element exo-form-element-js">';
    }
    // If (!empty($element['country_code']['#default_value']) && isset($element['country_code']['#available_countries']) && count($element['country_code']['#available_countries']) === 1) {
    //   $element['country_code']['#prefix'] = '<div class="hidden">';
    //   $element['country_code']['#suffix'] = '</div>';
    // }.
    return $element;
  }

}
