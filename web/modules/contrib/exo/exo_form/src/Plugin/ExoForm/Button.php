<?php

namespace Drupal\exo_form\Plugin\ExoForm;

use Drupal\exo_form\Plugin\ExoFormBase;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "button",
 *   label = @Translation("Button"),
 *   element_types = {
 *     "button",
 *     "submit",
 *   }
 * )
 */
class Button extends ExoFormBase {

  /**
   * {@inheritdoc}
   */
  protected $intersectSupported = TRUE;

  /**
   * {@inheritdoc}
   */
  protected $featureAttributeKey = 'exo_form_attributes';

  /**
   * {@inheritdoc}
   */
  public function process(&$element) {
    // Support for views UI.
    if (!empty($element['#process']) && in_array('views_ui_form_button_was_clicked', $element['#process'])) {
      $this->disableWrapper();
    }
    parent::process($element);
  }

  /**
   * {@inheritdoc}
   */
  public function preRender($element) {
    $element = parent::preRender($element);
    $element['#attributes']['class'][] = 'exo-form-button';
    return $element;
  }

}
