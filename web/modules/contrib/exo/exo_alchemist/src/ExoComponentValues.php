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
class ExoComponentValues implements \IteratorAggregate, \ArrayAccess, \Countable {

  /**
   * The field definition.
   *
   * @var \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField
   */
  protected $definition;

  /**
   * The array.
   *
   * @var \Drupal\exo_alchemist\ExoComponentValue[]
   */
  protected $data;

  /**
   * Array object constructor.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField $field
   *   The field definition.
   * @param mixed $data
   *   An string, array of arrays or ExoComponentValue[].
   */
  public function __construct(ExoComponentDefinitionField $field, $data = []) {
    $this->definition = $field;
    $this->setItems($data);
  }

  /**
   * Creates a ExoComponentValues object from a given field object.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField $field
   *   The component definition.
   *
   * @return static
   */
  public static function fromFieldDefaults(ExoComponentDefinitionField $field) {
    $data = [];
    foreach ($field->getDefaults() as $default) {
      $data[] = $default->toArray();
    }
    return new static($field, $data);
  }

  /**
   * Set the items.
   *
   * @param mixed $data
   *   An string, array of arrays or ExoComponentValue[].
   *
   * @return ExoComponentValue[]
   *   An array of items.
   */
  public function setItems($data) {
    $this->data = [];
    if (!is_array($data)) {
      $data = [['value' => $data]];
    }
    else {
      // Data should be a simple array. If it isn't, we assume we
      // have a complex default value and it needs to be nested.
      $modified_data = [];
      foreach ($data as $delta => $value) {
        if (!is_int($delta)) {
          $modified_data[] = $data;
          break;
        }
        if (!is_array($value)) {
          $modified_data[] = ['value' => $value];
        }
        else {
          $modified_data[] = $value;
        }
      }
      $data = $modified_data;
    }
    foreach ($data as $delta => $value) {
      $this->set($delta, $value);
    }
    return $this->data;
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
   * Returns whether the requested key exists.
   *
   * @param mixed $key
   *   A key.
   *
   * @return bool
   *   TRUE or FALSE
   */
  public function __isset($key) {
    return $this->offsetExists($key);
  }

  /**
   * Sets the value at the specified key to value.
   *
   * @param mixed $key
   *   A key.
   * @param mixed $value
   *   A value.
   */
  public function __set($key, $value) {
    $this->offsetSet($key, $value);
  }

  /**
   * Unsets the value at the specified key.
   *
   * @param mixed $key
   *   A key.
   */
  public function __unset($key) {
    $this->offsetUnset($key);
  }

  /**
   * Returns the value at the specified key by reference.
   *
   * @param mixed $key
   *   A key.
   *
   * @return mixed
   *   The stored value.
   */
  public function &__get($key) {
    $ret =& $this->offsetGet($key);
    return $ret;
  }

  /**
   * Returns the collection.
   *
   * @return \Drupal\exo_alchemist\ExoComponentValue[]
   *   The array.
   */
  public function items() {
    return $this->data;
  }

  /**
   * Returns the collection as an array.
   *
   * @return array
   *   The array.
   */
  public function toArray() {
    $data = [];
    foreach ($this->data as $key => $item) {
      $data[$key] = $item->toArray();
    }
    return $data;
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
   * Get the first item.
   *
   * @return int
   *   The count.
   */
  public function first() {
    if ($this->count()) {
      return $this->offsetGet(key($this->data));
    }
    return NULL;
  }

  /**
   * Get the last item.
   *
   * @return int
   *   The count.
   */
  public function last() {
    if ($this->count()) {
      $keys = array_keys($this->data);
      return $this->offsetGet(end($keys));
    }
    return NULL;
  }

  /**
   * Check ArrayObject for results.
   *
   * @return int
   *   The count.
   */
  public function hasItems() {
    return !empty($this->count());
  }

  /**
   * Check ArrayObject for results.
   *
   * @return array
   *   The slice.
   */
  public function slice($offset, $length, $preserve_keys = FALSE) {
    $this->data = array_slice($this->data, $offset, $length, $preserve_keys);
    return $this->data;
  }

  /**
   * Returns whether the requested key is empty.
   *
   * @param mixed $delta
   *   A key.
   *
   * @return bool
   *   TRUE or FALSE
   */
  public function empty($delta) {
    return empty($this->offsetGet($delta));
  }

  /**
   * Returns whether the requested key exists.
   *
   * @param mixed $delta
   *   A key.
   *
   * @return bool
   *   TRUE or FALSE
   */
  public function has($delta) {
    return $this->offsetExists($delta);
  }

  /**
   * Returns the value at the specified key.
   *
   * @param mixed $delta
   *   A key.
   *
   * @return mixed
   *   The value.
   */
  public function &get($delta) {
    return $this->offsetGet($delta);
  }

  /**
   * Sets the value at the specified key to value.
   *
   * @param mixed $delta
   *   A key.
   * @param mixed $value
   *   A value.
   */
  public function set($delta, $value) {
    $this->offsetSet($delta, $value);
  }

  /**
   * Unsets the value at the specified key.
   *
   * @param mixed $delta
   *   A key.
   */
  public function unset($delta) {
    $this->offsetUnset($delta);
  }

  /**
   * Returns whether the requested key exists.
   *
   * @param mixed $delta
   *   A key.
   *
   * @return bool
   *   TRUE or FALSE
   */
  public function offsetExists($delta) {
    $exists = NULL;
    NestedArray::getValue($this->data, (array) $delta, $exists);
    return $exists;
  }

  /**
   * Returns the value at the specified key.
   *
   * @param mixed $delta
   *   A key.
   *
   * @return mixed
   *   The value.
   */
  public function &offsetGet($delta) {
    $value = NULL;
    if (!$this->offsetExists($delta)) {
      return $value;
    }
    $value = &NestedArray::getValue($this->data, (array) $delta);
    return $value;
  }

  /**
   * Sets the value at the specified key to value.
   *
   * @param mixed $delta
   *   A key.
   * @param mixed $value
   *   A value.
   */
  public function offsetSet($delta, $value) {
    if (!$value instanceof ExoComponentValue) {
      $value = new ExoComponentValue($this->getDefinition(), $value);
    }
    $value->setDelta($delta);
    NestedArray::setValue($this->data, (array) $delta, $value, TRUE);
  }

  /**
   * Unsets the value at the specified key.
   *
   * @param mixed $delta
   *   A key.
   */
  public function offsetUnset($delta) {
    NestedArray::unsetValue($this->data, (array) $delta);
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
