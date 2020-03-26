<?php

namespace Drupal\exo_site_settings\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides a generic SiteSettings block.
 *
 * @Block(
 *   id = "exo_site_settings",
 *   admin_label = @Translation("eXo Site Settings"),
 * )
 */
class SiteSettingsBlock extends BlockBase implements BlockPluginInterface, ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityDisplayRepositoryInterface $entity_display_repository, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->setConfiguration($configuration);
    $this->entityDisplayRepository = $entity_display_repository;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_display.repository'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $config = $this->getConfiguration();
    if (!empty($config['site_settings_type'])) {
      $site_settings = $this->entityTypeManager->getStorage('exo_site_settings')->loadByType($config['site_settings_type']);
      return $site_settings ? $site_settings->getCacheTags() : [];
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $config = $this->getConfiguration();
    if (!empty($config['site_settings_type'])) {
      $site_settings = $this->entityTypeManager->getStorage('exo_site_settings')->loadByType($config['site_settings_type']);
      if ($site_settings) {
        $view_mode = $config['site_settings_view_mode'];
        $build = $this->entityTypeManager->getViewBuilder('exo_site_settings')->view($site_settings, $view_mode, NULL);
      }
    }
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $settings = parent::defaultConfiguration();
    $settings['site_settings_type'] = '';
    $settings['site_settings_view_mode'] = '';
    // Set custom cache settings.
    if (isset($this->pluginDefinition['cache'])) {
      $settings['cache'] = $this->pluginDefinition['cache'];
    }
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function getMachineNameSuggestion() {
    return 'exo_site_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    // Get all available SiteSettings types and prepare options list.
    $config = $this->getConfiguration();
    $options = [];
    foreach ($this->entityTypeManager->getStorage('exo_site_settings_type')->loadMultiple() as $type) {
      $id = $type->id();
      $label = $type->label();
      $options[$id] = $label;
    }
    $form['site_settings_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Select type to show'),
      '#options' => $options,
      '#default_value' => $config['site_settings_type'],
    ];

    $view_modes = $this->entityDisplayRepository->getViewModes('exo_site_settings');
    $options = ['' => $this->t('Default')];
    foreach ($view_modes as $id => $view_mode) {
      $options[$id] = $view_mode['label'];
    }
    // Get view modes.
    $form['site_settings_view_mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Select view mode to show'),
      '#options' => $options,
      '#default_value' => $config['site_settings_view_mode'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('site_settings_type', $form_state->getValue('site_settings_type'));
    $this->setConfigurationValue('site_settings_view_mode', $form_state->getValue('site_settings_view_mode'));
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $config = $this->getConfiguration();
    $dependencies = parent::calculateDependencies();
    $dependencies['config'][] = 'exo_site_settings.exo_site_settings_type.' . $config['site_settings_type'];
    if (!empty($config['site_settings_view_mode'])) {
      $dependencies['config'][] = 'core.entity_view_mode.exo_site_settings.' . $config['site_settings_view_mode'];
    }
    return $dependencies;
  }

}
