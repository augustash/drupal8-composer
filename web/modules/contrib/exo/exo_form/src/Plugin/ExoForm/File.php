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
    // // $is_wrapped = !empty($element['#is_wrapped']);
    // // $is_managed = !empty($element['#is_managed']);
    $element['#attached']['library'][] = 'exo_form/file';
    $element['#exo_form_attributes']['class'][] = 'exo-form-file';
    $element['#exo_form_attributes']['class'][] = 'exo-form-file-js';
    $element['#exo_form_input_attributes']['class'][] = 'exo-form-file-input';
    $element['#exo_form_input_attributes']['class'][] = 'exo-form-file-button';
    $element['#exo_form_input_attributes']['data-text'] = t('Select a file');
    if (empty($element['#theme_wrappers']) || !in_array('form_element', $element['#theme_wrappers'])) {
      $element['#theme_wrappers'][] = 'form_element';
    }
    // ksm($element);
    // // $this->disableWrapper();
    // if ($is_wrapped) {
    //   // $this->disableWrapper();
    //   // $element['#exo_form_input_attributes']['class'][] = 'exo-form-file-item';
    //   // $element['#exo_form_input_attributes']['class'][] = 'exo-form-file-item-js';
    // }
    // else {
    //   // $element['#exo_form_attributes']['class'][] = 'exo-form-file';
    //   // $element['#exo_form_attributes']['class'][] = 'exo-form-file-js';
    //   // $element['#exo_form_input_attributes']['class'][] = 'exo-form-file-item';
    //   // $element['#exo_form_input_attributes']['class'][] = 'exo-form-file-item-js';
    // }.
    // // if ($is_wrapped) {
    // //   $element['#exo_form_inner_attributes']['class'][] = 'exo-form-file-item';
    // //   $element['#exo_form_inner_attributes']['class'][] = 'exo-form-file-item-js';
    // // }
    // // else {
    // //   $element['#exo_form_attributes']['class'][] = 'exo-form-file';
    // //   $element['#exo_form_attributes']['class'][] = 'exo-form-file-js';
    // //   $element['#exo_form_input_attributes']['class'][] = 'exo-form-file-item';
    // //   $element['#exo_form_input_attributes']['class'][] = 'exo-form-file-item-js';
    // // }
    // // if (!$is_managed) {
    // //   $element['#exo_form_attributes']['class'][] = 'exo-form-file-unmanaged';
    // //   $element['#exo_form_attributes']['class'][] = 'exo-form-file-unmanaged-js';
    // // }.
    return parent::preRender($element);
  }

}
