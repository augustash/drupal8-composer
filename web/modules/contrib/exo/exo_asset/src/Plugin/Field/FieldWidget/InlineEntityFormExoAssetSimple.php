<?php

namespace Drupal\exo_asset\Plugin\Field\FieldWidget;

use Drupal\inline_entity_form\Plugin\Field\FieldWidget\InlineEntityFormSimple;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Complex inline widget.
 *
 * @FieldWidget(
 *   id = "ief_exo_asset_simple",
 *   label = @Translation("eXo Asset - Simple"),
 *   field_types = {
 *     "entity_reference"
 *   },
 *   multiple_values = false
 * )
 */
class InlineEntityFormExoAssetSimple extends InlineEntityFormSimple {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $parents = array_merge($element['#field_parents'], [$items->getName()]);
    $ief_id = sha1(implode('-', $parents));
    $form_state->set(['inline_entity_form', $ief_id, 'instance'], $this->fieldDefinition);
    $element['inline_entity_form']['#ief_id'] = $ief_id;
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    return parent::isApplicable($field_definition) && $field_definition->getSetting('target_type') === 'exo_asset';
  }

}
