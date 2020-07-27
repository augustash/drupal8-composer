<?php

namespace Drupal\exo_alchemist\Plugin;

use Drupal\Core\Field\WidgetInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides methods for interactacting with field forms.
 */
trait ExoComponentFieldFormTrait {

  /**
   * {@inheritdoc}
   */
  public function widgetAlter(WidgetInterface $widget, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function formAlter(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function formValidate(array $form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function formSubmit(array $form, FormStateInterface $form_state) {
  }

}
