<?php

namespace Drupal\exo_filter\Plugin\ExoFilter;

use Drupal\exo_filter\Plugin\ExoFilterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'datetime' formatter.
 *
 * @ExoFilter(
 *   id = "exo_datetime",
 *   label = @Translation("eXo Datetime"),
 *   field_types = {
 *     "datetime",
 *   }
 * )
 */
class ExoDatetime extends ExoFilterBase {

  /**
   * {@inheritdoc}
   */
  public function exposedElementAlter(&$element, FormStateInterface $form_state, $context) {
    $element['#type'] = 'exo_datetime';
  }

}
