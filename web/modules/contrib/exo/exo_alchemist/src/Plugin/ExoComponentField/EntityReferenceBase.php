<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\exo_alchemist\ExoComponentValue;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldFieldableBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base component for entity reference fields.
 */
class EntityReferenceBase extends ExoComponentFieldFieldableBase implements ContainerFactoryPluginInterface {

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
    if ($value->has('target_id')) {
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
    return [
      'label' => $this->t('The entity label.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function viewValue(FieldItemInterface $item, $delta, array $contexts) {
    return [
      'label' => $item->entity->label(),
    ];
  }

  /**
   * Get the entity type.
   */
  protected function getEntityType() {
    return $this->entityType;
  }

  /**
   * Get the entity type.
   */
  protected function getEntityTypeBundles() {
    return $this->getFieldDefinition()->getAdditionalValue('bundles');
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
