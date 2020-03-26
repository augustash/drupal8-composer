<?php

namespace Drupal\exo_form\Plugin\ExoForm;

use Drupal\exo_form\Plugin\ExoFormBase;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "hidden",
 *   label = @Translation("Hidden"),
 *   element_types = {
 *     "hidden",
 *   }
 * )
 */
class Hidden extends ExoFormBase {

  /**
   * {@inheritdoc}
   */
  public function process(&$element) {
    parent::process($element);
    $this->disableWrapper();
  }

  /**
   * {@inheritdoc}
   */
  public function preRender($element) {
    $element = parent::preRender($element);
    $element['#wrapper_attributes']['class'][] = 'exo-form-container-hide';
    return $element;
  }

}
