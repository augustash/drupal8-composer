<?php

namespace Drupal\exo_asset;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\exo_asset\Entity\ExoAssetInterface;

/**
 * Defines the storage handler class for Asset entities.
 *
 * This extends the base storage class, adding required special handling for
 * Asset entities.
 *
 * @ingroup exo_asset
 */
class ExoAssetStorage extends SqlContentEntityStorage implements ExoAssetStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(ExoAssetInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {exo_asset_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {exo_asset_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(ExoAssetInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {exo_asset_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('exo_asset_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
