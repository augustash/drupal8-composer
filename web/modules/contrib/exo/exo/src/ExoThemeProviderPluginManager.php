<?php

namespace Drupal\exo;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the eXo theme provider plugin manager.
 */
class ExoThemeProviderPluginManager extends DefaultPluginManager implements ExoThemeProviderPluginManagerInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('ExoThemeProvider', $namespaces, $module_handler, 'Drupal\exo\ExoThemeProvider\ExoThemeProviderPluginInterface', 'Drupal\exo\Annotation\ExoThemeProvider');
    $this->alterInfo('exo_theme_provider_info');
    $this->setCacheBackend($cache_backend, 'exo_theme_provider_plugins');
    $this->defaults = [
      'template' => 'ExoTheme.scss.twig',
      'path' => 'css',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function findDefinitions() {
    // Namespaces at this stage contain only enabled modules. Due to our need
    // to build out complete themes even for modules that are disabled we
    // rebuild the namespaces.
    // @see \Drupal\Core\DrupalKernal\getModuleNamespacesPsr4().
    $modules = system_rebuild_module_data();
    $namespaces = [];
    foreach ($modules as $id => $module) {
      $namespaces["Drupal\\$id"] = $module->getPath() . '/src';
    }
    $this->namespaces = new \ArrayObject($namespaces);
    $definitions = $this->getDiscovery()->getDefinitions();
    foreach ($definitions as $plugin_id => &$definition) {
      $this->defaults['library'] = $definition['provider'];
      $this->defaults['providerPath'] = $modules[$definition['provider']]->getPath();
      $this->defaults['filename'] = $definition['provider'] . '.theme.css';
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
   * {@inheritdoc}
   */
  public function getDefinitions() {
    return array_filter($this->getAllDefinitions(), function ($definition) {
      return $this->providerEnabled($definition['provider']);
    });
  }

  /**
   * {@inheritdoc}
   */
  public function getAllDefinitions() {
    $definitions = $this->getCachedDefinitions();
    if (!isset($definitions)) {
      $definitions = $this->findDefinitions();
      $this->setCachedDefinitions($definitions);
    }
    return $definitions;
  }

  /**
   * {@inheritdoc}
   */
  protected function providerExists($provider) {
    return TRUE;
  }

  /**
   * Determines if the provider is enabled.
   *
   * @return bool
   *   TRUE if provider exists, FALSE otherwise.
   */
  protected function providerEnabled($provider) {
    return $this->moduleHandler->moduleExists($provider);
  }

}
