<?php

namespace Drupal\exo\Element;

use Drupal\Core\Render\Element\Radios;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;

/**
 * Provides a form element for a set of radio buttons that can be render arrays.
 *
 * {@inheritdoc}
 *
 * @FormElement("exo_radios")
 */
class ExoRadios extends Radios {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return parent::getInfo() + [
       // inline, grid, grid-compact, stacked.
      '#exo_style' => 'stacked',
    ];
  }

  /**
   * Expands a radios element into individual radio elements.
   */
  public static function processRadios(&$element, FormStateInterface $form_state, &$complete_form) {
    $renderer = \Drupal::service('renderer');
    $element += [
      '#options' => [],
    ];
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
    $element['#attributes']['class'][] = 'exo-' . $element['#exo_style'];
    $element['#attached']['library'][] = 'exo/element.options';
    return parent::processRadios($element, $form_state, $complete_form);
  }

  /**
   * {@inheritdoc}
   *
   * If exo_clean is passed in this field will not be wrapped in a fieldset.
   */
  public static function preRenderCompositeFormElement($element) {
    if (empty($element['#exo_clean'])) {
      $element = parent::preRenderCompositeFormElement($element);
    }
    return $element;
  }

}
