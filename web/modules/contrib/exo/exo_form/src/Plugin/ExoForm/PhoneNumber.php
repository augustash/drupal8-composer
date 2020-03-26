<?php

namespace Drupal\exo_form\Plugin\ExoForm;

use Drupal\Core\Render\Element;
use Drupal\exo_form\Plugin\ExoFormBase;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "phone_number",
 *   label = @Translation("PhoneNumber"),
 *   element_types = {
 *     "phone_number",
 *   }
 * )
 */
class PhoneNumber extends ExoFormBase {

  /**
   * {@inheritdoc}
   */
  public function process(&$element) {
    parent::process($element);
    $element['country-code']['#exo_form_default'] = TRUE;
    $element['country-code']['#theme_wrappers'][] = 'exo_form_element_container';
    $element['country-code']['#exo_form_inner_attributes']['class'][] = 'exo-form-pseudo';
  }

  /**
   * {@inheritdoc}
   */
  public function preRender($element) {
    $element = parent::preRender($element);
    $element['#theme'] = 'form_element';
    $element['#exo_form_input_attributes']['class'][] = 'exo-form-inline';
    $element['#exo_form_input_attributes']['class'][] = 'exo-form-inline-align-top';
    unset($element['label']);
    foreach (Element::children($element) as $key) {
      $element['#children'][$key] = $element[$key];
    }
    return $element;
  }

}
