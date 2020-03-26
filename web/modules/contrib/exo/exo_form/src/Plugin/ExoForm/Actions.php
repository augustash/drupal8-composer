<?php

namespace Drupal\exo_form\Plugin\ExoForm;

/**
 * Provides a plugin for actions type(s).
 *
 * @ExoForm(
 *   id = "actions",
 *   label = @Translation("Actions"),
 *   element_types = {
 *     "actions",
 *   }
 * )
 */
class Actions extends Container {

  /**
   * {@inheritdoc}
   */
  protected $intersectSupported = TRUE;

  /**
   * {@inheritdoc}
   */
  public function preRender($element) {
    $this->enableWrapper();
    $element['#exo_form_attributes']['class'][] = 'exo-form-element';
    $element['#exo_form_attributes']['class'][] = 'exo-form-element-js';
    $element['#attributes']['class'][] = 'exo-form-inline';
    $element['#attributes']['class'][] = 'exo-form-inline-compact';
    return parent::preRender($element);
  }

}
