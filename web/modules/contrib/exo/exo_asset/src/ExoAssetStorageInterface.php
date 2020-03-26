<?php

namespace Drupal\exo_asset;

use Drupal\Core\Entity\ContentEntityStorageInterface;
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
interface ExoAssetStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Asset revision IDs for a specific Asset.
   *
   * @param \Drupal\exo_asset\Entity\ExoAssetInterface $entity
   *   The Asset entity.
   *
   * @return int[]
   *   Asset revision IDs (in ascending order).
   */
  public function revisionIds(ExoAssetInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Asset author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Asset revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\exo_asset\Entity\ExoAssetInterface $entity
   *   The Asset entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(ExoAssetInterface $entity);

  /**
   * Unsets the language for all Asset with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
