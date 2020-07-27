<?php

namespace Drupal\exo_alchemist\Definition;

use Drupal\Component\Utility\NestedArray;
use Drupal\exo\Shared\ExoArrayAccessDefinitionTrait;

/**
 * Class ExoComponentDefinitionFieldDefault.
 *
 * @package Drupal\exo_alchemist\Definition
 */
class ExoComponentDefinitionFieldDefault implements \ArrayAccess {

  use ExoArrayAccessDefinitionTrait;

  /**
   * The parent field.
   *
   * @var \Drupal\exo_alchemist\Definition\ExoComponentFieldDefinition
   */
  protected $field;

  /**
   * Default field values.
   *
   * @var array
   */
  protected $definition = [];

  /**
   * ExoFieldDefinitionField constructor.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField $field
   *   Property value.
   * @param array $values
   *   An array of default values.
   */
  public function __construct(ExoComponentDefinitionField $field, array $values) {
    $this->field = $field;
    foreach ($values as $key => $value) {
      $this->definition[$key] = $value;
    }
  }

  /**
   * Get field property.
   *
   * @return \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField
   *   Property value.
   */
  public function getField() {
    return $this->field;
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
   * Retrieves a value from a nested array with variable depth.
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
  public function &getValue($parents, &$key_exists = NULL) {
    $array = &$this->definition;
    return NestedArray::getValue($array, (array) $parents, $key_exists);
  }

  /**
   * Sets a value in a nested array with variable depth.
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
  public function setValue($parents, $value, $force = FALSE) {
    $array = &$this->definition;
    return NestedArray::setValue($array, (array) $parents, $value, $force);
  }

  /**
   * Unsets a value in a nested array with variable depth.
   *
   * @param mixed $parents
   *   An array of parent keys, starting with the outermost key and including
   *   the key to be unset.
   * @param bool $key_existed
   *   (optional) If given, an already defined variable that is altered by
   *   reference.
   *
   * @see NestedArray::setValue()
   * @see NestedArray::getValue()
   */
  public function unsetValue($parents, &$key_existed = NULL) {
    $array = &$this->definition;
    return NestedArray::unsetValue($array, (array) $parents, $key_existed);
  }

  /**
   * Determines whether a nested array contains the requested keys.
   *
   * @param mixed $parents
   *   An array of parent keys of the value, starting with the outermost key.
   *
   * @return bool
   *   TRUE if all the parent keys exist, FALSE otherwise.
   *
   * @see NestedArray::getValue()
   */
  public function keyExists($parents) {
    return NestedArray::keyExists($this->definition, (array) $parents);
  }

}
