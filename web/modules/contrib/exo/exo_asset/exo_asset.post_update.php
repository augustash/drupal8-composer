<?php

/**
 * @file
 * Post-update functions for the exo_asset module.
 */

use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Add mobile image field to base entity.
 */
function exo_asset_post_update_add_mobile_image_field(&$sandbox) {
  $definition_update_manager = \Drupal::entityDefinitionUpdateManager();
  /** @var \Drupal\Core\Entity\EntityLastInstalledSchemaRepositoryInterface $last_installed_schema_repository */
  $last_installed_schema_repository = \Drupal::service('entity.last_installed_schema.repository');
  $has_media_library = \Drupal::service('module_handler')->moduleExists('media_library');

  $entity_type = $definition_update_manager->getEntityType('exo_asset');
  $field_storage_definitions = $last_installed_schema_repository->getLastInstalledFieldStorageDefinitions('exo_asset');

  $field_storage_definitions['image_mobile'] = BaseFieldDefinition::create('entity_reference')
    ->setName('image_mobile')
    ->setLabel(t('Mobile Image'))
    ->setTargetEntityTypeId('exo_asset')
    ->setTargetBundle(NULL)
    ->setRevisionable(TRUE)
    ->setTranslatable(TRUE)
    ->setSetting('target_type', 'media')
    ->setSetting('handler', 'default')
    ->setSetting('handler_settings', ['target_bundles' => ['image' => 'image']])
    ->setDisplayOptions('form', [
      'type' => 'entity_browser_entity_reference',
      'weight' => -3,
      'settings' => [
        'entity_browser' => 'exo_media_image',
        'field_widget_display' => 'rendered_entity',
        'open' => TRUE,
        'field_widget_edit' => FALSE,
        'field_widget_remove' => TRUE,
        'field_widget_replace' => FALSE,
        'field_widget_display_settings' => [
          'view_mode' => 'preview',
        ],
      ],
    ])
    ->setDisplayOptions('view', [
      'type' => 'entity_reference_entity_view',
      'label' => 'hidden',
      'settings' => [
        'view_mode' => 'full',
      ],
      'weight' => 0,
    ])
    ->setDisplayConfigurable('form', TRUE)
    ->setDisplayConfigurable('view', TRUE);

  $definition_update_manager->updateFieldableEntityType($entity_type, $field_storage_definitions, $sandbox);
}
