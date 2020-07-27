<?php

namespace Drupal\exo_alchemist;

use Drupal\Component\Utility\NestedArray;
use Drupal\exo_alchemist\Definition\ExoComponentDefinitionField;

/**
 * Custom ArrayObject implementation.
 *
 * The native ArrayObject is unnecessarily complicated.
 *
 * @ingroup utility
 */
class ExoComponentValue implements \IteratorAggregate, \ArrayAccess, \Countable {

  /**
   * The field definition.
   *
   * @var \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField
   */
  protected $definition;

  /**
   * The array.
   *
   * @var array
   */
  protected $data;

  /**
   * The delta.
   *
   * @var int
   */
  protected $delta;

  /**
   * Array object constructor.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField $field
   *   The field definition.
   * @param array $data
   *   An array.
   * @param int $delta
   *   The delta of the value.
   */
  public function __construct(ExoComponentDefinitionField $field, array $data = [], $delta = 0) {
    $this->definition = $field;
    $this->data = $data;
    $this->delta = $delta;
  }

  /**
   * Get the field definition.
   *
   * @return \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField
   *   The field definition.
   */
  public function getDefinition() {
    return $this->definition;
  }

  /**
   * Get delta property.
   *
   * @return string
   *   Property value.
   */
  public function getDelta() {
    return $this->delta;
  }

  /**
   * Set delta property.
   *
   * @param int $delta
   *   The delta.
   *
   * @return $this
   */
  public function setDelta($delta) {
    $this->delta = $delta;
    return $this;
  }

  /**
   * Returns whether the requested key exists.
   *
   * @param mixed $property
   *   A key.
   *
   * @return bool
   *   TRUE or FALSE
   */
  public function __isset($property) {
    return $this->offsetExists($property);
  }

  /**
   * Sets the value at the specified key to value.
   *
   * @param mixed $property
   *   A key.
   * @param mixed $value
   *   A value.
   */
  public function __set($property, $value) {
    $this->offsetSet($property, $value);
  }

  /**
   * Unsets the value at the specified key.
   *
   * @param mixed $property
   *   A key.
   */
  public function __unset($property) {
    $this->offsetUnset($property);
  }

  /**
   * Returns the value at the specified key by reference.
   *
   * @param mixed $property
   *   A key.
   *
   * @return mixed
   *   The stored value.
   */
  public function &__get($property) {
    $ret =& $this->offsetGet($property);
    return $ret;
  }

  /**
   * Returns the data as an array.
   *
   * @return array
   *   The array.
   */
  public function toArray() {
    return $this->data;
  }

  /**
   * Get the number of public properties in the ArrayObject.
   *
   * @return int
   *   The count.
   */
  public function count() {
    return count($this->data);
  }

  /**
   * Returns whether the requested key is empty.
   *
   * @param mixed $property
   *   A key.
   *
   * @return bool
   *   TRUE or FALSE
   */
  public function empty($property) {
    return empty($this->offsetGet($property));
  }

  /**
   * Returns whether the requested key exists.
   *
   * @param mixed $property
   *   A key.
   *
   * @return bool
   *   TRUE or FALSE
   */
  public function has($property) {
    return $this->offsetExists($property);
  }

  /**
   * Returns the value at the specified key.
   *
   * @param mixed $property
   *   A key.
   *
   * @return mixed
   *   The value.
   */
  public function &get($property) {
    return $this->offsetGet($property);
  }

  /**
   * Sets the value at the specified key to value.
   *
   * @param mixed $property
   *   A key.
   * @param mixed $value
   *   A value.
   *
   * @return $this
   */
  public function set($property, $value) {
    $this->offsetSet($property, $value);
    return $this;
  }

  /**
   * Sets the value at the specified key to value if it is not set already.
   *
   * @param mixed $property
   *   A key.
   * @param mixed $value
   *   A value.
   *
   * @return $this
   */
  public function setIfUnset($property, $value) {
    if (!$this->offsetExists($property)) {
      $this->offsetSet($property, $value);
    }
    return $this;
  }

  /**
   * Unsets the value at the specified key.
   *
   * @param mixed $property
   *   A key.
   */
  public function unset($property) {
    $this->offsetUnset($property);
  }

  /**
   * Returns whether the requested key exists.
   *
   * @param mixed $property
   *   A key.
   *
   * @return bool
   *   TRUE or FALSE
   */
  public function offsetExists($property) {
    $exists = NULL;
    NestedArray::getValue($this->data, (array) $property, $exists);
    return $exists;
  }

  /**
   * Returns the value at the specified key.
   *
   * @param mixed $property
   *   A key.
   *
   * @return mixed
   *   The value.
   */
  public function &offsetGet($property) {
    $value = NULL;
    if (!$this->offsetExists($property)) {
      return $value;
    }
    $value = &NestedArray::getValue($this->data, (array) $property);
    return $value;
  }

  /**
   * Sets the value at the specified key to value.
   *
   * @param mixed $property
   *   A key.
   * @param mixed $value
   *   A value.
   */
  public function offsetSet($property, $value) {
    if ($value instanceof ExoComponentValues || $value instanceof ExoComponentValue) {
      $value = $value->toArray();
    }
    NestedArray::setValue($this->data, (array) $property, $value, TRUE);
  }

  /**
   * Unsets the value at the specified key.
   *
   * @param mixed $property
   *   A key.
   */
  public function offsetUnset($property) {
    NestedArray::unsetValue($this->data, (array) $property);
  }

  /**
   * Returns an iterator for entities.
   *
   * @return \ArrayIterator
   *   An \ArrayIterator instance
   */
  public function getIterator() {
    return new \ArrayIterator($this->data);
  }

}
