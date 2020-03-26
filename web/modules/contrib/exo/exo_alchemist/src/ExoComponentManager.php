<?php

namespace Drupal\exo_alchemist;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\PhpStorage\PhpStorageFactory;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeDiscoveryDecorator;
use Drupal\exo_alchemist\Plugin\Discovery\ExoComponentDiscovery;
use Drupal\Core\Plugin\CategorizingPluginManagerTrait;
use Drupal\Core\Render\Element;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\exo_alchemist\Definition\ExoComponentDefinition;
use Drupal\exo_alchemist\Plugin\Discovery\ExoComponentInstalledDiscovery;
use Drupal\layout_builder\Entity\LayoutBuilderEntityViewDisplay;
use Drupal\layout_builder\LayoutEntityHelperTrait;

/**
 * Provides the default exo_component manager.
 */
class ExoComponentManager extends DefaultPluginManager {
  use LayoutEntityHelperTrait;

  use CategorizingPluginManagerTrait {
    getSortedDefinitions as traitGetSortedDefinitions;
  }

  /**
   * The entity bundle type to use as component entities.
   */
  const ENTITY_BUNDLE_TYPE = 'block_content_type';

  /**
   * The entity type to use as component entities.
   */
  const ENTITY_TYPE = 'block_content';

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  public $entityTypeManager;

  /**
   * The theme handler service.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * The eXo component field manager.
   *
   * @var \Drupal\exo_alchemist\ExoComponentFieldManager
   */
  protected $exoComponentFieldManager;

  /**
   * The eXo component property manager.
   *
   * @var \Drupal\exo_alchemist\ExoComponentPropertyManager
   */
  protected $exoComponentPropertyManager;

  /**
   * The eXo component animation manager.
   *
   * @var \Drupal\exo_alchemist\ExoComponentAnimationManager
   */
  protected $exoComponentAnimationManager;

  /**
   * Cached definitions array.
   *
   * @var array
   */
  protected $definitionsInstalled;

  /**
   * Constructs a new ExoComponentManager object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity type manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler service.
   * @param \Drupal\exo_alchemist\ExoComponentFieldManager $exo_component_field_manager
   *   The eXo component field manager.
   * @param \Drupal\exo_alchemist\ExoComponentPropertyManager $exo_component_property_manager
   *   The eXo component property manager.
   * @param \Drupal\exo_alchemist\ExoComponentAnimationManager $exo_component_animation_manager
   *   The eXo component animation manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager, CacheBackendInterface $cache, ModuleHandlerInterface $module_handler, ThemeHandlerInterface $theme_handler, ExoComponentFieldManager $exo_component_field_manager, ExoComponentPropertyManager $exo_component_property_manager, ExoComponentAnimationManager $exo_component_animation_manager) {
    $this->entityTypeManager = $entity_manager;
    $this->moduleHandler = $module_handler;
    $this->themeHandler = $theme_handler;
    $this->exoComponentFieldManager = $exo_component_field_manager;
    $this->exoComponentPropertyManager = $exo_component_property_manager;
    $this->exoComponentAnimationManager = $exo_component_animation_manager;
    $this->setCacheBackend($cache, 'exo_component_info', ['exo_component_info']);
    $this->alterInfo('exo_component_info');
  }

  /**
   * Determines if the provider of a definition exists.
   *
   * @return bool
   *   TRUE if provider exists, FALSE otherwise.
   */
  protected function providerExists($provider) {
    return $this->moduleHandler->moduleExists($provider) || $this->themeHandler->themeExists($provider);
  }

  /**
   * Get the eXo component field manager.
   *
   * @return \Drupal\exo_alchemist\ExoComponentFieldManager
   *   The eXo component field manager.
   */
  public function getExoComponentFieldManager() {
    return $this->exoComponentFieldManager;
  }

  /**
   * Get the eXo component property manager.
   *
   * @return \Drupal\exo_alchemist\ExoComponentPropertyManager
   *   The eXo component property manager.
   */
  public function getExoComponentPropertyManager() {
    return $this->exoComponentPropertyManager;
  }

  /**
   * Get the eXo component animation manager.
   *
   * @return \Drupal\exo_alchemist\ExoComponentAnimationManager
   *   The eXo component animation manager.
   */
  public function getExoComponentAnimationManager() {
    return $this->exoComponentAnimationManager;
  }

  /**
   * {@inheritdoc}
   */
  public function hasInstalledDefinition($plugin_id) {
    return (bool) $this->getInstalledDefinition($plugin_id, FALSE);
  }

  /**
   * Gets installed definitions.
   */
  public function getInstalledDefinitions() {
    $definitions = $this->getCachedInstalledDefinitions();
    if (!isset($definitions)) {
      $definitions = $this->findInstalledDefinitions();
      $this->setCachedInstalledDefinitions($definitions);
    }
    return $definitions;
  }

  /**
   * {@inheritdoc}
   *
   * @return \Drupal\exo_alchemist\Definition\ExoComponentDefinition
   *   The eXo component definition.
   */
  public function getInstalledDefinition($plugin_id, $exception_on_invalid = TRUE) {
    $definitions = $this->getInstalledDefinitions();
    return $this->doGetDefinition($definitions, $plugin_id, $exception_on_invalid);
  }

  /**
   * Finds plugin definitions.
   *
   * @return array
   *   List of definitions to store in cache.
   */
  protected function findInstalledDefinitions() {
    $definitions = $this->getInstalledDiscovery()->getDefinitions();
    foreach ($definitions as $plugin_id => &$definition) {
      $this->processInstalledDefinition($definition, $plugin_id);
      if (!$this->loadEntity($definition)) {
        $this->buildEntity($definition);
      }
    }
    return $definitions;
  }

  /**
   * {@inheritdoc}
   */
  protected function getDiscovery() {
    if (!isset($this->discovery)) {
      $discovery = new ExoComponentDiscovery($this->getDirectories());
      $discovery->addTranslatableProperty('label', 'label_context');
      $this->discovery = new ContainerDerivativeDiscoveryDecorator($discovery);
    }
    return $this->discovery;
  }

  /**
   * {@inheritdoc}
   */
  protected function getInstalledDiscovery() {
    if (!isset($this->installedDiscovery)) {
      $discovery = new ExoComponentInstalledDiscovery($this->entityTypeManager);
      $discovery->addTranslatableProperty('label', 'label_context');
      $this->installedDiscovery = new ContainerDerivativeDiscoveryDecorator($discovery);
    }
    return $this->installedDiscovery;
  }

  /**
   * Create a list of all directories to scan.
   *
   * This includes all module directories and directories of the default theme
   * and all of its possible base themes.
   *
   * @return array
   *   An array containing directory paths keyed by their extension name.
   */
  protected function getDirectories() {
    $default_theme = $this->themeHandler->getDefault();
    $base_themes = $this->themeHandler->getBaseThemes($this->themeHandler->listInfo(), $default_theme);
    $theme_directories = $this->themeHandler->getThemeDirectories();

    $directories = [];
    if (isset($theme_directories[$default_theme])) {
      $directories[$default_theme] = $theme_directories[$default_theme];
      foreach ($base_themes as $name => $theme) {
        $directories[$name] = $theme_directories[$name];
      }
    }

    return $directories + $this->moduleHandler->getModuleDirectories();
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition(&$definition, $plugin_id) {
    $this->processDefinitionCategory($definition);
    // You can add validation of the plugin definition here.
    if (empty($definition['id'])) {
      throw new PluginException(sprintf('eXo Component plugin (%s) definition "id" is required.', $plugin_id));
    }
    $definition = new ExoComponentDefinition($definition);
    $this->exoComponentFieldManager->processComponentDefinition($definition);
    $this->exoComponentPropertyManager->processComponentDefinition($definition);
  }

  /**
   * {@inheritdoc}
   */
  public function processInstalledDefinition(&$definition, $plugin_id) {
    $this->processDefinition($definition, $plugin_id);
    $definition->setInstalled();
    $definition->setMissing(!$this->hasDefinition($definition->id()));
  }

  /**
   * Check access on a definition.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   * @param string $operation
   *   The operation. Can be 'create', 'update', 'delete', 'view'.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function accessDefinition(ExoComponentDefinition $definition, $operation, AccountInterface $account = NULL) {
    if (empty($account)) {
      $account = \Drupal::currentUser();
    }
    if ($definition->isMissing()) {
      return AccessResult::allowedIf($operation == 'delete')
        ->andIf(AccessResult::allowedIfHasPermission($account, 'administer exo alchemist'));
    }
    switch ($operation) {
      case 'create':
        return AccessResult::allowedIf(!$definition->isInstalled())
          ->andIf(AccessResult::allowedIfHasPermission($account, 'administer exo alchemist'));

      case 'update':
        if (!$this->loadEntity($definition)) {
          return AccessResult::allowed();
        }
        return AccessResult::allowedIf($definition->isInstalled() && $definition->toArray() !== $this->getDefinition($definition->id())->toArray())
          ->andIf(AccessResult::allowedIfHasPermission($account, 'administer exo alchemist'));
    }
    return AccessResult::allowedIf($definition->isInstalled())
      ->andIf(AccessResult::allowedIfHasPermission($account, 'administer exo alchemist'));
  }

  /**
   * {@inheritdoc}
   */
  public function clearCachedDefinitions() {
    parent::clearCachedDefinitions();
    Cache::invalidateTags(['library_info']);
    PhpStorageFactory::get('twig')->deleteAll();
    \Drupal::service('theme.registry')->reset();
    $this->definitionsInstalled = NULL;
  }

  /**
   * {@inheritdoc}
   */
  protected function getCachedInstalledDefinitions() {
    if (!isset($this->definitionsInstalled) && $cache = $this->cacheGet($this->cacheKey . '_installed')) {
      $this->definitionsInstalled = $cache->data;
    }
    return $this->definitionsInstalled;
  }

  /**
   * {@inheritdoc}
   */
  protected function setCachedInstalledDefinitions($definitions) {
    $this->cacheSet($this->cacheKey . '_installed', $definitions, Cache::PERMANENT, $this->cacheTags);
    $this->definitionsInstalled = $definitions;
  }

  /**
   * Extract component definition from a config entity.
   *
   * @param \Drupal\Core\Config\Entity\ConfigEntityInterface $entity
   *   The entity bundle to load the definition from.
   * @param bool $no_cache
   *   If TRUE, will build component definition directly from the provided
   *   entity.
   *
   * @return \Drupal\exo_alchemist\Definition\ExoComponentDefinition
   *   The component definition.
   */
  public function getEntityBundleComponentDefinition(ConfigEntityInterface $entity, $no_cache = FALSE) {
    $definition = NULL;
    if ($definition = ExoComponentInstalledDiscovery::getEntityDefinition($entity)) {
      if ($no_cache) {
        $this->processInstalledDefinition($definition, $definition['id']);
      }
      else {
        $definition = $this->getInstalledDefinition($definition['id']);
      }
    }
    return $definition;
  }

  /**
   * Extract component definition from a content entity.
   *
   * @param \Drupal\Core\Config\Entity\ContentEntityInterface $entity
   *   The entity bundle to load the definition from.
   *
   * @return \Drupal\exo_alchemist\Definition\ExoComponentDefinition
   *   The component definition.
   */
  public function getEntityComponentDefinition(ContentEntityInterface $entity) {
    $plugin_id = $this->getPluginIdFromSafeId($entity->bundle());
    return $this->getInstalledDefinition($plugin_id, FALSE);
  }

  /**
   * Get property info.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   */
  public function getPropertyInfo(ExoComponentDefinition $definition) {
    $info = [
      '_global' => [
        'label' => $this->t('Component'),
        'properties' => [
          'attributes' => $this->t('Component attributes.'),
          'content_attributes' => $this->t('Component content attributes.'),
        ],
      ],
    ];
    $info += $this->exoComponentFieldManager->getPropertyInfo($definition);
    $info += $this->exoComponentPropertyManager->getPropertyInfo($definition);
    $info += $this->exoComponentAnimationManager->getPropertyInfo($definition);
    return $info;
  }

  /**
   * Load content type bundle for definition.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   *
   * @return \Drupal\Core\Config\Entity\ConfigEntityInterface
   *   The config entity.
   */
  public function loadEntityType(ExoComponentDefinition $definition) {
    $storage = $this->entityTypeManager->getStorage(self::ENTITY_BUNDLE_TYPE);
    return $storage->load($definition->safeId());
  }

  /**
   * Install content type bundle for definition.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   *
   * @return \Drupal\Core\Config\Entity\ConfigEntityInterface
   *   The config entity.
   */
  public function installEntityType(ExoComponentDefinition $definition) {
    $entity = $this->loadEntityType($definition);
    // That can be called even when an entity type is already installed. It can
    // be called over and over and will only run if entity has not yet been
    // created.
    if (!$entity) {
      $storage = $this->entityTypeManager->getStorage(self::ENTITY_BUNDLE_TYPE);
      $entity = $storage->create([
        'id' => $definition->safeId(),
        'label' => $definition->getLabel(),
        'description' => $definition->getDescription(),
      ]);
      $this->exoComponentFieldManager->installEntityType($definition, $entity);
      $this->saveEntityType($definition, $entity);
    }
    return $entity;
  }

  /**
   * Update content type bundle for definition.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   *
   * @return \Drupal\Core\Config\Entity\ConfigEntityInterface|null
   *   The config entity.
   */
  public function updateEntityType(ExoComponentDefinition $definition) {
    if ($entity = $this->loadEntityType($definition)) {
      // Clean up all dependents as they are rebuilt each time.
      $this->cleanEntityTypeDependents($entity);
      $this->exoComponentFieldManager->updateEntityType($definition, $entity);
      $this->saveEntityType($definition, $entity, $this->getEntityBundleComponentDefinition($entity));
      return $entity;
    }
    return NULL;
  }

  /**
   * Save content type bundle for definition.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   * @param \Drupal\Core\Config\Entity\ConfigEntityInterface $entity
   *   The entity bundle to load the definition from.
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $original_definition
   *   The current component definition.
   */
  protected function saveEntityType(ExoComponentDefinition $definition, ConfigEntityInterface $entity, ExoComponentDefinition $original_definition = NULL) {
    $entity->setThirdPartySetting('exo_alchemist', 'exo_component_definition', $definition->toArray());
    if ($dependents = $definition->calculateDependents()) {
      $entity->setThirdPartySetting('exo_alchemist', 'exo_component_dependents', $dependents);
    }
    else {
      $entity->unsetThirdPartySetting('exo_alchemist', 'exo_component_dependents');
    }
    $entity->save();
    $this->buildEntityType($definition, $original_definition);
    $this->buildEntity($definition);
  }

  /**
   * Uninstall content type bundle for definition.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   */
  public function uninstallEntityType(ExoComponentDefinition $definition) {
    if ($entity = $this->loadEntityType($definition)) {
      $entity_storage = $this->entityTypeManager->getStorage(self::ENTITY_TYPE);
      // Delete all entities belonging to this entity type.
      $entities = $entity_storage->loadByProperties(['type' => $entity->id()]);
      if (!empty($entities)) {
        $entity_storage->delete($entities);
      }
      // Clean up all dependents as they are rebuilt each time.
      $this->cleanEntityTypeDependents($entity);
      $this->exoComponentFieldManager->uninstallEntityType($definition, $entity);
      // Delete entity type.
      $entity->delete();
      $this->clearCachedDefinitions();
    }
  }

  /**
   * Move thumbnail into files directory.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   */
  public function installThumbnail(ExoComponentDefinition $definition) {
    // Clean up existing thumbnail first.
    $this->uninstallThumbnail($definition);
    $file_system = \Drupal::service('file_system');
    $directory = $definition->getThumbnailDirectory();
    if ($thumbnail = $definition->getThumbnailSource()) {
      $path = Url::fromUri('base://' . ltrim($thumbnail, '/'));
      $file_system->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY);
      $file_system->copy(\Drupal::service('app.root') . $path->toString(), $definition->getThumbnailUri());
    }
  }

  /**
   * Remove thumbnail from files directory.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   */
  public function uninstallThumbnail(ExoComponentDefinition $definition) {
    $file_system = \Drupal::service('file_system');
    $directory = $definition->getThumbnailDirectory();
    $file_system->deleteRecursive($directory);
    if ($image_style = $this->entityTypeManager->getStorage('image_style')->load('exo_alchemist_preview')) {
      /** @var \Drupal\Image\Entity\ImageStyle $image_style */
      $image_style->flush($definition->getThumbnailUri());
    }
  }

  /**
   * Clean content type bundle of dependents.
   *
   * @param \Drupal\Core\Config\Entity\ConfigEntityInterface $entity
   *   The entity bundle to load the definition from.
   */
  protected function cleanEntityTypeDependents(ConfigEntityInterface $entity) {
    $dependents = $entity->getThirdPartySetting('exo_alchemist', 'exo_component_dependents');
    if ($dependents) {
      $config_factory = \Drupal::configFactory();
      $config_manager = \Drupal::service('config.manager');
      foreach ($dependents as $type => $names) {
        foreach ($names as $name) {
          switch ($type) {
            case 'config':
              $entity = $config_manager->loadConfigEntityByName($name);
              if ($entity) {
                $entity->delete();
              }
              else {
                $config = $config_factory->getEditable($name);
                if ($config) {
                  $config->delete();
                }
              }
              break;

            case 'content':
              list($entity_id,, $uuid) = explode(':', $name);
              $entity = \Drupal::service('entity.repository')->loadEntityByConfigTarget($entity_id, $uuid);
              if ($entity) {
                $entity->delete();
              }
              break;

            default:
              throw new \Exception('Only content and config dependents are supported.');
          }
        }
      }
    }
  }

  /**
   * Build content type bundle as defined in definition.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $original_definition
   *   The current component definition.
   */
  public function buildEntityType(ExoComponentDefinition $definition, ExoComponentDefinition $original_definition = NULL) {
    $entity_type = ExoComponentManager::ENTITY_TYPE;
    $bundle = $definition->safeId();

    // Form display.
    $storage = $this->entityTypeManager->getStorage('entity_form_display');
    $form_display = $storage->load($entity_type . '.' . $bundle . '.default');
    if (!$form_display) {
      $form_display = $storage->create([
        'targetEntityType' => $entity_type,
        'bundle' => $bundle,
        'mode' => 'default',
        'status' => TRUE,
      ]);
    }
    // View display.
    $storage = $this->entityTypeManager->getStorage('entity_view_display');
    $view_display = $storage->load($entity_type . '.' . $bundle . '.default');
    if (!$view_display) {
      $view_display = $storage->create([
        'targetEntityType' => $entity_type,
        'bundle' => $bundle,
        'mode' => 'default',
        'status' => TRUE,
      ]);
    }

    /** @var \Drupal\Core\Entity\Display\EntityFormDisplayInterface $form_display */
    /** @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface $view_display */
    $this->exoComponentFieldManager->buildEntityType($definition, $form_display, $view_display, $original_definition);
    $this->exoComponentPropertyManager->buildEntityType($definition, $form_display, $view_display, $original_definition);

    if (count($form_display->getComponents())) {
      $form_display->save();
    }
    if (count($view_display->getComponents())) {
      $view_display->save();
    }
  }

  /**
   * Get the field changes given a definition.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $to_definition
   *   The component definition.
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition|null $from_definition
   *   The component definition.
   *
   * @return array
   *   An array containing ['add' => [], 'update' => [], 'remove' => []].
   */
  public function getEntityBundleFieldChanges(ExoComponentDefinition $to_definition, ExoComponentDefinition $from_definition = NULL) {
    return $this->exoComponentFieldManager->getEntityBundleFieldChanges($to_definition, $from_definition);
  }

  /**
   * Load default content for definition.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   * @param bool $no_cache
   *   Flag indicating if entity should be cached.
   * @param int $delta
   *   The delta of the entity to load. This is useful for sequence fields
   *   where multiple defaults are created.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   The content entity.
   */
  public function loadEntity(ExoComponentDefinition $definition, $no_cache = FALSE, $delta = 0) {
    $entity = NULL;
    $storage = $this->entityTypeManager->getStorage(self::ENTITY_TYPE);
    $entities = $storage->loadByProperties([
      'type' => $definition->safeId(),
      'alchemist_default' => TRUE,
    ]);
    if (!empty($entities)) {
      $entities = array_values($entities);
      if (!empty($entities[$delta])) {
        $entity = $entities[$delta];
        if ($no_cache) {
          $storage->resetCache([$entity->id()]);
        }
      }
    }
    return $entity;
  }

  /**
   * Build content entity for definition.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   The content entity.
   */
  public function buildEntity(ExoComponentDefinition $definition) {
    $entity = $this->loadEntity($definition);
    if (!$entity) {
      $storage = $this->entityTypeManager->getStorage(self::ENTITY_TYPE);
      $entity = $storage->create([
        'type' => $definition->safeId(),
        'info' => 'Preview for ' . $definition->getLabel(),
        'reusable' => FALSE,
        'alchemist_default' => TRUE,
      ]);
    }
    /** @var \Drupal\core\Entity\ContentEntityInterface $entity */
    $this->populateEntity($definition, $entity);
    return $entity;
  }

  /**
   * Build content entity for definition.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity to populate.
   */
  public function populateEntity(ExoComponentDefinition $definition, ContentEntityInterface $entity) {
    // Populate properties before fields as we use the form_display save hook
    // to create the default entity.
    $this->exoComponentPropertyManager->populateEntity($definition, $entity);
    $this->exoComponentFieldManager->populateEntity($definition, $entity);
    $entity->save();
  }

  /**
   * Called on update while layout building.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity to populate.
   */
  public function onUpdateEntity(ExoComponentDefinition $definition, ContentEntityInterface $entity) {
    $this->exoComponentFieldManager->onUpdateEntity($definition, $entity);
  }

  /**
   * Clone the default content for definition.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Optional entity. If not supplied, default will be used.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   The content entity.
   */
  public function cloneEntity(ExoComponentDefinition $definition, ContentEntityInterface $entity = NULL) {
    $entity = $entity ? $entity : $this->loadEntity($definition);
    if ($entity) {
      $entity = $entity->createDuplicate();
      $entity->set('alchemist_default', FALSE);
      $this->exoComponentFieldManager->cloneEntityFields($definition, $entity);
    }
    return $entity;
  }

  /**
   * Restore the default content for fields that are empty.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to restore.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   The content entity.
   */
  public function restoreEntity(ExoComponentDefinition $definition, ContentEntityInterface $entity) {
    $this->exoComponentFieldManager->restoreEntityFields($definition, $entity);
    return $entity;
  }

  /**
   * On post-save root content entity.
   *
   * The root content entity is the entity where layout builder is enabled.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param string $callback
   *   The callback to fire on the component field manager.
   */
  protected function handleRootEntityCallback(EntityInterface $entity, $callback) {
    if (!$this->isLayoutCompatibleEntity($entity)) {
      return;
    }
    if ($sections = $this->getEntitySections($entity)) {
      $block_storage = $this->entityTypeManager->getStorage('block_content');
      foreach ($this->getInlineBlockComponents($sections) as $component) {
        /** @var \Drupal\layout_builder\Plugin\Block\InlineBlock $plugin */
        $plugin = $component->getPlugin();
        $configuration = $plugin->getConfiguration();
        $child_entity = NULL;
        if (!empty($configuration['block_revision_id'])) {
          $child_entity = $block_storage->loadByProperties([
            'revision_id' => $configuration['block_revision_id'],
          ]);
          $child_entity = array_pop($child_entity);
          /** @var \Drupal\Core\Entity\EntityInterface $child_entity */
          if ($child_entity) {
            $plugin_id = $this->getPluginIdFromSafeId($child_entity->bundle());
            if ($definition = $this->getInstalledDefinition($plugin_id, FALSE)) {
              $child_entity->exoComponentRoot = $entity;
              if (method_exists($this->exoComponentFieldManager, $callback)) {
                $this->exoComponentFieldManager->{$callback}($definition, $child_entity);
              }
            }
          }
        }
      }
    }
  }

  /**
   * On post-save root content entity.
   *
   * The root content entity is the entity where layout builder is enabled.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   */
  public function handleRootEntityUpdate(EntityInterface $entity) {
    $this->handleRootEntityCallback($entity, 'updateEntityFields');
  }

  /**
   * On delete root content entity.
   *
   * The root content entity is the entity where layout builder is enabled.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   */
  public function handleRootEntityDelete(EntityInterface $entity) {
    $this->handleRootEntityCallback($entity, 'deleteEntityFields');
  }

  /**
   * Update content entity for definition.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity.
   */
  public function updateEntity(ExoComponentDefinition $definition, ContentEntityInterface $entity) {
    $this->exoComponentFieldManager->updateEntityFields($definition, $entity);
  }

  /**
   * Uninstall content entity for definition.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity.
   */
  public function uninstallEntity(ExoComponentDefinition $definition, ContentEntityInterface $entity) {
    $this->exoComponentFieldManager->uninstallEntityFields($definition, $entity);
  }

  /**
   * View content entity for definition.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   * @param array $build
   *   The build array.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity.
   * @param \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display
   *   The entity display.
   * @param string $view_mode
   *   The view mode.
   */
  public function viewEntity(ExoComponentDefinition $definition, array &$build, ContentEntityInterface $entity, EntityViewDisplayInterface $display, $view_mode) {
    $route_match = \Drupal::routeMatch();
    $route_name = $route_match->getRouteName();
    $is_layout_builder = $display instanceof LayoutBuilderEntityViewDisplay && strpos($route_name, 'layout_builder.') === 0;
    $build += ['#attached' => []];
    $build['#theme'] = $definition->getThemeHook();
    $build['#theme_wrappers'][] = 'exo_component_wrapper';

    $build['#exo_component'] = $definition->id();
    $build['#is_layout_builder'] = $is_layout_builder;
    $build['#wrapper_attributes']['class'][] = 'exo-component-wrapper';
    $build['#attributes']['class'][] = 'exo-component';
    $build['#attributes']['class'][] = Html::getClass('exo-component-' . $definition->getName());
    $build['#content_attributes']['class'][] = 'exo-component-content';
    if ($definition->hasLibrary()) {
      $build['#attached']['library'][] = 'exo_alchemist/' . $definition->getLibraryId();
    }
    if ($is_layout_builder) {
      $section_storage = \Drupal::routeMatch()->getParameter('section_storage');
      $build['#wrapper_attributes']['class'][] = 'exo-component-edit';
      $build['#attached']['drupalSettings']['exoAlchemist']['entityType'] = self::ENTITY_TYPE;
      $build['#attached']['drupalSettings']['exoAlchemist']['storageType'] = $section_storage->getStorageType();
      $build['#attached']['drupalSettings']['exoAlchemist']['storageId'] = $section_storage->getStorageId();
      $build['#attached']['drupalSettings']['exoAlchemist']['sectionDelta'] = '0';
      $build['#attached']['drupalSettings']['exoAlchemist']['sectionRegion'] = 'content';
    }
    $values = $this->viewEntityValues($definition, $entity, $is_layout_builder);
    foreach ($values as $key => $value) {
      if (Element::property($key)) {
        $build[$key] = NestedArray::mergeDeep($build[$key], $value);
        continue;
      }
      $build['#' . $key] = $value;
    }
  }

  /**
   * View content entity for definition as values.
   *
   * Values are broken out this way so sequence and other nested fields can
   * access the raw values before they are turned into attributes.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity.
   * @param string $is_layout_builder
   *   TRUE if we are in layout builder mode.
   */
  public function viewEntityValues(ExoComponentDefinition $definition, ContentEntityInterface $entity, $is_layout_builder) {
    $values = [
      '#attached' => [],
      '#wrapper_attributes' => [],
      '#attributes' => [],
      '#content_attributes' => [],
    ];
    $this->exoComponentFieldManager->viewEntityValues($definition, $values, $entity, $is_layout_builder);
    $this->exoComponentPropertyManager->viewEntityValues($definition, $values, $entity, $is_layout_builder);
    $this->exoComponentAnimationManager->viewEntityValues($definition, $values, $entity, $is_layout_builder);
    return $values;
  }

  /**
   * Convert a safe id to a plugin id.
   *
   * @param string $safe_id
   *   The safe id.
   *
   * @return string
   *   The plugin id.
   */
  public function getPluginIdFromSafeId($safe_id) {
    foreach ($this->getInstalledDefinitions() as $plugin_id => $definition) {
      if ($safe_id === $definition->safeId()) {
        return $plugin_id;
      }
    }
    return NULL;
  }

}
