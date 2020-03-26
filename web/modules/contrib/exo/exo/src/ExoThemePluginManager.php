<?php

namespace Drupal\exo;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the eXo theme plugin manager.
 */
class ExoThemePluginManager extends DefaultPluginManager implements ExoThemePluginManagerInterface {

  /**
   * Constructs a new ExoTheme object.
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
    parent::__construct('ExoTheme', $namespaces, $module_handler, 'Drupal\exo\ExoTheme\ExoThemePluginInterface', 'Drupal\exo\Annotation\ExoTheme');
    $this->alterInfo('exo_theme_info');
    $this->setCacheBackend($cache_backend, 'exo_theme_plugins');
    $this->defaults = [
      'path' => 'css',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function findDefinitions() {
    $definitions = $this->getDiscovery()->getDefinitions();
    foreach ($definitions as $plugin_id => &$definition) {
      $plugin_id_camel_upper = ucfirst(str_replace('_', '', ucwords($plugin_id, '_')));
      $this->defaults['providerPath'] = $this->moduleHandler->getModule($definition['provider'])->getPath();
      $this->defaults['scssPath'] = $this->defaults['providerPath'] . '/src/ExoTheme/' . $plugin_id_camel_upper . '/scss';
      $this->processDefinition($definition, $plugin_id);
    }
    $this->alterDefinitions($definitions);
    // If this plugin was provided by a module that does not exist, remove the
    // plugin definition.
    foreach ($definitions as $plugin_id => $plugin_definition) {
      $provider = $this->extractProviderFromDefinition($plugin_definition);
      if ($provider && !in_array($provider, ['core', 'component']) && !$this->providerExists($provider)) {
        unset($definitions[$plugin_id]);
      }
    }
    return $definitions;
  }

  /**
   * Get the current active theme.
   *
   * @return \Drupal\exo\ExoTheme\ExoThemePluginInterface
   *   The current active theme.
   */
  public function getCurrentTheme() {
    $theme = \Drupal::config('exo.theme')->get('theme');
    return $theme ? $this->createInstance($theme) : NULL;
  }

}
