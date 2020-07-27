<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\exo_alchemist\ExoComponentValue;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldFieldableBase;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldFileTrait;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldImageStylesTrait;

/**
 * A 'image' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "image",
 *   label = @Translation("Image"),
 *   storage = {
 *     "type" = "image",
 *   },
 *   widget = {
 *     "type" = "image_image",
 *   },
 * )
 */
class Image extends ExoComponentFieldFieldableBase {

  use ExoComponentFieldFileTrait;
  use ExoComponentFieldImageStylesTrait;

  /**
   * {@inheritdoc}
   */
  public function getFieldConfig() {
    $field = $this->getFieldDefinition();
    return [
      'settings' => [
        'file_directory' => $field->getType() . '/' . $field->getName(),
        'file_extensions' => 'png gif jpg jpeg',
      ],
    ];
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
  public function validateValue(ExoComponentValue $value) {
    parent::validateValue($value);
    if ($value->get('value')) {
      $value->set('path', $value->get('value'));
      $value->unset('value');
    }
    $this->validateValueFile($value, TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    $properties = parent::propertyInfo();
    $properties['url'] = $this->t('The image url.');
    $properties['width'] = $this->t('The image width.');
    $properties['height'] = $this->t('The image height.');
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
  public function viewValue(FieldItemInterface $item, $delta, array $contexts) {
    /** @var \Drupal\file\FileInterface $file */
    $file = $item->entity;
    if ($file) {
      return [
        'url' => $file->url(),
        'width' => $item->width,
        'height' => $item->height,
        'title' => $file->label(),
        'style' => $this->getImageStylesAsUrl($this->getFieldDefinition(), $file),
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function cleanValue(FieldItemInterface $item, $delta, $update = TRUE) {
    parent::cleanValue($item, $delta, $update);
    if ($item && $item->entity) {
      $item->entity->delete();
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getValue(ExoComponentValue $value, FieldItemInterface $item = NULL) {
    if ($item) {
      // We always remove the current value.
      $this->cleanValue($item, $value->getDelta(), TRUE);
    }
    if ($file = $this->componentFile($value)) {
      return $file;
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  protected function getFileDirectory(ExoComponentValue $value) {
    return 'public://images';
  }

}
