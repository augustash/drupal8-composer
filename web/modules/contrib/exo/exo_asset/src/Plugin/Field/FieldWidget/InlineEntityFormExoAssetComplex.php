<?php

namespace Drupal\exo_asset\Plugin\Field\FieldWidget;

use Drupal\inline_entity_form\Plugin\Field\FieldWidget\InlineEntityFormComplex;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Complex inline widget.
 *
 * @FieldWidget(
 *   id = "ief_exo_asset_complex",
 *   label = @Translation("eXo Asset - Complex"),
 *   field_types = {
 *     "entity_reference",
 *     "entity_reference_revisions",
 *   },
 *   multiple_values = true
 * )
 */
class InlineEntityFormExoAssetComplex extends InlineEntityFormComplex {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $element['entities']['#theme'] = 'inline_entity_form_entity_table__exo_asset';
    $element['#attached']['library'][] = 'exo_asset/inline_entity_form';
    $element['#attributes']['class'][] = 'exo-asset-inline-entity-form';
    return $element;
  }

  /**
   * Adds actions to the inline entity form.
   *
   * @param array $element
   *   Form array structure.
   */
  public static function buildEntityFormActions($element) {
    $element = parent::buildEntityFormActions($element);
    $element['actions']['#type'] = 'actions';
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    return parent::isApplicable($field_definition) && $field_definition->getSetting('target_type') === 'exo_asset';
  }

}
