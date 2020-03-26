<?php

namespace Drupal\exo_form\Plugin\ExoForm;

use Drupal\exo_form\Plugin\ExoFormBase;
use Drupal\Core\Render\Markup;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "fieldset_as_field",
 *   label = @Translation("Fieldset as Field"),
 *   element_types = {
 *     "webform_select_other",
 *   }
 * )
 */
class FieldsetAsField extends ExoFormBase {

  /**
   * {@inheritdoc}
   */
  protected $intersectSupported = TRUE;

  /**
   * {@inheritdoc}
   */
  public function preRender($element) {
    $element = parent::preRender($element);
    if (!empty($element['#title'])) {
      $element_class = !empty($element['#required']) ? 'js-form-required form-required' : '';
      $element['#title'] = Markup::create('<label class="' . $element_class . '">' . $element['#title'] . '</label>');
    }
    $element['#exo_form_attributes']['class'][] = 'exo-form-fieldset-as-field';
    if (isset($element['other']) && isset($element['other']['#option_delimiter'])) {
      $element['#exo_form_attributes']['class'][] = 'exo-form-fieldset-has-other';
      $element['other']['#exo_form_attributes']['class'][] = 'exo-form-element-last-exclude';
      $element['other']['#exo_form_attributes']['class'][] = 'exo-form-hide-exclude';
      $element['other']['#exo_form_attributes']['class'][] = 'exo-form-other';
    }
    return $element;
  }

}
