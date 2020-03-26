<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\exo_alchemist\Definition\ExoComponentDefinitionField;
use Drupal\exo_alchemist\Definition\ExoComponentDefinitionFieldPreview;
use Drupal\media\MediaInterface;

/**
 * Base component for entity entity reference fields.
 */
class MediaBase extends EntityReferenceBase {

  /**
   * {@inheritdoc}
   */
  protected $entityType = 'media';

  /**
   * {@inheritdoc}
   */
  public function componentWidget(ExoComponentDefinitionField $field) {
    return [
      'type' => 'media_library_widget',
    ];
  }

  /**
   * {@inheritdoc}
   *
   * Any associated media is deleted on each rebuild. Extending class is
   * responsible for media creation.
   */
  protected function componentValueClean(ExoComponentDefinitionField $field, FieldItemInterface $item, $update = TRUE) {
    if ($item->entity) {
      $entity = $item->entity;
      $entity_type = $entity->bundle->entity;
      $entity_field_name = $entity_type->getSource()->getSourceFieldDefinition($entity_type)->getName();
      if ($entity->hasField($entity_field_name) && !$entity->get($entity_field_name)->isEmpty()) {
        $entity_field = $entity->get($entity_field_name);
        if ($entity_field instanceof EntityReferenceFieldItemListInterface) {
          $entity_child = $entity_field->entity;
          if ($entity_child) {
            $entity_child->delete();
          }
        }
      }
      if (!$update) {
        // Delete the media entity when uninstalling.
        $entity->delete();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function componentEntity(ExoComponentDefinitionFieldPreview $preview, FieldItemInterface $item = NULL) {
    if ($item && $item->entity && $item->entity instanceof MediaInterface) {
      $media = $item->entity;
      if ($media->bundle() !== $preview->getValue('bundle')) {
        // We have a media item that has changed types. We want to perserve its
        // id and uuid but switch it to the new bundle. To do this we delete the
        // current one and create a new one with the same id and uuid.
        $id = $media->id();
        $uuid = $media->uuid();
        $media->delete();
        $media = \Drupal::entityTypeManager()->getStorage('media')->create([
          'id' => $id,
          'uuid' => $uuid,
          'bundle' => $preview->getValue('bundle'),
          'uid' => \Drupal::currentUser()->id(),
        ]);
        $media->enforceIsNew(FALSE);
      }
    }
    else {
      $media = \Drupal::entityTypeManager()->getStorage('media')->create([
        'bundle' => $preview->getValue('bundle'),
        'uid' => \Drupal::currentUser()->id(),
      ]);
    }
    $media_type = \Drupal::entityTypeManager()->getStorage('media_type')->load($preview->getValue('bundle'));
    $media_field_name = $media_type->getSource()->getSourceFieldDefinition($media_type)->getName();
    $field = $preview->getField();
    $media->get($media_field_name)->setValue($this->componentMediaValue($preview, $item));
    $media->setName($field->getComponent()->getLabel() . ': ' . $field->getLabel());
    $media->setPublished(TRUE);
    $media->save();
    return $media;
  }

  /**
   * Extending classes can use this method to set individual values.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionFieldPreview $preview
   *   The field preview.
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   The current item.
   *
   * @return mixed
   *   A value suitable for setting to \Drupal\Core\Field\FieldItemInterface.
   */
  protected function componentMediaValue(ExoComponentDefinitionFieldPreview $preview, FieldItemInterface $item = NULL) {
    return NULL;
  }

}
