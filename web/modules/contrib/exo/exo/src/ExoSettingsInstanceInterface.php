<?php

namespace Drupal\exo;

/**
 * Defines an object which is used to store instance settings.
 */
interface ExoSettingsInstanceInterface {

  /**
   * Sets the value for a setting by name.
   *
   * @param string $key
   *   The name of the setting.
   * @param mixed $value
   *   The value of the setting.
   *
   * @return $this
   */
  public function setSetting($key, $value);

}
