<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\Context\EntityContext;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldComputedBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldFormInterface;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldFormTrait;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldPreviewEntityTrait;

/**
 * A 'display' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "display",
 *   deriver = "\Drupal\exo_alchemist\Plugin\Derivative\ExoComponentDisplayEntityDeriver"
 * )
 */
class EntityDisplay extends ExoComponentFieldComputedBase implements ContextAwarePluginInterface, ContainerFactoryPluginInterface, ExoComponentFieldFormInterface {

  use ExoComponentFieldFormTrait;
  use ExoComponentFieldPreviewEntityTrait;

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
   * The entity type ID.
   *
   * @var string
   */
  protected $entityTypeId;

  /**
   * The bundle ID.
   *
   * @var string
   */
  protected $bundle;

  /**
   * Component definition.
   *
   * @var \Drupal\exo_alchemist\Definition\ExoComponentDefinition
   *   The component definition.
   */
  protected $componentDefinition;

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
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   The entity display repository.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, LoggerInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger;
    $this->entityTypeId = $this->getEntityTypeId();
    $this->bundle = $this->getBundle();
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
      $container->get('logger.channel.exo_alchemist')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    $component = $this->getComponentDefinition();
    $info = $this->exoComponentManager()->getPropertyInfo($component);
    $properties = [
      'entity' => $this->t('The entity object.'),
      'entity_id' => $this->t('The entity id.'),
      'entity_type_id' => $this->t('The entity type id.'),
      'entity_view_url' => $this->t('The entity canonical url.'),
      'entity_edit_url' => $this->t('The entity edit url.'),
    ];
    foreach ($info as $key => $data) {
      if ($key !== '_global') {
        $properties += $data['properties'];
      }
    }
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function onFieldInstall() {
    parent::onFieldInstall();
    $this->getEntityViewMode()->save();
    $this->getEntityViewDisplay()->set('status', TRUE)->save();
  }

  /**
   * {@inheritdoc}
   */
  public function onFieldUpdate() {
    parent::onFieldUpdate();
    $this->getEntityViewMode()->save();
    $this->getEntityViewDisplay()->save();
  }

  /**
   * {@inheritdoc}
   */
  public function onFieldUninstall() {
    parent::onFieldUninstall();
    $this->getEntityViewDisplay()->delete();
    $this->getEntityViewMode()->delete();
  }

  /**
   * {@inheritdoc}
   */
  public function viewValue(ContentEntityInterface $entity, array $contexts) {
    $definition = $this->getComponentDefinition();
    $values = [];
    if ($entity = $this->getReferencedEntity($contexts)) {
      $contexts['layout_builder.entity'] = EntityContext::fromEntity($entity);
      unset($contexts['layout_entity']);
      // $contexts['layout_entity'] = EntityContext::fromEntity($entity);
      $values['entity'] = $entity;
      $values['entity_id'] = $entity->id();
      $values['entity_type_id'] = $entity->getEntityTypeId();
      $values['entity_view_url'] = $entity->toUrl()->toString();
      $values['entity_edit_url'] = $entity->toUrl('edit-form')->toString();
      $values += $this->exoComponentManager()->viewEntityValues($definition, $entity, $contexts);
    }
    return $values;
  }

  /**
   * Get the entity of the display.
   *
   * @param \Drupal\Core\Plugin\Context\Context[] $contexts
   *   An array of current contexts.
   *
   * @return \Drupal\Core\Entity\FieldableEntityInterface
   *   The entity.
   */
  protected function getReferencedEntity(array $contexts) {
    $entity = $this->getParentEntity();
    if ($this->isPreview($contexts) || $this->isDefaultStorage($contexts)) {
      // Always use plugin id for entity type id and bundle as these will be
      // the root entity.
      $entity_type_id = static::getEntityTypeIdFromPluginId($this->getPluginId());
      $bundle = static::getBundleFromPluginId($this->getPluginId());
      if ($entity = $this->getPreviewEntity($entity_type_id, $bundle)) {
        \Drupal::messenger()->addMessage($this->t('This component is being previewed using <a href="@url">@label</a>.', [
          '@url' => $entity->toUrl()->toString(),
          '@label' => $entity->getEntityType()->getLabel() . ': ' . $entity->label(),
        ]));
      }
      else {
        \Drupal::messenger()->addWarning($this->t('Please create a @entity_type_id:@bundle entity to improve preview.', [
          '@entity_type_id' => $entity_type_id,
          '@bundle' => $bundle,
        ]));
      }
    }
    return $entity;
  }

  /**
   * Get the component view mode.
   */
  protected function getViewMode() {
    return $this->getFieldDefinition()->safeId();
  }

  /**
   * Get the entity view mode.
   *
   * @return \Drupal\Core\Entity\Entity\EntityViewModeInterface
   *   The entity view mode.
   */
  protected function getEntityViewMode() {
    $storage = $this->entityTypeManager->getStorage('entity_view_mode');
    $view_mode = $this->getViewMode();
    $id = $this->entityTypeId . '.' . $view_mode;
    $display = $storage->load($id);
    if (!$display) {
      $display = $storage->create([
        'id' => $id,
        'label' => $this->getFieldDefinition()->getComponent()->getLabel() . ': ' . $this->getFieldDefinition()->getLabel(),
        'targetEntityType' => $this->entityTypeId,
      ]);
    }
    return $display;
  }

  /**
   * Get the entity view display.
   *
   * @return \Drupal\Core\Entity\Display\EntityViewDisplayInterface
   *   The entity view display.
   */
  protected function getEntityViewDisplay() {
    $view_mode = $this->getViewMode();
    $id = $this->entityTypeId . '.' . $this->bundle . '.' . $view_mode;
    $storage = $this->entityTypeManager->getStorage('entity_view_display');
    $display = $storage->load($id);
    if (!$display) {
      $display = $storage->create([
        'id' => $id,
        'targetEntityType' => $this->entityTypeId,
        'bundle' => $this->bundle,
        'mode' => $view_mode,
      ]);
    }
    return $display;
  }

  /**
   * {@inheritdoc}
   *
   * Pass alter to children.
   */
  public function formAlter(array &$form, FormStateInterface $form_state) {
    $field_name = $form_state->get('exo_component_key');
    $definition = $this->getComponentDefinition();
    if ($field = $definition->getField($field_name)) {
      $component_field = $this->exoComponentManager()->getExoComponentFieldManager()->createFieldInstance($field);
      if ($component_field instanceof ExoComponentFieldFormInterface) {
        $component_field->formAlter($form, $form_state);
      }
    }
  }

  /**
   * {@inheritdoc}
   *
   * Pass validate to children.
   */
  public function formValidate(array $form, FormStateInterface $form_state) {
    $field_name = $form_state->get('exo_component_key');
    $definition = $this->getComponentDefinition();
    if ($field = $definition->getField($field_name)) {
      $component_field = $this->exoComponentManager()->getExoComponentFieldManager()->createFieldInstance($field);
      if ($component_field instanceof ExoComponentFieldFormInterface) {
        $component_field->formValidate($form, $form_state);
      }
    }
  }

  /**
   * {@inheritdoc}
   *
   * Pass submit to children.
   */
  public function formSubmit(array $form, FormStateInterface $form_state) {
    $field_name = $form_state->get('exo_component_key');
    $definition = $this->getComponentDefinition();
    if ($field = $definition->getField($field_name)) {
      $component_field = $this->exoComponentManager()->getExoComponentFieldManager()->createFieldInstance($field);
      if ($component_field instanceof ExoComponentFieldFormInterface) {
        $component_field->formSubmit($form, $form_state);
      }
    }
  }

  /**
   * Get entity type id.
   *
   * @return string
   *   The entity type id.
   */
  public function getEntityTypeId() {
    return static::getEntityTypeIdFromPluginId($this->getPluginId());
  }

  /**
   * Get bundle id.
   *
   * @return string
   *   The bundle id.
   */
  public function getBundle() {
    return static::getBundleFromPluginId($this->getPluginId());
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

  /**
   * Get the component definition.
   *
   * @return \Drupal\exo_alchemist\Definition\ExoComponentDefinition
   *   The component definition.
   */
  protected function getComponentDefinition() {
    if (!isset($this->componentDefinition)) {
      $field = $this->getFieldDefinition();
      $view_mode = $this->getViewMode();
      $definition = [
        'id' => $field->id(),
        'label' => $field->getComponent()->getLabel() . ': ' . $field->getLabel(),
        'description' => $field->getComponent()->getDescription(),
        'fields' => [],
        'modifier_globals' => FALSE,
        'computed' => TRUE,
      ] + $field->toArray() + $field->getComponent()->toArray();
      /** @var \Drupal\exo_alchemist\Entity\ExoLayoutBuilderEntityViewDisplay $display */
      $display = $this->getEntityViewDisplay();
      foreach ($display->getComponents() as $id => $component) {
        $field_key = $id;
        $field_name = $id;
        // Add fieldupe module support.
        if (substr($id, 0, 9) === 'fieldupe_') {
          /** @var \Drupal\fieldupe\Entity\Fieldupe $dupe */
          $dupe = $this->entityTypeManager->getStorage('fieldupe')->load($id);
          if ($dupe) {
            $field_name = $dupe->getParentField();
            // Shorten the key a bit.
            $field_key = str_replace('fieldupe_' . $dupe->getParentEntityType() . '_' . $dupe->getParentBundle() . '_', 'dupe_', $id);
          }
        }
        $definition['fields'][$field_key] = [
          'type' => 'display_component:' . $this->entityTypeId . ':' . $this->bundle,
          'label' => $display->getComponentLabel($id),
          'component_name' => $id,
          'field_name' => $field_name,
          'view_mode' => $view_mode,
          'computed' => TRUE,
        ];
      }
      $this->exoComponentManager()->processDefinition($definition, $this->getPluginId());
      /** @var \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition */
      $definition->addParentField($field);
      $this->componentDefinition = $definition;
    }
    return $this->componentDefinition;
  }

  /**
   * Get the eXo component manager.
   *
   * @return \Drupal\exo_alchemist\ExoComponentManager
   *   The eXo component manager.
   */
  public function exoComponentManager() {
    if (!isset($this->exoComponentManager)) {
      $this->exoComponentManager = \Drupal::service('plugin.manager.exo_component');
    }
    return $this->exoComponentManager;
  }

}
