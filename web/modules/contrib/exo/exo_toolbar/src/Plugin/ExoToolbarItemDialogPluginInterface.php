<?php

namespace Drupal\exo_toolbar\Plugin;

use Drupal\exo_toolbar\Entity\ExoToolbarItemInterface;

/**
 * Defines an interface for eXo Toolbar Item dialog plugins.
 */
interface ExoToolbarItemDialogPluginInterface extends ExoToolbarItemPluginInterface {

  /**
   * Get the dialog type plugin instance.
   *
   * @return \Drupal\exo_toolbar\Plugin\ExoToolbarDialogTypePluginInterface
   *   The dialog type plugin instance.
   */
  public function getDialogType();

  /**
   * Build dialog content.
   *
   * @param \Drupal\exo_toolbar\Entity\ExoToolbarItemInterface $exo_toolbar_item
   *   The eXo toolbar item.
   * @param string $arg
   *   An optional argument that can be passed through do alter the response.
   *
   * @return mixed
   *   The render array.
   */
  public function dialogBuild(ExoToolbarItemInterface $exo_toolbar_item, $arg = NULL);

}
