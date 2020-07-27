<?php

namespace Drupal\exo_modal\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'metatag' field type.
 *
 * @FieldType(
 *   id = "exo_modal_meta",
 *   label = @Translation("eXo Modal: Meta"),
 *   description = @Translation("Stores modal information that can be used to create a modal."),
 *   default_widget = "exo_modal_meta",
 *   no_ui = TRUE,
 * )
 */
class ExoModalMetaItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field) {
    return [
      'columns' => [
        'trigger_text' => [
          'type' => 'varchar',
          'length' => 256,
          'not null' => FALSE,
        ],
        'trigger_icon' => [
          'type' => 'varchar',
          'length' => 256,
        ],
        'modal_title' => [
          'type' => 'varchar',
          'length' => 256,
        ],
        'modal_subtitle' => [
          'type' => 'varchar',
          'length' => 256,
        ],
        'modal_icon' => [
          'type' => 'varchar',
          'length' => 256,
        ],
        'settings' => [
          'type' => 'blob',
          'size' => 'big',
          'serialize' => TRUE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['trigger_text'] = DataDefinition::create('string')
      ->setLabel(t('Trigger text'))
      ->setRequired(TRUE);

    $properties['trigger_icon'] = DataDefinition::create('string')
      ->setLabel(t('Trigger icon'));

    $properties['modal_title'] = DataDefinition::create('string')
      ->setLabel(t('Modal title'));

    $properties['modal_subtitle'] = DataDefinition::create('string')
      ->setLabel(t('Modal subtitle'));

    $properties['modal_icon'] = DataDefinition::create('string')
      ->setLabel(t('Modal icon'));

    $properties['settings'] = DataDefinition::create('map')
      ->setLabel(t('Modal Settings'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('trigger_text')->getValue();
    return $value === NULL || $value === '';
  }

}
