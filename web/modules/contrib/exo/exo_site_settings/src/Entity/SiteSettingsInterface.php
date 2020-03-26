<?php

namespace Drupal\exo_site_settings\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining config pages.
 *
 * @ingroup exo_site_settings
 */
interface SiteSettingsInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Gets the config page creation timestamp.
   *
   * @return int
   *   Creation timestamp of the config page.
   */
  public function getCreatedTime();

  /**
   * Sets the config page creation timestamp.
   *
   * @param int $timestamp
   *   The config page creation timestamp.
   *
   * @return \Drupal\exo_site_settings\Entity\SiteSettingsInterface
   *   The called config page entity.
   */
  public function setCreatedTime($timestamp);

}
