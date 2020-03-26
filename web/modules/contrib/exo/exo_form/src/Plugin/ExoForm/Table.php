<?php

namespace Drupal\exo_form\Plugin\ExoForm;

use Drupal\exo_form\Plugin\ExoFormBase;

/**
 * Provides a plugin for table type(s).
 *
 * @ExoForm(
 *   id = "table",
 *   label = @Translation("Table"),
 *   element_types = {
 *     "table",
 *   }
 * )
 */
class Table extends ExoFormBase {

  /**
   * {@inheritdoc}
   */
  public function preRender($element) {
    // Ignore tables with drag.
    if (isset($element['#tabledrag'])) {
      $element['#exo_form_no_wrap'] = TRUE;
    }
    if (empty($element['#exo_form_attributes']['data-exo-theme'])) {
      $theme = !empty($element['#exo_theme']) ? $element['#exo_theme'] : NULL;
      $classes = exo_form_classes($theme, empty($element['#exo_form_no_wrap']));
      if (!isset($element['#exo_form_attributes']['class'])) {
        $element['#exo_form_attributes']['class'] = [];
      }
      $element['#exo_form_attributes']['class'] = array_merge($element['#exo_form_attributes']['class'], $classes);
      $element['#attached']['library'][] = 'exo_form/base';
    }
    return parent::preRender($element);
  }

  /**
   * {@inheritdoc}
   */
  public function process(&$element) {
    $element['#exo_form_no_wrap'] = TRUE;
  }

}
