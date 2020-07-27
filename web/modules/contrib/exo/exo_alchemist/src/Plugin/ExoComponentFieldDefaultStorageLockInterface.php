<?php

namespace Drupal\exo_alchemist\Plugin;

/**
 * Defines an interface for a field that shouldn't allow edit on entity display.
 */
interface ExoComponentFieldDefaultStorageLockInterface extends ExoComponentFieldInterface {

  /**
   * Return a message shown to users if they try to edit this field.
   *
   * This message will only be shows when trying to edit a field within the
   * entity display interface.
   *
   * @return string
   *   A value that will be shown to users.
   */
  public function getDefaultStorageLockMessage();

}
