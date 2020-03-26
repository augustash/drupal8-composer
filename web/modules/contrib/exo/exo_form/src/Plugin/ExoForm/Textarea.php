<?php

namespace Drupal\exo_form\Plugin\ExoForm;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "textarea",
 *   label = @Translation("Textarea"),
 *   element_types = {
 *     "textarea",
 *   }
 * )
 */
class Textarea extends Input {

  /**
   * {@inheritdoc}
   */
  public function preRender($element) {
    if (!empty($element['#allowed_formats']) || !empty($element['#format'])) {
      // This textarea has a format selector and does not support label
      // floating.
      $this->disableFloatSupport();
      $this->disableIntersectSupport();
    }
    elseif (!empty($element['#autogrow'])) {
      $element['#attached']['library'][] = 'exo_form/autogrow';
      $element['#attributes']['data-autogrow'] = 'true';
      if (!empty($element['#autogrow_max'])) {
        $element['#attributes']['data-autogrow-max'] = $element['#autogrow_max'];
      }
    }
    return parent::preRender($element);
  }

}
