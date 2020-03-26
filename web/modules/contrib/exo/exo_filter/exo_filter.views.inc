<?php

/**
 * @file
 * Views integration for eXo Filter.
 */

use Drupal\field\FieldStorageConfigInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Entity\Sql\SqlEntityStorageInterface;

/**
 * Implements hook_views_data().
 */
function exo_filter_views_data() {

  $data['views']['exo_filter_summary'] = [
    'title' => t('Filter Summary'),
    'help' => t('Displays summary of active exposed filters.'),
    'area' => [
      'id' => 'exo_filter_summary',
    ],
  ];

  return $data;
}

/**
 * Implements hook_views_data_alter().
 */
function exo_filter_views_data_alter(array &$data) {
  $manager = \Drupal::entityTypeManager();
  $field_config_storage = $manager->getStorage('field_config');
  foreach ($manager->getDefinitions() as $entity_type) {
    if (!$entity_type->isSubclassOf(FieldableEntityInterface::class)) {
      continue;
    }
    // We cannot alter Views data if we cannot map fields to tables.
    $entity_storage = $manager->getStorage($entity_type->id());
    if (!($entity_storage instanceof SqlEntityStorageInterface)) {
      continue;
    }
    /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $base_field_definitions */
    $base_field_definitions = \Drupal::service('entity_field.manager')
      ->getBaseFieldDefinitions($entity_type->id());
    $table_mapping = $entity_storage->getTableMapping();
    // Loop through all of this entity type's stored fields.
    foreach ($table_mapping->getTableNames() as $table_name) {
      foreach ($table_mapping->getFieldNames($table_name) as $field_name) {
        // Skip fields that are not base fields.
        if (!isset($base_field_definitions[$field_name])) {
          continue;
        }
        // Only add to entity_reference fields.
        $base_field_storage_definition = $base_field_definitions[$field_name]->getFieldStorageDefinition();
        if ($base_field_storage_definition->getType() !== 'entity_reference') {
          continue;
        }
        $settings = $base_field_storage_definition->getSettings();
        $column_name = $table_mapping->getFieldColumnName($base_field_storage_definition, $field_name);
        $data[$table_name][$column_name . '_exo_filter'] = exo_filter_get_filter(
          $base_field_storage_definition->getLabel(),
          $column_name,
          $table_name,
          $field_name,
          $entity_type->id(),
          $settings['target_type'],
          $entity_type->getLabel()
        );
      }
    }
  }
}

/**
 * Implements hook_field_views_data_alter().
 *
 * @see views_field_default_views_data()
 */
function exo_filter_field_views_data_alter(array &$data, FieldStorageConfigInterface $field_storage) {
  if ($field_storage->getType() == 'entity_reference' && $field_storage->getSetting('target_type') != 'taxonomy_term') {
    // Adds a new filter type to entity reference fields that provides an entity
    // select or autocomplete field. Hopefully this will be provided by Drupal
    // core soon.
    $entity_type_manager = \Drupal::entityTypeManager();
    foreach ($data as $table_name => $table_data) {
      $entity_type_id = $field_storage->getTargetEntityTypeId();
      $entity_type = $entity_type_manager->getDefinition($entity_type_id);
      $field_name = $field_storage->getName();
      $args = [
        '@label' => $entity_type->getLabel(),
        '@field_name' => $field_name,
      ];

      $field_target_name = $field_name . '_target_id';
      if (isset($table_data[$field_target_name]['filter'])) {
        $filter = $table_data[$field_target_name]['filter'];
        $pseudo_field_name = 'exo_filter__' . $entity_type_id . '__' . $field_name;

        $filter['title'] = t('@field_name (eXo Filter)', $args);
        $filter['id'] = 'exo_filter_entity_reference';
        $filter['group'] = $entity_type->getLabel();
        $data[$table_name][$pseudo_field_name]['filter'] = $filter;
      }
    }
  }
  if ($field_storage->getType() == 'daterange') {
    $entity_type_manager = \Drupal::entityTypeManager();
    foreach ($data as $table_name => $table_data) {
      $entity_type_id = $field_storage->getTargetEntityTypeId();
      $entity_type = $entity_type_manager->getDefinition($entity_type_id);
      $field_name = $field_storage->getName();
      foreach (['value', 'end_value'] as $column_name) {
        if (isset($table_data[$field_name . '_' . $column_name]['filter'])) {
          $args = [
            '@label' => $entity_type->getLabel(),
            '@field_name' => $field_name . ':' . $column_name,
          ];
          $filter = $table_data[$field_name . '_' . $column_name]['filter'];
          $pseudo_field_name = 'exo_filter__' . $entity_type_id . '__' . $field_name . '__' . $column_name;

          $filter['title'] = t('eXo Filter (@field_name)', $args);
          $filter['id'] = 'exo_filter_date';
          $filter['group'] = $entity_type->getLabel();
          $data[$table_name][$pseudo_field_name]['filter'] = $filter;
        }
      }
    }
  }
}

/**
 * Gets an array for views.
 */
function exo_filter_get_filter($label, $column_name, $table_name, $field_name, $entity_type_id, $target_entity_type_id, $group = 'eXo Filter', $help = '') {
  return [
    'title' => t('@label (eXo Filter)', ['@label' => $label]),
    'group' => $group,
    'help' => $help,
    'filter' => [
      'field' => $column_name,
      'table' => $table_name,
      'id' => 'exo_filter_entity_reference',
      'additional fields' => [],
      'field_name' => $field_name,
      'entity_type' => $entity_type_id,
      'exo_filter_target_entity_type_id' => $target_entity_type_id,
      'allow empty' => TRUE,
    ],
  ];
}
