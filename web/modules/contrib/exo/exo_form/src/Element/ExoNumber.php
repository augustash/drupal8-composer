<?php

namespace Drupal\exo_form\Element;

use Drupal\Core\Render\Element\Number;

/**
 * Provides a form element for numeric input, with + and -.
 *
 * @FormElement("exo_number")
 */
class ExoNumber extends Number {

  /**
   * {@inheritdoc}
   */
  public static function preRenderNumber($element) {
    $element = parent::preRenderNumber($element);
    $element['#field_prefix'] = exo_icon('Decrease')->setIcon('regular-minus')->setIconOnly();
    $element['#field_suffix'] = exo_icon('Increase')->setIcon('regular-plus')->setIconOnly();
    $element['#attached']['library'][] = 'exo_form/number';
    $element['#wrapper_attributes']['class'][] = 'exo-form-number';
    $element['#wrapper_attributes']['class'][] = 'exo-form-number-js';
    return $element;
  }

}
