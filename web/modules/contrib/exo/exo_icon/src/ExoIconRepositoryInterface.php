<?php

namespace Drupal\exo_icon;

/**
 * Interface ExoIconRepositoryInterface.
 */
interface ExoIconRepositoryInterface {

  /**
   * Get icon instance by icon id.
   *
   * @var string $id
   *   An icon id.
   *
   * @return \Drupal\exo_icon\ExoIconInterface
   *   An icon instance.
   */
  public function getInstanceById($id);

}
