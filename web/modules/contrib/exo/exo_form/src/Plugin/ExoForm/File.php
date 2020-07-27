<?php

namespace Drupal\exo_form\Plugin\ExoForm;

use Drupal\exo_form\Plugin\ExoFormBase;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "file",
 *   label = @Translation("File"),
 *   element_types = {
 *     "file",
 *     "exo_config_file",
 *   }
 * )
 */
class File extends ExoFormBase {

  /**
   * {@inheritdoc}
   */
  protected $intersectSupported = TRUE;

  /**
   * {@inheritdoc}
   */
  public function preRender($element) {
    $element['#attached']['library'][] = 'exo_form/file';
    $element['#exo_form_attributes']['class'][] = 'exo-form-file';
    $element['#exo_form_attributes']['class'][] = 'exo-form-file-js';
    $element['#exo_form_input_attributes']['class'][] = 'exo-form-file-input';
    $element['#exo_form_input_attributes']['class'][] = 'exo-form-file-button';
    $element['#exo_form_input_attributes']['data-text'] = t('Select a file');
    if (empty($element['#theme_wrappers']) || !in_array('form_element', $element['#theme_wrappers'])) {
      $element['#theme_wrappers'][] = 'form_element';
    }
    return parent::preRender($element);
  }

}
