<?php

namespace Drupal\exo;

use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the interface for eXo settings.
 */
interface ExoSettingsInterface {

  /**
   * The module id implementing these options.
   *
   * @return string
   *   The module id implementing these options.
   */
  public function getModuleId();

  /**
   * Gets the configuration name that will be editable.
   *
   * @return string
   *   An opject name that are editable if called in conjunction with the
   *   trait's config() method.
   */
  public function getEditableConfigName();

  /**
   * Get default settings as defined in the base settings yml file.
   *
   * @return array
   *   The settings array.
   */
  public function getDefaultSettings();

  /**
   * Get site settings as defined in site config and config form.
   *
   * @return array
   *   The settings array.
   */
  public function getSiteSettings();

  /**
   * Create a local instance of settings.
   *
   * @param array $settings
   *   An array of local settings.
   *
   * @return \Drupal\exo\ExoSettingsInstanceInterface
   *   An instance of the local settings.
   */
  public function createInstance(array $settings);

  /**
   * Sets the settings.
   *
   * @return $this
   */
  public function setSettings(array $settings);

  /**
   * Set a setting.
   *
   * @param string $key
   *   A string that maps to a key within the configuration data.
   *   If no key is specified, then the entire data array is returned.
   * @param mixed $value
   *   The value to set.
   *
   * @return $this
   */
  public function setSetting($key, $value);

  /**
   * Gets the settings.
   *
   * @return array
   *   The array of settings.
   */
  public function getSettings();

  /**
   * Gets data from this settings object.
   *
   * @param string $key
   *   A string that maps to a key within the configuration data.
   *   If no key is specified, then the entire data array is returned.
   *
   * @return mixed
   *   The data that was requested.
   */
  public function getSetting($key = '');

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state);

  /**
   * Validate form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function validateForm(array $form, FormStateInterface $form_state);

  /**
   * Submit form values.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array $form, FormStateInterface $form_state);

  /**
   * Save settings.
   *
   * @param array $settings
   *   An associative array containing the settings to save.
   *
   * @return $this
   */
  public function saveSettings(array $settings);

  /**
   * Reset settings.
   *
   * @return $this
   */
  public function resetSettings();

  /**
   * Get system-defined presets.
   *
   * @return array
   *   An array of presets.
   */
  public function getPresets();

  /**
   * Get system-defined presets.
   *
   * @return array
   *   An array of presets with key being preset ID and value being label.
   */
  public function getPresetOptions();

  /**
   * Get site settings that are different from the default settings.
   */
  public function getSiteSettingsDiff();

  /**
   * Remove empty select values.
   *
   * Use via #element_validate.
   */
  public static function selectRemoveEmpty(array &$element, FormStateInterface $form_state);

}
