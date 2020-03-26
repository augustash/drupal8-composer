<?php

namespace Drupal\exo_form\Plugin\ExoForm;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "language_select",
 *   label = @Translation("Language Select"),
 *   element_types = {
 *     "language_select",
 *   }
 * )
 */
class LanguageSelect extends Select {

  /**
   * {@inheritdoc}
   */
  public function process(&$element) {
    parent::process($element);
    if (!isset($element['#options'])) {
      $this->disableWrapper();
    }
  }

}
