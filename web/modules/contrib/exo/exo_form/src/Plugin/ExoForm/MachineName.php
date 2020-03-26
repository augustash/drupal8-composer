<?php

namespace Drupal\exo_form\Plugin\ExoForm;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "machine_name",
 *   label = @Translation("Machine Name"),
 *   element_types = {
 *     "machine_name",
 *   }
 * )
 */
class MachineName extends Input {

  /**
   * {@inheritdoc}
   */
  public function preRender($element) {
    $element = parent::preRender($element);
    $element['#field_prefix'] = str_replace('dir="ltr"', 'class="field-prefix" dir="ltr"', $element['#field_prefix']);
    $element['#wrapper_attributes']['class'][] = 'exo-form-machine-name';
    return $element;
  }

}
