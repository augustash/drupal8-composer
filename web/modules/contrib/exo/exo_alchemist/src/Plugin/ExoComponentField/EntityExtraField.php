<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldComputedBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A 'extra field' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "extra_field",
 *   deriver = "\Drupal\exo_alchemist\Plugin\Derivative\ExoComponentExtraFieldEntityDeriver"
 * )
 */
class EntityExtraField extends ExoComponentFieldComputedBase implements ContextAwarePluginInterface, ContainerFactoryPluginInterface {

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The field name.
   *
   * @var string
   */
  protected $fieldName;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
    // Get field name from the plugin ID.
    list (, , , $field_name) = explode(static::DERIVATIVE_SEPARATOR, $plugin_id, 4);
    assert(!empty($field_name));
    $this->fieldName = $field_name;

    parent::__construct($configuration, $plugin_id, $plugin_definition);
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
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    $properties = [
      'render' => $this->t('The rendered field.'),
    ];
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function viewValue(ContentEntityInterface $entity, array $contexts) {
    $parent_entity = $this->getParentEntity();
    // Add a placeholder to replace after the entity view is built.
    // @see layout_builder_entity_view_alter().
    $extra_fields = $this->entityFieldManager->getExtraFields($parent_entity->getEntityTypeId(), $parent_entity->bundle());
    if (!isset($extra_fields['display'][$this->fieldName])) {
      $build = [];
    }
    else {
      $build = [
        '#extra_field_placeholder_field_name' => $this->fieldName,
        // Always provide a placeholder. The Layout Builder will NOT invoke
        // hook_entity_view_alter() so extra fields will not be added to the
        // render array. If the hook is invoked the placeholder will be
        // replaced.
        // @see ::replaceFieldPlaceholder()
        '#markup' => $this->t('Placeholder for the @preview_fallback', ['@preview_fallback' => $this->getPreviewFallbackString()]),
      ];
    }
    CacheableMetadata::createFromObject($this)->applyTo($build);
    return [
      'render' => $build,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getPreviewFallbackString() {
    $parent_entity = $this->getParentEntity();
    $extra_fields = $this->entityFieldManager->getExtraFields($parent_entity->getEntityTypeId(), $parent_entity->bundle());
    return new TranslatableMarkup('"@field" field', ['@field' => $extra_fields['display'][$this->fieldName]['label']]);
  }

  /**
   * {@inheritdoc}
   */
  protected function componentAccess(array $contexts, AccountInterface $account) {
    return $this->getparentEntity()->access('view', $account, TRUE);
  }

}
