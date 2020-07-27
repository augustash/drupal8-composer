<?php

namespace Drupal\exo_alchemist\Plugin;

/**
 * Provides methods for creating file entities.
 */
trait ExoComponentFieldPreviewEntityTrait {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Get an entity to preview.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The entity.
   */
  protected function getPreviewEntity($entity_type_id, $bundle = NULL) {
    $entity = NULL;
    $entity_definition = $this->entityTypeManager()->getDefinition($entity_type_id);
    $storage = $this->entityTypeManager()->getStorage($entity_type_id);
    $query = $storage->getQuery();
    if ($key = $entity_definition->getKey('id')) {
      $query->sort($key);
    }
    if ($bundle && ($bundle_key = $entity_definition->getKey('bundle'))) {
      $query->condition($bundle_key, $bundle);
    }
    $query->range(0, 1);
    $results = $query->execute();
    if (!empty($results)) {
      $entity = $storage->load(reset($results));
      if (is_a($this, '\Drupal\exo_alchemist\Plugin\ExoComponentField\EntityDisplay')) {
        $route = \Drupal::routeMatch();
        if ($entity_type_id == 'node') {
          // Set route match so that views and other modules can access the
          // current entity.
          $route->getParameters()->set($entity_type_id, $entity);
        }
      }
    }
    return $entity;
  }

  /**
   * Retrieves the entity type manager.
   *
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface
   *   The entity type manager.
   */
  protected function entityTypeManager() {
    return $this->entityTypeManager ?: \Drupal::service('entity_type.manager');
  }

}
