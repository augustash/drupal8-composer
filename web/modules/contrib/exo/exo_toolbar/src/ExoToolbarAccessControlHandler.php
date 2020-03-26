<?php

namespace Drupal\exo_toolbar;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityHandlerInterface;
use Drupal\exo\Shared\ExoVisibilityEntityAccessControlTrait;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Defines the access control handler for the eXo Toolbar entity type.
 *
 * @see \Drupal\exo_toolbar\Entity\Block
 */
class ExoToolbarAccessControlHandler extends EntityAccessControlHandler implements EntityHandlerInterface {
  use ExoVisibilityEntityAccessControlTrait {
    ExoVisibilityEntityAccessControlTrait::checkAccess as checkVisibilityAccess;
  }

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    if ($operation == 'view' && !$account->hasPermission('access exo toolbar')) {
      return AccessResult::forbidden()->addCacheableDependency($entity)->addCacheContexts(['user.permissions']);
    }
    return $this->checkVisibilityAccess($entity, $operation, $account);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultVisibilityAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    return AccessResult::allowed()->addCacheContexts(['user.permissions']);
  }

}
