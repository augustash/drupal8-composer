<?php

namespace Drupal\exo\Element;

use Drupal\Core\Render\Element\Checkboxes;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;

/**
 * Provides a form element for a set of checkboxs that can be render arrays.
 *
 * {@inheritdoc}
 *
 * @FormElement("exo_checkboxes")
 */
class ExoCheckboxes extends Checkboxes {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return parent::getInfo() + [
      '#exo_style' => NULL,
    ];
  }

  /**
   * Expands a checkboxes element into individual checkbox elements.
   */
  public static function processCheckboxes(&$element, FormStateInterface $form_state, &$complete_form) {
    $renderer = \Drupal::service('renderer');
    if (count($element['#options']) > 0) {
      foreach ($element['#options'] as $key => &$choice) {
        if (is_array($choice)) {
          $choice = $renderer->render($choice);
        }
        else {
          $choice = Markup::create($choice);
        }
      }
    }
    $element['#theme_wrappers'][] = 'container';
    $element['#attributes']['class'][] = 'exo-element-options';
    if ($element['#exo_style'] == 'inline') {
      $element['#attributes']['class'][] = 'exo-inline';
    }
    elseif ($element['#exo_style'] == 'grid') {
      $element['#attributes']['class'][] = 'exo-grid';
    }
    else {
      $element['#attributes']['class'][] = 'exo-stacked';
    }
    $element['#attached']['library'][] = 'exo/element.options';
    return parent::processCheckboxes($element, $form_state, $complete_form);
  }

}
