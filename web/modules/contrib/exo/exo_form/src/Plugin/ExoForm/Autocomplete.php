<?php

namespace Drupal\exo_form\Plugin\ExoForm;

use Drupal\exo_form\Plugin\ExoFormBase;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "autocomplete",
 *   label = @Translation("Autocomplete"),
 *   element_types = {
 *     "exo_autocomplete",
 *     "autocomplete_deluxe",
 *   }
 * )
 */
class Autocomplete extends ExoFormBase {

  /**
   * {@inheritdoc}
   */
  protected $intersectSupported = TRUE;

  /**
   * {@inheritdoc}
   */
  protected $floatSupported = TRUE;

  /**
   * Wrapping attributes are sent to template via this attribute name.
   *
   * @var string
   */
  protected $featureAttributeKey = 'exo_form_inner_attributes';

  /**
   * {@inheritdoc}
   */
  public function preRender($element) {
    $element = parent::preRender($element);
    $element['#exo_form_attributes']['class'][] = 'form-item';
    $element['#exo_form_attributes']['class'][] = 'exo-form-input';
    $element['#exo_form_attributes']['class'][] = 'exo-form-input-js';
    $element['#exo_form_inner_attributes']['class'][] = 'exo-form-pseudo';
    $element['textfield']['#attributes']['class'][] = 'exo-form-input-item';
    $element['textfield']['#attributes']['class'][] = 'exo-form-input-item-js';
    $element['textfield']['#attached']['library'][] = 'exo_form/input';
    $element['textfield']['#exo_form_default'] = TRUE;
    // $element['textfield']['#attributes']['class'][] = 'exo-form-input-value';
    $element['value_field']['#exo_form_default'] = TRUE;
    // $element['#exo_form_inner_attributes']['class'][] = 'exo-form-pseudo';
    // $element['#wrapper_outer_prefix']['label'] = $element['label'];
    // $element['label']['#access'] = FALSE;
    // ksm($element);
    $element['#wrapper_outer_suffix']['#markup'] = '<div class="exo-form-input-line"></div>';

    // $element['value_field']['#exo_form_default'] = TRUE;
    // $element['textfield']['#suffix'] = '<div class="exo-form-input-line"></div>' . $element['textfield']['#suffix'];
    // ksm($element);

    // $element['#wrapper_attributes']['class'][] = 'exo-form-input';
    // $element['#wrapper_attributes']['class'][] = 'exo-form-input-js';
    // $element['textfield']['#attributes']['class'][] = 'exo-form-input-item';
    // $element['textfield']['#attributes']['class'][] = 'exo-form-input-item-js';
    // $element['textfield']['#attached']['library'][] = 'exo_form/input';
    // $element['textfield']['#wrapper_suffix']['#markup'] = '<div class="exo-form-input-line"></div>';
    return $element;
  }

}
