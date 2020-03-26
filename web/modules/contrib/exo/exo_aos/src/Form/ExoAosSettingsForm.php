<?php

namespace Drupal\exo_aos\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\exo\ExoSettingsFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ExoAosSettingsForm.
 */
class ExoAosSettingsForm extends ExoSettingsFormBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('exo_aos.settings')
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
    $form['global']['disable'] = [
      '#type' => 'select',
      '#title' => $this->t('Disable'),
      '#options' => [
        0 => $this->t('Not disabled'),
        'phone' => $this->t('Phone'),
        'tablet' => $this->t('Tablet'),
        'mobile' => $this->t('Mobile'),
      ],
      '#default_value' => $this->exoSettings->getSetting(['disable']),
    ];

    $form['global']['startEvent'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Start Event'),
      '#description' => $this->t('name of the event dispatched on the document, that AOS should initialize on'),
      '#required' => TRUE,
      '#default_value' => $this->exoSettings->getSetting(['startEvent']),
    ];

    $form['global']['initClassName'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Init Class Name'),
      '#description' => $this->t('class applied after initialization'),
      '#required' => TRUE,
      '#default_value' => $this->exoSettings->getSetting(['initClassName']),
    ];

    $form['global']['animatedClassName'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Animated Class Name'),
      '#description' => $this->t('class applied on animation'),
      '#default_value' => $this->exoSettings->getSetting(['animatedClassName']),
    ];

    $form['global']['useClassNames'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use Class Names'),
      '#description' => $this->t('if true, will add content of `data-aos` as classes on scroll'),
      '#default_value' => $this->exoSettings->getSetting(['useClassNames']),
    ];

    $form['global']['disableMutationObserver'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable Mutation Observer'),
      '#description' => $this->t("disables automatic mutations' detections (advanced)"),
      '#default_value' => $this->exoSettings->getSetting(['disableMutationObserver']),
    ];

    $form['global']['debounceDelay'] = [
      '#type' => 'number',
      '#title' => $this->t('Debounce Delay'),
      '#description' => $this->t('the delay on debounce used while resizing window (advanced)'),
      '#required' => TRUE,
      '#default_value' => $this->exoSettings->getSetting(['debounceDelay']),
    ];

    $form['global']['throttleDelay'] = [
      '#type' => 'number',
      '#title' => $this->t('Throttle Delay'),
      '#description' => $this->t('the delay on throttle used while scrolling the page (advanced)'),
      '#required' => TRUE,
      '#default_value' => $this->exoSettings->getSetting(['throttleDelay']),
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
