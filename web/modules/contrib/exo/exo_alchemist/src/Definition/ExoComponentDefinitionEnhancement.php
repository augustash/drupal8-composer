<?php

namespace Drupal\exo_alchemist\Definition;

use Drupal\Component\Utility\NestedArray;
use Drupal\exo\Shared\ExoArrayAccessDefinitionTrait;

/**
 * Class ExoComponentDefinitionEnhancement.
 *
 * @package Drupal\exo_alchemist\Definition
 */
class ExoComponentDefinitionEnhancement implements \ArrayAccess {

  use ExoArrayAccessDefinitionTrait;

  /**
   * Default field values.
   *
   * @var array
   */
  protected $definition = [
    'name' => NULL,
    'label' => NULL,
    'type' => NULL,
    'additional' => [],
  ];

  /**
   * The parent component.
   *
   * @var \Drupal\exo_alchemist\Definition\ExoComponentDefinition
   */
  protected $component;

  /**
   * ExoComponentDefinitionEnhancementProperty constructor.
   */
  public function __construct($name, array $values, $component) {
    $this->component = $component;
    foreach ($values as $key => $value) {
      if (array_key_exists($key, $this->definition)) {
        $this->definition[$key] = $value;
      }
      else {
        $this->definition['additional'][$key] = $value;
      }
    }
    $this->definition['name'] = !isset($values['name']) ? $name : $values['name'];
    $this->definition['label'] = !isset($values['label']) ? ucwords(str_replace(['-', '_'], ' ', $this->definition['name'])) : $values['label'];
  }

  /**
   * Return array definition.
   *
   * @return array
   *   Array definition.
   */
  public function toArray() {
    $definition = $this->definition;
    return array_filter($definition);
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
   * Get type property.
   *
   * @return mixed
   *   Property value.
   */
  public function getType() {
    return $this->definition['type'];
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
   * Get additional property.
   *
   * @return array
   *   Property value.
   */
  public function getAdditional() {
    return $this->definition['additional'];
  }

  /**
   * Check if additional property has value.
   *
   * @param mixed $parents
   *   An array of parent keys of the value, starting with the outermost key.
   *
   * @return bool
   *   Returns TRUE if additional property has value.
   */
  public function hasAdditionalValue($parents) {
    return !empty($this->getAdditionalValue($parents));
  }

  /**
   * Get additional property value.
   *
   * @param mixed $parents
   *   An array of parent keys of the value, starting with the outermost key.
   * @param bool $key_exists
   *   (optional) If given, an already defined variable that is altered by
   *   reference.
   *
   * @return mixed
   *   The requested nested value. Possibly NULL if the value is NULL or not all
   *   nested parent keys exist. $key_exists is altered by reference and is a
   *   Boolean that indicates whether all nested parent keys exist (TRUE) or not
   *   (FALSE). This allows to distinguish between the two possibilities when
   *   NULL is returned.
   */
  public function getAdditionalValue($parents, &$key_exists = NULL) {
    return NestedArray::getValue($this->definition['additional'], (array) $parents, $key_exists);
  }

  /**
   * Set additional property value.
   *
   * @param mixed $parents
   *   An array of parent keys, starting with the outermost key.
   * @param mixed $value
   *   The value to set.
   * @param bool $force
   *   (optional) If TRUE, the value is forced into the structure even if it
   *   requires the deletion of an already existing non-array parent value. If
   *   FALSE, PHP throws an error if trying to add into a value that is not an
   *   array. Defaults to FALSE.
   *
   * @see NestedArray::unsetValue()
   * @see NestedArray::getValue()
   */
  public function setAdditionalValue($parents, $value, $force = FALSE) {
    return NestedArray::setValue($this->definition['additional'], (array) $parents, $value, $force);
  }

  /**
   * Unset additional property value.
   *
   * @param mixed $parents
   *   An array of parent keys, starting with the outermost key.
   *
   * @see NestedArray::unsetValue()
   * @see NestedArray::getValue()
   */
  public function unsetAdditionalValue($parents) {
    return NestedArray::unsetValue($this->definition['additional'], (array) $parents);
  }

  /**
   * Set additional property.
   *
   * @param array $additional
   *   Property value.
   *
   * @return $this
   */
  public function setAdditional(array $additional) {
    $this->definition['additional'] = $additional;
    return $this;
  }

  /**
   * Add additional property.
   *
   * @param string $key
   *   Property key.
   * @param mixed $value
   *   Property value.
   *
   * @return $this
   */
  public function addAdditional($key, $value) {
    $this->definition['additional'][$key] = $value;
    return $this;
  }

}
