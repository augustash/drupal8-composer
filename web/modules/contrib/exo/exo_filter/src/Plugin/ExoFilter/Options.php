<?php

namespace Drupal\exo_filter\Plugin\ExoFilter;

use Drupal\exo_filter\Plugin\ExoFilterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'boolean' formatter.
 *
 * @ExoFilter(
 *   id = "options",
 *   label = @Translation("Checkboxes/Radios"),
 *   field_types = {
 *     "list_field",
 *     "taxonomy_index_tid",
 *   }
 * )
 */
class Options extends ExoFilterBase {

  /**
   * {@inheritdoc}
   */
  public function exposedElementAlter(&$element, FormStateInterface $form_state, $context) {
    $element_id = $context['id'];
    $user_input = $form_state->getUserInput();
    if (empty($element['#multiple'])) {
      $element['#type'] = 'radios';
    }
    else {
      $element['#type'] = 'checkboxes';
      $user_input[$element_id] = array_filter(array_combine(array_values($user_input[$element_id]), array_values($user_input[$element_id])));
      $element['#default_value'] = $user_input[$element_id];
      $form_state->setUserInput($user_input);
    }
    unset($element['#options']['All']);
    if ($user_input[$element_id] == 'All') {
      $user_input[$element_id] = '';
      $form_state->setUserInput($user_input);
    }
  }

}
