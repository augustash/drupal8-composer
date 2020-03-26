<?php

namespace Drupal\exo_toolbar\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the eXo toolbar region plugin manager.
 */
class ExoToolbarRegionManager extends DefaultPluginManager implements ExoToolbarRegionManagerInterface {

  /**
   * Constructs a new ExoToolbarRegion object.
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
    parent::__construct('Plugin/ExoToolbarRegion', $namespaces, $module_handler, 'Drupal\exo_toolbar\Plugin\ExoToolbarRegionPluginInterface', 'Drupal\exo_toolbar\Annotation\ExoToolbarRegion');
    $this->alterInfo('exo_toolbar_region_info');
    $this->setCacheBackend($cache_backend, 'exo_toolbar_region_plugins');
    $this->defaults = [
      'position' => 'top',
      'weight' => 0,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitions($show_hidden = TRUE) {
    $definitions = parent::getDefinitions();
    uasort($definitions, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);

    if (!$show_hidden) {
      // Filter out definitions that can not be configured in Field UI.
      $definitions = array_filter($definitions, function ($definition) {
        return empty($definition['no_ui']);
      });
    }

    return $definitions;
  }

}
