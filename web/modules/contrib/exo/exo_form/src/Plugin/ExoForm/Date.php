<?php

namespace Drupal\exo_form\Plugin\ExoForm;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "date",
 *   label = @Translation("Date"),
 *   element_types = {
 *     "date",
 *   },
 * )
 */
class Date extends Input {

  /**
   * {@inheritdoc}
   */
  public function process(&$element) {
    parent::process($element);
    $this->disableFloatSupport();
    $this->disableIntersectSupport();
    // The property #checkboxes_child will be true if this date is part
    // of a datetime element.
    // @see Drupal\exo_form\Plugin\ExoForm\DateTime
    if (!empty($element['#datetime_child'])) {
      // Do not wrap this element in exo_form_element_container because it is
      // part of a checkbox collection and the collection will be wrapped.
      $this->disableWrapper();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function preRender($element) {
    $element = parent::preRender($element);
    // The last key of parents contains the type... either date or time.
    $type = array_values(array_slice($element['#parents'], -1))[0];
    switch ($type) {
      case 'date':
        $element['#wrapper_attributes']['class'][] = 'exo-form-date';
        break;

      case 'time':
        $element['#wrapper_attributes']['class'][] = 'exo-form-time';
        break;
    }
    return $element;
  }

}
