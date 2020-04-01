<?php

namespace Drupal\exo\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteWidget;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'exo_entity_reference_autocomplete' widget.
 *
 * @FieldWidget(
 *   id = "exo_entity_reference_autocomplete",
 *   label = @Translation("Autocomplete (hide if only 1 option)"),
 *   description = @Translation("An autocomplete text field that will hide when only 1 option is selectable."),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class ExoEntityReferenceAutocompleteWidget extends EntityReferenceAutocompleteWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $element['target_id']['#type'] = 'exo_entity_autocomplete';
    return $element;
  }

}
