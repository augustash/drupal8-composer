<?php

namespace Drupal\exo_alchemist\Definition;

use Drupal\Component\Utility\NestedArray;
use Drupal\exo\Shared\ExoArrayAccessDefinitionTrait;

/**
 * Class ExoComponentDefinitionModifierProperty.
 *
 * @package Drupal\exo_alchemist\Definition
 */
class ExoComponentDefinitionModifierProperty implements \ArrayAccess {

  use ExoArrayAccessDefinitionTrait;

  /**
   * Default field values.
   *
   * @var array
   */
  protected $definition = [
    'type' => NULL,
    'name' => NULL,
    'label' => NULL,
    'description' => NULL,
    'required' => FALSE,
    'default' => NULL,
    'additional' => [],
  ];

  /**
   * The parent modifier.
   *
   * @var \Drupal\exo_alchemist\Definition\ExoComponentDefinitionModifier
   */
  protected $modifier;

  /**
   * ExoComponentDefinitionModifierProperty constructor.
   */
  public function __construct($name, $values) {
    if (is_scalar($values)) {
      $this->definition['name'] = is_numeric($name) ? $values : $name;
      $this->definition['label'] = $values;
    }
    else {
      foreach ($values as $key => $value) {
        if (array_key_exists($key, $this->definition)) {
          $this->definition[$key] = $value;
        }
      }
      foreach ($values as $key => $value) {
        if (!array_key_exists($key, $this->definition)) {
          $this->definition['additional'][$key] = $value;
        }
      }
      $this->definition['name'] = !isset($values['name']) ? $name : $values['name'];
      $this->definition['label'] = $values['label'];
    }
  }

  /**
   * Return array definition.
   *
   * @return array
   *   Array definition.
   */
  public function toArray() {
    return $this->definition;
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->getModifier()->id() . '_' . $this->getType() . '_' . $this->getName();
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
   * Get Description property.
   *
   * @return string
   *   Property value.
   */
  public function getDescription() {
    return $this->definition['description'];
  }

  /**
   * Set Description property.
   *
   * @param string $description
   *   Property value.
   *
   * @return $this
   */
  public function setDescription($description) {
    $this->definition['description'] = $description;
    return $this;
  }

  /**
   * Check if field is required.
   *
   * @return bool
   *   Returns TRUE if required.
   */
  public function isRequired() {
    return !empty($this->definition['required']);
  }

  /**
   * Get Type property.
   *
   * @return string
   *   Property value.
   */
  public function getType() {
    return $this->definition['type'];
  }

  /**
   * Set Type property.
   *
   * @param string $type
   *   Property value.
   *
   * @return $this
   */
  public function setType($type) {
    $this->definition['type'] = $type;
    return $this;
  }

  /**
   * Get default property.
   *
   * @return string
   *   Property value.
   */
  public function getDefault() {
    return $this->definition['default'];
  }

  /**
   * Set default property.
   *
   * @param string $default
   *   Property value.
   *
   * @return $this
   */
  public function setDefault($default) {
    $this->definition['default'] = $default;
    return $this;
  }

  /**
   * Get modifier property.
   *
   * @return \Drupal\exo_alchemist\Definition\ExoComponentDefinition
   *   The eXo modifier.
   */
  public function getModifier() {
    return $this->modifier;
  }

  /**
   * Set modifier property.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionModifier $modifier
   *   Property value.
   *
   * @return $this
   */
  public function setModifier(ExoComponentDefinitionModifier $modifier) {
    $this->modifier = $modifier;
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
