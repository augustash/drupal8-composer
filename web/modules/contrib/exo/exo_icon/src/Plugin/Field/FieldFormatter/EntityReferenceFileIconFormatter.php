<?php

namespace Drupal\exo_icon\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\TypedData\TranslatableInterface;

/**
 * Plugin implementation of the 'exo image' formatter.
 *
 * @FieldFormatter(
 *   id = "entity_reference_file_icon",
 *   label = @Translation("eXo File Icon"),
 *   provider = "exo_media",
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class EntityReferenceFileIconFormatter extends FileIconFormatter {

  /**
   * {@inheritdoc}
   *
   * This has to be overriden because FileFormatterBase expects $item to be
   * of type \Drupal\file\Plugin\Field\FieldType\FileItem and calls
   * isDisplayed() which is not in FieldItemInterface.
   */
  protected function needsEntityLoad(EntityReferenceItem $item) {
    return !$item->hasNewEntity();
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntitiesToView(EntityReferenceFieldItemListInterface $items, $langcode) {
    $entities = [];

    foreach ($items as $delta => $item) {
      // Ignore items where no entity could be loaded in prepareView().
      if (!empty($item->_loaded)) {
        $media_entity = $item->entity;
        $source_field = $media_entity->getSource()->getConfiguration()['source_field'];
        $entity = $media_entity->{$source_field}->entity;

        // Set the entity in the correct language for display.
        if ($entity instanceof TranslatableInterface) {
          $entity = \Drupal::service('entity.repository')->getTranslationFromContext($entity, $langcode);
        }

        $access = $this->checkAccess($entity);
        // Add the access result's cacheability, ::view() needs it.
        $item->_accessCacheability = CacheableMetadata::createFromObject($access);
        if ($access->isAllowed()) {
          // Add the referring item, in case the formatter needs it.
          $entity->_referringItem = $media_entity->{$source_field}->get(0);
          $entities[$delta] = $entity;
        }
      }
    }

    return $entities;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    $target_type = $field_definition->getFieldStorageDefinition()->getSetting('target_type');
    if ($target_type !== 'media') {
      return FALSE;
    }

    $storage = \Drupal::service('entity_type.manager')->getStorage('media_type');
    $settings = $field_definition->getSetting('handler_settings');
    if (isset($settings['target_bundles'])) {
      foreach ($settings['target_bundles'] as $bundle) {
        if ($storage->load($bundle)->getSource()->getPluginId() !== 'file') {
          return FALSE;
        }
      }
    }
    return parent::isApplicable($field_definition);
  }

}
