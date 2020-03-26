<?php

namespace Drupal\exo_form\Plugin\ExoForm;

use Drupal\exo_form\Plugin\ExoFormBase;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "text_format",
 *   label = @Translation("Text Format"),
 *   element_types = {
 *     "text_format",
 *   }
 * )
 */
class TextFormat extends ExoFormBase {

  /**
   * {@inheritdoc}
   */
  public function preRender($element) {
    $element = parent::preRender($element);
    $element['format']['#attributes']['class'][] = 'exo-filter-wrapper';
    return $element;
  }

}
