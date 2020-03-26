<?php

namespace Drupal\exo_toolbar\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the eXo toolbar region plugin manager.
 */
class ExoToolbarBadgeTypeManager extends DefaultPluginManager implements ExoToolbarBadgeTypeManagerInterface {

  /**
   * Constructs a new ExoToolbarBadgeType object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/ExoToolbarBadgeType', $namespaces, $module_handler, 'Drupal\exo_toolbar\Plugin\ExoToolbarBadgeTypePluginInterface', 'Drupal\exo_toolbar\Annotation\ExoToolbarBadgeType');
    $this->alterInfo('exo_toolbar_badge_type_info');
    $this->setCacheBackend($cache_backend, 'exo_toolbar_badge_type_plugins');
  }

  /**
   * {@inheritdoc}
   */
  public function getBadgeTypeLabels() {
    $options = [];
    $definitions = $this->getDefinitions();
    foreach ($definitions as $entity_type_id => $definition) {
      $options[$definition['id']] = $definition['label'];
    }
    return $options;
  }

}
