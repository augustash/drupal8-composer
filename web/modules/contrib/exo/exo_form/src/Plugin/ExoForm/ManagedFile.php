<?php

namespace Drupal\exo_form\Plugin\ExoForm;

use Drupal\Core\Render\Element;
use Drupal\exo_form\Plugin\ExoFormBase;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "managed_file",
 *   label = @Translation("Managed File"),
 *   element_types = {
 *     "managed_file",
 *     "exo_config_image",
 *   }
 * )
 */
class ManagedFile extends ExoFormBase {

  /**
   * {@inheritdoc}
   */
  protected $intersectSupported = TRUE;

  /**
   * {@inheritdoc}
   */
  public function preRender($element) {
    $element['upload']['#is_wrapped'] = TRUE;
    // $element['upload']['#is_managed'] = $element['#type'] === 'managed_file';
    // $element['#exo_form_attributes']['class'][] = 'exo-form-managed-file';
    // @see \Drupal\exo_form\Plugin\ExoForm\File for file preRender().
    $element['#exo_form_attributes']['class'][] = 'exo-form-managed-file';
    $element['#exo_form_attributes']['class'][] = 'exo-form-managed-file-js';
    // If (!empty($element['#value']['fids'])) {
    // }.
    if ($element['#type'] == 'managed_file') {
      foreach (Element::children($element) as $key) {
        if (substr($key, 0, 5) === 'file_') {
          $element[$key]['filename']['#attributes']['class'][] = 'exo-form-file-input';
          // $element[$key]['filename']['#attributes']['class'][] = 'exo-form-file-item-js';
          // $element[$key]['filename']['#attributes']['class'][] = 'ready';
        }
      }
      // ($element);
      // die;
    }
    return parent::preRender($element);
  }

}
