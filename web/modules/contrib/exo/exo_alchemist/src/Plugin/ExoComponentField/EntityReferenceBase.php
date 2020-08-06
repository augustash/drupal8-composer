<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\Context\EntityContext;
use Drupal\exo_alchemist\ExoComponentValue;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldDisplayInterface;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldDisplayTrait;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldFieldableBase;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldPreviewEntityTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base component for entity reference fields.
 */
class EntityReferenceBase extends ExoComponentFieldFieldableBase implements ContainerFactoryPluginInterface, ExoComponentFieldDisplayInterface {

  use ExoComponentFieldPreviewEntityTrait;
  use ExoComponentFieldDisplayTrait {
    useDisplay as traitUseDisplay;
    getViewMode as traitGetViewMode;
  }

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The entity type to reference.
   *
   * @var string
   */
  protected $entityType;

  /**
   * Constructs a LocalActionDefault object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition() {
    $field = $this->getFieldDefinition();
    $entity_type = $this->getEntityType();
    if (!$entity_type) {
      throw new PluginException(sprintf('eXo Component Field plugin (%s) must define an entity type.', $field->getType()));
    }
    if (!$this->getEntityTypeBundles()) {
      // Default bundle same as entity type.
      $field->setAdditionalValue('bundles', [$entity_type]);
    }
    parent::processDefinition();
  }

  /**
   * {@inheritdoc}
   */
  public function validateValue(ExoComponentValue $value) {
    $bundles = $this->getEntityTypeBundles();
    $value->setIfUnset('bundle', reset($bundles));
    $value->setIfUnset('view_mode', 'default');
    $value->setIfUnset('custom_view_mode', FALSE);
    if ($value->has('value')) {
      // We do not unset the 'value' as other fields may use this differently.
      $value->set('target_id', $value->get('value'));
    }
    parent::validateValue($value);
  }

  /**
   * {@inheritdoc}
   */
  public function getStorageConfig() {
    return [
      'type' => 'entity_reference',
      'settings' => [
        'target_type' => $this->getEntityType(),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldConfig() {
    return [
      'settings' => [
        'handler' => 'default',
        'handler_settings' => [
          'target_bundles' => array_combine($this->getEntityTypeBundles(), $this->getEntityTypeBundles()),
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getWidgetConfig() {
    return [
      'type' => 'entity_reference_autocomplete',
      'settings' => [
        'match_operator' => 'CONTAINS',
        'match_limit' => 10,
        'size' => 60,
        'placeholder' => '',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function useDisplay() {
    return $this->traitUseDisplay() && !empty($this->getFieldDefinition()->getAdditionalValue('custom_view_mode'));
  }

  /**
   * {@inheritdoc}
   */
  public function onFieldInstall() {
    parent::onFieldInstall();
    if ($this->useDisplay()) {
      $this->onFieldInstallFieldDisplay();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function onFieldUpdate() {
    parent::onFieldUpdate();
    if ($this->useDisplay()) {
      $this->onFieldUpdateFieldDisplay();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function onFieldUninstall() {
    parent::onFieldUninstall();
    if ($this->useDisplay()) {
      $this->onFieldUninstallFieldDisplay();
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function hasDefault() {
    foreach ($this->getFieldDefinition()->getDefaults() as $default) {
      // Components can pass in default as a boolean. When this happens, we
      // treat the component as if it has no defaults.
      if (is_bool($default->getValue('value'))) {
        return FALSE;
      }
    }
    return $this->getFieldDefinition()->hasDefault();
  }

  /**
   * {@inheritdoc}
   */
  public function view(FieldItemListInterface $items, array $contexts) {
    if ($items->isEmpty() && $this->isPreview($contexts)) {
      // When we are previewing an empty entity reference, we need to populate
      // entities for display.
      $values = [];
      foreach ($this->getFieldDefinition()->getDefaults() as $default) {
        $bundles = $this->getEntityTypeBundles();
        $bundle = !empty($bundles) ? reset($bundles) : NULL;
        $values[] = ['target_id' => $this->getPreviewEntity($this->getEntityType(), $bundle)];
      }
      $items->setValue($values);
    }
    return parent::view($items, $contexts);
  }

  /**
   * {@inheritdoc}
   */
  protected function getValue(ExoComponentValue $value, FieldItemInterface $item = NULL) {
    return $this->getValueEntity($value, $item);
  }

  /**
   * Extending classes can return an entity that will be set as the value.
   *
   * @param \Drupal\exo_alchemist\ExoComponentValue $value
   *   The field value.
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   The current item.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   An entity that will be used to set the value of the field.
   */
  protected function getValueEntity(ExoComponentValue $value, FieldItemInterface $item = NULL) {
    if ($value->has('target_id') && !is_bool($value->has('target_id'))) {
      return [
        'target_id' => $value->get('target_id'),
      ];
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    $properties = [
      'entity' => $this->t('The entity object.'),
      'entity_id' => $this->t('The entity id.'),
      'entity_label' => $this->t('The entity label.'),
      'entity_type_id' => $this->t('The entity type id.'),
      'entity_view_url' => $this->t('The entity canonical url.'),
      'entity_edit_url' => $this->t('The entity edit url.'),
    ];
    if ($this->useDisplay()) {
      $properties += $this->propertyInfoFieldDisplay();
    }
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function viewValue(FieldItemInterface $item, $delta, array $contexts) {
    $entity = $this->getReferencedEntity($item, $contexts);
    $value = [
      'entity' => $entity,
      'entity_id' => $entity->id(),
      'entity_label' => $entity->label(),
      'entity_type_id' => $entity->getEntityTypeId(),
      'entity_view_url' => $entity->toUrl()->toString(),
      'entity_edit_url' => $entity->toUrl('edit-form')->toString(),
    ];
    if ($this->useDisplay()) {
      $contexts['layout_builder.entity'] = EntityContext::fromEntity($entity);
      unset($contexts['layout_entity']);
      $value += $this->viewValueFieldDisplay($entity, $contexts);
    }
    return $value;
  }

  /**
   * Get the entity type.
   *
   * @return string
   *   The entity type.
   */
  protected function getEntityType() {
    return $this->entityType;
  }

  /**
   * Get the entity type.
   *
   * @return array
   *   An array of support bundles.
   */
  protected function getEntityTypeBundles() {
    $bundles = $this->getFieldDefinition()->getAdditionalValue('bundles');
    return is_array($bundles) ? $bundles : [$bundles => $bundles];
  }

  /**
   * Get the entity view mode.
   *
   * @return string
   *   The entity view mode.
   */
  protected function getViewMode() {
    if ($this->useDisplay()) {
      return $this->traitGetViewMode();
    }
    return $this->getFieldDefinition()->getAdditionalValue('view_mode');
  }

  /**
   * {@inheritdoc}
   *
   * Required when using a display.
   */
  public function getDisplayedEntityTypeId() {
    return $this->getEntityType();
  }

  /**
   * {@inheritdoc}
   *
   * Required when using a display.
   */
  public function getDisplayedBundle() {
    $bundles = $this->getEntityTypeBundles();
    if (!empty($bundles) && count($bundles) === 1) {
      return reset($bundles);
    }
    return NULL;
  }

  /**
   * Get the referenced entity.
   */
  protected function getReferencedEntity(FieldItemInterface $item, array $contexts) {
    if ($this->isPreview($contexts)) {
      $bundles = $this->getEntityTypeBundles();
      $bundle = !empty($bundles) ? reset($bundles) : NULL;
      return $this->getPreviewEntity($this->getEntityType(), $bundle);
    }
    return $item->entity;
  }

  /**
   * Returns the module handler.
   *
   * @return \Drupal\Core\Extension\ModuleHandlerInterface
   *   The module handler.
   */
  protected function moduleHandler() {
    if (!$this->moduleHandler) {
      $this->moduleHandler = \Drupal::service('module_handler');
    }
    return $this->moduleHandler;
  }

}
