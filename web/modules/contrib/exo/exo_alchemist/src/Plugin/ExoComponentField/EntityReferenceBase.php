<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\exo_alchemist\Definition\ExoComponentDefinitionField;
use Drupal\exo_alchemist\Definition\ExoComponentDefinitionFieldPreview;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldFieldableBase;

/**
 * Base component for entity reference fields.
 */
class EntityReferenceBase extends ExoComponentFieldFieldableBase {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The entity type to reference.
   *
   * @var string
   */
  protected $entityType;

  /**
   * {@inheritdoc}
   */
  public function componentProcessDefinition(ExoComponentDefinitionField $field) {
    if (!$this->getEntityTypeBundles($field)) {
      // Default bundle same as entity type.
      $entity_type = $this->getEntityType($field);
      if (!$entity_type) {
        throw new PluginException(sprintf('eXo Component Field plugin (%s) must define an entity type.', $field->getType()));
      }
      $field->setAdditionalValue('bundles', [$entity_type]);
      $field->setPreviewValueOnAll('bundle', $this->getEntityType($field));
    }
    else {
      // Make sure bundle is set on each preview.
      $bundle = $this->getEntityTypeBundles($field);
      $field->setPreviewValueOnAll('bundle', reset($bundle));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function componentStorage(ExoComponentDefinitionField $field) {
    return [
      'type' => 'entity_reference',
      'settings' => [
        'target_type' => $this->getEntityType($field),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function componentField(ExoComponentDefinitionField $field) {
    return [
      'settings' => [
        'handler' => 'default',
        'handler_settings' => [
          'target_bundles' => array_combine($this->getEntityTypeBundles($field), $this->getEntityTypeBundles($field)),
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function componentValue(ExoComponentDefinitionFieldPreview $preview, FieldItemInterface $item = NULL) {
    return $this->componentEntity($preview, $item);
  }

  /**
   * Extending classes can return an entity that will be set as the value.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionFieldPreview $preview
   *   The field preview.
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   The current item.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   An entity that will be used to set the value of the field.
   */
  protected function componentEntity(ExoComponentDefinitionFieldPreview $preview, FieldItemInterface $item = NULL) {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function componentViewValue(ExoComponentDefinitionField $field, FieldItemInterface $item, $delta, $is_layout_builder) {
    return [
      'label' => $item->entity->label(),
    ];
  }

  /**
   * Get the entity type.
   */
  protected function getEntityType(ExoComponentDefinitionField $field) {
    return $this->entityType;
  }

  /**
   * Get the entity type.
   */
  protected function getEntityTypeBundles(ExoComponentDefinitionField $field) {
    return $field->getAdditionalValue('bundles');
  }

  /**
   * Returns the module handler.
   *
   * @return \Drupal\Core\Extension\ModuleHandlerInterface
   *   The module handler.
   */
  protected function moduleHandler() {
    if (!$this->moduleHandler) {
      $this->moduleHandler = \Drupal::service('module_handler');
    }
    return $this->moduleHandler;
  }

}
