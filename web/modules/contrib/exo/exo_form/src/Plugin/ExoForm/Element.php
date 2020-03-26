<?php

namespace Drupal\exo_form\Plugin\ExoForm;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "element",
 *   label = @Translation("Element"),
 *   element_types = {
 *     "webform_flexbox",
 *   }
 * )
 */
class Element extends Container {

  /**
   * {@inheritdoc}
   */
  public function preRender($element) {
    $this->enableWrapper();
    $element = parent::preRender($element);
    return $element;
  }

}
