<?php

namespace Drupal\exo_alchemist\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Plugin\Context\EntityContextDefinition;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeRepositoryInterface;

/**
 * Provides entity field block definitions for every field.
 *
 * @internal
 *   Plugin derivers are internal.
 */
class ExoComponentExtraFieldEntityDeriver extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

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
   * The entity type bundle info.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * The entity type repository.
   *
   * @var \Drupal\Core\Entity\EntityTypeRepositoryInterface
   */
  protected $entityTypeRepository;

  /**
   * The exo_alchemist.settings config object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Constructs new FieldBlockDeriver.
   *
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info.
   * @param \Drupal\Core\Entity\EntityTypeRepositoryInterface $entity_type_repository
   *   The entity type repository.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(EntityFieldManagerInterface $entity_field_manager, EntityTypeManagerInterface $entity_type_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info, EntityTypeRepositoryInterface $entity_type_repository, ConfigFactoryInterface $config_factory) {
    $this->entityFieldManager = $entity_field_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
    $this->entityTypeRepository = $entity_type_repository;
    $this->config = $config_factory->get('exo_alchemist.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity_field.manager'),
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('entity_type.repository'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $entity_type_labels = $this->entityTypeRepository->getEntityTypeLabels();
    $exposed_entity_type_fields = $this->config->get('exposed_entity_type_fields');
    foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $entity_type) {
      if (!isset($exposed_entity_type_fields[$entity_type_id])) {
        continue;
      }
      // Only process fieldable entity types.
      if (!$entity_type->entityClassImplements(FieldableEntityInterface::class)) {
        continue;
      }

      $bundles = $this->entityTypeBundleInfo->getBundleInfo($entity_type_id);
      foreach ($bundles as $bundle_id => $bundle) {
        $extra_fields = $this->entityFieldManager->getExtraFields($entity_type_id, $bundle_id);
        // Skip bundles without any extra fields.
        if (empty($extra_fields['display'])) {
          continue;
        }

        foreach ($extra_fields['display'] as $extra_field_id => $extra_field) {
          $derivative = $base_plugin_definition;

          $derivative['category'] = $this->t('@entity fields', ['@entity' => $entity_type_labels[$entity_type_id]]);

          $derivative['label'] = $extra_field['label'];
          $derivative['base_type'] = $base_plugin_definition['id'];

          // Entity alone.
          $context_definition = EntityContextDefinition::fromEntityType($entity_type);
          $derivative['context_definitions'] = [
            'entity' => $context_definition,
          ];
          $derivative_id = $entity_type_id . PluginBase::DERIVATIVE_SEPARATOR . $extra_field_id;
          $this->derivatives[$derivative_id] = $derivative;

          // Entity with bundle.
          $context_definition = EntityContextDefinition::fromEntityType($entity_type)
            ->addConstraint('Bundle', [$bundle_id]);
          $derivative['context_definitions'] = [
            'entity' => $context_definition,
          ];
          $derivative_id = $entity_type_id . PluginBase::DERIVATIVE_SEPARATOR . $bundle_id . PluginBase::DERIVATIVE_SEPARATOR . $extra_field_id;
          $this->derivatives[$derivative_id] = $derivative;
        }
      }
    }
    return $this->derivatives;
  }

}
