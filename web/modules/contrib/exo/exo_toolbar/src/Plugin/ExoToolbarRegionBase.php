<?php

namespace Drupal\exo_toolbar\Plugin;

use Drupal\Core\Plugin\ContextAwarePluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Plugin\ContextAwarePluginAssignmentTrait;
use Drupal\Core\Plugin\PluginWithFormsTrait;
use Drupal\Core\Plugin\PluginWithFormsInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Access\AccessResult;
use Drupal\exo_toolbar\ExoToolbarSectionInterface;
use Drupal\exo_toolbar\ExoToolbarSection;
use Drupal\exo_toolbar\ExoToolbarJsSettingsTrait;

/**
 * Base class for eXo theme plugins.
 */
abstract class ExoToolbarRegionBase extends ContextAwarePluginBase implements ExoToolbarRegionPluginInterface, PluginWithFormsInterface {
  use StringTranslationTrait;
  use ContextAwarePluginAssignmentTrait;
  use PluginWithFormsTrait;
  use ExoToolbarJsSettingsTrait;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

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
  public function getSections() {
    return [
      new ExoToolbarSection('full', $this->t('Full')),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getSection($section_id) {
    $section = array_filter($this->getSections(), function (ExoToolbarSectionInterface $section) use ($section_id) {
      return $section->id() == $section_id;
    });
    return !empty($section) ? reset($section) : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getAlignment() {
    return 'none';
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
      'size' => 'standard',
      'mark_only' => FALSE,
      'theme' => 'default',
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
  public function calculateDependencies() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account, $return_as_object = FALSE) {
    $access = $this->regionAccess($account);
    return $return_as_object ? $access : $access->isAllowed();
  }

  /**
   * Indicates whether the region should be shown.
   *
   * Regions with specific access checking should override this method rather
   * than access(), in order to avoid repeating the handling of the
   * $return_as_object argument.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user session for which to check access.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result.
   *
   * @see self::access()
   */
  protected function regionAccess(AccountInterface $account) {
    // By default, the block is visible.
    return AccessResult::allowed();
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
    $form['size'] = [
      '#type' => 'select',
      '#title' => $this->t('Size'),
      '#description' => $this->t('The size of the region.'),
      '#options' => $this->getSizeOptions(),
      '#default_value' => $this->getSize(),
    ];
    $form['mark_only'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mark only'),
      '#description' => $this->t('Hide item titles and only show the item icon/image.'),
      '#default_value' => $this->isMarkOnly(),
    ];
    $form['theme'] = [
      '#type' => 'select',
      '#title' => $this->t('Theme'),
      '#description' => $this->t('The theme style to use for the region.'),
      '#options' => exo_theme_options(),
      '#default_value' => $this->getTheme(),
    ];
    return $form;
  }

  /**
   * Returns modifiable lazyload options.
   */
  public function getSizeOptions() {
    $options = [
      'standard' => $this->t('Standard'),
      'small' => $this->t('Small'),
    ];

    $this->getModuleHandler()->alter('exo_toolbar_size_options_info', $options);
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    $definition = $this->getPluginDefinition();
    return $definition['weight'];
  }

  /**
   * {@inheritdoc}
   */
  public function getEdge() {
    $definition = $this->getPluginDefinition();
    return $definition['edge'];
  }

  /**
   * {@inheritdoc}
   */
  public function getSize() {
    return $this->configuration['size'];
  }

  /**
   * {@inheritdoc}
   */
  public function isRenderedOnInit() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function isMarkOnly() {
    return (bool) $this->configuration['mark_only'];
  }

  /**
   * {@inheritdoc}
   */
  public function isExpandable() {
    return $this instanceof ExoToolbarRegionVerticalInterface;
  }

  /**
   * {@inheritdoc}
   */
  public function isToggleable() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isHidden() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getTheme() {
    return $this->configuration['theme'];
  }

  /**
   * Gets the module handler.
   *
   * @return \Drupal\Core\Extension\ModuleHandlerInterface
   *   The module handler.
   */
  protected function getModuleHandler() {
    if (!isset($this->moduleHandler)) {
      $this->moduleHandler = \Drupal::moduleHandler();
    }
    return $this->moduleHandler;
  }

}
