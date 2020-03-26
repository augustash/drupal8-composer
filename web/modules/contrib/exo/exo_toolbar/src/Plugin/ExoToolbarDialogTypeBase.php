<?php

namespace Drupal\exo_toolbar\Plugin;

use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Plugin\PluginWithFormsTrait;
use Drupal\Core\Plugin\PluginWithFormsInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\NestedArray;
use Drupal\exo_toolbar\ExoToolbarElementInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\exo_toolbar\Entity\ExoToolbarItemInterface;
use Drupal\exo_toolbar\Ajax\ExoToolbarDialogCommand;
use Drupal\Component\Utility\Html;
use Drupal\Core\Plugin\PluginDependencyTrait;

/**
 * Base class for eXo dialog plugins.
 */
abstract class ExoToolbarDialogTypeBase extends PluginBase implements ExoToolbarDialogTypePluginInterface, PluginWithFormsInterface {
  use StringTranslationTrait;
  use PluginWithFormsTrait;
  use PluginDependencyTrait;

  /**
   * Constructs a Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    $this->configuration = $configuration;
    $this->pluginId = $plugin_id;
    $this->pluginDefinition = $plugin_definition;
    $this->setConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    $definition = $this->getPluginDefinition();
    // Cast the admin label to a string since it is an object.
    // @see \Drupal\Core\StringTranslation\TranslatableMarkup
    return (string) $definition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function getProvider() {
    return isset($this->pluginDefinition['provider']) ? $this->pluginDefinition['provider'] : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = NestedArray::mergeDeep(
      $this->baseConfigurationDefaults(),
      $this->defaultConfiguration(),
      $configuration
    );
  }

  /**
   * Returns generic default configuration for block plugins.
   *
   * @return array
   *   An associative array with the default configuration.
   */
  protected function baseConfigurationDefaults() {
    return [
      'id' => $this->getPluginId(),
      'provider' => $this->pluginDefinition['provider'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function setConfigurationValue($key, $value) {
    $this->configuration[$key] = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $definition = $this->getPluginDefinition();
    $form['id'] = [
      '#type' => 'value',
      '#value' => $definition['id'],
    ];
    // Add plugin-specific settings for this item type.
    $form += $this->dialogTypeForm($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function dialogTypeForm(array $form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->dialogTypeValidate($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function dialogTypeValidate(array $form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    // Process the item's submission handling if no errors occurred only.
    if (!$form_state->getErrors()) {
      $this->dialogTypeSubmit($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function dialogTypeSubmit(array $form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function elementPrepare(ExoToolbarElementInterface $element) {
    $element->setAsLink('')
      ->addLibrary('exo_toolbar/dialog');
  }

  /**
   * {@inheritdoc}
   */
  public function dialogBuild(ExoToolbarItemInterface $exo_toolbar_item, $arg = NULL) {
    $plugin = $exo_toolbar_item->getPlugin();
    /** @var \Drupal\exo_toolbar\Plugin\ExoToolbarItemDialogPluginInterface $plugin */
    $build = $plugin->dialogBuild($exo_toolbar_item, $arg);
    if (!is_array($build)) {
      $build = ['#markup' => $build];
    }
    $build['#theme_wrappers'][] = 'exo_toolbar_dialog';
    $build['#wrapper_attributes']['class'][] = 'exo-toolbar-dialog-type-' . Html::getClass($this->getPluginId());
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function dialogResponse(ExoToolbarItemInterface $exo_toolbar_item, $arg = NULL) {
    $response = new AjaxResponse();
    $response->addCommand(new ExoToolbarDialogCommand($exo_toolbar_item, $this->dialogBuild($exo_toolbar_item, $arg), $this->getConfiguration()));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return $this->dependencies;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    $cache_contexts = [];
    return $cache_contexts;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $tags = [];
    return $tags;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    $max_age = Cache::PERMANENT;
    return $max_age;
  }

}
