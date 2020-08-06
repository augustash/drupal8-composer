<?php

namespace Drupal\exo_form\Plugin\ExoForm;

use Drupal\exo_form\Plugin\ExoFormBase;
use Drupal\Core\Render\Element;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "checkboxes",
 *   label = @Translation("Checkboxes"),
 *   element_types = {
 *     "checkboxes",
 *     "webform_entity_checkboxes",
 *     "webform_roles",
 *     "exo_checkboxes",
 *   }
 * )
 */
class Checkboxes extends ExoFormBase {

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
  public function process(&$element) {
    parent::process($element);
    foreach (Element::children($element) as $key) {
      $child_element = &$element[$key];
      if (isset($child_element['#type']) && $child_element['#type'] == 'checkbox') {
        $child_element['#exo_wrapper_supported'] = FALSE;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function preRender($element) {
    $element = parent::preRender($element);
    $element['#attributes']['class'][] = 'exo-form-checkboxes';
    $element['#attributes']['class'][] = 'exo-form-checkboxes-js';
    if (!empty($element['#inline'])) {
      $element['#attributes']['class'][] = 'exo-form-checkboxes-inline';
    }
    return $element;
  }

}
