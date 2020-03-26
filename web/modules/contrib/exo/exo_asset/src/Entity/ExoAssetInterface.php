<?php

namespace Drupal\exo_asset\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Asset entities.
 *
 * @ingroup exo_asset
 */
interface ExoAssetInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Gets the Asset creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Asset.
   */
  public function getCreatedTime();

  /**
   * Sets the Asset creation timestamp.
   *
   * @param int $timestamp
   *   The Asset creation timestamp.
   *
   * @return \Drupal\exo_asset\Entity\ExoAssetInterface
   *   The called Asset entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Asset published status indicator.
   *
   * Unpublished Asset are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Asset is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Asset.
   *
   * @param bool $published
   *   TRUE to set this Asset to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\exo_asset\Entity\ExoAssetInterface
   *   The called Asset entity.
   */
  public function setPublished($published);

  /**
   * Gets the Asset revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Asset revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\exo_asset\Entity\ExoAssetInterface
   *   The called Asset entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Asset revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Asset revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\exo_asset\Entity\ExoAssetInterface
   *   The called Asset entity.
   */
  public function setRevisionUserId($uid);

}
