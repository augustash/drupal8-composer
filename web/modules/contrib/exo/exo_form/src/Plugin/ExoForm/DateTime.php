<?php

namespace Drupal\exo_form\Plugin\ExoForm;

use Drupal\Core\Render\Element;
use Drupal\exo_form\Plugin\ExoFormBase;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "datetime",
 *   label = @Translation("Date"),
 *   element_types = {
 *     "datetime",
 *     "exo_datetime",
 *   }
 * )
 */
class DateTime extends ExoFormBase {

  /**
   * {@inheritdoc}
   */
  protected $intersectSupported = TRUE;

  /**
   * {@inheritdoc}
   */
  public function process(&$element) {
    parent::process($element);
    foreach (Element::children($element) as $key) {
      $child_element = &$element[$key];
      if (isset($child_element['#type']) && $child_element['#type'] == 'date') {
        $child_element['#datetime_child'] = TRUE;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function preRender($element) {
    $element = parent::preRender($element);
    $element['#wrapper_attributes']['class'][] = 'exo-form-datetime';
    $element['#attributes']['class'][] = 'exo-form-inline';
    $element['#theme_wrappers'] = ['form_element'];
    $element['date']['#attributes']['placeholder'] = t('Date');
    $element['time']['#attributes']['placeholder'] = t('Time');
    return $element;
  }

}
