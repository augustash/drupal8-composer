<?php

namespace Drupal\exo_toolbar;

/**
 * Defines an interface for eXo toolbar JS settings.
 */
interface ExoToolbarJsSettingsInterface {

  /**
   * Add JS settings.
   *
   * @return $this
   */
  public function addJsSettings(array $value);

  /**
   * Add JS setting.
   *
   * @param string $name
   *   The setting name.
   * @param mixed $value
   *   The setting value.
   *
   * @return $this
   */
  public function addJsSetting($name, $value);

  /**
   * Get the JS settings.
   *
   * @return array
   *   The settings.
   */
  public function getJsSettings();

  /**
   * Get a JS setting.
   *
   * @return array
   *   The settings.
   */
  public function getJsSetting($name);

}
