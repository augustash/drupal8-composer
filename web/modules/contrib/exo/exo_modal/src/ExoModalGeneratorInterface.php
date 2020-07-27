<?php

namespace Drupal\exo_modal;

/**
 * Defines an interface for generating eXo modals.
 */
interface ExoModalGeneratorInterface {

  /**
   * Generate an eXo modal.
   *
   * @param string $id
   *   The unique modal id.
   * @param array $settings
   *   The modal settings.
   * @param mixed $modal
   *   The modal content.
   *
   * @return \Drupal\exo_modal\ExoModalInterface
   *   An eXo modal.
   */
  public function generate($id, array $settings = [], $modal = NULL);

}
