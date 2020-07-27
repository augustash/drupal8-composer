<?php

namespace Drupal\exo_alchemist;

use Drupal\layout_builder\SectionStorageInterface;

/**
 * Defines an interface for Section Storage type plugins.
 */
interface ExoComponentSectionStorageInterface extends SectionStorageInterface {

  /**
   * Get the entity associated with this storage.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The entity.
   */
  public function getEntity();

  /**
   * Get the parent entity associated with this storage.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   The entity.
   */
  public function getParentEntity();

  /**
   * Gets the view mode associated with this storage.
   *
   * @return string
   *   The string.
   */
  public function getViewMode();

  /**
   * Given a region, determine its size.
   *
   * @param int $delta
   *   The section delta.
   * @param string $region
   *   The region id.
   *
   * @return string
   *   Can me small, medium, large, full.
   */
  public function getRegionSize($delta, $region);

}
