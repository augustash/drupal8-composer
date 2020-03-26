<?php

namespace Drupal\exo_modal\Plugin\field_group\FieldGroupFormatter;

use Drupal\Component\Utility\Html;
use Drupal\field_group\FieldGroupFormatterBase;
use Drupal\Core\Form\FormState;
use Drupal\exo_modal\Element\ExoModal;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;

/**
 * Plugin implementation of the 'modal' formatter.
 *
 * @FieldGroupFormatter(
 *   id = "modal",
 *   label = @Translation("Modal"),
 *   description = @Translation("This fieldgroup renders the inner content in a modal with the title as legend."),
 *   supported_contexts = {
 *     "form",
 *     "view",
 *   }
 * )
 */
class Modal extends FieldGroupFormatterBase {

  protected $exoModalSettings;

  /**
   * {@inheritdoc}
   */
  public function preRender(&$element, $rendering_object) {

    $element += [
      '#type' => 'exo_modal',
      '#trigger_text' => Html::escape($this->t($this->getLabel())),
      '#trigger_icon' => $this->getSetting('icon'),
      '#trigger_as_button' => TRUE,
      '#use_close' => TRUE,
      '#attributes' => [],
      '#modal_settings' => $this->getSetting('modal'),
    ];
    $element['#modal_settings']['modal']['subtitle'] = $this->t('All changes made to this information will only be saved when the parent form is saved.');

    if ($this->getSetting('description')) {
      $element += [
        '#description' => $this->getSetting('description'),
      ];

      // When a fieldset has a description, an id is required.
      if (!$this->getSetting('id')) {
        $element['#id'] = Html::getId($this->group->group_name);
      }

    }

    if ($this->getSetting('id')) {
      $element['#id'] = Html::getId($this->getSetting('id'));
    }

    $classes = $this->getClasses();
    if (!empty($classes)) {
      $element['#attributes'] += ['class' => $classes];
    }

    $form_state = new FormState();
    $complete_form = [];
    ExoModal::processContainer($element, $form_state, $complete_form);
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return parent::defaultSettings() + [
      'icon' => '',
      'modal' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm() {
    $form = parent::settingsForm();
    $form['#process'][] = [$this, 'processModalSettings'];
    $form['#element_validate'][] = [$this, 'validateModalSettings'];

    $form['description'] = [
      '#title' => $this->t('Description'),
      '#type' => 'textarea',
      '#default_value' => $this->getSetting('description'),
      '#weight' => -4,
    ];

    if ($this->context == 'form') {
      $form['required_fields'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Mark group as required if it contains required fields.'),
        '#default_value' => $this->getSetting('required_fields'),
        '#weight' => 2,
      ];
    }

    $form['icon'] = [
      '#type' => 'exo_icon',
      '#title' => $this->t('Icon'),
      '#default_value' => $this->getSetting('icon'),
    ];

    $form['modal'] = [
      '#settings' => $this->getSetting('modal'),
      '#weight' => 15,
    ];

    return $form;
  }

  /**
   * Validate modal settings.
   */
  public function processModalSettings($form, FormStateInterface $form_state) {
    // Wow. Thanks field group module for NEVER giving me access to the form
    // state!
    $settings = $form['modal']['#settings'];
    if ($state_values = $form_state->getValue(array_merge($form['#parents'], ['modal']))) {
      $settings = $state_values;
    }
    $exo_settings_instance = $this->exoModalSettings()->createInstance($settings);
    $form['modal'] = $exo_settings_instance->buildForm($form['modal'], $form_state) + [
      '#type' => 'fieldset',
      '#title' => t('Modal'),
    ];
    $form['modal']['settings']['modal']['header']['subtitle']['#access'] = FALSE;
    $form['modal']['settings']['trigger']['#access'] = FALSE;
    return $form;
  }

  /**
   * Validate modal settings.
   */
  public function validateModalSettings($form, FormStateInterface $form_state) {
    $subform_state = SubformState::createForSubform($form['modal'], $form_state->getCompleteForm(), $form_state);
    $exo_settings_instance = $this->exoModalSettings()->createInstance($subform_state->getValues());
    $exo_settings_instance->validateForm($form['modal'], $subform_state);
    $exo_settings_instance->submitForm($form['modal'], $subform_state);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {

    $summary = parent::settingsSummary();

    if ($this->getSetting('required_fields')) {
      $summary[] = $this->t('Mark as required');
    }

    if ($this->getSetting('description')) {
      $summary[] = $this->t('Description : @description',
        ['@description' => $this->getSetting('description')]
      );
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultContextSettings($context) {
    $defaults = [
      'description' => '',
    ] + parent::defaultSettings($context);

    if ($context == 'form') {
      $defaults['required_fields'] = 1;
    }

    return $defaults;
  }

  /**
   * Gets the modal settings service.
   */
  protected function exoModalSettings() {
    if (!isset($this->exoModalSettings)) {
      $this->exoModalSettings = \Drupal::service('exo_modal.settings');
    }
    return $this->exoModalSettings;
  }

}
