<?php

namespace Drupal\exo_alchemist\Plugin;

use Drupal\Core\Field\WidgetInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines an interface for fields that support forms.
 */
interface ExoComponentFieldFormInterface extends ExoComponentFieldInterface {

  /**
   * Method called before a widget form is built for use within a field form.
   *
   * @param Drupal\Core\Field\WidgetInterface $widget
   *   The widget definition.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state interface.
   */
  public function widgetAlter(WidgetInterface $widget, FormStateInterface $form_state);

  /**
   * Method called when displaying a form widget for a field.
   *
   * @param array $form
   *   The form to alter.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state interface.
   */
  public function formAlter(array &$form, FormStateInterface $form_state);

  /**
   * Method called when validating a form widget for a field.
   *
   * @param array $form
   *   The form to alter.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state interface.
   */
  public function formValidate(array $form, FormStateInterface $form_state);

  /**
   * Method called when submitting a form widget for a field.
   *
   * @param array $form
   *   The form to alter.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state interface.
   */
  public function formSubmit(array $form, FormStateInterface $form_state);

}
