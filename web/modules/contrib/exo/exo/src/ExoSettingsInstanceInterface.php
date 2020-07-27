<?php

namespace Drupal\exo;

use Drupal\Core\Form\FormStateInterface;

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
   * Gets the settings.
   *
   * @return array
   *   The array of settings.
   */
  public function getSettings();

  /**
   * Get site settings that are different from the default settings.
   */
  public function getSiteSettingsDiff();

  /**
   * Return local settings not matching those in the site settings.
   *
   * @param bool $clean
   *   Clean settings against default settings returning the values of only
   *   those settings that exist as defaults.
   * @param bool $prepare
   *   Prepare for rendering will call alter hooks and allow the settings to be
   *   prepared for use. Should not be used when saving items. Only when using
   *   items.
   *
   * @return array
   *   An array containing only settings different than the site settings.
   */
  public function getLocalSettingsDiff($clean = TRUE, $prepare = TRUE);

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
  public function validateForm(array &$form, FormStateInterface $form_state);

  /**
   * Submit form values.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array $form, FormStateInterface $form_state);

}
