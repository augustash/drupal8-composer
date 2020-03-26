<?php

namespace Drupal\exo;

use Drupal\Component\Plugin\ConfigurableInterface;

/**
 * Defines an object which is used to provide settings to eXo settings.
 */
interface ExoSettingsPluginWithSettingsInterface extends ConfigurableInterface {

  /**
   * An array of setting keys to exclude from diff comparisons.
   *
   * @return array
   *   An array of setting keys.
   */
  public function diffExcludes();

}
