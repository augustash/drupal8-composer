<?php

namespace Drupal\exo_icon\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;

/**
 * Defines dynamic icons.
 */
class DynamicIcons extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity type bundle info service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * Constructs a new EntityActionDeriverBase object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    if (empty($this->derivatives)) {
      $definitions = [];
      foreach ($this->entityTypeManager->getDefinitions() as $entity_type) {
        $icon = exo_icon_entity_icon($entity_type);
        if (empty($icon)) {
          $icon = $entity_type->get('label_icon');
        }
        if ($icon) {
          $definitions["entity.{$entity_type->id()}"] = $base_plugin_definition + [
            'regex' => '^\b' . strtolower($entity_type->getLabel()) . '(s|es)?\b',
            'icon' => $icon,
            'prefix' => ['entity', 'local_task'],
          ];
        }
        if ($bundle_entity_type = $entity_type->getBundleEntityType()) {
          $bundles = $this->entityTypeBundleInfo->getBundleInfo($entity_type->id());
          if (!empty($bundles)) {
            $bundle_entity_types = $this->entityTypeManager->getStorage($bundle_entity_type)->loadMultiple(array_keys($bundles));
            foreach ($bundle_entity_types as $bundle_entity_type) {
              if ($icon = exo_icon_entity_icon($bundle_entity_type)) {
                $definitions["entity.{$entity_type->id()}.{$bundle_entity_type->id()}"] = $base_plugin_definition + [
                  'regex' => '^\b' . strtolower($bundle_entity_type->label()) . '(s|es)?\b',
                  'icon' => $icon,
                  'prefix' => ['entity', 'local_task'],
                ];
              }
            }
          }
        }
      }
      $this->derivatives = $definitions;
    }
    return $this->derivatives;
  }

}
