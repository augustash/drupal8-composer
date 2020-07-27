<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\exo_alchemist\ExoComponentValue;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldDefaultStorageLockInterface;
use Drupal\media\MediaInterface;

/**
 * Base component for entity entity reference fields.
 */
abstract class MediaBase extends EntityReferenceBase implements ExoComponentFieldDefaultStorageLockInterface {

  /**
   * {@inheritdoc}
   */
  protected $entityType = 'media';

  /**
   * {@inheritdoc}
   */
  public function getWidgetConfig() {
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
  protected function cleanValue(FieldItemInterface $item, $delta, $update = TRUE) {
    parent::cleanValue($item, $delta, $update);
    if ($item->entity) {
      $entity = $item->entity;
      $this->componentMediaSourceFieldClean($entity);
      if (!$update) {
        // Delete the media entity when uninstalling.
        $entity->delete();
      }
    }
  }

  /**
   * Clean up source field entity if necessary.
   *
   * @param \Drupal\media\MediaInterface $entity
   *   The media entity.
   */
  protected function componentMediaSourceFieldClean(MediaInterface $entity) {
    $entity_type = $entity->bundle->entity;
    $entity_field_name = $entity_type->getSource()->getSourceFieldDefinition($entity_type)->getName();
    if ($entity->hasField($entity_field_name) && !$entity->get($entity_field_name)->isEmpty()) {
      $entity_field = $entity->get($entity_field_name);
      if ($entity_field instanceof EntityReferenceFieldItemListInterface) {
        $entity_child = $entity_field->entity;
        if ($entity_child) {
          // Reload child entity to make sure it exists as it may be stored in
          // the definition.
          $entity_child = $this->entityTypeManager->getStorage($entity_child->getEntityTypeId())->load($entity_child->id());
          if ($entity_child) {
            $entity_child->delete();
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getValueEntity(ExoComponentValue $value, FieldItemInterface $item = NULL) {
    $media = NULL;
    $key = md5($this->getMediaKey($value));
    if ($item && $item->entity && $item->entity instanceof MediaInterface) {
      $media = $item->entity;
      if ($media->bundle() !== $value->get('bundle')) {
        // We have a media item that has changed types. We want to perserve its
        // id and uuid but switch it to the new bundle. To do this we delete the
        // current one and create a new one with the same id and uuid.
        $id = $media->id();
        $uuid = $media->uuid();
        $media->delete();
        $media = \Drupal::entityTypeManager()->getStorage('media')->create([
          'id' => $id,
          'uuid' => $uuid,
          'bundle' => $value->get('bundle'),
          'uid' => \Drupal::currentUser()->id(),
        ]);
        $media->enforceIsNew(FALSE);
      }
    }
    // Support lookup by key.
    if (empty($media) && $key) {
      $medias = \Drupal::entityTypeManager()->getStorage('media')->loadByProperties([
        'alchemist_key' => $key,
      ]);
      if (!empty($medias)) {
        $media = reset($medias);
      }
    }
    // Create new media entity if none found.
    if (empty($media)) {
      $media = \Drupal::entityTypeManager()->getStorage('media')->create([
        'bundle' => $value->get('bundle'),
        'uid' => \Drupal::currentUser()->id(),
      ]);
    }
    // Clean current source field as it will be repopulated again.
    $this->componentMediaSourceFieldClean($media);
    /** @var \Drupal\media\MediaTypeInterface $media_type */
    $media_type = \Drupal::entityTypeManager()->getStorage('media_type')->load($value->get('bundle'));
    $media->setName($this->getMediaName($value));
    $media_field_name = $media_type->getSource()->getSourceFieldDefinition($media_type)->getName();
    $media->get($media_field_name)->setValue($this->setMediaValue($value, $item));
    $media->get('alchemist_key')->setValue($key);
    $media->setPublished(TRUE);
    $media->save();
    return $media;
  }

  /**
   * Get the media key.
   *
   * @param \Drupal\exo_alchemist\ExoComponentValue $value
   *   The component value.
   */
  protected function getMediaKey(ExoComponentValue $value) {
    return $value->get('key');
  }

  /**
   * Get the media name.
   *
   * @param \Drupal\exo_alchemist\ExoComponentValue $value
   *   The component value.
   */
  protected function getMediaName(ExoComponentValue $value) {
    $name = $value->get('name');
    if (empty($name)) {
      $field = $value->getDefinition();
      $name = $field->getComponent()->getLabel() . ': ' . $field->getLabel();
    }
    return $name;
  }

  /**
   * {@inheritdoc}
   */
  protected function onCloneValue(FieldItemInterface $item, $all) {
    if ($all) {
      return [];
    }
    else {
      return parent::onCloneValue($item, $all);
    }
  }

  /**
   * Extending classes can use this method to set individual values.
   *
   * @param \Drupal\exo_alchemist\ExoComponentValue $value
   *   The field value.
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   The current item.
   *
   * @return mixed
   *   A value suitable for setting to \Drupal\Core\Field\FieldItemInterface.
   */
  protected function setMediaValue(ExoComponentValue $value, FieldItemInterface $item = NULL) {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultStorageLockMessage() {
    return $this->t('Media fields cannot be changed from the display interface.');
  }

}
