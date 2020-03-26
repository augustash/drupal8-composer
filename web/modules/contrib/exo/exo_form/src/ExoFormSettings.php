<?php

namespace Drupal\exo_form;

use Drupal\exo\ExoSettingsBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ExoFormSettings.
 *
 * @package Drupal\exo_form
 */
class ExoFormSettings extends ExoSettingsBase {

  /**
   * {@inheritdoc}
   */
  public function getModuleId() {
    return 'exo_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $form['theme'] = [
      '#type' => 'select',
      '#title' => $this->t('Theme'),
      '#options' => exo_theme_options(),
      '#empty_option' => $this->t('Custom'),
      '#attributes' => [
        'class' => ['exo-modal-theme'],
      ],
      '#default_value' => $this->getSetting(['theme']),
    ];

    // Support deprecated 'float' option.
    if ($this->getSetting(['float'])) {
      $this->setSetting(['style'], 'float');
    }

    $form['style'] = [
      '#type' => 'radios',
      '#title' => $this->t('Style'),
      '#options' => [
        '' => $this->t('Default'),
        'float' => $this->t('Float'),
        'intersect' => $this->t('Intersect'),
      ],
      '#default_value' => $this->getSetting(['style']),
    ];

    $form['wrap'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Style the form wrapper'),
      '#description' => $this->t('Useful when the page background is the same as the input backgrounds. It will stylize the form tag itself.'),
      '#default_value' => $this->getSetting(['wrap']),
    ];

    return $form;
  }

}
