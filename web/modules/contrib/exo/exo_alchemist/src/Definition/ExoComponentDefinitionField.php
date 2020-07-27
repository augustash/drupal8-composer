<?php

namespace Drupal\exo_alchemist\Definition;

use Drupal\Component\Utility\NestedArray;
use Drupal\exo\Shared\ExoArrayAccessDefinitionTrait;

/**
 * Class ExoComponentDefinitionField.
 *
 * @package Drupal\exo_alchemist\Definition
 */
class ExoComponentDefinitionField implements \ArrayAccess {

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
    'type' => NULL,
    'alias' => NULL,
    'computed' => FALSE,
    'group' => NULL,
    'component' => NULL,
    'cardinality' => 1,
    'required' => FALSE,
    'edit' => TRUE,
    'hide' => TRUE,
    'default' => [],
    'modifier' => '',
    'additional' => [],
  ];

  /**
   * The object's dependents.
   *
   * @var array
   */
  protected $dependents = [];

  /**
   * The parent component.
   *
   * @var \Drupal\exo_alchemist\Definition\ExoComponentDefinition
   */
  protected $component;

  /**
   * ExoComponentDefinitionField constructor.
   */
  public function __construct($name, $values, $component) {
    $this->component = $component;
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
      $this->definition['label'] = isset($values['label']) ? $values['label'] : ucwords(str_replace('_', ' ', $this->definition['name']));
      if (!empty($values['default'])) {
        $this->setDefaults($values['default']);
      }
      elseif (!empty($values['preview'])) {
        \Drupal::messenger()->addWarning(t('@label (@name) in component (@id) needs to be updated to use "default" instead of "preview".', [
          '@label' => $this->definition['label'],
          '@name' => $this->definition['name'],
          '@id' => $component->id(),
        ]));
        $this->setDefaults($values['preview']);
        unset($values['preview']);
      }
    }
  }

  /**
   * Return array definition.
   *
   * @return array
   *   Array definition.
   */
  public function toArray() {
    $definition = $this->definition;
    $definition['default'] = $this->getDefaultsAsArray();
    return $definition;
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->getComponent()->id() . '_' . $this->getType() . '_' . $this->getName();
  }

  /**
   * A string that is 32 characters long and can be used for entity ids.
   *
   * @return string
   *   A 32 character string.
   */
  public function safeId() {
    return 'exo_field_' . substr(hash('sha256', $this->id()), 0, 22);
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldName() {
    return $this->safeId();
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
   * Get alias property.
   *
   * @return mixed
   *   Property value.
   */
  public function getAlias() {
    return $this->definition['alias'];
  }

  /**
   * Getter.
   *
   * @return mixed
   *   Property value.
   */
  public function isComputed() {
    return !empty($this->definition['computed']);
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
   * Get cardinality property.
   *
   * @return string
   *   Property value.
   */
  public function getCardinality() {
    return $this->definition['cardinality'];
  }

  /**
   * Set cardinality property.
   *
   * @param string $cardinality
   *   Property value.
   *
   * @return $this
   */
  public function setCardinality($cardinality) {
    $this->definition['cardinality'] = $cardinality;
    return $this;
  }

  /**
   * Check if supports multiple values.
   *
   * @return bool
   *   TRUE if supports multiple.
   */
  public function supportsMultiple() {
    return $this->getCardinality() != 1;
  }

  /**
   * Check if supports unlimited values.
   *
   * @return bool
   *   TRUE if supports multiple.
   */
  public function supportsUnlimimted() {
    return $this->getCardinality() === -1;
  }

  /**
   * Check if field is required.
   *
   * @return bool
   *   TRUE if field is required.
   */
  public function isRequired() {
    return $this->definition['required'] === TRUE;
  }

  /**
   * Set required property.
   *
   * @param bool $required
   *   Property value.
   *
   * @return $this
   */
  public function setRequired($required = TRUE) {
    $this->definition['required'] = $required === TRUE;
    return $this;
  }

  /**
   * Check if field is editable.
   *
   * @return bool
   *   TRUE if field is editable.
   */
  public function isEditable() {
    return $this->definition['edit'] === TRUE;
  }

  /**
   * Set editable property.
   *
   * @param bool $editable
   *   Property value.
   *
   * @return $this
   */
  public function setEditable($editable = TRUE) {
    $this->definition['edit'] = $editable === TRUE;
    return $this;
  }

  /**
   * Check if field is hideable.
   *
   * @return bool
   *   TRUE if field is hideable.
   */
  public function isHideable() {
    return $this->definition['hide'] === TRUE;
  }

  /**
   * Set hideable property.
   *
   * @param bool $hideable
   *   Property value.
   *
   * @return $this
   */
  public function setHideable($hideable = TRUE) {
    $this->definition['hide'] = $hideable === TRUE;
    return $this;
  }

  /**
   * Get the modifier target id.
   *
   * @return string
   *   Property value.
   */
  public function getModifierTarget() {
    return !empty($this->definition['modifier']) ? $this->definition['modifier'] : $this->getName();
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
   * Has default property.
   *
   * @return bool
   *   TRUE if has property.
   */
  public function hasDefault() {
    return !empty($this->getDefaults());
  }

  /**
   * Get Preview property.
   *
   * @return \Drupal\exo_alchemist\Definition\ExoComponentDefinitionFieldDefault[]
   *   Property value.
   */
  public function getDefaults() {
    return $this->definition['default'];
  }

  /**
   * Return array definition.
   *
   * @return array
   *   Array definition.
   */
  public function getDefaultsAsArray() {
    $defaults = [];
    foreach ($this->getDefaults() as $delta => $default) {
      $defaults[$delta] = $default->toArray();
    }
    return $defaults;
  }

  /**
   * Set Preview property.
   *
   * @param mixed $defaults
   *   Property value.
   *
   * @return $this
   */
  public function setDefaults($defaults) {
    $this->definition['default'] = [];
    if (!empty($defaults)) {
      if (!is_array($defaults)) {
        $defaults = [['value' => $defaults]];
      }
      else {
        // Preview value should be a simple array. If it isn't, we assume we
        // have a complex default value and it needs to be nested.
        $modified_defaults = [];
        foreach ($defaults as $key => $value) {
          if (!is_int($key)) {
            $modified_defaults[] = $defaults;
            break;
          }
          if (!is_array($value)) {
            $modified_defaults[] = ['value' => $value];
          }
          else {
            $modified_defaults[] = $value;
          }
        }
        $defaults = $modified_defaults;
      }
      foreach ($defaults as $delta => $default) {
        $this->setDefault($default, $delta);
      }
    }
    return $this;
  }

  /**
   * Set Preview property.
   *
   * @param array $values
   *   Property value.
   * @param int $delta
   *   The delta of the default.
   *
   * @return $this
   */
  public function setDefault(array $values, $delta = 0) {
    $this->definition['default'][$delta] = new ExoComponentDefinitionFieldDefault($this, $values);
    return $this;
  }

  /**
   * Set default property value on all available deltas.
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
   */
  public function setDefaultValueOnAll($parents = [], $value = NULL, $force = FALSE) {
    foreach ($this->getDefaults() as $default) {
      if (!$default->getValue($parents)) {
        $default->setValue($parents, $value, $force);
      }
    }
  }

  /**
   * Determines whether all defaults contains the requested property.
   *
   * @param mixed $parents
   *   An array of parent keys, starting with the outermost key.
   *
   * @see NestedArray::unsetValue()
   * @see NestedArray::getValue()
   */
  public function hasDefaultPropertyOnAll($parents = []) {
    foreach ($this->getDefaults() as $default) {
      if (!$default->keyExists($parents)) {
        return FALSE;
      }
    }
    return TRUE;
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

  /**
   * Adds a dependent.
   *
   * @param string $type
   *   Type of dependent being added: 'module', 'theme', 'config', 'content'.
   * @param string $name
   *   If $type is 'module' or 'theme', the name of the module or theme. If
   *   $type is 'config' or 'content', the result of
   *   EntityInterface::getConfigDependencyName().
   *
   * @see \Drupal\Core\Entity\EntityInterface::getConfigDependencyName()
   *
   * @return $this
   */
  public function addDependent($type, $name) {
    if (empty($this->dependents[$type])) {
      $this->dependents[$type] = [$name];
      if (count($this->dependents) > 1) {
        // Ensure a consistent order of type keys.
        ksort($this->dependents);
      }
    }
    elseif (!in_array($name, $this->dependents[$type])) {
      $this->dependents[$type][] = $name;
      // Ensure a consistent order of dependent names.
      sort($this->dependents[$type], SORT_FLAG_CASE);
    }
    return $this;
  }

  /**
   * Adds multiple dependents.
   *
   * @param array $dependents
   *   An array of dependents keyed by the type of dependent. One example:
   *   @code
   *   array(
   *     'module' => array(
   *       'node',
   *       'field',
   *       'image',
   *     ),
   *   );
   *   @endcode
   *
   * @see \Drupal\Core\Entity\DependencyTrait::addDependent
   */
  public function addDependents(array $dependents) {
    foreach ($dependents as $dependent_type => $list) {
      foreach ($list as $name) {
        $this->addDependent($dependent_type, $name);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependents() {
    return $this->dependents;
  }

}
