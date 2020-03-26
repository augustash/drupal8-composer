<?php

namespace Drupal\exo_toolbar\Plugin;

use Drupal\exo_toolbar\ExoToolbarSection;
use Drupal\Core\Form\FormStateInterface;

/**
 * Base class for eXo theme plugins.
 */
abstract class ExoToolbarRegionVerticalBase extends ExoToolbarRegionBase implements ExoToolbarRegionVerticalInterface {

  /**
   * {@inheritdoc}
   */
  public function getSections() {
    return [
      new ExoToolbarSection('top', $this->t('Top')),
      new ExoToolbarSection('bottom', $this->t('Bottom'), 'desc'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getAlignment() {
    return 'vertical';
  }

  /**
   * Returns generic default configuration for block plugins.
   *
   * @return array
   *   An associative array with the default configuration.
   */
  protected function baseConfigurationDefaults() {
    return [
      'expanded' => FALSE,
    ] + parent::baseConfigurationDefaults();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['expanded'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show as expanded'),
      '#description' => $this->t('When in expanded mode, the region will always show item labels and icons.'),
      '#default_value' => $this->configuration['expanded'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function isExpanded() {
    return (bool) $this->configuration['expanded'];
  }

}
