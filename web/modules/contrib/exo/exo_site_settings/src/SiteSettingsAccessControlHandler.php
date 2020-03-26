<?php

namespace Drupal\exo_site_settings;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the config page entity.
 *
 * @see \Drupal\exo_site_settings\Entity\SiteSettings.
 */
class SiteSettingsAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\exo_site_settings\Entity\SiteSettingsInterface $entity */
    switch ($operation) {
      case 'view':
        return AccessResult::allowed();

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit config pages')->orIf(AccessResult::allowedIfHasPermission($account, 'edit ' . $entity->bundle() . ' config page'));

      case 'delete':
        return AccessResult::forbidden();
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    $access = AccessResult::allowedIfHasPermission($account, 'edit config pages');
    if ($entity_bundle) {
      $access->orIf(AccessResult::allowedIfHasPermission($account, 'edit ' . $entity_bundle->id() . ' config page'));
    }
    return $access;
  }

}
