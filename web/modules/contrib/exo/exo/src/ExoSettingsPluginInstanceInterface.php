<?php

namespace Drupal\exo;

use Drupal\Core\Form\FormStateInterface;

/**
 * Defines an object which is used to store instance settings.
 */
interface ExoSettingsPluginInstanceInterface {

  /**
   * An array of setting keys to exclude from diff comparisons.
   *
   * @return array
   *   An array of setting keys.
   */
  public function getDiffExcludes();

  /**
   * Validate settings form.
   *
   * @param array $form
   *   The form.
   * @param Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function validateForm(array &$form, FormStateInterface $form_state);

  /**
   * Submit settings form.
   *
   * @param array $form
   *   The form.
   * @param Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function submitForm(array $form, FormStateInterface $form_state);

}
