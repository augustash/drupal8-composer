<?php

namespace Drupal\exo_alchemist\Plugin;

/**
 * Defines an interface for Component Field plugins.
 */
interface ExoComponentFieldDisplayInterface extends ExoComponentFieldInterface {

  /**
   * Use display.
   *
   * @return bool
   *   If TRUE will use display.
   */
  public function useDisplay();

  /**
   * Get entity type id.
   *
   * @return string
   *   The entity type id.
   */
  public function getDisplayedEntityTypeId();

  /**
   * Get bundle id.
   *
   * @return string
   *   The bundle id.
   */
  public function getDisplayedBundle();

}
