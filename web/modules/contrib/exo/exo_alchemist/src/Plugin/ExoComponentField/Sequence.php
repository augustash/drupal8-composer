<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\exo_alchemist\ExoComponentManager;
use Drupal\exo_alchemist\ExoComponentValue;
use Drupal\exo_alchemist\ExoComponentValues;
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
   * @var \Drupal\exo_alchemist\Definition\ExoComponentDefinition
   *   The component definition.
   */
  protected $componentDefinition;

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
  public function processDefinition() {
    parent::processDefinition();
    $field = $this->getFieldDefinition();
    if (!$field->hasAdditionalValue('sequence_fields')) {
      throw new PluginException(sprintf('eXo Component Field plugin (%s) requires [fields] be set.', $field->getType()));
    }

    // Merge in base defaults.
    foreach ($field->getDefaults() as $default) {
      foreach ($field->getAdditionalValue('sequence_fields') as $sequence_field_name => $sequence_field) {
        if (!empty($sequence_field['default']) && empty($default->getValue($sequence_field_name))) {
          $default->setValue($sequence_field_name, $sequence_field['default']);
        }
      }
    }

    $definition = $this->getComponentDefinition()->toArray();
    $this->exoComponentManager()->processDefinition($definition, $definition['id']);
  }

  /**
   * {@inheritdoc}
   */
  public function onFieldInstall() {
    parent::onFieldInstall();
    $component = $this->getComponentDefinition();
    $this->exoComponentManager()->installEntityType($component);
  }

  /**
   * {@inheritdoc}
   */
  public function onFieldUpdate() {
    parent::onFieldUpdate();
    $component = $this->getComponentDefinition();
    $this->exoComponentManager()->updateEntityType($component);
    // On update, we need to make sure we build the entity.
    $values = ExoComponentValues::fromFieldDefaults($this->getFieldDefinition());
    foreach ($values as $value) {
      $this->exoComponentManager()->buildEntity($this->getComponentDefinitionWithValue($value));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function onFieldUninstall() {
    parent::onFieldUninstall();
    $component = $this->getComponentDefinition();
    $this->exoComponentManager()->uninstallEntityType($component);
  }

  /**
   * {@inheritdoc}
   */
  public function onDraftUpdateLayoutBuilderEntity(FieldItemListInterface $items) {
    if ($items->count()) {
      $this->deepSerializeEntity($items);
    }
  }

  /**
   * Workaround for deep serialization.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   The field items.
   */
  protected function deepSerializeEntity(FieldItemListInterface $items) {
    // This is an ugly workaround of the lack of deep serialization. Entities
    // nested more than 1 level are never serialized and we therefore set
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
  public function getDefaultValue($delta = 0) {
    $field = $this->getFieldDefinition();
    $value = new ExoComponentValue($field, [
      '_delta' => $delta,
    ]);
    if ($entity = $this->getValueEntity($value)) {
      return [
        'target_id' => $entity->id(),
      ];
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function viewValue(FieldItemInterface $item, $delta, array $contexts) {
    $output = [];
    if ($item->entity) {
      $field = $this->getFieldDefinition();
      $component = $this->getComponentDefinition();
      $component->addParentFieldDelta($field, $delta);
      $output = $this->exoComponentManager()->viewEntityValues($component, $item->entity, $contexts);
    }
    return $output;
  }

  /**
   * {@inheritdoc}
   */
  protected function onCloneValue(FieldItemInterface $item, $all) {
    $component = $this->getComponentDefinition();
    return $this->exoComponentManager()->cloneEntity($component, $item->entity);
  }

  /**
   * {@inheritdoc}
   */
  public function onFieldRestore(ExoComponentValues $values, FieldItemListInterface $items) {
    $field_values = [];
    if ($items->isEmpty()) {
      $field_values = parent::onFieldRestore($values, $items);
    }
    else {
      $component = $this->getComponentDefinition();
      foreach ($items as $delta => $item) {
        $component->addParentFieldDelta($this->getFieldDefinition(), $delta);
        $field_values[] = $this->exoComponentManager()->restoreEntity($component, $item->entity);
      }
    }
    return $field_values;
  }

  /**
   * {@inheritdoc}
   */
  public function onPostSaveLayoutBuilderEntity(FieldItemListInterface $items, EntityInterface $parent_entity) {
    parent::onPostSaveLayoutBuilderEntity($items, $parent_entity);
    $sequence_entity = $items->getEntity();
    $component = $this->getComponentDefinition();
    foreach ($items as $delta => $item) {
      $component->addParentFieldDelta($this->getFieldDefinition(), $delta);
      $entity = $item->entity;
      if ($entity) {
        $this->exoComponentManager()->getExoComponentFieldManager()->onPostSaveLayoutBuilderEntity($component, $entity, $parent_entity);
        // We need to save usage.
        \Drupal::service('inline_block.usage')->addUsage($entity->id(), $sequence_entity);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function onPostDeleteLayoutBuilderEntity(FieldItemListInterface $items, EntityInterface $parent_entity) {
    parent::onPostDeleteLayoutBuilderEntity($items, $parent_entity);
    $sequence_entity = $items->getEntity();
    $component = $this->getComponentDefinition();
    foreach ($items as $delta => $item) {
      $component->addParentFieldDelta($this->getFieldDefinition(), $delta);
      $entity = $item->entity;
      if ($entity) {
        $this->exoComponentManager()->getExoComponentFieldManager()->onPostDeleteLayoutBuilderEntity($component, $entity, $parent_entity);
        // We need to remove usage.
        \Drupal::service('inline_block.usage')->removeByLayoutEntity($sequence_entity);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getValueEntity(ExoComponentValue $value, FieldItemInterface $item = NULL) {
    $component = $this->getComponentDefinitionWithValue($value);
    $entity = $this->exoComponentManager()->loadEntity($component);
    if (!$entity) {
      $entity = $this->exoComponentManager()->buildEntity($component);
    }
    else {
      $this->exoComponentManager()->populateEntity($component, $entity);
    }
    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    $component = $this->getComponentDefinition();
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
   * {@inheritdoc}
   */
  public function getRequiredPaths() {
    $paths = [];
    $field = $this->getFieldDefinition();
    $component = $this->getComponentDefinition();
    $count = $field->getCardinality() > 1 ? $field->getCardinality() : 1;
    for ($delta = 0; $delta < $count; $delta++) {
      $component->addParentFieldDelta($field, $delta);
      $paths = array_merge($paths, $this->exoComponentManager()->getExoComponentFieldManager()->getRequiredPaths($component));
    }
    return $paths;
  }

  /**
   * Get the component definition.
   *
   * @return \Drupal\exo_alchemist\Definition\ExoComponentDefinition
   *   The component definition.
   */
  protected function getComponentDefinition() {
    if (!isset($this->componentDefinition)) {
      $field = $this->getFieldDefinition();
      $definition = [
        'id' => $field->id(),
        'label' => $field->getComponent()->getLabel() . ': ' . $field->getLabel(),
        'description' => 'A sequenced item.',
        'fields' => $field->getAdditionalValue('sequence_fields'),
        'modifier' => $field->getAdditionalValue('sequence_modifier'),
        'modifiers' => [],
        'modifier_globals' => FALSE,
        'enhancements' => [],
        'computed' => TRUE,
      ] + $field->toArray() + $field->getComponent()->toArray();
      // Sequence fields do not need to be inherited.
      unset($definition['additional']['sequence_fields']);
      unset($definition['additional']['sequence_modifier']);
      $this->exoComponentManager()->processDefinition($definition, $this->getPluginId());
      /** @var \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition */
      $definition->addParentField($field);
      $this->componentDefinition = $definition;
    }
    return $this->componentDefinition;
  }

  /**
   * Get the component definition with set values.
   *
   * @param \Drupal\exo_alchemist\ExoComponentValue $value
   *   The component value.
   *
   * @return \Drupal\exo_alchemist\Definition\ExoComponentDefinition
   *   The component definition.
   */
  protected function getComponentDefinitionWithValue(ExoComponentValue $value) {
    $field = $this->getFieldDefinition();
    $definition = clone $this->getComponentDefinition();
    $definition->addParentFieldDelta($field, $value->getDelta());
    // When passed with a value, we want to make sure the defaults are set
    // correctly.
    $definition->setAdditionalValue('_delta', $value->getDelta());
    foreach ($definition->getFields() as $subfield) {
      $subfield->setDefaults($value->get($subfield->getName()));
      $subfield->setComponent($definition);
    }
    // Because we are dynamically settings the default values, we need to let
    // the field manager process these values to make sure they are correct.
    $this->exoComponentManager()->getExoComponentFieldManager()->processComponentDefinition($definition);
    return $definition;
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
  protected function getEntityTypeBundles() {
    return [$this->getFieldDefinition()->safeId()];
  }

}
