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
use Drupal\exo_alchemist\Plugin\ExoComponentFieldDisplayInterface;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldDisplayTrait;
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
class EntityDisplay extends ExoComponentFieldComputedBase implements ContextAwarePluginInterface, ContainerFactoryPluginInterface, ExoComponentFieldFormInterface, ExoComponentFieldDisplayInterface {

  use ExoComponentFieldFormTrait;
  use ExoComponentFieldPreviewEntityTrait;
  use ExoComponentFieldDisplayTrait;

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
    $this->entityTypeId = $this->getDisplayedEntityTypeId();
    $this->bundle = $this->getDisplayedBundle();
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
    $properties = [
      'entity' => $this->t('The entity object.'),
      'entity_id' => $this->t('The entity id.'),
      'entity_label' => $this->t('The entity label.'),
      'entity_type_id' => $this->t('The entity type id.'),
      'entity_view_url' => $this->t('The entity canonical url.'),
      'entity_edit_url' => $this->t('The entity edit url.'),
    ];
    $properties += $this->propertyInfoFieldDisplay();
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function onFieldInstall() {
    parent::onFieldInstall();
    $this->onFieldInstallFieldDisplay();
  }

  /**
   * {@inheritdoc}
   */
  public function onFieldUpdate() {
    parent::onFieldUpdate();
    $this->onFieldUpdateFieldDisplay();
  }

  /**
   * {@inheritdoc}
   */
  public function onFieldUninstall() {
    parent::onFieldUninstall();
    $this->onFieldUninstallFieldDisplay();
  }

  /**
   * {@inheritdoc}
   */
  public function viewValue(ContentEntityInterface $entity, array $contexts) {
    $values = [];
    if ($entity = $this->getReferencedEntity($contexts)) {
      $contexts['layout_builder.entity'] = EntityContext::fromEntity($entity);
      unset($contexts['layout_entity']);
      $values['entity'] = $entity;
      $values['entity_id'] = $entity->id();
      $values['entity_label'] = $entity->label();
      $values['entity_type_id'] = $entity->getEntityTypeId();
      $values['entity_view_url'] = $entity->toUrl()->toString();
      $values['entity_edit_url'] = $entity->toUrl('edit-form')->toString();
      $values += $this->viewValueFieldDisplay($entity, $contexts);
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
   * {@inheritdoc}
   *
   * Pass alter to children.
   */
  public function formAlter(array &$form, FormStateInterface $form_state) {
    $this->formAlterFieldDisplay($form, $form_state);
  }

  /**
   * {@inheritdoc}
   *
   * Pass validate to children.
   */
  public function formValidate(array $form, FormStateInterface $form_state) {
    $this->formValidateFieldDisplay($form, $form_state);
  }

  /**
   * {@inheritdoc}
   *
   * Pass submit to children.
   */
  public function formSubmit(array $form, FormStateInterface $form_state) {
    $this->formSubmitFieldDisplay($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function getDisplayedEntityTypeId() {
    return static::getEntityTypeIdFromPluginId($this->getPluginId());
  }

  /**
   * {@inheritdoc}
   */
  public function getDisplayedBundle() {
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

}
