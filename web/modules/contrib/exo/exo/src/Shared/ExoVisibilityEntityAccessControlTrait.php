<?php

namespace Drupal\exo\Shared;

use Drupal\Component\Plugin\Exception\ContextException;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Condition\ConditionAccessResolverTrait;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Plugin\Context\ContextHandlerInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides visibility entity access control helpers.
 */
trait ExoVisibilityEntityAccessControlTrait {
  use ConditionAccessResolverTrait;

  /**
   * The plugin context handler.
   *
   * @var \Drupal\Core\Plugin\Context\ContextHandlerInterface
   */
  protected $contextHandler;

  /**
   * The context manager service.
   *
   * @var \Drupal\Core\Plugin\Context\ContextRepositoryInterface
   */
  protected $contextRepository;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('context.handler'),
      $container->get('context.repository')
    );
  }

  /**
   * Constructs the eXo Toolbar access control handler instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Plugin\Context\ContextHandlerInterface $context_handler
   *   The ContextHandler for applying contexts to conditions properly.
   * @param \Drupal\Core\Plugin\Context\ContextRepositoryInterface $context_repository
   *   The lazy context repository service.
   */
  public function __construct(EntityTypeInterface $entity_type, ContextHandlerInterface $context_handler, ContextRepositoryInterface $context_repository) {
    parent::__construct($entity_type);
    $this->contextHandler = $context_handler;
    $this->contextRepository = $context_repository;
  }

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\exo_toolbar\Entity\ExoToolbarInterface $entity */
    if ($operation != 'view') {
      return parent::checkAccess($entity, $operation, $account);
    }

    // Don't grant access to disabled entities.
    if (!$entity->status()) {
      return AccessResult::forbidden()->addCacheableDependency($entity);
    }
    else {
      $conditions = [];
      $missing_context = FALSE;
      foreach ($entity->getVisibilityConditions() as $condition_id => $condition) {
        if ($condition instanceof ContextAwarePluginInterface) {
          try {
            $contexts = $this->contextRepository->getRuntimeContexts(array_values($condition->getContextMapping()));
            $this->contextHandler->applyContextMapping($condition, $contexts);
          }
          catch (ContextException $e) {
            $missing_context = TRUE;
          }
        }
        $conditions[$condition_id] = $condition;
      }

      if ($missing_context) {
        // If any context is missing then we might be missing cacheable
        // metadata, and don't know based on what conditions the exo_toolbar is
        // accessible or not. For example, exo_entities that have a node type
        // condition will have a missing context on any non-node route like the
        // frontpage.
        // @todo Avoid setting max-age 0 for some or all cases, for example by
        //   treating available contexts without value differently in
        //   https://www.drupal.org/node/2521956.
        $access = AccessResult::forbidden()->setCacheMaxAge(0);
      }
      elseif ($this->resolveConditions($conditions, 'and') !== FALSE) {
        if (method_exists($entity, 'getPlugin')) {
          // Delegate to the plugin.
          $plugin = $entity->getPlugin();
          try {
            if ($plugin instanceof ContextAwarePluginInterface) {
              $contexts = $this->contextRepository->getRuntimeContexts(array_values($plugin->getContextMapping()));
              $this->contextHandler->applyContextMapping($plugin, $contexts);
            }
            $access = $plugin->access($account, TRUE);
          }
          catch (ContextException $e) {
            // Setting access to forbidden if any context is missing for the same
            // reasons as with conditions (described in the comment above).
            // @todo Avoid setting max-age 0 for some or all cases, for example by
            //   treating available contexts without value differently in
            //   https://www.drupal.org/node/2521956.
            $access = AccessResult::forbidden()->setCacheMaxAge(0);
          }
        }
        else {
          $access = $this->getDefaultVisibilityAccess($entity, $operation, $account);
        }
      }
      else {
        $access = AccessResult::forbidden();
      }

      $this->mergeCacheabilityFromConditions($access, $conditions);

      // Ensure that access is evaluated again when the exo_toolbar changes.
      return $access->addCacheableDependency($entity);
    }
  }

  /**
   * Default access when no conditions have prevented access.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   An access result object.
   */
  protected function getDefaultVisibilityAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    return $access = parent::checkAccess($entity, $operation, $account, TRUE);
  }

  /**
   * Merges cacheable metadata from conditions onto the access result object.
   *
   * @param \Drupal\Core\Access\AccessResult $access
   *   The access result object.
   * @param \Drupal\Core\Condition\ConditionInterface[] $conditions
   *   List of visibility conditions.
   */
  protected function mergeCacheabilityFromConditions(AccessResult $access, array $conditions) {
    foreach ($conditions as $condition) {
      if ($condition instanceof CacheableDependencyInterface) {
        $access->addCacheTags($condition->getCacheTags());
        $access->addCacheContexts($condition->getCacheContexts());
        $access->setCacheMaxAge(Cache::mergeMaxAges($access->getCacheMaxAge(), $condition->getCacheMaxAge()));
      }
    }
  }

}
