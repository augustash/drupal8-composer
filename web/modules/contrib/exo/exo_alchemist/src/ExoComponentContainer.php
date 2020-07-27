<?php

namespace Drupal\exo_alchemist;

/**
 * Provides a management object for new components.
 */
class ExoComponentContainer {

  /**
   * The eXo component manager.
   *
   * @var \Drupal\exo_alchemist\ExoComponentManager
   */
  protected $exoComponentManager;

  /**
   * The component definition.
   *
   * @var \Drupal\exo_alchemist\Definition\ExoComponentDefinition
   */
  protected $definition;

  /**
   * The component.
   *
   * @var \Drupal\Core\Entity\ContentEntityInterface
   */
  protected $entity;

  /**
   * Constructs a new SectionComponent.
   *
   * @param \Drupal\exo_alchemist\ExoComponentManager $exo_component_manager
   *   The component manager.
   * @param string $component_type_id
   *   The component type id.
   */
  public function __construct(ExoComponentManager $exo_component_manager, $component_type_id) {
    $this->exoComponentManager = $exo_component_manager;
    $this->definition = $this->exoComponentManager->getInstalledDefinition($component_type_id);
    $this->entity = $this->createComponent();
  }

  /**
   * Get the component.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   The component.
   */
  public function getComponent() {
    return $this->entity;
  }

  /**
   * Get the component definition.
   *
   * @return \Drupal\exo_alchemist\Definition\ExoComponentDefinition
   *   The component definition.
   */
  public function getDefinition() {
    return $this->definition;
  }

  /**
   * Get a component field.
   *
   * @param string $field_name
   *   The component field name.
   *
   * @return \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField
   *   The component field definition.
   */
  public function getFieldDefinition($field_name) {
    return $this->definition->getField($field_name);
  }

  /**
   * Set a field's value.
   *
   * @param string $field_name
   *   The component field name.
   * @param mixed $values
   *   The values to set on the field.
   */
  public function setFieldValue($field_name, $values) {
    if ($field = $this->getFieldDefinition($field_name)) {
      $this->getComponent()->_exoComponentValues[$field_name] = new ExoComponentValues($field, $values);
    }
    return $this;
  }

  /**
   * Get a field's value.
   *
   * @param string $field_name
   *   The component field name.
   *
   * @return \Drupal\exo_alchemist\ExoComponentValues
   *   The component field values.
   */
  public function getFieldValue($field_name) {
    if ($this->getFieldDefinition($field_name)) {
      return $this->getComponent()->_exoComponentValues[$field_name];
    }
    return NULL;
  }

  /**
   * Create a component.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   The content entity that makes up the component.
   */
  protected function createComponent() {
    return $this->exoComponentManager->cloneEntity($this->definition, NULL, TRUE);
  }

}
