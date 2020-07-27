<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldComputedBase;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\exo_alchemist\ExoComponentSectionStorageInterface;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldFormInterface;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldFormTrait;
use Drupal\exo_alchemist\Plugin\SectionStorage\ExoOverridesSectionStorage;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A 'display' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "display_component",
 *   deriver = "\Drupal\exo_alchemist\Plugin\Derivative\ExoComponentDisplayComponentEntityDeriver"
 * )
 */
class EntityDisplayComponent extends ExoComponentFieldComputedBase implements ContextAwarePluginInterface, ContainerFactoryPluginInterface, ExoComponentFieldFormInterface {

  use ExoComponentFieldFormTrait;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Stores cached entity build render arrays.
   *
   * @var array
   *   Entity build render arrays.
   */
  protected static $entityBuilds = [];

  /**
   * Constructs a new FieldBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager, LoggerInterface $logger) {
    $this->entityTypeId = static::getEntityTypeIdFromPluginId($plugin_id);
    $this->bundle = static::getBundleFromPluginId($plugin_id);
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('logger.channel.exo_alchemist')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition() {
    parent::processDefinition();
    $field = $this->getFieldDefinition();
    if (!$field->hasAdditionalValue('component_name')) {
      throw new PluginException(sprintf('eXo Component Field plugin (%s) requires [component_name] be set.', $field->getType()));
    }
    if (!$field->hasAdditionalValue('view_mode')) {
      throw new PluginException(sprintf('eXo Component Field plugin (%s) requires [view_mode] be set.', $field->getType()));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    $properties = [
      'render' => $this->t('The rendered output of @id.', [
        '@id' => $this->getComponentName(),
        '@entity_type_id' => $this->entityTypeId,
        '@bundle' => $this->bundle,
      ]),
    ];
    return $properties;
  }

  /**
   * Get the component name.
   */
  protected function getComponentName() {
    return $this->getFieldDefinition()->getAdditionalValue('component_name');
  }

  /**
   * Get the field name.
   *
   * Can be different than the component name to alter the edit workflow.
   */
  protected function getFieldName() {
    return $this->getFieldDefinition()->getAdditionalValue('field_name') ?: $this->getComponentName();
  }

  /**
   * Get the component view mode.
   */
  protected function getViewMode() {
    return $this->getFieldDefinition()->getAdditionalValue('view_mode');
  }

  /**
   * Get entity build.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity.
   * @param string $view_mode
   *   The view mode.
   *
   * @return array
   *   The build.
   */
  protected static function getEntityBuild(ContentEntityInterface $entity, $view_mode) {
    if (!isset(static::$entityBuilds[$entity->uuid()])) {
      /** @var \Drupal\Core\Entity\EntityViewBuilder $view_builder */
      $view_builder = \Drupal::entityTypeManager()->getViewBuilder($entity->getEntityTypeId());
      $build = $view_builder->view($entity, $view_mode);
      $build = $view_builder->build($build);
      static::$entityBuilds[$entity->uuid()] = $build;
    }
    return static::$entityBuilds[$entity->uuid()];
  }

  /**
   * {@inheritdoc}
   */
  public function viewValue(ContentEntityInterface $entity, array $contexts) {
    $value = [];
    $field = $this->getFieldDefinition();
    $parent_entity = $this->getParentEntity();
    $view_mode = $field->getAdditionalValue('view_mode');
    $component_name = $this->getComponentName();
    $build = static::getEntityBuild($parent_entity, $view_mode);
    if (isset($build[$component_name])) {
      // If this is a field, and not an extra field, we check to make sure it
      // has a value.
      if ($parent_entity->hasField($component_name) && $parent_entity->get($component_name)->isEmpty()) {
        // We may want to handle cache information here.
        return [];
      }
      $value['#field_attributes']['class'][] = 'entity--field';
      $value['render'] = $build[$component_name];
    }
    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function formAlter(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\exo_alchemist\Form\ExoFieldUpdateForm $form_object */
    $form_object = $form_state->getFormObject();
    $section_storage = $form_object->getSectionStorage();
    if ($section_storage instanceof ExoComponentSectionStorageInterface) {
      $entity = $form_state->get('display_entity');
      if (empty($entity)) {
        $entity = $section_storage->getParentEntity();
        $form_state->set('display_entity', $entity);
      }
      /** @var \Drupal\Core\Entity\Display\EntityFormDisplayInterface $form_display */
      $form_display = $this->entityTypeManager->getStorage('entity_form_display')->load($entity->getEntityTypeId() . '.' . $entity->bundle() . '.' . $section_storage->getViewMode());
      if ($form_display) {
        // We use field name instead of component name. This allows this field
        // to display one component, but edit something else.
        $field_name = $this->getFieldName();
        $form_state->set('form_display', $form_display);
        /** @var \Drupal\Core\Field\WidgetInterface $widget */
        $widget = $form_display->getRenderer($field_name);
        if ($widget) {
          $form['entity'] = [
            '#type' => 'fieldset',
            '#title' => $this->t('Element Value'),
            '#tree' => TRUE,
            '#weight' => -10,
            '#field_parents' => ['settings', 'block_form'],
            '#parents' => ['settings', 'block_form'],
          ];
          $items = $entity->get($field_name);
          $items->filterEmptyItems();
          $form['entity'][$field_name] = $widget->form($items, $form['entity'], $form_state);
          $form['entity'][$field_name]['#access'] = $items->access('edit');
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function formSubmit(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Entity\Display\EntityFormDisplayInterface $form_display */
    $form_display = $form_state->get('form_display');
    $entity = $form_state->get('display_entity');
    if ($entity) {
      $form_display->extractFormValues($entity, $form['entity'], $form_state);
      $entity->_exoComponentSave = TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function isEditable(array $contexts) {
    $parent_entity = $this->getParentEntity();
    // We use field name instead of component as that is used during edit.
    $field_name = $this->getFieldName();
    $extra_fields = $this->entityFieldManager->getExtraFields($parent_entity->getEntityTypeId(), $parent_entity->bundle());
    // Extra fields can not be edited.
    if (isset($extra_fields['display'][$field_name])) {
      return FALSE;
    }
    // Entity fields are attached to an entity and should be able to be edited
    // even when within locked sections.
    return $this->getFieldDefinition()->isEditable();
  }

  /**
   * Get entity type id from plugin id.
   *
   * @param string $plugin_id
   *   The plugin id.
   *
   * @return string
   *   The entity type id.
   */
  public static function getEntityTypeIdFromPluginId($plugin_id) {
    $parts = explode(static::DERIVATIVE_SEPARATOR, $plugin_id, 4);
    return $parts[1];
  }

  /**
   * Get bundle id from plugin id.
   *
   * @param string $plugin_id
   *   The plugin id.
   *
   * @return string
   *   The bundle.
   */
  public static function getBundleFromPluginId($plugin_id) {
    $parts = explode(static::DERIVATIVE_SEPARATOR, $plugin_id, 4);
    return $parts[2];
  }

}
