<?php

namespace Drupal\exo_alchemist\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeRepositoryInterface;
use Drupal\Core\Field\FieldConfigInterface;
use Drupal\Core\Field\FieldTypePluginManagerInterface;
use Drupal\Core\Field\FormatterPluginManager;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Plugin\Context\EntityContextDefinition;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\exo_alchemist\ExoComponentManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides entity field block definitions for every field.
 *
 * @internal
 *   Plugin derivers are internal.
 */
class ExoComponentFieldEntityDeriver extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * The entity type repository.
   *
   * @var \Drupal\Core\Entity\EntityTypeRepositoryInterface
   */
  protected $entityTypeRepository;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The field type manager.
   *
   * @var \Drupal\Core\Field\FieldTypePluginManagerInterface
   */
  protected $fieldTypeManager;

  /**
   * The formatter manager.
   *
   * @var \Drupal\Core\Field\FormatterPluginManager
   */
  protected $formatterManager;

  /**
   * The exo_alchemist.settings config object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Constructs new FieldBlockDeriver.
   *
   * @param \Drupal\Core\Entity\EntityTypeRepositoryInterface $entity_type_repository
   *   The entity type repository.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\Field\FieldTypePluginManagerInterface $field_type_manager
   *   The field type manager.
   * @param \Drupal\Core\Field\FormatterPluginManager $formatter_manager
   *   The formatter manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(EntityTypeRepositoryInterface $entity_type_repository, EntityFieldManagerInterface $entity_field_manager, FieldTypePluginManagerInterface $field_type_manager, FormatterPluginManager $formatter_manager, ConfigFactoryInterface $config_factory) {
    $this->entityTypeRepository = $entity_type_repository;
    $this->entityFieldManager = $entity_field_manager;
    $this->fieldTypeManager = $field_type_manager;
    $this->formatterManager = $formatter_manager;
    $this->config = $config_factory->get('exo_alchemist.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity_type.repository'),
      $container->get('entity_field.manager'),
      $container->get('plugin.manager.field.field_type'),
      $container->get('plugin.manager.field.formatter'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $entity_type_labels = $this->entityTypeRepository->getEntityTypeLabels();
    $exposed_entity_type_fields = $this->config->get('exposed_entity_type_fields');
    foreach ($this->entityFieldManager->getFieldMap() as $entity_type_id => $entity_field_map) {
      if (!isset($exposed_entity_type_fields[$entity_type_id])) {
        continue;
      }
      // Skip component entity type.
      if ($entity_type_id === ExoComponentManager::ENTITY_TYPE) {
        continue;
      }
      foreach ($entity_field_map as $field_name => $field_info) {
        // Skip fields without any formatters.
        $options = $this->formatterManager->getOptions($field_info['type']);
        if (empty($options)) {
          continue;
        }

        foreach ($field_info['bundles'] as $bundle) {
          $derivative = $base_plugin_definition;
          $field_definition = $this->entityFieldManager->getFieldDefinitions($entity_type_id, $bundle)[$field_name];

          // Store the default formatter on the definition.
          $derivative['default_formatter'] = '';
          $field_type_definition = $this->fieldTypeManager->getDefinition($field_info['type']);
          if (isset($field_type_definition['default_formatter'])) {
            $derivative['default_formatter'] = $field_type_definition['default_formatter'];
          }

          $derivative['category'] = $this->t('@entity fields', ['@entity' => $entity_type_labels[$entity_type_id]]);

          $derivative['label'] = $field_definition->getLabel();

          // Add a dependency on the field if it is configurable.
          if ($field_definition instanceof FieldConfigInterface) {
            $derivative['config_dependencies'][$field_definition->getConfigDependencyKey()][] = $field_definition->getConfigDependencyName();
          }
          // For any field that is not display configurable, mark it as
          // unavailable to place in the block UI.
          $derivative['no_ui'] = !$field_definition->isDisplayConfigurable('view');

          // Entity alone.
          $context_definition = EntityContextDefinition::fromEntityTypeId($entity_type_id)->setLabel($entity_type_labels[$entity_type_id]);
          $derivative['context_definitions'] = [
            'entity' => $context_definition,
            'view_mode' => new ContextDefinition('string'),
          ];
          $derivative_id = $entity_type_id . PluginBase::DERIVATIVE_SEPARATOR . $field_name;
          $this->derivatives[$derivative_id] = $derivative;

          // Entity with bundle.
          $context_definition = EntityContextDefinition::fromEntityTypeId($entity_type_id)->setLabel($entity_type_labels[$entity_type_id]);
          $context_definition->addConstraint('Bundle', [$bundle]);
          $derivative['context_definitions'] = [
            'entity' => $context_definition,
            'view_mode' => new ContextDefinition('string'),
          ];
          $derivative_id = $entity_type_id . PluginBase::DERIVATIVE_SEPARATOR . $bundle . PluginBase::DERIVATIVE_SEPARATOR . $field_name;
          $this->derivatives[$derivative_id] = $derivative;
        }
      }
    }
    return $this->derivatives;
  }

}
