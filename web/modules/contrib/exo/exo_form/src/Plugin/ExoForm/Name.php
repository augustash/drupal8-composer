<?php

namespace Drupal\exo_form\Plugin\ExoForm;

use Drupal\Core\Render\Element;
use Drupal\exo_form\Plugin\ExoFormBase;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "name",
 *   label = @Translation("Name"),
 *   element_types = {
 *     "name",
 *   }
 * )
 */
class Name extends ExoFormBase {

  /**
   * {@inheritdoc}
   */
  public function preRender($element) {
    $element = parent::preRender($element);
    $element['#exo_form_input_attributes']['class'][] = 'exo-form-inline';
    $element['#exo_form_input_attributes']['class'][] = 'exo-form-inline-align-top';
    $element['#prefix'] = '';
    $element['#suffix'] = '';
    $element['_name']['#prefix'] = '';
    $element['_name']['#suffix'] = '';
    foreach (Element::children($element['_name']) as $key) {
      $element['_name'][$key]['#prefix'] = '';
      $element['_name'][$key]['#suffix'] = '';
    }
    return $element;
  }

}
