<?php

namespace Drupal\exo_filter\Plugin\ExoFilter;

use Drupal\exo_filter\Plugin\ExoFilterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'datetime' formatter.
 *
 * @ExoFilter(
 *   id = "html5_datetime",
 *   label = @Translation("HTML5 Datetime"),
 *   field_types = {
 *     "datetime",
 *   }
 * )
 */
class Html5Datetime extends ExoFilterBase {

  /**
   * {@inheritdoc}
   */
  public function exposedElementAlter(&$element, FormStateInterface $form_state, $context) {
    $element['#type'] = 'date';
  }

}
