<?php

namespace Drupal\exo_alchemist;

/**
 * Defines an interface for Section Storage type plugins.
 */
interface ExoComponentSectionNestedStorageInterface {

  /**
   * Get the parent overrides storage.
   *
   * @return \Drupal\layout_builder\OverridesSectionStorageInterface
   *   The overrides storage of the layout.
   */
  public function getParentEntityStorage();

}
