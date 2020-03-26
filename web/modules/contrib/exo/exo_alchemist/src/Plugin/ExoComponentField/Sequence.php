<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Field\EntityReferenceFieldItemList;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\exo_alchemist\Definition\ExoComponentDefinition;
use Drupal\exo_alchemist\Definition\ExoComponentDefinitionField;
use Drupal\exo_alchemist\Definition\ExoComponentDefinitionFieldPreview;
use Drupal\exo_alchemist\ExoComponentManager;
use Drupal\layout_builder\InlineBlockEntityOperations;
use Drupal\layout_builder\LayoutEntityHelperTrait;

/**
 * A 'sequence' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "sequence",
 *   label = @Translation("Sequence"),
 * )
 */
class Sequence extends EntityReferenceBase {
  use LayoutEntityHelperTrait;

  /**
   * The entity type to reference.
   *
   * @var string
   */
  protected $entityType = ExoComponentManager::ENTITY_TYPE;

  /**
   * Component definition.
   *
   * @var \Drupal\exo_alchemist\Definition\ExoComponentDefinition[]
   *   The component definition.
   */
  protected $componentDefinitions = [];

  /**
   * The parent component's modifier attributes.
   *
   * @var array
   *   An array of attributes.
   */
  protected $parentModifierAttributes;

  /**
   * {@inheritdoc}
   */
  public function componentProcessDefinition(ExoComponentDefinitionField $field) {
    parent::componentProcessDefinition($field);
    if (!$field->hasAdditionalValue('sequence_fields')) {
      throw new PluginException(sprintf('eXo Component Field plugin (%s) requires [fields] be set.', $field->getType()));
    }

    $definition = $this->getComponentDefinition($field)->toArray();
    $this->exoComponentManager()->processDefinition($definition, $definition['id']);
  }

  /**
   * {@inheritdoc}
   */
  public function componentInstallEntityType(ExoComponentDefinitionField $field, ConfigEntityInterface $entity) {
    parent::componentInstallEntityType($field, $entity);
    $component = $this->getComponentDefinition($field);
    $this->exoComponentManager()->installEntityType($component);
  }

  /**
   * {@inheritdoc}
   */
  public function componentUpdateEntityType(ExoComponentDefinitionField $field, ConfigEntityInterface $entity) {
    parent::componentUpdateEntityType($field, $entity);
    $component = $this->getComponentDefinition($field);
    if (!$this->exoComponentManager()->updateEntityType($component)) {
      // When a sequence field is added post-install, it may need to be
      // installed.
      $this->exoComponentManager()->installEntityType($component);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function componentUninstallEntityType(ExoComponentDefinitionField $field, ConfigEntityInterface $entity) {
    parent::componentUninstallEntityType($field, $entity);
    $component = $this->getComponentDefinition($field);
    $this->exoComponentManager()->uninstallEntityType($component);
  }

  /**
   * {@inheritdoc}
   */
  public function componentPreUpdate(ExoComponentDefinitionField $field, FieldItemListInterface $items) {
    if ($items->count()) {
      $this->deepSerializeEntity($items);
    }
  }

  /**
   * Workaround for deep serialization.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   */
  protected function deepSerializeEntity(FieldItemListInterface $items) {
    // This is an ugly workaround of the lack of deep serialization. Entities
    // nested more than 1 level are never serialized and we therefore we set
    // these entities as "new" so that they are serialized and then we set
    // them back here.
    // @see exo_alchemist_block_content_presave().
    // @see https://www.drupal.org/project/drupal/issues/2824097
    // @TODO Remove when patch added to core.
    foreach ($items as $delta => $item) {
      if ($item->entity) {
        if (!$item->entity->isNew()) {
          $item->entity->enforceIsNew();
          $item->setValue([
            'target_id' => NULL,
            'entity' => $item->entity,
          ]);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function componentView(ExoComponentDefinitionField $field, FieldItemListInterface $items, $is_layout_builder) {
    $output = [];
    if ($items->count()) {
      foreach ($items as $delta => $item) {
        $output[$delta] = $this->componentViewValue($field, $item, $delta, $is_layout_builder);
      }
    }
    elseif ($value = $this->componentViewEmptyValue($field, $is_layout_builder)) {
      $output[0] = $value;
    }
    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function componentViewValue(ExoComponentDefinitionField $field, FieldItemInterface $item, $delta, $is_layout_builder) {
    $output = [];
    if ($item->entity) {
      $component = $this->getComponentDefinition($field);
      // Perserve path.
      $component->setParents(array_merge($field->getComponent()->getParents(), [
        $field->safeId(),
        $delta,
      ]));
      $output = $this->exoComponentManager()->viewEntityValues($component, $item->entity, $is_layout_builder);
    }
    return $output;
  }

  /**
   * Get parent modifier attributes.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity.
   * @param string $is_layout_builder
   *   TRUE if we are in layout builder mode.
   *
   * @return array
   *   An array of attributes.
   */
  protected function componentParentModifierAttributes(ExoComponentDefinition $definition, ContentEntityInterface $entity, $is_layout_builder) {
    if (!isset($this->parentModifierAttributes)) {
      $this->parentModifierAttributes = $this->exoComponentManager()->getExoComponentPropertyManager()->getModifierAttributes($definition, $entity, $is_layout_builder);
    }
    return $this->parentModifierAttributes;
  }

  /**
   * {@inheritdoc}
   */
  protected function componentCloneValue(ExoComponentDefinitionField $field, FieldItemInterface $item) {
    $component = $this->getComponentDefinition($field);
    return $this->exoComponentManager()->cloneEntity($component, $item->entity);
  }

  /**
   * {@inheritdoc}
   */
  public function componentRestore(ExoComponentDefinitionField $field, FieldItemListInterface $items) {
    $values = [];
    if ($items->isEmpty()) {
      $values = parent::componentRestore($field, $items);
    }
    else {
      $component = $this->getComponentDefinition($field);
      foreach ($items as $item) {
        $values[] = $this->exoComponentManager()->restoreEntity($component, $item->entity);
      }
    }
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function componentUpdate(ExoComponentDefinitionField $field, FieldItemListInterface $items) {
    parent::componentUpdate($field, $items);
    $parent_entity = $items->getEntity();
    $component = $this->getComponentDefinition($field);
    foreach ($items as $item) {
      $entity = $item->entity;
      $this->exoComponentManager()->getExoComponentFieldManager()->updateEntityFields($component, $entity);
      // We need to save usage.
      \Drupal::service('inline_block.usage')->addUsage($entity->id(), $parent_entity);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function componentDelete(ExoComponentDefinitionField $field, FieldItemListInterface $items) {
    parent::componentDelete($field, $items);
    $component = $this->getComponentDefinition($field);
    foreach ($items as $item) {
      $entity = $item->entity;
      // $entity->exoComponentRoot = $parent_entity;
      $this->exoComponentManager()->getExoComponentFieldManager()->updateEntityFields($component, $entity);
      // We need to save usage.
      \Drupal::service('inline_block.usage')->deleteUsage([$entity->id()]);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function componentEntity(ExoComponentDefinitionFieldPreview $preview, FieldItemInterface $item = NULL) {
    $component = $this->getComponentDefinition($preview->getField());
    $entity = $this->exoComponentManager()->loadEntity($component, FALSE, $preview->getDelta());
    // When a sequence field is installed via a config import, the default
    // entity has not yet been created. We create it at this point.
    if (!$entity && $preview->getDelta() === 0) {
      $entity = $this->exoComponentManager()->buildEntity($component);
    }
    // We have a base entity but have not yet created an entity for the provided
    // delta.
    elseif (!$entity) {
      $base_entity = $this->exoComponentManager()->loadEntity($component);
      $entity = $base_entity->createDuplicate();
    }
    $this->exoComponentManager()->populateEntity($component, $entity);
    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function componentPropertyInfo(ExoComponentDefinitionField $field) {
    $component = $this->getComponentDefinition($field);
    $info = $this->exoComponentManager()->getPropertyInfo($component);
    $properties = [];
    foreach ($info as $key => $data) {
      if ($key !== '_global') {
        $properties += $data['properties'];
      }
    }
    return $properties;
  }

  /**
   * Get the component definition.
   *
   * @return \Drupal\exo_alchemist\Definition\ExoComponentDefinition
   *   The component definition.
   */
  protected function getComponentDefinition(ExoComponentDefinitionField $field, $rebuild = FALSE) {
    if (!isset($this->componentDefinitions[$field->id()])) {
      $definition = [
        'id' => $field->id(),
        'label' => $field->getComponent()->getLabel() . ': ' . $field->getLabel(),
        'description' => 'A sequenced item.',
        'fields' => $field->getAdditionalValue('sequence_fields'),
        'modifier' => $field->getAdditionalValue('sequence_modifier'),
        'modifiers' => [],
        'modifier_globals' => FALSE,
        'hidden' => TRUE,
      ] + $field->toArray() + $field->getComponent()->toArray();
      // Sequence fields do not need to be inherited.
      unset($definition['additional']['sequence_fields']);
      unset($definition['additional']['sequence_modifier']);
      $this->exoComponentManager()->processDefinition($definition, $this->getPluginId());
      $this->componentDefinitions[$field->id()] = $definition;
    }
    return $this->componentDefinitions[$field->id()];
  }

  /**
   * Get the eXo component manager.
   *
   * @return \Drupal\exo_alchemist\ExoComponentManager
   *   The eXo component manager.
   */
  public function exoComponentManager() {
    if (!isset($this->exoComponentManager)) {
      $this->exoComponentManager = \Drupal::service('plugin.manager.exo_component');
    }
    return $this->exoComponentManager;
  }

  /**
   * Get the entity type.
   */
  protected function getEntityTypeBundles(ExoComponentDefinitionField $field) {
    return [$field->safeId()];
  }

}
