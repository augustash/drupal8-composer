<?php

namespace Drupal\exo_menu\Plugin;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\exo\ExoSettingsPluginInterface;
use Drupal\exo_menu\ExoMenuGeneratorInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Cache\Cache;

/**
 * Provides a base for eXo menu blocks.
 */
abstract class ExoMenuBlockBase extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The eXo Menu options service.
   *
   * @var \Drupal\exo\ExoSettingsPluginSelectInstanceInterface
   */
  protected $exoSettings;

  /**
   * The eXo menu generator.
   *
   * @var \Drupal\exo_menu\ExoMenuGeneratorInterface
   */
  protected $exoMenuGenerator;

  /**
   * Creates a LocalActionsBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\exo\ExoSettingsPluginInterface $exo_settings
   *   The eXo options service.
   * @param \Drupal\exo_menu\ExoMenuGeneratorInterface $exo_menu_generator
   *   The eXo menu generator.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, ExoSettingsPluginInterface $exo_settings, ExoMenuGeneratorInterface $exo_menu_generator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->exoSettings = $exo_settings->createPluginSelectInstance($this->configuration['menu']);
    $this->exoMenuGenerator = $exo_menu_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('exo_menu.settings'),
      $container->get('exo_menu.generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'menus' => [],
      'menu' => [
        'plugin' => '',
        'plugin_settings' => [
          'exo_default' => 1,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['menus'] = $this->exoSettings->getExoSettings()->buildMenuForm($this->configuration['menus']);
    $form['menu'] = [];
    $subform_state = SubformState::createForSubform($form['menu'], $form, $form_state);
    $form['menu'] = $this->exoSettings->buildForm($form['menu'], $subform_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    parent::blockValidate($form, $form_state);
    $subform_state = SubformState::createForSubform($form['menu'], $form, $form_state);
    $this->exoSettings->validateForm($form['menu'], $subform_state);
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    // There is a bug in BlockForm that passes the whole form vs just the
    // settings subform like it does in validate.
    $subform_state = SubformState::createForSubform($form['settings']['menu'], $form['settings'], $form_state);
    $this->exoSettings->submitForm($form['settings']['menu'], $subform_state);
    $this->configuration['menus'] = $form_state->getValue('menus');
    $this->configuration['menu'] = $form_state->getValue('menu');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['menu'] = $this->exoMenuGenerator->generate(
      \Drupal::service('uuid')->generate(),
      $this->configuration['menu']['plugin'],
      $this->configuration['menus'],
      $this->configuration['menu']['plugin_settings']
    )->toRenderable();
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    // Even when the menu block renders to the empty string for a user, we want
    // the cache tag for this menu to be set: whenever the menu is changed, this
    // menu block must also be re-rendered for that user, because maybe a menu
    // link that is accessible for that user has been added.
    $cache_tags = parent::getCacheTags();
    foreach ($this->configuration['menus'] as $menu) {
      $cache_tags[] = 'config:system.menu.' . $menu;
    }
    return $cache_tags;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    // ::build() uses MenuLinkTreeInterface::getCurrentRouteMenuTreeParameters()
    // to generate menu tree parameters, and those take the active menu trail
    // into account. Therefore, we must vary the rendered menu by the active
    // trail of the rendered menu.
    // Additional cache contexts, e.g. those that determine link text or
    // accessibility of a menu, will be bubbled automatically.
    $cache_contexts = [];
    foreach ($this->configuration['menus'] as $menu) {
      $cache_contexts[] = 'route.menu_active_trails:' . $menu;
    }
    return Cache::mergeContexts(parent::getCacheContexts(), $cache_contexts);
  }

}
