<?php

namespace Drupal\exo_breadcrumbs;

use Drupal\exo\ExoSettingsBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ExoFixedSettings.
 *
 * @package Drupal\exo_breadcrumbs
 */
class ExoBreadcrumbsSettings extends ExoSettingsBase {

  /**
   * {@inheritdoc}
   */
  public function getModuleId() {
    return 'exo_breadcrumbs';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $form['home_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Home Title'),
      '#description' => $this->t('The first link within the breadcrumbs list.'),
      '#default_value' => $this->getSetting(['home_title']),
      '#required' => TRUE,
    ];

    return $form;
  }

}
