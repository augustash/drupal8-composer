<?php

namespace Drupal\exo_imagine\Form;

use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\exo\ExoSettingsFormBase;

/**
 * Class ExoImagineSettingsForm.
 */
class ExoImagineSettingsForm extends ExoSettingsFormBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('exo_imagine.settings')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $form['global'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Initialization settings'),
      '#weight' => -10,
      '#tree' => TRUE,
    ];

    $form['global']['webp'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable WebP Support'),
      '#default_value' => $this->exoSettings->getSetting('webp'),
      '#description' => $this->t('Automatically convert images to webp on supported browsers.'),
    ];

    $form['global']['webp_quality'] = [
      '#type' => 'number',
      '#title' => $this->t('WebP Quality'),
      '#default_value' => $this->exoSettings->getSetting('webp_quality'),
      '#description' => $this->t('Images will be encoded into WebP format if possible. This is the quality that will be used.'),
      '#min' => 1,
      '#max' => 100,
      '#step' => 1,
      '#states' => [
        'visible' => [
          ':input[name="global[webp]"]' => [
            'checked' => TRUE,
          ],
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    // Move instance settings into the global setting scope so that they get
    // saved.
    foreach ($form_state->getValue('global') as $setting => $value) {
      $form_state->setValue(['settings', $setting], $value);
    }
  }

}
