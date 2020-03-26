<?php

namespace Drupal\exo_form\Plugin\ExoForm;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "wrapper",
 *   label = @Translation("Wrapper"),
 * )
 */
class Wrapper extends Container {

  /**
   * {@inheritdoc}
   */
  public function preRender($element) {
    $element = parent::preRender($element);
    $element += ['#attributes' => []];
    $element += ['#exo_form_attributes' => []];
    $element['#exo_form_attributes']['class'][] = 'exo-form-wrapper';
    $element['#exo_form_attributes']['class'][] = 'exo-form-wrapper-js';
    if (!empty($element['#description'])) {
      if (!is_array($element['#description'])) {
        $element['#description'] = '<div class="exo-form-element-wrapper-description">' . $element['#description'] . '</div>';
      }
    }
    return $element;
  }

}
