<?php

namespace Drupal\exo_alchemist\EventSubscriber;

use Drupal\Core\Config\ConfigImporterEvent;
use Drupal\Core\Config\Entity\ConfigEntityStorage;
use Drupal\Core\Entity\Event\BundleConfigImportValidate;
use Drupal\exo_alchemist\ExoComponentManager;

/**
 * Entity config importer validation event subscriber.
 */
class ExoComponentConfigImportValidate extends BundleConfigImportValidate {

  /**
   * Ensures bundles that will be deleted are not in use.
   *
   * @param \Drupal\Core\Config\ConfigImporterEvent $event
   *   The config import event.
   */
  public function onConfigImporterValidate(ConfigImporterEvent $event) {
    foreach ($event->getChangelist('delete') as $config_name) {
      // Get the config entity type ID. This also ensure we are dealing with a
      // configuration entity.
      if ($entity_type_id = $this->configManager->getEntityTypeIdByName($config_name)) {
        $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
        if ($entity_type_id == ExoComponentManager::ENTITY_BUNDLE_TYPE) {
          // Does this entity type define a bundle of another entity type.
          if ($bundle_of = $entity_type->getBundleOf()) {
            // Work out if there are entities with this bundle.
            $bundle_id = ConfigEntityStorage::getIDFromConfigName($config_name, $entity_type->getConfigPrefix());
            if (substr($bundle_id, 0, 4) == 'exo_') {
              $entity_storage = $this->entityTypeManager->getStorage($bundle_of);
              // Delete all entities belonging to this entity type.
              $entities = $entity_storage->loadByProperties(['type' => $bundle_id]);
              if (!empty($entities)) {
                $entity_storage->delete($entities);
              }
            }
          }
        }
      }
    }
  }

}
