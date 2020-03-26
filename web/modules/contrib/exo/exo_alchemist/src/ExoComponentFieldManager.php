<?php

namespace Drupal\exo_alchemist;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\Display\EntityFormDisplayInterface;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\exo_alchemist\Definition\ExoComponentDefinition;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Render\Element;
use Drupal\exo_alchemist\Definition\ExoComponentDefinitionField;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldComputedInterface;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldFieldableInterface;

/**
 * Provides the Component Field plugin manager.
 */
class ExoComponentFieldManager extends DefaultPluginManager {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  public $entityTypeManager;

  /**
   * An array of instances.
   *
   * @var \Drupal\exo_alchemist\Plugin\ExoComponentFieldInterface[]
   */
  protected $instances = [];

  /**
   * {@inheritdoc}
   */
  protected $defaults = [
    'storage' => [],
    'field' => [],
    'widget' => [],
    'formatter' => [],
    'properties' => [],
  ];

  /**
   * Constructs a new Entity plugin manager.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The entity last installed schema repository.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(\Traversable $namespaces, EntityTypeManager $entity_type_manager, CacheBackendInterface $cache, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/ExoComponentField', $namespaces, $module_handler, 'Drupal\exo_alchemist\Plugin\ExoComponentFieldInterface', 'Drupal\exo_alchemist\Annotation\ExoComponentField');
    $this->alterInfo('exo_component_field_info');
    $this->setCacheBackend($cache, 'exo_component_field_info', ['exo_component_field_info']);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Get an instance of a plugin.
   *
   * If an instance of a given plugin id has not yet been created, a new one
   * will be created.
   *
   * This works because our plugins do not support configuration at this time.
   *
   * @param string $plugin_id
   *   The plugin id.
   * @param bool $exception_on_invalid
   *   (optional) If TRUE, an invalid plugin ID will throw an exception.
   *
   * @return \Drupal\exo_alchemist\Plugin\ExoComponentFieldInterface
   *   The component field.
   */
  public function loadInstance($plugin_id, $exception_on_invalid = TRUE) {
    if (!isset($this->instances[$plugin_id])) {
      if ($exception_on_invalid || $this->hasDefinition($plugin_id)) {
        $this->instances[$plugin_id] = $this->createInstance($plugin_id);
      }
      else {
        $this->instances[$plugin_id] = NULL;
      }
    }
    return $this->instances[$plugin_id];
  }

  /**
   * Process component definition.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   */
  public function processComponentDefinition(ExoComponentDefinition $definition) {
    foreach ($definition->getFields() as $field) {
      if (!$this->hasDefinition($field->getType())) {
        throw new PluginException(sprintf('eXo Component Field plugin property (%s) does not exist.', $field->getType()));
      }
      if ($field->isRequired() && empty($field->getPreviews())) {
        throw new PluginException(sprintf('eXo Component Field plugin property (%s) is required but does not supply a preview.', $field->getType()));
      }
      $this->loadInstance($field->getType())->componentProcessDefinition($field);
    }
  }

  /**
   * Get the field changes given a definition.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $to_definition
   *   The component definition.
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition|null $from_definition
   *   The component definition.
   *
   * @return array
   *   An array containing ['add' => [], 'update' => [], 'remove' => []].
   */
  public function getEntityBundleFieldChanges(ExoComponentDefinition $to_definition, ExoComponentDefinition $from_definition = NULL) {
    $changes = [
      'add' => [],
      'update' => [],
      'remove' => [],
    ];
    $to_fields = $to_definition->getFields();
    if (!$from_definition) {
      $changes['add'] = $to_fields;
    }
    else {
      $from_fields = $from_definition->getFields();
      $changes['add'] = array_diff_key($to_fields, $from_fields);
      $changes['update'] = array_filter(array_intersect_key($to_fields, $from_fields), function ($field) use ($from_fields) {
        return $field->toArray() !== $from_fields[$field->getName()]->toArray();
      });
      $changes['remove'] = array_diff_key($from_fields, $to_fields);
    }
    return $changes;
  }

  /**
   * Install the config entity used as the entity type.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   * @param \Drupal\Core\Config\Entity\ConfigEntityInterface $entity
   *   The config entity used as the entity type.
   */
  public function installEntityType(ExoComponentDefinition $definition, ConfigEntityInterface $entity) {
    foreach ($definition->getFields() as $field) {
      $component_field = $this->loadInstance($field->getType());
      $component_field->componentInstallEntityType($field, $entity);
    }
  }

  /**
   * Update the config entity used as the entity type.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   * @param \Drupal\Core\Config\Entity\ConfigEntityInterface $entity
   *   The config entity used as the entity type.
   */
  public function updateEntityType(ExoComponentDefinition $definition, ConfigEntityInterface $entity) {
    foreach ($definition->getFields() as $field) {
      $component_field = $this->loadInstance($field->getType());
      $component_field->componentUpdateEntityType($field, $entity);
    }
  }

  /**
   * Delete the config entity used as the entity type.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   * @param \Drupal\Core\Config\Entity\ConfigEntityInterface $entity
   *   The config entity used as the entity type.
   */
  public function uninstallEntityType(ExoComponentDefinition $definition, ConfigEntityInterface $entity) {
    foreach ($definition->getFields() as $field) {
      $component_field = $this->loadInstance($field->getType());
      $component_field->componentUninstallEntityType($field, $entity);
    }
  }

  /**
   * Build content type bundle fields as defined in definition.
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
    $changes = $this->getEntityBundleFieldChanges($definition, $original_definition);
    $entity_type = ExoComponentManager::ENTITY_TYPE;
    $bundle = $definition->safeId();
    $fields = $definition->getFields();

    foreach ($changes['remove'] as $key => $field) {
      /** @var \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField $field */
      $field_name = $field->getFieldName();
      $field_config = FieldConfig::loadByName($entity_type, $bundle, $field_name);
      if ($field_config) {
        $entity = \Drupal::service('plugin.manager.exo_component')->loadEntity($definition, TRUE);
        $this->deleteEntityField($field, $entity);
        $field_config->delete();
      }
    }

    $changed = $changes['add'] + $changes['update'];
    if (!empty($changed)) {
      /** @var \Drupal\Core\Entity\Display\EntityFormDisplayInterface $form_display */
      /** @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface $view_display */
      foreach ($changed as $key => $field) {
        /** @var \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField $field */
        if ($this->hasDefinition($field->getType())) {
          $component_field = $this->loadInstance($field->getType());
          if ($component_field instanceof ExoComponentFieldFieldableInterface) {
            $field_name = $field->getFieldName();
            $weight = array_search($field->getName(), array_keys($fields));

            // Storage config.
            $field_storage_config = FieldStorageConfig::loadByName($entity_type, $field_name);
            $config = [
              'field_name' => $field_name,
              'entity_type' => $entity_type,
              'cardinality' => $field->getCardinality(),
              'translatable' => TRUE,
              'locked' => TRUE,
            ] + $component_field->componentStorage($field);
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
              'label' => $field->getLabel(),
              'description' => $field->getDescription(),
              'required' => $field->isRequired(),
              'locked' => TRUE,
            ] + $component_field->componentField($field);
            if (empty($field_config)) {
              $field_config = FieldConfig::create($config);
            }
            /** @var \Drupal\field\Entity\FieldConfig $field_config */
            foreach ($config as $key => $value) {
              $field_config->set($key, $value);
            }
            $field_config->save();

            // Field widget.
            $config = $component_field->componentWidget($field);
            if (!empty($config)) {
              $form_display->setComponent($field_name, $config + [
                'weight' => $weight,
              ]);
            }
            else {
              $form_display->removeComponent($field_name);
            }
            $form_display->removeComponent('info');

            // Field formatter.
            $config = $component_field->componentFormatter($field);
            if (!empty($config)) {
              $view_display->setComponent($field_name, $config + [
                'weight' => $weight,
              ]);
            }
          }
        }
      }
    }
  }

  /**
   * Get property info.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   */
  public function getPropertyInfo(ExoComponentDefinition $definition) {
    $info = [];
    foreach ($definition->getFields() as $key => $field) {

      $component_field = $this->loadInstance($field->getType());
      $properties = [];
      $field_properties = [
        'attributes' => t('Field attributes.'),
      ] + $component_field->componentPropertyInfo($field);
      if ($field->supportsMultiple()) {
        $properties[$field->getName() . '.attributes'] = t('Field group attributes.');
      }
      foreach ($field_properties as $property => $label) {
        $property_parts = [];
        $property_parts[] = $field->getName();
        if ($field->supportsMultiple()) {
          $property_parts[] = 'value.%';
        }
        $property_parts[] = $property;
        $properties[implode('.', $property_parts)] = $label;
      }
      $info[$key] = [
        'label' => $field->getLabel() ,
        'properties' => $properties,
      ];
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
   */
  public function populateEntity(ExoComponentDefinition $definition, ContentEntityInterface $entity) {
    foreach ($definition->getFields() as $field) {
      $this->buildEntityField($field, $entity);
    }
    return $entity;
  }

  /**
   * Called on update while layout building.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity to populate.
   */
  public function onUpdateEntity(ExoComponentDefinition $definition, ContentEntityInterface $entity) {
    foreach ($definition->getFields() as $field) {
      if ($this->hasDefinition($field->getType())) {
        $component_field = $this->loadInstance($field->getType());
        if ($component_field instanceof ExoComponentFieldFieldableInterface) {
          $field_name = $field->getFieldName();
          if ($entity->hasField($field_name)) {
            $component_field->componentPreUpdate($field, $entity->get($field_name));
          }
        }
      }
    }
  }

  /**
   * Build entity field.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField $field
   *   The component definition.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity to populate.
   */
  public function buildEntityField(ExoComponentDefinitionField $field, ContentEntityInterface $entity) {
    if ($this->hasDefinition($field->getType())) {
      $component_field = $this->loadInstance($field->getType());
      if ($component_field instanceof ExoComponentFieldFieldableInterface) {
        $field_name = $field->getFieldName();
        if ($entity->hasField($field_name)) {
          $entity->get($field_name)->setValue($component_field->componentValues($field, $entity->get($field_name)));
        }
      }
    }
  }

  /**
   * Post-save entity fields.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity.
   */
  public function updateEntityFields(ExoComponentDefinition $definition, ContentEntityInterface $entity) {
    foreach ($definition->getFields() as $field) {
      $this->updateEntityField($field, $entity);
    }
  }

  /**
   * Post-save field.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField $field
   *   The component definition.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity.
   */
  public function updateEntityField(ExoComponentDefinitionField $field, ContentEntityInterface $entity) {
    if ($this->hasDefinition($field->getType())) {
      $component_field = $this->loadInstance($field->getType());
      if ($component_field instanceof ExoComponentFieldFieldableInterface) {
        $field_name = $field->getFieldName();
        if ($entity->hasField($field_name)) {
          $component_field->componentUpdate($field, $entity->get($field_name));
        }
      }
    }
  }

  /**
   * Delete entity fields.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity.
   */
  public function deleteEntityFields(ExoComponentDefinition $definition, ContentEntityInterface $entity) {
    foreach ($definition->getFields() as $field) {
      $this->deleteEntityField($field, $entity);
    }
  }

  /**
   * Delete field.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField $field
   *   The component definition.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity.
   */
  public function deleteEntityField(ExoComponentDefinitionField $field, ContentEntityInterface $entity) {
    if ($this->hasDefinition($field->getType())) {
      $component_field = $this->loadInstance($field->getType());
      if ($component_field instanceof ExoComponentFieldFieldableInterface) {
        $field_name = $field->getFieldName();
        if ($entity->hasField($field_name)) {
          $component_field->componentDelete($field, $entity->get($field_name));
        }
      }
    }
  }

  /**
   * Uninstall entity fields.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity.
   */
  public function uninstallEntityFields(ExoComponentDefinition $definition, ContentEntityInterface $entity) {
    foreach ($definition->getFields() as $field) {
      $this->uninstallEntityField($field, $entity);
    }
  }

  /**
   * Uninstall field.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField $field
   *   The component definition.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity.
   */
  public function uninstallEntityField(ExoComponentDefinitionField $field, ContentEntityInterface $entity) {
    if ($this->hasDefinition($field->getType())) {
      $component_field = $this->loadInstance($field->getType());
      if ($component_field instanceof ExoComponentFieldFieldableInterface) {
        $field_name = $field->getFieldName();
        if ($entity->hasField($field_name)) {
          $component_field->componentUninstall($field, $entity->get($field_name));
        }
      }
    }
  }

  /**
   * Clone content entity for fields.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity.
   */
  public function cloneEntityFields(ExoComponentDefinition $definition, ContentEntityInterface $entity) {
    foreach ($definition->getFields() as $field) {
      $this->cloneEntityField($field, $entity);
    }
  }

  /**
   * Clone field.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField $field
   *   The component definition.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity.
   */
  public function cloneEntityField(ExoComponentDefinitionField $field, ContentEntityInterface $entity) {
    if ($this->hasDefinition($field->getType())) {
      $component_field = $this->loadInstance($field->getType());
      if ($component_field instanceof ExoComponentFieldFieldableInterface) {
        $field_name = $field->getFieldName();
        if ($entity->hasField($field_name)) {
          $component_field->componentClone($field, $entity->get($field_name));
        }
      }
    }
  }

  /**
   * Restore content entity values for fields.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity.
   */
  public function restoreEntityFields(ExoComponentDefinition $definition, ContentEntityInterface $entity) {
    foreach ($definition->getFields() as $field) {
      $this->restoreEntityField($field, $entity);
    }
  }

  /**
   * Restore field.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField $field
   *   The component definition.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity.
   */
  public function restoreEntityField(ExoComponentDefinitionField $field, ContentEntityInterface $entity) {
    if ($this->hasDefinition($field->getType())) {
      $component_field = $this->loadInstance($field->getType());
      if ($component_field instanceof ExoComponentFieldFieldableInterface) {
        $field_name = $field->getFieldName();
        if ($entity->hasField($field_name)) {
          $value = $component_field->componentRestore($field, $entity->get($field_name));
          if ($value) {
            $entity->get($field_name)->setValue($value);
          }
        }
      }
    }
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
   * @param string $is_layout_builder
   *   TRUE if we are in layout builder mode.
   */
  public function viewEntityValues(ExoComponentDefinition $definition, array &$values, ContentEntityInterface $entity, $is_layout_builder) {
    foreach ($definition->getFields() as $field) {
      if ($this->hasDefinition($field->getType())) {
        $component_field = $this->loadInstance($field->getType());
        $field_name = $field->getFieldName();
        $attributes = [
          'class' => [
            'type--' . Html::getClass($field->getType()),
            'name--' . Html::getClass($field->getName()),
          ],
        ];

        $field_build = [];
        if ($component_field instanceof ExoComponentFieldFieldableInterface) {
          if ($entity->hasField($field_name)) {
            $field_build = $component_field->componentView($field, $entity->get($field_name), $is_layout_builder);
            if ($is_layout_builder) {
              $values['#attached']['drupalSettings']['exoAlchemist']['fields'][$field_name] = [
                'label' => $field->getLabel(),
                'cardinality' => $field->getCardinality(),
                'required' => $field->isRequired(),
                'bundle' => $entity->bundle(),
              ];
            }
          }
        }
        elseif ($component_field instanceof ExoComponentFieldComputedInterface) {
          $field_build = $component_field->componentView($field, $is_layout_builder);
        }

        $output = [];
        foreach ($field_build as $delta => &$value) {
          // Properties can be sent through as a standalone item.
          if (Element::property($delta)) {
            if (isset($values[$delta]) && is_array($values[$delta])) {
              $values[$delta] = NestedArray::mergeDeep($values[$delta], $value);
            }
            continue;
          }
          // Properties can be sent through with the value.
          if (is_array($value)) {
            foreach (Element::properties($value) as $key) {
              if (isset($values[$key]) && is_array($values[$key])) {
                $values[$key] = NestedArray::mergeDeep($values[$key], $value[$key]);
              }
              unset($value[$key]);
            }
          }
          $field_attributes = $attributes;
          if ($is_layout_builder) {
            $path = array_merge($definition->getParents(), $component_field->componentParents($field, $delta));
            $config = [
              'fieldName' => $field_name,
              'delta' => $delta,
              'total' => count($field_build),
              'path' => implode('.', $path),
            ];
            $field_attributes['data-exo-alchemist-field'] = json_encode($config);
          }
          $value['attributes'] = new ExoComponentAttribute($field_attributes);
          $value['attributes']->setAsLayoutBuilder($is_layout_builder);
          $is_editable = $is_layout_builder && empty($field->getGroup());
          $value['attributes']->editable($is_editable);
          $output[$delta] = $value;
        }
        if ($field->supportsMultiple()) {
          $output = [
            'value' => $output,
            'attributes' => new ExoComponentAttribute([
              'class' => [
                'group--' . Html::getClass($field->getName()),
              ],
            ]),
          ];
        }
        else {
          if (is_array($output) && count($output) == 1) {
            $output = reset($output);
          }
        }
        $values[$field->getName()] = $output;
      }
    }
  }

}
