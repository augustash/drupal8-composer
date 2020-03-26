<?php

namespace Drupal\exo_icon;

use Drupal\Component\Assertion\Inspector;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\UseCacheBackendTrait;

/**
 * Class ExoIconRepository.
 */
class ExoIconRepository implements ExoIconRepositoryInterface {
  use UseCacheBackendTrait;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The icon instances.
   *
   * @var array
   */
  protected $instances;

  /**
   * The icon definitions.
   *
   * @var array
   */
  protected $definitions;

  /**
   * The icon definitions.
   *
   * @var array
   */
  protected $definitionsFiltered;

  /**
   * Constructs a new ExoIconRepository object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, CacheBackendInterface $cache_discovery) {
    $this->entityTypeManager = $entity_type_manager;
    $this->setCacheBackend($cache_discovery, 'exo_icon_definitions', ['exo_icon_definitions']);
  }

  /**
   * Get icon instances.
   *
   * @var array $definitions
   *   An array of icon definitions.
   *
   * @return \Drupal\exo_icon\ExoIconInterface[]
   *   An array of icon instances.
   */
  public function getInstances(array $definitions) {
    $instances = [];
    foreach ($definitions as $id => $definition) {
      $instances[$id] = $this->getInstance($definition);
    }
    return $instances;
  }

  /**
   * Get icon instance.
   *
   * @var array $definition
   *   An icon definition.
   *
   * @return \Drupal\exo_icon\ExoIconInterface
   *   An icon instance.
   */
  public function getInstance(array $definition) {
    if (isset($definition['id'])) {
      $id = $definition['id'];
      if (!isset($this->instances[$id])) {
        $this->instances[$id] = ExoIcon::create($definition);
      }
      return $this->instances[$id];
    }
    return NULL;
  }

  /**
   * Get icon instance by icon id.
   *
   * @var string $id
   *   An icon id.
   *
   * @return \Drupal\exo_icon\ExoIconInterface
   *   An icon instance.
   */
  public function getInstanceById($id) {
    $definition = $this->getDefinition($id, TRUE);
    if ($definition) {
      return $this->getInstance($definition);
    }
    if (substr($id, 0, 8) === 'regular-') {
      $globals = array_intersect_key($this->getPackagesByGlobal(), array_flip($this->getSystemPackageIds()));
      if ($globals) {
        $global = reset($globals);
        $id = str_replace('regular-', $global->id() . '-', $id);
        $definition = $this->getDefinition($id, TRUE);
        if ($definition) {
          return $this->getInstance($definition);
        }
      }
    }
    return NULL;
  }

  /**
   * Get icon definition by id.
   *
   * @param string $id
   *   The icon id.
   * @param bool $active_only
   *   The status.
   *
   * @return array
   *   An array of icon definitions.
   */
  public function getDefinition($id, $active_only = FALSE) {
    $definitions = $active_only ? $this->getDefinitionsByStatus() : $this->getDefinitions();
    foreach ($definitions as $definition) {
      if ($definition['id'] == $id) {
        return $definition;
      }
    }
    return NULL;
  }

  /**
   * Get icon definitions from all icon packages.
   *
   * @return array
   *   An array of icon definitions.
   */
  public function getDefinitions() {
    $definitions = $this->getCachedDefinitions();
    if ($definitions == NULL) {
      $definitions = [];
      foreach ($this->getPackages() as $entity) {
        $definitions += $entity->getDefinitions();
      }
      $this->setCachedDefinitions($definitions);
    }
    return $definitions;
  }

  /**
   * Get icon definitions by status.
   *
   * @param bool $status
   *   The status.
   *
   * @return array
   *   An array of icon definitions.
   */
  public function getDefinitionsByStatus($status = TRUE) {
    if (!isset($this->definitionsFiltered['status'][$status])) {
      $this->definitionsFiltered['status'][$status] = array_filter($this->getDefinitions(), function ($definition) use ($status) {
        return $definition['status'] == $status;
      });
    }
    return $this->definitionsFiltered['status'][$status];
  }

  /**
   * Get icon definitions from a specific eXo Icon package.
   *
   * @return array
   *   An array of icon definitions.
   */
  public function getDefinitionsByPackage($package_id) {
    if (!isset($this->definitionsFiltered['package'][$package_id])) {
      $this->definitionsFiltered['package'][$package_id] = array_filter($this->getDefinitions(), function ($definition) use ($package_id) {
        return $definition['package_id'] == $package_id;
      });
    }
    return $this->definitionsFiltered['package'][$package_id];
  }

  /**
   * Get eXo icon entities.
   *
   * @var array $ids
   *   An array of package ids.
   * @var bool $status
   *   The status.
   *
   * @return \Drupal\exo_icon\Entity\ExoIconInterface[]
   *   An array of exo_icon entities.
   */
  public function getPackages($ids = NULL, $status = NULL) {
    $properties = [];
    if ($ids) {
      $properties['id'] = $ids;
    }
    if ($status !== NULL) {
      $properties['status'] = $status;
    }
    return $this->entityTypeManager->getStorage('exo_icon_package')->loadByProperties($properties);
  }

  /**
   * Get eXo icon entities by status.
   *
   * @var bool $status
   *   The status.
   *
   * @return \Drupal\exo_icon\Entity\ExoIconInterface[]
   *   An array of exo_icon entities.
   */
  public function getPackagesByStatus($status = TRUE) {
    return $this->entityTypeManager->getStorage('exo_icon_package')->loadByProperties(['status' => $status]);
  }

  /**
   * Get icon definitions by global flag.
   *
   * @var bool $global
   *   The global status.
   *
   * @return array
   *   An array of icon definitions.
   */
  public function getPackagesByGlobal($global = TRUE) {
    return $this->entityTypeManager->getStorage('exo_icon_package')->loadByProperties(['global' => $global]);
  }

  /**
   * The icon packages that are valid system packages.
   *
   * @return array
   *   An array of icon pagckage ids.
   */
  public function getSystemPackageIds() {
    return [
      'regular',
      'duotone',
      'solid',
      'thin',
    ];
  }

  /**
   * Get eXo icon entities as id => label.
   *
   * @var bool $status
   *   The status.
   *
   * @return array
   *   An array of exo_icon entities as labels keyed by id.
   */
  public function getPackagesAsLabels($status = TRUE) {
    $labels = [];
    foreach ($this->getPackagesByStatus($status) as $package) {
      $labels[$package->id()] = $package->label();
    }
    return $labels;
  }

  /**
   * Initialize the cache backend.
   *
   * Plugin icons are cached using the provided cache backend. The
   * interface language is added as a suffix to the cache key.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param string $cache_key
   *   Cache key prefix to use, the language code will be appended
   *   automatically.
   * @param array $cache_tags
   *   (optional) When providing a list of cache tags, the cached
   *   icons are tagged with the provided cache tags. These cache tags can
   *   then be used to clear the corresponding cached icons. Note
   *   that this should be used with care! For clearing all cached
   *   icons of a manager, call that manager's
   *   clearCachedDefinitions() method. Only use cache tags when cached
   *   icons should be cleared along with other, related cache entries.
   */
  public function setCacheBackend(CacheBackendInterface $cache_backend, $cache_key, array $cache_tags = []) {
    assert(Inspector::assertAllStrings($cache_tags), 'Cache Tags must be strings.');
    $this->cacheBackend = $cache_backend;
    $this->cacheKey = $cache_key;
    $this->cacheTags = $cache_tags;
  }

  /**
   * Returns the cached icons.
   *
   * @return array|null
   *   On success this will return an array of icons. On failure
   *   this should return NULL, indicating to other methods that this has not
   *   yet been defined. Success with no values should return as an empty array
   *   and would actually be returned by the getIcons() method.
   */
  protected function getCachedDefinitions() {
    if (!isset($this->definitions)) {
      $this->definitions = NULL;
      if ($cache = $this->cacheGet($this->cacheKey)) {
        $this->definitions = $cache->data;
      }
    }
    return $this->definitions;
  }

  /**
   * Sets a cache of icons.
   *
   * @param array $icons
   *   List of icons to store in cache.
   */
  protected function setCachedDefinitions(array $icons) {
    $this->cacheSet($this->cacheKey, $icons, Cache::PERMANENT, $this->cacheTags);
    $this->icons = $icons;
  }

}
