<?php

namespace Drupal\exo_form\Plugin\ExoForm;

use Drupal\exo_form\Plugin\ExoFormBase;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "input",
 *   label = @Translation("Input"),
 *   element_types = {
 *     "textfield",
 *     "number",
 *     "url",
 *     "email",
 *     "password",
 *     "search",
 *     "tel",
 *     "entity_autocomplete",
 *     "exo_entity_autocomplete",
 *     "commerce_number",
 *     "search_api_autocomplete",
 *     "exo_number",
 *     "exo_icon",
 *   }
 * )
 */
class Input extends ExoFormBase {

  /**
   * {@inheritdoc}
   */
  protected $intersectSupported = TRUE;

  /**
   * {@inheritdoc}
   */
  protected $floatSupported = TRUE;

  /**
   * {@inheritdoc}
   */
  public function preRender($element) {
    $element = parent::preRender($element);
    if (empty($element['#exo_form_clean'])) {
      $element['#wrapper_attributes']['class'][] = 'exo-form-input';
      $element['#wrapper_attributes']['class'][] = 'exo-form-input-js';
      $element['#attributes']['class'][] = 'exo-form-input-item';
      $element['#attributes']['class'][] = 'exo-form-input-item-js';
      $element['#attached']['library'][] = 'exo_form/input';
      if (!empty($element['#field_prefix'])) {
        $element['#wrapper_attributes']['class'][] = 'has-prefix';
      }
      if (!empty($element['#field_suffix'])) {
        $element['#wrapper_attributes']['class'][] = 'has-suffix';
      }
      if (!empty($element['#placeholder'])) {
        $element['#attributes']['placeholder'] = $element['#placeholder'];
      }
    }
    if ($element['#type'] == 'exo_number') {
      $this->floatSupported = FALSE;
    }
    return $element;
  }

}
