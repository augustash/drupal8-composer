<?php

namespace Drupal\exo_alchemist\Definition;

use Drupal\Component\Plugin\Definition\PluginDefinition;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\exo\Shared\ExoArrayAccessDefinitionTrait;

/**
 * Class ExoComponentDefinition.
 *
 * @package Drupal\exo_alchemist\Definition
 */
class ExoComponentDefinition extends PluginDefinition implements \ArrayAccess {

  use DependencySerializationTrait;
  use ExoArrayAccessDefinitionTrait;

  /**
   * Component prefix.
   */
  const PATTERN_PREFIX = 'exo_component_';

  /**
   * Provides default values for all exo_component plugins.
   *
   * @var array
   */
  protected $definition = [
    // Add required and optional plugin properties.
    'id' => '',
    'name' => '',
    'label' => '',
    'description' => '',
    'version' => '0.0.0',
    'ignore' => FALSE,
    'hidden' => FALSE,
    'modifier' => '',
    'modifiers' => [],
    'modifier_globals' => [],
    'fields' => [],
    'js' => [],
    'css' => [],
    'path' => '',
    'template' => '',
    'theme hook' => '',
    'thumbnail' => '',
    'category' => '',
    'provider' => '',
    'animations' => [],
    'additional' => [],
  ];

  /**
   * The object's dependents.
   *
   * @var array
   */
  protected $dependents = [];

  /**
   * Provides TRUE if definition is installed.
   *
   * @var bool
   */
  protected $installed = FALSE;

  /**
   * Provides TRUE if definition is installed but no longer available.
   *
   * @var bool
   */
  protected $missing = FALSE;

  /**
   * The render parents.
   *
   * @var string[]
   */
  protected $parents = [];

  /**
   * The default component modifiers for each component.
   *
   * @var array
   */
  protected static $globalModifiers = [
    'color_bg' => [
      'type' => 'exo_theme_color',
      'label' => 'Background Color',
      'status' => TRUE,
    ],
    'invert' => [
      'type' => 'invert',
      'label' => 'Invert Colors',
      'status' => TRUE,
    ],
    'text_shadow' => [
      'type' => 'text_shadow',
      'label' => 'Text Shadow',
      'status' => FALSE,
    ],
    'overlay' => [
      'type' => 'overlay',
      'label' => 'Overlay',
      'status' => TRUE,
    ],
    'height' => [
      'type' => 'height',
      'label' => 'Height',
      'status' => TRUE,
    ],
    'margin_v' => [
      'type' => 'margin_vertical',
      'label' => 'Margin',
      'description' => 'Margin is the space between components.',
      'status' => TRUE,
    ],
    'padding_v' => [
      'type' => 'padding_vertical',
      'label' => 'Padding',
      'description' => 'Padding is the space between a component and its contents.',
      'status' => TRUE,
    ],
    'containment' => [
      'type' => 'containment',
      'label' => 'Containment',
      'status' => TRUE,
    ],
    'containment_content' => [
      'type' => 'containment',
      'label' => 'Content Containment',
      'status' => FALSE,
    ],
    'border_radius' => [
      'type' => 'border_radius',
      'label' => 'Border Radius',
      'status' => TRUE,
    ],
  ];

  /**
   * ExoComponentDefinition constructor.
   */
  public function __construct(array $definition = []) {
    foreach ($definition as $name => $value) {
      if (array_key_exists($name, $this->definition)) {
        $this->definition[$name] = $value;
      }
      else {
        $this->definition['additional'][$name] = $value;
      }
    }
    $this->id = $this->definition['id'];
    $this->provider = $this->definition['provider'];
    $this->setThemeHook(self::PATTERN_PREFIX . $this->id());
    $this->setFields($this->definition['fields']);
    $this->setModifiers($this->definition['modifiers']);
    $this->setAnimations($this->definition['animations']);
  }

  /**
   * Return array definition.
   *
   * @return array
   *   Array definition.
   */
  public function toArray() {
    $definition = $this->definition;
    $definition['label'] = (string) $definition['label'];
    foreach ($this->getFields() as $field) {
      $definition['fields'][$field->getName()] = $field->toArray();
    }
    foreach ($this->getModifiers() as $modifier) {
      $definition['modifiers'][$modifier->getName()] = $modifier->toArray();
    }
    foreach ($this->getAnimations() as $animation) {
      $definition['animations'][$animation->getName()] = $animation->toArray();
    }
    return $definition;
  }

  /**
   * A string that is 32 characters long and can be used for entity ids.
   *
   * @return string
   *   A 32 character string.
   */
  public function safeId() {
    return 'exo_' . substr(hash('sha256', $this->id()), 0, 28);
  }

  /**
   * Getter.
   *
   * @return string
   *   Property value.
   */
  public function getName() {
    return $this->definition['name'];
  }

  /**
   * Getter.
   *
   * @return string
   *   Property value.
   */
  public function getLabel() {
    return $this->definition['label'];
  }

  /**
   * Setter.
   *
   * @param mixed $label
   *   Property value.
   *
   * @return $this
   */
  public function setLabel($label) {
    $this->definition['label'] = $label;
    return $this;
  }

  /**
   * Getter.
   *
   * @return mixed
   *   Property value.
   */
  public function getVersion() {
    return $this->definition['version'];
  }

  /**
   * Getter.
   *
   * @return mixed
   *   Property value.
   */
  public function getCategory() {
    return $this->definition['category'];
  }

  /**
   * Getter.
   *
   * @return mixed
   *   Property value.
   */
  public function getPath() {
    return $this->definition['path'];
  }

  /**
   * Setter.
   *
   * @param mixed $path
   *   Property value.
   *
   * @return $this
   */
  public function setPath($path) {
    $this->definition['path'] = $path;
    return $this;
  }

  /**
   * Setter.
   *
   * @param array $fields
   *   Property value.
   *
   * @return $this
   */
  public function setFields(array $fields) {
    foreach ($fields as $name => $value) {
      $field = $this->getFieldDefinition($name, $value);
      $this->definition['fields'][$field->getName()] = $field;
    }
    return $this;
  }

  /**
   * Getter.
   *
   * @return ExoComponentDefinitionField[]
   *   Property value.
   */
  public function getFields() {
    return $this->definition['fields'];
  }

  /**
   * Get field as options.
   *
   * @return array
   *   Fields as select options.
   */
  public function getFieldsAsOptions() {
    $options = [];
    foreach ($this->getFields() as $field) {
      $options[$field->getName()] = $field->getLabel();
    }
    return $options;
  }

  /**
   * Get field.
   *
   * @param string $name
   *   Field name.
   *
   * @return ExoComponentDefinitionField|null
   *   Definition field.
   */
  public function getField($name) {
    return $this->hasField($name) ? $this->definition['fields'][$name] : NULL;
  }

  /**
   * Get field by its safe id.
   *
   * @param string $safe_id
   *   Safe id. This is used as the field name.
   *
   * @return ExoComponentDefinitionField|null
   *   Definition field.
   */
  public function getFieldBySafeId($safe_id) {
    foreach ($this->getFields() as $field) {
      if ($field->safeId() == $safe_id) {
        return $field;
      }
    }
    return NULL;
  }

  /**
   * Check whereas field exists.
   *
   * @param string $name
   *   Field name.
   *
   * @return bool
   *   Whereas field exists
   */
  public function hasField($name) {
    return isset($this->definition['fields'][$name]);
  }

  /**
   * Set field.
   *
   * @param string $name
   *   Field name.
   * @param string $label
   *   Field label.
   *
   * @return $this
   */
  public function setField($name, $label) {
    $this->definition['fields'][$name] = $this->getFieldDefinition($name, $label);
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
   * Setter.
   *
   * @param mixed $modifiers
   *   Property value.
   *
   * @return $this
   */
  public function setModifiers($modifiers) {
    $this->definition['modifiers'] = [];
    if ($this->definition['modifier_globals'] !== FALSE) {
      $globals = [
        'label' => 'Global',
        'properties' => self::getGlobalModifiers(),
      ];
      if (!empty($this->definition['modifier_globals']['properties'])) {
        $globals['properties'] = $this->definition['modifier_globals']['properties'];
      }
      if (!empty($this->definition['modifier_globals']['defaults'])) {
        foreach ($this->definition['modifier_globals']['defaults'] as $property => $default) {
          if (isset($globals['properties'][$property])) {
            $globals['properties'][$property]['default'] = $default;
          }
        }
      }
      if (!empty($this->definition['modifier_globals']['status'])) {
        foreach ($this->definition['modifier_globals']['status'] as $property => $value) {
          if (isset($globals['properties'][$property])) {
            $globals['properties'][$property]['status'] = !empty($value);
          }
        }
      }
      if (!empty($this->definition['modifier_globals']['extend'])) {
        $globals['properties'] = NestedArray::mergeDeep($globals['properties'], $this->definition['modifier_globals']['extend']);
      }
      // Globals use a status key to allow components to easily enable/disable
      // them without having to redefine them.
      foreach ($globals['properties'] as $property => &$info) {
        if (isset($info['status']) && empty($info['status'])) {
          unset($globals['properties'][$property]);
        }
        // Status is not a real property attribute and is only used for globals.
        unset($info['status']);
      }
      $modifiers += [
        '_global' => $globals,
      ];
    }
    foreach ($modifiers as $name => $value) {
      if ($value === FALSE) {
        continue;
      }
      $modifier = $this->getModifierDefinition($name, $value);
      $this->definition['modifiers'][$modifier->getName()] = $modifier;
    }
    ksort($this->definition['modifiers']);
    return $this;
  }

  /**
   * Getter.
   *
   * @return ExoComponentDefinitionModifier[]
   *   Property value.
   */
  public function getModifiers() {
    return $this->definition['modifiers'];
  }

  /**
   * Get modifier.
   *
   * @param string $name
   *   Modifier name.
   *
   * @return ExoComponentDefinitionModifier|null
   *   Definition modifier.
   */
  public function getModifier($name) {
    return $this->hasModifier($name) ? $this->definition['modifiers'][$name] : NULL;
  }

  /**
   * Get global modifier.
   *
   * @return ExoComponentDefinitionModifier|null
   *   Definition modifier.
   */
  public function getGlobalModifier() {
    return $this->getModifier('_global');
  }

  /**
   * Check whereas modifier exists.
   *
   * @param string $name
   *   Field name.
   *
   * @return bool
   *   Whereas field exists
   */
  public function hasModifier($name) {
    return isset($this->definition['modifiers'][$name]);
  }

  /**
   * Set animations.
   *
   * @param mixed $animations
   *   Property value.
   *
   * @return $this
   */
  public function setAnimations($animations) {
    foreach ($animations as $name => $value) {
      if ($value === FALSE) {
        continue;
      }
      $animation = $this->getAnimationDefinition($name, $value);
      $this->definition['animations'][$animation->getName()] = $animation;
    }
    return $this;
  }

  /**
   * Getter.
   *
   * @return ExoComponentDefinitionAnimation[]
   *   Property value.
   */
  public function getAnimations() {
    return $this->definition['animations'];
  }

  /**
   * Getter.
   *
   * @return mixed
   *   Property value.
   */
  public function getCss() {
    return $this->definition['css'];
  }

  /**
   * Getter.
   *
   * @return mixed
   *   Property value.
   */
  public function getJs() {
    return $this->definition['js'];
  }

  /**
   * Getter.
   *
   * @return bool
   *   Whereas has library.
   */
  public function hasLibrary() {
    return !empty($this->getCss()) || !empty($this->getJs());
  }

  /**
   * Getter.
   *
   * @return string
   *   The library id.
   */
  public function getLibraryId() {
    return 'exo_component.' . $this->id();
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
   * Setter.
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
   * Getter.
   *
   * @return string
   *   Property value.
   */
  public function getTemplate() {
    return $this->definition['template'];
  }

  /**
   * Setter.
   *
   * @param string $template
   *   Property value.
   *
   * @return $this
   */
  public function setTemplate($template) {
    $this->definition['template'] = $template;
    return $this;
  }

  /**
   * Getter.
   *
   * @return mixed
   *   Property value.
   */
  public function getThumbnailSource() {
    return $this->definition['thumbnail'];
  }

  /**
   * Getter.
   *
   * @return mixed
   *   Property value.
   */
  public function getThumbnailDirectory() {
    return 'public://exo-alchemist/' . $this->id();
  }

  /**
   * Getter.
   *
   * @return mixed
   *   Property value.
   */
  public function getThumbnailFilename() {
    $info = pathinfo($this->getThumbnailSource());
    return $info['basename'];
  }

  /**
   * Getter.
   *
   * @return mixed
   *   Property value.
   */
  public function getThumbnailUri() {
    return $this->getThumbnailSource() ? $this->getThumbnailDirectory() . '/' . $this->getThumbnailFilename() : NULL;
  }

  /**
   * Setter.
   *
   * @param mixed $thumbnail
   *   Property value.
   *
   * @return $this
   */
  public function setThumbnail($thumbnail) {
    $this->definition['thumbnail'] = $thumbnail;
    return $this;
  }

  /**
   * Getter.
   *
   * @return bool
   *   Whereas has thumbnail.
   */
  public function hasThumbnail() {
    return !empty($this->definition['thumbnail']);
  }

  /**
   * Getter.
   *
   * @return string
   *   Property value.
   */
  public function getThemeHook() {
    return $this->definition['theme hook'];
  }

  /**
   * Setter.
   *
   * @param string $theme_hook
   *   Property value.
   *
   * @return $this
   */
  public function setThemeHook($theme_hook) {
    $this->definition['theme hook'] = $theme_hook;
    return $this;
  }

  /**
   * Getter.
   *
   * @return mixed
   *   Property value.
   */
  public function isHidden() {
    return !empty($this->definition['hidden']);
  }

  /**
   * Get Provider property.
   *
   * @return string
   *   Property value.
   */
  public function getProvider() {
    return $this->definition['provider'];
  }

  /**
   * Setter.
   *
   * @param mixed $provider
   *   Property value.
   *
   * @return $this
   */
  public function setProvider($provider) {
    $this->definition['provider'] = $provider;
    return $this;
  }

  /**
   * Get Deriver property.
   *
   * @return mixed
   *   Property value.
   */
  public function getDeriver() {
    return $this->definition['deriver'];
  }

  /**
   * Set Deriver property.
   *
   * @param mixed $deriver
   *   Property value.
   *
   * @return $this
   */
  public function setDeriver($deriver) {
    $this->definition['deriver'] = $deriver;
    return $this;
  }

  /**
   * Add an item as a parent.
   *
   * @param string $value
   *   A string that will be appending to the parents.
   *
   * @return $this
   */
  public function addParent($value) {
    $this->parents[] = $value;
    return $this;
  }

  /**
   * Add an item as a parent.
   *
   * @param array $parents
   *   An array of parent keys.
   *
   * @return $this
   */
  public function setParents(array $parents) {
    $this->parents = $parents;
    return $this;
  }

  /**
   * Get the parents of this component.
   *
   * @return array
   *   An array of parent keys.
   */
  public function getParents() {
    return $this->parents;
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

  /**
   * Set Class property.
   *
   * @param string $class
   *   Property value.
   *
   * @return $this
   */
  public function setClass($class) {
    parent::setClass($class);
    $this->definition['class'] = $class;
    return $this;
  }

  /**
   * Get Class property.
   *
   * @return string
   *   Property value.
   */
  public function getClass() {
    return $this->definition['class'];
  }

  /**
   * Factory method: create a new field definition.
   *
   * @param string $name
   *   Field name.
   * @param string $value
   *   Field value.
   *
   * @return \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField
   *   Definition instance.
   */
  public function getFieldDefinition($name, $value) {
    $field = new ExoComponentDefinitionField($name, $value);
    $field->setComponent($this);
    return $field;
  }

  /**
   * Factory method: create a new modifier definition.
   *
   * @param string $name
   *   Field name.
   * @param string $value
   *   Field value.
   *
   * @return \Drupal\exo_alchemist\Definition\ExoComponentDefinitionModifier
   *   Definition instance.
   */
  public function getModifierDefinition($name, $value) {
    $field = new ExoComponentDefinitionModifier($name, $value);
    $field->setComponent($this);
    return $field;
  }

  /**
   * Factory method: create a new animation definition.
   *
   * @param string $name
   *   Field name.
   * @param array $value
   *   Field value.
   *
   * @return \Drupal\exo_alchemist\Definition\ExoComponentDefinitionAnimation
   *   Definition instance.
   */
  public function getAnimationDefinition($name, array $value) {
    $animation = new ExoComponentDefinitionAnimation($name, $value);
    $animation->setComponent($this);
    return $animation;
  }

  /**
   * Is installed.
   *
   * @return bool
   *   Property value.
   */
  public function isInstalled() {
    return $this->installed === TRUE;
  }

  /**
   * Setter.
   *
   * @param mixed $value
   *   Property value.
   *
   * @return $this
   */
  public function setInstalled($value = TRUE) {
    $this->installed = $value == TRUE;
    return $this;
  }

  /**
   * Is missing.
   *
   * @return bool
   *   Property value.
   */
  public function isMissing() {
    return $this->missing === TRUE;
  }

  /**
   * Setter.
   *
   * @param mixed $value
   *   Property value.
   *
   * @return $this
   */
  public function setMissing($value = TRUE) {
    $this->missing = $value == TRUE;
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
    foreach ($this->getFields() as $field) {
      $this->addDependents($field->calculateDependents());
    }
    return $this->dependents;
  }

  /**
   * Get the global modifiers.
   */
  public static function getGlobalModifiers() {
    return self::$globalModifiers;
  }

}
