<?php

namespace Drupal\exo_alchemist;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\Display\EntityFormDisplayInterface;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\exo_alchemist\Definition\ExoComponentDefinition;
use Drupal\exo_alchemist\Definition\ExoComponentDefinitionModifierProperty;
use Drupal\exo_alchemist\Plugin\ExoComponentPropertyAsClassInterface;
use Drupal\exo_alchemist\Plugin\ExoComponentPropertyInterface;
use Drupal\exo_alchemist\Plugin\ExoComponentPropertyOptionsInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Provides the Component Property plugin manager.
 */
class ExoComponentPropertyManager extends DefaultPluginManager implements ExoComponentContextInterface {

  use StringTranslationTrait;
  use ExoComponentContextTrait;

  /**
   * The entity type to use as component entities.
   */
  const MODIFIERS_FIELD_NAME = 'exo_modifiers';

  /**
   * Constructs a new ExoComponentPropertyManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/ExoComponentProperty', $namespaces, $module_handler, 'Drupal\exo_alchemist\Plugin\ExoComponentPropertyInterface', 'Drupal\exo_alchemist\Annotation\ExoComponentProperty');
    $this->alterInfo('exo_alchemist_exo_component_property_info');
    $this->setCacheBackend($cache_backend, 'exo_alchemist_exo_component_property_plugins');
  }

  /**
   * {@inheritdoc}
   */
  public function createPropertyInstance(ExoComponentDefinitionModifierProperty $property, $configuration = []) {
    return parent::createInstance($property->getType(), $configuration + ['property' => $property]);
  }

  /**
   * Process component definition.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   */
  public function processComponentDefinition(ExoComponentDefinition $definition) {
    foreach ($definition->getModifiers() as $modifier) {
      foreach ($modifier->getProperties() as $property) {
        if (!$this->hasDefinition($property->getType())) {
          throw new PluginException(sprintf('eXo Component Property plugin property (%s) does not exist.', $property->getType()));
        }
        $instance = $this->createPropertyInstance($property);
        $default = $property->getDefault();
        if ($instance instanceof ExoComponentPropertyInterface && $instance instanceof ExoComponentPropertyOptionsInterface) {
          if ($default && $default !== '_none') {
            if (!isset($instance->getOptions()[$default])) {
              throw new PluginException(sprintf('eXo Component Property plugin property (%s) is trying to use a default value (%s) that is not allowed.', $property->getLabel(), $default));
            }
          }
        }
        if (!$default) {
          // Make sure option-enabled properties always have a default.
          $property->setDefault($instance->getDefault());
        }
      }
    }
  }

  /**
   * Build content type bundle as defined in definition.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   * @param \Drupal\Core\Entity\Display\EntityFormDisplayInterface $form_display
   *   The form display for the entity type.
   * @param \Drupal\Core\Entity\Display\EntityViewDisplayInterface $view_display
   *   The view display for the entity type.
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $original_definition
   *   The current component definition.
   */
  public function buildEntityType(ExoComponentDefinition $definition, EntityFormDisplayInterface $form_display, EntityViewDisplayInterface $view_display, ExoComponentDefinition $original_definition = NULL) {
    if (!empty($definition->getModifiers())) {
      $entity_type = ExoComponentManager::ENTITY_TYPE;
      $bundle = $definition->safeId();
      $field_name = self::MODIFIERS_FIELD_NAME;

      // Storage config.
      $field_storage_config = FieldStorageConfig::loadByName($entity_type, $field_name);
      $config = [
        'type' => 'exo_alchemist_map',
        'field_name' => $field_name,
        'entity_type' => $entity_type,
        'cardinality' => 1,
        'translatable' => FALSE,
        'locked' => TRUE,
      ];
      if (empty($field_storage_config)) {
        $field_storage_config = FieldStorageConfig::create($config);
      }
      /** @var \Drupal\field\Entity\FieldStorageConfig $field_storage_config */
      foreach ($config as $key => $value) {
        $field_storage_config->set($key, $value);
      }
      $field_storage_config->save();

      // Field config.
      $field_config = FieldConfig::loadByName($entity_type, $bundle, $field_name);
      $config = [
        'field_storage' => $field_storage_config,
        'bundle' => $bundle,
        'label' => 'Modifiers',
        'description' => 'A serialized data store of modifiers and properties.',
        'required' => FALSE,
        'locked' => TRUE,
      ];
      if (empty($field_config)) {
        $field_config = FieldConfig::create($config);
      }
      /** @var \Drupal\field\Entity\FieldConfig $field_config */
      foreach ($config as $key => $value) {
        $field_config->set($key, $value);
      }
      $field_config->save();
    }
  }

  /**
   * Get attribute info.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   */
  public function getAttributeInfo(ExoComponentDefinition $definition) {
    $info = [];
    foreach ($definition->getModifiers() as $modifier) {
      foreach ($modifier->getProperties() as $property) {
        $instance = $this->createPropertyInstance($property);
        if ($instance instanceof ExoComponentPropertyAsClassInterface) {
          $info += [
            $modifier->getName() => [
              'label' => $this->t('Modifier: %label', ['%label' => $modifier->getLabel()]),
            ],
          ];
          $first = TRUE;
          foreach ($instance->getFormattedOptions() as $key => $value) {
            if ($key !== '_none') {
              $info[$modifier->getName()]['properties'][$value] = $first ? t('Classes used by the %label property.', ['%label' => $property->getlabel()]) : '-';
              $first = FALSE;
            }
          }
        }
      }
    }
    return $info;
  }

  /**
   * Populate content entity.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity to populate.
   * @param array $modifier_names
   *   An array of modifier names. If provided, will only reset those modifiers.
   */
  public function populateEntity(ExoComponentDefinition $definition, ContentEntityInterface $entity, array $modifier_names = []) {
    $value = [];
    if (($modifiers = $definition->getModifiers()) && $entity->hasField(self::MODIFIERS_FIELD_NAME)) {
      $original_value = !$entity->get(self::MODIFIERS_FIELD_NAME)->isEmpty() ? $entity->get(self::MODIFIERS_FIELD_NAME)->first()->value : [];
      foreach ($modifiers as $modifier) {
        if (!empty($modifier_names) && !in_array($modifier->getName(), $modifier_names)) {
          if (isset($original_value[$modifier->getname()])) {
            $value[$modifier->getName()] = $original_value[$modifier->getname()];
          }
        }
        else {
          foreach ($modifier->getProperties() as $property) {
            $value[$modifier->getName()][$property->getName()] = $property->getDefault();
          }
        }
      }
      $entity->get(self::MODIFIERS_FIELD_NAME)->setValue(['value' => $value]);
    }
    return $entity;
  }

  /**
   * Get property info.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   */
  public function getPropertyInfo(ExoComponentDefinition $definition) {
    $info = [];
    if ($modifiers = $definition->getModifiers()) {
      foreach ($modifiers as $modifier) {
        if ($modifier->getName() !== '_global') {
          $info[self::modifierNameToKey($modifier->getName())] = [
            'label' => $this->t('Modifier: %label', ['%label' => $modifier->getLabel()]),
            'properties' => [
              self::modifierNameToKey($modifier->getName()) => $this->t('Modifier attributes.'),
            ],
          ];
        }
      }
    }
    return $info;
  }

  /**
   * View content entity for definition as values.
   *
   * Values are broken out this way so sequence and other nested fields can
   * access the raw values before they are turned into attributes.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   * @param array $values
   *   The values array.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity.
   * @param \Drupal\Core\Plugin\Context\Context[] $contexts
   *   An array of current contexts.
   */
  public function viewEntityValues(ExoComponentDefinition $definition, array &$values, ContentEntityInterface $entity, array $contexts) {
    $modifiers = $this->getModifierAttributes($definition, $entity, $contexts);
    if (!empty($modifiers)) {
      if ($this->isLayoutBuilder($contexts)) {
        $values['#content_attributes']['class'][] = 'exo-modifier';
      }
      foreach ($modifiers as $modifier_name => $attribute_array) {
        $values['#attached']['library'][] = 'exo_alchemist/property';
        // Global modifiers.
        if ($modifier_name == '_global') {
          $values['#wrapper_attributes'] = NestedArray::mergeDeep($values['#wrapper_attributes'], $attribute_array);
          continue;
        }
        $key = self::modifierNameToKey($modifier_name);
        $values += [
          $key => new ExoComponentAttribute(),
        ];
        $values[$key] = new ExoComponentAttribute(NestedArray::mergeDeep($values[$key]->toArray(), $attribute_array));
      }
    }
  }

  /**
   * Get the modifier attributes array given an entity.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity.
   * @param \Drupal\Core\Plugin\Context\Context[] $contexts
   *   An array of current contexts.
   *
   * @return array
   *   An array of attributes.
   */
  public function getModifierAttributes(ExoComponentDefinition $definition, ContentEntityInterface $entity, array $contexts) {
    $modifier_attributes = [];
    if (($modifiers = $definition->getModifiers()) && $entity->hasField(self::MODIFIERS_FIELD_NAME)) {
      $values = self::getEntityModifierValues($entity);
      foreach ($modifiers as $modifier) {
        $modifier_name = $modifier->getName();
        $modifier_attributes[$modifier_name] = [];
        if ($this->isLayoutBuilder($contexts) || $this->isPreview($contexts) || !empty($entity->exoAlchemistPreview)) {
          $modifier_attributes[$modifier_name]['data-exo-alchemist-modifier'] = $modifier_name . '_' . $entity->uuid();
          $modifier_attributes[$modifier_name]['class'][] = 'exo-modifier';
        }
        foreach ($modifier->getProperties() as $property) {
          $value = isset($values[$modifier_name][$property->getName()]) ? $values[$modifier_name][$property->getName()] : NULL;
          if (!empty($value) && $value !== '_none') {
            $instance = $this->createPropertyInstance($property, [
              'value' => $value,
            ]);
            $modifier_attributes[$modifier_name] = NestedArray::mergeDeep($modifier_attributes[$modifier_name], $instance->asAttributeArray());
          }
        }
      }
    }
    return $modifier_attributes;
  }

  /**
   * Convert a modifier name to its render array key.
   *
   * @param string $modifier_name
   *   The modifier name.
   */
  public static function modifierNameToKey($modifier_name) {
    return 'modifier_' . $modifier_name;
  }

  /**
   * Get modifier values for a given entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity.
   * @param string $modifier_name
   *   An optional modifier name.
   *
   * @return array
   *   The modifier values.
   */
  public static function getEntityModifierValues(ContentEntityInterface $entity, $modifier_name = NULL) {
    $values = !$entity->get(self::MODIFIERS_FIELD_NAME)->isEmpty() ? $entity->get(self::MODIFIERS_FIELD_NAME)->first()->value : [];
    if (!empty($modifier_name)) {
      return isset($values[$modifier_name]) ? $values[$modifier_name] : [];
    }
    return $values;
  }

  /**
   * Build the modifier form.
   *
   * @param array $form
   *   The form to add the modifier fields to.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity.
   */
  public function buildForm(array &$form, FormStateInterface $form_state, ExoComponentDefinition $definition, ContentEntityInterface $entity) {
    if (($modifiers = $definition->getModifiers()) && $entity->hasField(self::MODIFIERS_FIELD_NAME)) {
      $form['modifiers'] = isset($form['modifiers']) ? $form['modifiers'] : [];
      $form['modifiers'] = $form['modifiers'] + [
        '#type' => 'container',
        '#tree' => TRUE,
        '#attributes' => [
          'class' => ['exo-alchemist-appearance-form'],
        ],
      ];
      $form['modifiers']['#attached']['library'][] = 'exo_alchemist/admin.appearance';
      $modifier_values = !$entity->get(self::MODIFIERS_FIELD_NAME)->isEmpty() ? $entity->get(self::MODIFIERS_FIELD_NAME)->first()->value : [];
      foreach ($modifiers as $modifier) {
        $modifier_name = $modifier->getName();
        $modifier_group = $modifier->getGroup();
        $form['modifiers'][$modifier_name] = [
          '#type' => 'fieldset',
          '#title' => $modifier->getLabel(),
          '#description' => $modifier->getDescription(),
        ];
        if ($modifier_group) {
          if (!$modifier->getDescription()) {
            $form['modifiers'][$modifier_name]['#type'] = 'container';
          }
          $form['modifiers'][$modifier_name]['#group'] = 'modifiers][' . $modifier_group;
          $form['modifiers'][$modifier_name]['#weight'] = 100;
        }
        foreach ($modifier->getProperties() as $property) {
          $name = $property->getName();
          if ($this->hasDefinition($property->getType())) {
            $instance = $this->createPropertyInstance($property, [
              'value' => isset($modifier_values[$modifier_name][$name]) ? $modifier_values[$modifier_name][$name] : NULL,
            ]);
            $plugin_form = [
              '#tree' => TRUE,
              '#title' => $property->getLabel(),
              '#description' => $property->getDescription(),
            ];
            $form['modifiers'][$modifier_name][$name] = $instance->buildConfigurationForm($plugin_form, $form_state);
          }
        }
      }
    }
  }

}
