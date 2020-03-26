<?php

namespace Drupal\exo_form\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the eXo Form plugin manager.
 */
class ExoFormManager extends DefaultPluginManager {

  /**
   * The plugin collection that holds the exo form plugin instances.
   *
   * @var \Drupal\exo_form\Plugin\ExoFormPluginCollection
   */
  protected $pluginCollection;

  /**
   * An array of widget options for each field type.
   *
   * @var array
   */
  protected $elementPlugins;

  /**
   * Constructs a new ExoFormManager object.
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
    parent::__construct('Plugin/ExoForm', $namespaces, $module_handler, 'Drupal\exo_form\Plugin\ExoFormInterface', 'Drupal\exo_form\Annotation\ExoForm');
    $this->alterInfo('exo_form_info');
    $this->defaults = exo_form_get_settings();
    $this->setCacheBackend($cache_backend, 'exo_form_plugins');
  }

  /**
   * Returns an array of element plugins for a field type.
   *
   * @param string|null $element_types
   *   (optional) The name of a field type, or NULL to retrieve all widget
   *   options. Defaults to NULL.
   *
   * @return array
   *   If no field type is provided, returns a nested array of all widget types,
   *   keyed by field type human name.
   */
  public function getPluginsByType($element_types = NULL) {
    if (!isset($this->elementPlugins)) {
      $options = [];
      $widget_types = $this->getDefinitions();
      uasort($widget_types, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);
      foreach ($widget_types as $name => $widget_type) {
        foreach ($widget_type['element_types'] as $widget_element_types) {
          $options[$widget_element_types][$name] = $widget_type['label'];
        }
      }
      $this->elementPlugins = $options;
    }
    if (isset($element_types)) {
      return !empty($this->elementPlugins[$element_types]) ? $this->elementPlugins[$element_types] : [];
    }

    return $this->elementPlugins;
  }

  /**
   * Returns an array of element plugins for a field type.
   *
   * @param string|null $element_types
   *   (optional) The name of a field type, or NULL to retrieve all widget
   *   options. Defaults to NULL.
   *
   * @return array
   *   If no field type is provided, returns a nested array of all widget types,
   *   keyed by field type human name.
   */
  public function getPluginInstancesByType($element_types = NULL) {
    if (!$this->pluginCollection) {
      $configurations = [];
      $settings = exo_form_get_settings();
      foreach ($this->getDefinitions() as $id => $definition) {
        $configurations[$id] = ['id' => $id] + $settings;
      }
      $this->pluginCollection = new ExoFormPluginCollection($this, $configurations);
    }
    $instances = [];
    foreach ($this->getPluginsByType($element_types) as $id => $name) {
      if ($this->pluginCollection->has($id)) {
        $instances[$id] = $this->pluginCollection->get($id);
      }
    }
    return $instances;
  }

}
