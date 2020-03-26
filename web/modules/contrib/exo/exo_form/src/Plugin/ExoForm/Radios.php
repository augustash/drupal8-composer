<?php

namespace Drupal\exo_form\Plugin\ExoForm;

use Drupal\exo_form\Plugin\ExoFormBase;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "radios",
 *   label = @Translation("Radios"),
 *   element_types = {
 *     "radios",
 *     "webform_entity_radios",
 *     "exo_radios",
 *   }
 * )
 */
class Radios extends ExoFormBase {

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
  public function preRender($element) {
    $element = parent::preRender($element);
    $element['#attributes']['class'][] = 'exo-form-radios';
    $element['#attributes']['class'][] = 'exo-form-radios-js';
    if (!empty($element['#inline'])) {
      $element['#attributes']['class'][] = 'exo-form-radios-inline';
    }
    return $element;
  }

}
