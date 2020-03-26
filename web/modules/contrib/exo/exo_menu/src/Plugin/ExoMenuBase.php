<?php

namespace Drupal\exo_menu\Plugin;

use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Plugin\PluginWithFormsInterface;
use Drupal\Core\Plugin\PluginWithFormsTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exo\ExoSettingsPluginWithSettingsInterface;
use Drupal\Component\Utility\NestedArray;

/**
 * Base class for eXo theme plugins.
 */
abstract class ExoMenuBase extends PluginBase implements ExoMenuPluginInterface, ExoSettingsPluginWithSettingsInterface, PluginWithFormsInterface {
  use PluginWithFormsTrait;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->setConfiguration($configuration);
  }

  /**
   * Returns generic default configuration for item plugins.
   *
   * @return array
   *   An associative array with the default configuration.
   */
  protected function baseConfigurationDefaults() {
    return [
      'provider' => $this->pluginDefinition['provider'],
    ];
  }

  /**
   * Returns generic default configuration for item plugins.
   *
   * @return array
   *   An associative array with the default configuration.
   */
  public function defaultConfiguration() {
    return [
      'theme' => '',
      'menus' => [],
      'level' => 1,
      'child_level' => 0,
      'depth' => 3,
      'expand' => 1,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function diffExcludes() {
    return [
      'theme',
      'menus',
      'level',
      'child_level',
      'depth',
      'expand',
    ];
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
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    $definition = $this->getPluginDefinition();
    return (string) $definition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

    $form['theme'] = [
      '#type' => 'select',
      '#title' => $this->t('Theme'),
      '#options' => exo_theme_options(),
      '#empty_option' => $this->t('Custom'),
      '#default_value' => $this->configuration['theme'],
    ];

    $form['expand'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Expand all menu links'),
      '#default_value' => $this->configuration['expand'],
      '#description' => $this->t('All menu links that have children will "Show as expanded".'),
    ];

    $form['levels'] = [
      '#type' => 'details',
      '#title' => $this->t('Menu levels'),
      // Open if not set to defaults.
      '#open' => $this->configuration['level'] != $this->configuration['level'] || $this->configuration['depth'] != $this->configuration['depth'],
      '#process' => [[get_class(), 'processToParent']],
    ];

    $options = range(0, \Drupal::service('menu.link_tree')->maxDepth());
    unset($options[0]);

    $form['levels']['level'] = [
      '#type' => 'select',
      '#title' => $this->t('Initial visibility level'),
      '#default_value' => $this->configuration['level'],
      '#options' => $options,
      '#description' => $this->t('The menu is only visible if the menu item for the current page is at this level or below it. Use level 1 to always display this menu.'),
      '#required' => TRUE,
    ];

    $options[0] = $this->t('Unlimited');

    $form['levels']['child_level'] = [
      '#type' => 'select',
      '#title' => $this->t('Child level'),
      '#default_value' => $this->configuration['child_level'],
      '#options' => $options,
      '#description' => $this->t('The menu items displayed will be at this level or below it. This level is based on the active trail level.'),
      '#required' => TRUE,
    ];

    $form['levels']['depth'] = [
      '#type' => 'select',
      '#title' => $this->t('Number of levels to display'),
      '#default_value' => $this->configuration['depth'],
      '#options' => $options,
      '#description' => $this->t('This maximum number includes the initial level.'),
      '#required' => TRUE,
    ];
    return $form;
  }

  /**
   * Form API callback: Processes the levels field element.
   *
   * Adjusts the #parents of levels to save its children at the top level.
   */
  public static function processToParent(&$element, FormStateInterface $form_state, &$complete_form) {
    array_pop($element['#parents']);
    return $element;
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
  public function prepareSettings(array $settings, $type) {
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareBuild(array $build) {
    $build['#attributes']['class'][] = 'exo-menu';
    $build['#attributes']['class'][] = 'exo-reset';
    if (!empty($this->configuration['theme'])) {
      $build['#attributes']['class'][] = 'exo-menu-theme-' . $this->configuration['theme'];
    }
    $build['#attached']['library'][] = 'exo_menu/theme';
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function renderAsLevels() {
    return $this->pluginDefinition['as_levels'] == TRUE;
  }

}
