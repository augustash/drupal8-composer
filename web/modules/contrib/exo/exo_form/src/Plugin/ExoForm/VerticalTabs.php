<?php

namespace Drupal\exo_form\Plugin\ExoForm;

use Drupal\exo_form\Plugin\ExoFormBase;
use Drupal\Core\Render\Element;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "vertical_tabs",
 *   label = @Translation("Vertical Tabs"),
 *   element_types = {
 *     "vertical_tabs",
 *   }
 * )
 */
class VerticalTabs extends ExoFormBase {

  /**
   * {@inheritdoc}
   */
  public function process(&$element) {
    parent::process($element);
    // This is a less-than ideal way to check if this vertical tab belongs to a
    // form. For non-form vertical tabs we are allowing exo_form to style
    // them -- but this may not be ideal.
    $element['#exo_is_form'] = isset($element['#defaults_loaded']);
  }

  /**
   * {@inheritdoc}
   */
  public function preRender($element) {
    $element = parent::preRender($element);
    $element['#attached']['library'][] = 'exo_form/base';
    $element['#exo_form_inner_attributes']['class'][] = 'exo-form-vertical-tabs';
    if (!isset($element['#exo_form_attributes']['class'])) {
      $element['#exo_form_attributes']['class'] = [];
    }
    $element['#exo_form_attributes']['class'] = array_merge($element['#exo_form_attributes']['class'], exo_form_classes());
    $element['group']['#exo_wrapper_supported'] = FALSE;
    $element['#prefix'] = '';
    $element['#suffix'] = '';
    // Make sure ids are used in attributes as horizontal tabs requires them.
    foreach (Element::children($element) as $key) {
      $el = &$element[$key];
      if (!empty($el['#id'])) {
        $el['#attributes']['id'] = $el['#id'];
      }
    }
    return $element;
  }

}
