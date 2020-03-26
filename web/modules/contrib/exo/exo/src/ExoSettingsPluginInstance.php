<?php

namespace Drupal\exo;

use Drupal\Core\Form\FormStateInterface;

/**
 * Defines an eXo modal.
 */
class ExoSettingsPluginInstance extends ExoSettingsInstance implements ExoSettingsPluginInstanceInterface {

  /**
   * The plugin id.
   *
   * @var string
   */
  protected $pluginId;

  /**
   * {@inheritdoc}
   */
  protected function mergePresets(array $settings) {
    return $this->exoSettings->mergePluginPresets($this->pluginId, $settings);
  }

  /**
   * Constructs a new ExoModal.
   */
  public function __construct(ExoSettingsInterface $exo_settings, $local_settings, $site_settings, $default_settings, $plugin_id) {
    $this->pluginId = $plugin_id;
    parent::__construct($exo_settings, $local_settings, $plugin_id);
    $this->siteSettings = $this->mergePresets($site_settings);
    $this->defaultSettings = $default_settings;
  }

  /**
   * An array of setting keys to exclude from diff comparisons.
   *
   * @return array
   *   An array of setting keys.
   */
  public function getDiffExcludes() {
    return $this->exoSettings->getPluginDiffExcludes($this->pluginId);
  }

  /**
   * {@inheritdoc}
   */
  protected function buildSettingsForm(array $form, FormStateInterface $form_state) {
    $this->exoSettings->setPluginSettings($this->pluginId, $this->getSettings());
    return $this->exoSettings->buildPluginForm($this->pluginId, $form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->exoSettings->setPluginSettings($this->pluginId, $this->getSettings());
    $this->exoSettings->validatePluginForm($this->pluginId, $form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->exoSettings->setPluginSettings($this->pluginId, $this->getSettings());
    $this->exoSettings->submitPluginForm($this->pluginId, $form, $form_state);
  }

}
