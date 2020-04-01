<?php

namespace Drupal\exo\Element;

use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form input element for selecting one or multiple entities.
 *
 * @FormElement("exo_entity_autocomplete")
 */
class ExoEntityAutocomplete extends EntityAutocomplete {

  /**
   * {@inheritdoc}
   */
  public static function processEntityAutocomplete(array &$element, FormStateInterface $form_state, array &$complete_form) {
    $element = parent::processEntityAutocomplete($element, $form_state, $complete_form);

    if ($element['#required']) {
      $options = $element['#selection_settings'] + [
        'target_type' => $element['#target_type'],
        'handler' => $element['#selection_handler'],
      ];
      /** @var /Drupal\Core\Entity\EntityReferenceSelection\SelectionInterface $handler */
      $handler = \Drupal::service('plugin.manager.entity_reference_selection')->getInstance($options);
      $entity_count = $handler->countReferenceableEntities();
      // No need to show anything, there's only one possible value.
      if ($entity_count == 1) {
        $entity_options = $handler->getReferenceableEntities();
        $entity_options = reset($entity_options);
        $element['#value'] = reset($entity_options) . ' (' . key($entity_options) . ')';
        $element['#access'] = FALSE;
      }
    }

    return $element;
  }

}
