<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\exo_alchemist\ExoComponentValue;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldFileTrait;
use Drupal\file\FileInterface;
use Drupal\media\MediaInterface;

/**
 * Base component for media that uses files.
 */
abstract class MediaFileBase extends MediaBase {

  use ExoComponentFieldFileTrait;

  /**
   * {@inheritdoc}
   */
  public function validateValue(ExoComponentValue $value) {
    if ($base_value = $value->get('value')) {
      // When base value is true, we want to set default.
      if ($base_value === TRUE) {
        foreach ($this->getDefaultValue($value->getDelta()) as $key => $val) {
          $value->set($key, $val);
        }
      }
      else {
        $value->set('path', $value->get('value'));
      }
      $value->unset('value');
    }
    $this->validateValueFile($value, TRUE);
    parent::validateValue($value);
  }

  /**
   * {@inheritdoc}
   */
  protected function getMediaKey(ExoComponentValue $value) {
    $key = parent::getMediaKey($value);
    if (empty($key)) {
      // Use the path as the default key.
      $key = $value->get('path');
    }
    return $key;
  }

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    $properties = parent::propertyInfo();
    $properties['url'] = $this->t('The file url.');
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function viewValue(FieldItemInterface $item, $delta, array $contexts) {
    /** @var \Drupal\media\MediaInterface $media */
    $media = $item->entity;
    if ($media) {
      $source_field_definition = $media->getSource()->getSourceFieldDefinition($media->bundle->entity);
      $file = $media->{$source_field_definition->getName()}->entity;
      if ($file) {
        return $this->viewFileValue($media, $file);
      }
    }
  }

  /**
   * Extending classes can use to act on the file.
   *
   * @param \Drupal\media\MediaInterface $media
   *   The media entity.
   * @param \Drupal\file\FileInterface $file
   *   The file entity.
   *
   * @return array
   *   A value that will be sent to twig.
   */
  protected function viewFileValue(MediaInterface $media, FileInterface $file) {
    return [
      'url' => $file->url(),
      'label' => $media->label(),
    ];
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
    $values = [];
    if ($file = $this->componentFile($value)) {
      $values[] = [
        'target_id' => $file->id(),
        'title' => $this->getMediaName($value),
        'alt' => $this->getMediaName($value),
      ];
    }
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  protected function getFileDirectory(ExoComponentValue $value) {
    return 'public://media/' . str_replace('_', '-', $value->get('bundle'));
  }

}
