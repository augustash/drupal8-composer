<?php

namespace Drupal\exo_imagine;

use Drupal\exo\ExoSettingsBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ExoImageSettings.
 */
class ExoImagineSettings extends ExoSettingsBase {

  /**
   * {@inheritdoc}
   */
  public function getModuleId() {
    return 'exo_imagine';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $form['animate'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Animate Reveal'),
      '#default_value' => $this->getSetting('animate'),
      '#description' => $this->t('Animate image in when fully loaded.'),
    ];

    $form['blur'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Blur'),
      '#default_value' => $this->getSetting('blur'),
      '#description' => $this->t('Show blurred image before full image has loaded.'),
    ];

    $form['visible'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Load when visible'),
      '#description' => $this->t('Full image will only be loaded when viewable within viewport.'),
      '#default_value' => $this->getSetting(['visible']),
    ];

    return $form;
  }

}
