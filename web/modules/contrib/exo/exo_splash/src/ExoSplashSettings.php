<?php

namespace Drupal\exo_splash;

use Drupal\exo\ExoSettingsBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ExoFormSettings.
 *
 * @package Drupal\exo_form
 */
class ExoSplashSettings extends ExoSettingsBase {

  /**
   * {@inheritdoc}
   */
  public function getModuleId() {
    return 'exo_splash';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $form['theme'] = [
      '#type' => 'exo_radios',
      '#title' => $this->t('Theme'),
      '#exo_style' => 'inline',
      '#options' => exo_theme_options(TRUE, TRUE),
      '#empty_option' => $this->t('Custom'),
      '#attributes' => [
        'class' => ['exo-modal-theme'],
      ],
      '#default_value' => $this->getSetting(['theme']),
    ];

    $form['animation'] = [
      '#type' => 'exo_radios',
      '#title' => $this->t('Animation'),
      '#exo_style' => 'inline',
      '#options' => [
        '' => $this->t('Custom'),
        'slide-up-left' => $this->t('Slide Up and Left'),
      ],
      '#default_value' => $this->getSetting(['animation']),
    ];

    $form['logo_color'] = [
      '#type' => 'exo_radios',
      '#title' => $this->t('Color Logo'),
      '#exo_style' => 'inline',
      '#options' => [
        '' => $this->t('Default'),
        'white' => $this->t('White'),
        'black' => $this->t('Black'),
      ],
      '#description' => $this->t('Will try to color the logo with CSS.'),
      '#default_value' => $this->getSetting(['logo_color']),
    ];

    $form['once'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show once'),
      '#description' => $this->t('By default, the splash will only show once per session.'),
      '#default_value' => $this->getSetting(['once']),
    ];

    $form['debug'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Debugging'),
      '#description' => $this->t('Debugging will enable click-by-click sequences.'),
      '#default_value' => $this->getSetting(['debug']),
    ];

    return $form;
  }

}
