<?php

namespace Drupal\exo;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\SubformState;

/**
 * Defines an eXo modal.
 */
class ExoSettingsPluginSelectInstance extends ExoSettingsInstance implements ExoSettingsPluginSelectInstanceInterface {

  /**
   * Constructs a new ExoModal.
   */
  public function __construct(ExoSettingsInterface $exo_settings, $local_settings, $id = NULL) {
    $local_settings += [
      'plugin' => '',
      'plugin_settings' => [
        'exo_default' => 1,
      ],
    ];
    parent::__construct($exo_settings, $local_settings, $id);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $allow_defaults = TRUE) {
    $form_state->set('exo_settings_is_local', TRUE);
    $id = Html::getId('exo-settings-plugin-select-' . $this->exoSettings->getModuleId());
    $plugin_instance = $this->getPluginInstance($form, $form_state);

    $options = [];
    foreach ($this->exoSettings->getPluginDefinitions() as $plugin_id => $definition) {
      $options[$plugin_id] = $definition['label'];
    }

    $form['plugin'] = [
      '#type' => 'select',
      '#title' => $this->t('Plugin'),
      '#options' => $options,
      '#default_value' => $this->getSetting('plugin'),
      '#empty_option' => $this->t('- Select -'),
      '#required' => TRUE,
      '#exo_settings_plugin' => TRUE,
      '#ajax' => [
        'callback' => [get_class($this), 'ajaxMenuStyle'],
        'event' => 'change',
        'wrapper' => $id,
        'progress' => [
          'type' => 'throbber',
          'message' => t('Getting menu style settings'),
        ],
      ],
    ];

    $form['plugin_settings'] = [
      '#type' => 'container',
      '#id' => $id,
    ];
    if ($plugin_instance) {
      $form['plugin_settings'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Plugin Settings'),
      ] + $plugin_instance->buildForm($form['plugin_settings'], $form_state);
    }

    return $form;
  }

  /**
   * Get style type instance.
   */
  protected function getPluginInstance(array $form, FormStateInterface $form_state) {
    $plugin = $this->getSetting('plugin');
    $settings = $this->getSetting('plugin_settings');
    $trigger = $form_state->getTriggeringElement();
    if ($trigger && !empty($trigger['#exo_settings_plugin'])) {
      $plugin = $form_state->getCompleteFormState()->getValue($trigger['#array_parents'], $plugin);
      $settings = $form_state->getCompleteFormState()->getValue(array_merge(array_slice($trigger['#array_parents'], 0, -1), ['plugin_settings']), $settings);
    }
    if (!$plugin) {
      return NULL;
    }
    return $this->exoSettings->createPluginInstance($plugin, $settings);
  }

  /**
   * AJAX function to get display IDs for a particular View.
   */
  public static function ajaxMenuStyle(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $element = NestedArray::getValue($form, array_slice($trigger['#array_parents'], 0, -1));
    return $element['plugin_settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $plugin_instance = $this->getPluginInstance($form, $form_state);
    if ($plugin_instance) {
      $plugin_instance->validateForm($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $plugin_instance = $this->getPluginInstance($form, $form_state);
    if ($plugin_instance) {
      $subform_state = SubformState::createForSubform($form['plugin_settings'], $form, $form_state);
      $plugin_instance->submitForm($form['plugin_settings'], $subform_state);
    }
  }

}
