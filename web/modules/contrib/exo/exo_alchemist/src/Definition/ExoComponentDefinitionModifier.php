<?php

namespace Drupal\exo_alchemist\Definition;

use Drupal\exo\Shared\ExoArrayAccessDefinitionTrait;

/**
 * Class ExoComponentDefinitionModifier.
 *
 * @package Drupal\exo_alchemist\Definition
 */
class ExoComponentDefinitionModifier implements \ArrayAccess {

  use ExoArrayAccessDefinitionTrait;

  /**
   * Default field values.
   *
   * @var array
   */
  protected $definition = [
    'name' => NULL,
    'label' => NULL,
    'description' => NULL,
    'group' => NULL,
    'properties' => [],
  ];

  /**
   * The parent component.
   *
   * @var \Drupal\exo_alchemist\Definition\ExoComponentDefinition
   */
  protected $component;

  /**
   * ExoComponentDefinitionModifierProperty constructor.
   */
  public function __construct($name, $values) {
    $this->definition['name'] = !isset($values['name']) ? $name : $values['name'];
    $this->definition['label'] = $values['label'];
    $this->definition['description'] = !isset($values['description']) ? NULL : $values['description'];
    $this->definition['group'] = !isset($values['group']) ? NULL : $values['group'];
    $this->setProperties($values['properties']);
  }

  /**
   * Return array definition.
   *
   * @return array
   *   Array definition.
   */
  public function toArray() {
    $definition = $this->definition;
    foreach ($this->getProperties() as $name => $property) {
      $definition['properties'][$name] = $property->toArray();
    }
    return $definition;
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->getComponent()->id() . '_' . $this->getName();
  }

  /**
   * Get Name property.
   *
   * @return mixed
   *   Property value.
   */
  public function getName() {
    return $this->definition['name'];
  }

  /**
   * Get Label property.
   *
   * @return mixed
   *   Property value.
   */
  public function getLabel() {
    return $this->definition['label'];
  }

  /**
   * Getter.
   *
   * @return string
   *   Property value.
   */
  public function getDescription() {
    return $this->definition['description'];
  }

  /**
   * Get group property.
   *
   * @return mixed
   *   Property value.
   */
  public function getGroup() {
    return $this->definition['group'];
  }

  /**
   * Get component property.
   *
   * @return \Drupal\exo_alchemist\Definition\ExoComponentDefinition
   *   The eXo component.
   */
  public function getComponent() {
    return $this->component;
  }

  /**
   * Set component property.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $component
   *   Property value.
   *
   * @return $this
   */
  public function setComponent(ExoComponentDefinition $component) {
    $this->component = $component;
    return $this;
  }

  /**
   * Setter.
   *
   * @param array $properties
   *   Property value.
   *
   * @return $this
   */
  public function setProperties(array $properties) {
    foreach ($properties as $name => $value) {
      $modifier = $this->getPropertyDefinition($name, $value);
      $this->definition['properties'][$modifier->getName()] = $modifier;
    }
    return $this;
  }

  /**
   * Getter.
   *
   * @return ExoComponentDefinitionModifierProperty[]
   *   Property value.
   */
  public function getProperties() {
    return $this->definition['properties'];
  }

  /**
   * Factory method: create a new modifier definition.
   *
   * @param string $name
   *   Field name.
   * @param string $value
   *   Field value.
   *
   * @return \Drupal\exo_alchemist\Definition\ExoComponentDefinitionModifierProperty
   *   Definition instance.
   */
  protected function getPropertyDefinition($name, $value) {
    $property = new ExoComponentDefinitionModifierProperty($name, $value);
    $property->setModifier($this);
    return $property;
  }

}
