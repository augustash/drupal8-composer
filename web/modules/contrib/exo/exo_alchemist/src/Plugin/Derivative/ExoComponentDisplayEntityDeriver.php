<?php

namespace Drupal\exo_alchemist\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeRepositoryInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Plugin\Context\EntityContextDefinition;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\exo_alchemist\ExoComponentManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides entity display field definitions for every bundle.
 *
 * @internal
 *   Plugin derivers are internal.
 */
class ExoComponentDisplayEntityDeriver extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity type repository.
   *
   * @var \Drupal\Core\Entity\EntityTypeRepositoryInterface
   */
  protected $entityTypeRepository;

  /**
   * The entity type bundle info.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * The exo_alchemist.settings config object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Constructs new FieldBlockDeriver.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityTypeRepositoryInterface $entity_type_repository
   *   The entity type repository.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityTypeRepositoryInterface $entity_type_repository, EntityTypeBundleInfoInterface $entity_type_bundle_info, ConfigFactoryInterface $config_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityTypeRepository = $entity_type_repository;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
    $this->config = $config_factory->get('exo_alchemist.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_type.repository'),
      $container->get('entity_type.bundle.info'),
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
      // Skip component entity type.
      if ($entity_type_id === ExoComponentManager::ENTITY_TYPE) {
        continue;
      }

      $bundles = $this->entityTypeBundleInfo->getBundleInfo($entity_type_id);
      foreach ($bundles as $bundle_id => $bundle) {
        $derivative = $base_plugin_definition;

        $derivative['category'] = $this->t('@entity fields', ['@entity' => $entity_type_labels[$entity_type_id]]);

        $derivative['label'] = $this->t('@label Display', [
          '@label' => $entity_type->getLabel(),
        ]);
        $derivative['base_type'] = $base_plugin_definition['id'];

        // Entity with bundle.
        $context_definition = EntityContextDefinition::fromEntityTypeId($entity_type_id)->setLabel($entity_type_labels[$entity_type_id]);
        $context_definition->addConstraint('Bundle', [$bundle_id]);
        $derivative['context_definitions'] = [
          'entity' => $context_definition,
          'view_mode' => new ContextDefinition('string'),
        ];

        $derivative_id = $entity_type_id . PluginBase::DERIVATIVE_SEPARATOR . $bundle_id;
        $this->derivatives[$derivative_id] = $derivative;
      }
    }
    return $this->derivatives;
  }

}
