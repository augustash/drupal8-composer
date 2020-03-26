<?php

namespace Drupal\exo_toolbar\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\exo\Shared\ExoVisibilityEntityTrait;
use Drupal\exo\Shared\ExoVisibilityEntityInterface;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\exo_toolbar\Plugin\ExoToolbarItemCollection;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Component\Serialization\Json;
use Drupal\exo_toolbar\Plugin\ExoToolbarRegionPluginInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\Cache\Cache;

/**
 * Defines the eXo Toolbar Item entity.
 *
 * @ConfigEntityType(
 *   id = "exo_toolbar_item",
 *   label = @Translation("eXo Toolbar Item"),
 *   handlers = {
 *     "access" = "Drupal\exo_toolbar\ExoToolbarItemAccessControlHandler",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\exo_toolbar\ExoToolbarItemListBuilder",
 *     "form" = {
 *       "default" = "Drupal\exo_toolbar\Form\ExoToolbarItemForm",
 *       "delete" = "Drupal\exo_toolbar\Form\ExoToolbarItemDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\exo_toolbar\ExoToolbarItemHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "exo_toolbar_item",
 *   admin_permission = "administer exo toolbar",
 *   static_cache = true,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "status" = "status",
 *     "weight" = "weight"
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/exo/toolbar/items/{exo_toolbar_item}",
 *     "delete-form" = "/admin/config/exo/toolbar/items/{exo_toolbar_item}/delete"
 *   },
 *   config_export = {
 *     "id",
 *     "toolbar",
 *     "region",
 *     "section",
 *     "weight",
 *     "plugin",
 *     "settings",
 *     "visibility",
 *   },
 *   lookup_keys = {
 *     "toolbar"
 *   }
 * )
 */
class ExoToolbarItem extends ConfigEntityBase implements ExoToolbarItemInterface, EntityWithPluginCollectionInterface, ExoVisibilityEntityInterface {
  use ExoVisibilityEntityTrait;

  /**
   * The eXo Toolbar Item ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The eXo Toolbar Item label.
   *
   * @var string
   */
  protected $label;

  /**
   * The plugin instance settings.
   *
   * @var array
   */
  protected $settings = [];

  /**
   * The toolbar this item is placed in.
   *
   * @var string
   */
  protected $toolbar;

  /**
   * The region this toolbar item is placed in.
   *
   * @var string
   */
  protected $region;

  /**
   * The section within a region this toolbar item is placed in.
   *
   * @var string
   */
  protected $section;

  /**
   * The toolbar item weight.
   *
   * @var int
   */
  protected $weight;

  /**
   * The plugin instance ID.
   *
   * @var string
   */
  protected $plugin;

  /**
   * The plugin collection that holds the item plugin for this entity.
   *
   * @var \Drupal\exo_toolbar\Plugin\ExoToolbarItemCollection
   */
  protected $pluginCollection;

  /**
   * The module handler to invoke hooks on.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected static $moduleHandler;

  /**
   * Boolean if we are in edit mode.
   *
   * @var bool
   */
  protected static $isAdminMode;

  /**
   * {@inheritdoc}
   */
  public function getPlugin() {
    return $this->getPluginCollection()->get($this->plugin);
  }

  /**
   * Encapsulates the creation of the item's LazyPluginCollection.
   *
   * @return \Drupal\Component\Plugin\LazyPluginCollection
   *   The item's plugin collection.
   */
  protected function getPluginCollection() {
    if (!$this->pluginCollection) {
      $configuration = $this->getSettings() + ['toolbar_item_id' => $this->id()];
      $this->pluginCollection = new ExoToolbarItemCollection(\Drupal::service('plugin.manager.exo_toolbar_item'), $this->plugin, $configuration, $this);
      $this->pluginCollection->get($this->plugin)->setItem($this);
    }
    return $this->pluginCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    return [
      'settings' => $this->getPluginCollection(),
      'visibility' => $this->getVisibilityConditions(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginId() {
    return $this->plugin;
  }

  /**
   * {@inheritdoc}
   */
  public function getToolbarId() {
    return $this->toolbar;
  }

  /**
   * {@inheritdoc}
   */
  public function getToolbar() {
    return ExoToolbar::load($this->getToolbarId());
  }

  /**
   * {@inheritdoc}
   */
  public function getRegionId() {
    return $this->region;
  }

  /**
   * {@inheritdoc}
   */
  public function getRegion() {
    return $this->getToolbar()->getRegion($this->getRegionId());
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return $this->weight;
  }

  /**
   * {@inheritdoc}
   */
  public function getSectionId() {
    return $this->section;
  }

  /**
   * {@inheritdoc}
   */
  public function getSection() {
    return $this->getRegion()->getSection($this->getSectionId());
  }

  /**
   * {@inheritdoc}
   */
  public function getSectionLabel() {
    if ($section = $this->getSection()) {
      return $section->label();
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getOperations() {
    $operations = $this->getDefaultOperations();
    $operations += self::moduleHandler()->invokeAll('entity_operation', [$this]);
    self::$moduleHandler->alter('entity_operation', $operations, $this);
    uasort($operations, '\Drupal\Component\Utility\SortArray::sortByWeightElement');
    return $operations;
  }

  /**
   * Gets this items's default operations.
   *
   * @return array
   *   The array structure is identical to the return value of
   *   self::getOperations().
   */
  public function getDefaultOperations() {
    $operations = [];
    if ($this->access('update') && $this->hasLinkTemplate('edit-form')) {
      $operations['edit'] = [
        'title' => t('Edit'),
        'weight' => 10,
        'url' => $this->toUrl('edit-form'),
        'attributes' => [
          'class' => ['exo-ajax'],
          'data-dialog-type' => 'exo_modal',
          'data-dialog-options' => Json::encode([
            'openFullscreen' => TRUE,
          ]),
        ],
      ];
    }
    if ($this->access('delete') && $this->hasLinkTemplate('delete-form')) {
      $operations['delete'] = [
        'title' => t('Delete'),
        'weight' => 100,
        'url' => $this->toUrl('delete-form'),
        'attributes' => [
          'class' => ['exo-ajax'],
          'data-dialog-type' => 'exo_modal',
          'data-dialog-options' => Json::encode([
            'width' => 700,
          ]),
        ],
      ];
    }
    if ($this->access('update')) {
      $operations += $this->getPlugin()->getOperations($this);
    }
    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $admin_mode = self::isAdminMode();
    $plugin = $this->getPlugin();
    $build = $plugin->build($admin_mode) + [
      '#exoToolbarItemJsSettings' => [],
      'aside' => [],
    ];
    $drupal_settings = [
      'id' => $this->id(),
      'toolbar' => $this->getToolbarId(),
      'region' => $this->getRegionId(),
      'section' => $this->getSectionId(),
    ] + $build['#exoToolbarItemJsSettings'];
    if ($admin_mode) {
      $drupal_settings += [
        'weight' => $this->getWeight(),
        'allow_sort' => $plugin->allowSort(),
        'allow_admin' => $plugin->allowAdmin(),
      ];
      if ($drupal_settings['allow_admin']) {
        $build['#attached']['library'][] = 'exo_toolbar/admin';
      }
      if ($drupal_settings['allow_sort']) {
        $build['#attached']['library'][] = 'exo_toolbar/sort';
      }
      $build['#attributes']['data-exo-item-id'] = $this->id();
      if ($plugin->allowAdmin()) {
        $build['aside']['labels']['#access'] = FALSE;
        $build['aside']['operations'] = [
          '#theme' => 'links__exo_toolbar_item_operations',
          '#links' => $this->getOperations(),
          '#attached' => [
            'library' => ['exo/ajax'],
          ],
          '#attributes' => [
            'class' => [
              'exo-toolbar-item-aside-tip',
              'exo-toolbar-item-operations',
            ],
          ],
        ];
      }
    }
    $build['#attributes'] += ['class' => []];
    $build['#attributes']['id'] = Html::getId('exo-toolbar-item-' . $this->id());
    // Make sure this class if first.
    array_unshift($build['#attributes']['class'], 'exo-toolbar-item-type-' . Html::getClass($plugin->getPluginId()));
    array_unshift($build['#attributes']['class'], 'exo-toolbar-item');
    $build['#attached']['drupalSettings']['exoToolbar']['toolbars'][$this->getToolbarId()]['items'][$this->id()] = $drupal_settings;
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    $settings = $this->getSettings();
    if ($settings['title']) {
      return $settings['title'];
    }
    else {
      $definition = $this->getPlugin()->getPluginDefinition();
      return $definition['admin_label'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getSettings() {
    return $this->get('settings');
  }

  /**
   * Sorts active items by weight; sorts inactive items by name.
   */
  public static function sort(ConfigEntityInterface $a, ConfigEntityInterface $b) {
    // Separate enabled from disabled.
    $status = (int) $b->status() - (int) $a->status();
    if ($status !== 0) {
      return $status;
    }

    // Sort by weight.
    $weight = $a->getWeight() - $b->getWeight();
    if ($weight) {
      return $weight;
    }

    // Sort by label.
    return strcmp($a->label(), $b->label());
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    parent::calculateDependencies();
    $parts = explode(':', $this->getRegionId());
    if ($parts[0] == 'item' && ($parent_item = $this->getToolbar()->getItem($parts[1]))) {
      // If the item belongs to a parent item, use it as the dependency.
      $this->addDependency('config', $parent_item->getConfigDependencyName());
    }
    else {
      $this->addDependency('config', $this->getToolbar()->getConfigDependencyName());
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setToolbar($toolbar) {
    $this->toolbar = $toolbar;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setRegion($region) {
    $this->region = $region;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setSection($section) {
    $this->section = $section;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight) {
    $this->weight = $weight;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function alterRegionJsSettings(array &$settings, ExoToolbarRegionPluginInterface $region) {
    $this->getPlugin()->alterRegionJsSettings($settings, $region);
  }

  /**
   * {@inheritdoc}
   */
  public function alterRegionElement(array &$element, ExoToolbarRegionPluginInterface $region) {
    $this->getPlugin()->alterRegionElement($element, $region);
  }

  /**
   * {@inheritdoc}
   */
  public function alterSectionElement(array &$element, array $context) {
    $this->getPlugin()->alterSectionElement($element, $context);
  }

  /**
   * Gets the module handler.
   *
   * @return \Drupal\Core\Extension\ModuleHandlerInterface
   *   The module handler.
   */
  protected function moduleHandler() {
    if (!static::$moduleHandler) {
      static::$moduleHandler = \Drupal::moduleHandler();
    }
    return static::$moduleHandler;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $tags = Cache::mergeTags(parent::getCacheTags(), $this->getPlugin()->getCacheTags());
    return $tags;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    $cache_contexts = Cache::mergeContexts(parent::getCacheContexts(), $this->getPlugin()->getCacheContexts());
    $cache_contexts[] = 'url.path.is_exo_toolbar_admin';
    return $cache_contexts;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    $max_age = Cache::mergeMaxAges(parent::getCacheMaxAge(), $this->getPlugin()->getCacheMaxAge());
    return $max_age;
  }

  /**
   * Check if in edit mode.
   *
   * @return bool
   *   TRUE if in edit mode.
   */
  protected static function isAdminMode() {
    if (!isset(static::$isAdminMode)) {
      static::$isAdminMode = \Drupal::service('exo_toolbar.path.matcher')->isAdmin();
    }
    return static::$isAdminMode;
  }

}
