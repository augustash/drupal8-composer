<?php

namespace Drupal\exo_toolbar\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the eXo toolbar region plugin manager.
 */
class ExoToolbarDialogTypeManager extends DefaultPluginManager implements ExoToolbarDialogTypeManagerInterface {

  /**
   * Constructs a new ExoToolbarDialogType object.
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
    parent::__construct('Plugin/ExoToolbarDialogType', $namespaces, $module_handler, 'Drupal\exo_toolbar\Plugin\ExoToolbarDialogTypePluginInterface', 'Drupal\exo_toolbar\Annotation\ExoToolbarDialogType');
    $this->alterInfo('exo_toolbar_dialog_type_info');
    $this->setCacheBackend($cache_backend, 'exo_toolbar_dialog_type_plugins');
  }

  /**
   * {@inheritdoc}
   */
  public function getDialogTypeLabels() {
    $options = [];
    $definitions = $this->getDefinitions();
    foreach ($definitions as $entity_type_id => $definition) {
      $options[$definition['id']] = $definition['label'];
    }
    return $options;
  }

}
