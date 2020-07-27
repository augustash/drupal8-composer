<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldImageStylesTrait;
use Drupal\file\FileInterface;
use Drupal\media\MediaInterface;

/**
 * A 'media' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "media_image",
 *   label = @Translation("Media: Image"),
 *   properties = {
 *     "url" = @Translation("The absolute url of the image."),
 *     "title" = @Translation("The title of the image."),
 *   },
 *   provider = "media",
 * )
 */
class MediaImage extends MediaFileBase {

  use ExoComponentFieldImageStylesTrait;

  /**
   * Get the entity type.
   */
  protected function getEntityTypeBundles() {
    return ['image' => 'image'];
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition() {
    parent::processDefinition();
    $field = $this->getFieldDefinition();
    $this->processDefinitionImageStyles($field);
  }

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    $properties = parent::propertyInfo();
    $field = $this->getFieldDefinition();
    foreach ($this->propertyInfoImageStyles($field) as $key => $property) {
      $properties['style.' . $key] = $property;
    }
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function onInstall(ConfigEntityInterface $entity) {
    parent::onInstall($entity);
    $field = $this->getFieldDefinition();
    $this->buildImageStyles($field);
  }

  /**
   * {@inheritdoc}
   */
  public function onUpdate(ConfigEntityInterface $entity) {
    parent::onUpdate($entity);
    $field = $this->getFieldDefinition();
    $this->buildImageStyles($field);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultValue($delta = 0) {
    return [
      'path' => drupal_get_path('module', 'exo_alchemist') . '/images/default.png',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function viewFileValue(MediaInterface $media, FileInterface $file) {
    $field = $this->getFieldDefinition();
    return [
      'style' => $this->getImageStylesAsUrl($field, $file),
    ] + parent::viewFileValue($media, $file);
  }

}
