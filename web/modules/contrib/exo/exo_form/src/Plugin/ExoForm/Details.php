<?php

namespace Drupal\exo_form\Plugin\ExoForm;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "details",
 *   label = @Translation("Details"),
 *   element_types = {
 *     "details",
 *   }
 * )
 */
class Details extends Wrapper {

  /**
   * {@inheritdoc}
   */
  public function preRender($element) {
    $this->enableWrapper(empty($element['#group']));
    $element = parent::preRender($element);
    return $element;
  }

}
