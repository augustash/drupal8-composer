<?php

namespace Drupal\exo_alchemist;

/**
 * Defines an interface for Component Field plugins.
 */
interface ExoComponentContextInterface {

  /**
   * Check if layout builder.
   *
   * @param \Drupal\Core\Plugin\Context\Context[] $contexts
   *   An array of current contexts.
   *
   * @return bool
   *   TRUE if layout builder.
   */
  public function isLayoutBuilder(array $contexts);

  /**
   * Check if preview.
   *
   * @param \Drupal\Core\Plugin\Context\Context[] $contexts
   *   An array of current contexts.
   *
   * @return bool
   *   TRUE if preview.
   */
  public function isPreview(array $contexts);

  /**
   * Check if locked.
   *
   * @param \Drupal\Core\Plugin\Context\Context[] $contexts
   *   An array of current contexts.
   *
   * @return bool
   *   TRUE if locked.
   */
  public function isLocked(array $contexts);

  /**
   * Check if default storage.
   *
   * @param \Drupal\Core\Plugin\Context\Context[] $contexts
   *   An array of current contexts.
   *
   * @return bool
   *   TRUE if default storage.
   */
  public function isDefaultStorage(array $contexts);

}
