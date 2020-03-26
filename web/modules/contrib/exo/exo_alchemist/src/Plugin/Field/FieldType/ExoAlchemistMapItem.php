<?php

namespace Drupal\exo_alchemist\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\MapItem;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Defines the 'map' entity field type.
 *
 * @FieldType(
 *   id = "exo_alchemist_map",
 *   label = @Translation("eXo Alchemist Map"),
 *   description = @Translation("An entity field for storing a serialized array of values."),
 *   no_ui = TRUE,
 *   list_class = "\Drupal\Core\Field\MapFieldItemList",
 * )
 */
class ExoAlchemistMapItem extends MapItem {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    // Reverse patch that results in map fields not being installable by Drupal.
    // @see https://www.drupal.org/project/drupal/issues/2743175
    // @see https://www.drupal.org/project/drupal/issues/2229181
    $properties['value'] = DataDefinition::create('string')->setLabel(t('Serialized values'));
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($values, $notify = TRUE) {
    parent::setValue($values, $notify);

    // Update any existing property objects.
    foreach ($this->properties as $name => $property) {
      $value = isset($values[$name]) ? $values[$name] : NULL;
      $property->setValue($value, FALSE);
      // Remove the value from $this->values to ensure it does not contain any
      // value for computed properties.
      unset($this->values[$name]);
    }
  }

}
