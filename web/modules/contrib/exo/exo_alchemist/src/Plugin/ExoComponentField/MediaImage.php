<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\exo_alchemist\Definition\ExoComponentDefinitionField;
use Drupal\exo_alchemist\Definition\ExoComponentDefinitionFieldPreview;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldFileTrait;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldImageStylesTrait;

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
class MediaImage extends MediaBase {

  use ExoComponentFieldFileTrait;
  use ExoComponentFieldImageStylesTrait;

  /**
   * Get the entity type.
   */
  protected function getEntityTypeBundles(ExoComponentDefinitionField $field) {
    return ['image' => 'image'];
  }

  /**
   * {@inheritdoc}
   */
  public function componentProcessDefinition(ExoComponentDefinitionField $field) {
    parent::componentProcessDefinition($field);
    $this->componentProcessDefinitionFile($field, TRUE);
    $this->componentProcessDefinitionImageStyles($field);
  }

  /**
   * {@inheritdoc}
   */
  public function componentPropertyInfo(ExoComponentDefinitionField $field) {
    $properties = parent::componentPropertyInfo($field);
    foreach ($this->componentPropertyInfoImageStyles($field) as $key => $property) {
      $properties['style.' . $key] = $property;
    }
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function componentInstallEntityType(ExoComponentDefinitionField $field, ConfigEntityInterface $entity) {
    parent::componentUpdateEntityType($field, $entity);
    $this->componentBuildImageStyles($field, $entity);
  }

  /**
   * {@inheritdoc}
   */
  public function componentUpdateEntityType(ExoComponentDefinitionField $field, ConfigEntityInterface $entity) {
    parent::componentUpdateEntityType($field, $entity);
    $this->componentBuildImageStyles($field, $entity);
  }

  /**
   * {@inheritdoc}
   */
  public function componentViewValue(ExoComponentDefinitionField $field, FieldItemInterface $item, $delta, $is_layout_builder) {
    $media = $item->entity;
    if ($media) {
      $source_field_definition = $media->getSource()->getSourceFieldDefinition($media->bundle->entity);
      $file = $media->{$source_field_definition->getName()}->entity;
      if ($file) {
        return [
          'url' => $file->url(),
          'title' => $media->label(),
          'style' => $this->componentViewFileImageStyles($field, $file),
        ];
      }
    }
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
    $values = [];
    if ($file = $this->componentFile($preview)) {
      $values[] = [
        'target_id' => $file->id(),
      ];
    }
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  protected function getFileDirectory(ExoComponentDefinitionFieldPreview $preview) {
    return 'public://media/' . str_replace('_', '-', $preview->getValue('bundle'));
  }

}
