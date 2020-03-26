<?php

namespace Drupal\exo_menu\Plugin\Block;

use Drupal\exo_modal\Plugin\ExoModalBlockBase;
use Drupal\exo\ExoSettingsInterface;
use Drupal\exo_modal\ExoModalGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\exo\ExoSettingsPluginInterface;
use Drupal\exo_menu\ExoMenuGeneratorInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides a block to display an eXo menu.
 *
 * @Block(
 *   id = "exo_menu_modal",
 *   admin_label = @Translation("eXo Menu Modal"),
 *   provider = "exo_modal"
 * )
 */
class ExoMenuModalBlock extends ExoModalBlockBase {

  /**
   * The eXo Menu options service.
   *
   * @var \Drupal\exo\ExoSettingsPluginSelectInstanceInterface
   */
  protected $exoMenuSettings;

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
   * @param \Drupal\exo\ExoSettingsInterface $exo_modal_settings
   *   The eXo options service.
   * @param \Drupal\exo_modal\ExoModalGeneratorInterface $exo_modal_generator
   *   The eXo modal generator.
   * @param \Drupal\exo\ExoSettingsPluginInterface $exo_menu_settings
   *   The eXo options service.
   * @param \Drupal\exo_menu\ExoMenuGeneratorInterface $exo_menu_generator
   *   The eXo menu generator.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, ExoSettingsInterface $exo_modal_settings, ExoModalGeneratorInterface $exo_modal_generator, ExoSettingsPluginInterface $exo_menu_settings, ExoMenuGeneratorInterface $exo_menu_generator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $exo_modal_settings, $exo_modal_generator);
    $this->exoMenuSettings = $exo_menu_settings->createPluginSelectInstance($this->configuration['menu']);
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
      $container->get('exo_modal.settings'),
      $container->get('exo_modal.generator'),
      $container->get('exo_menu.settings'),
      $container->get('exo_menu.generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
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
    $form = parent::blockForm($form, $form_state);
    $form['menu'] = [
      '#type' => 'details',
      '#title' => $this->t('Menu'),
    ];
    $form['menu']['menus'] = $this->exoMenuSettings->getExoSettings()->buildMenuForm($this->configuration['menus']);
    $form['menu']['menu'] = [];
    $subform_state = SubformState::createForSubform($form['menu']['menu'], $form, $form_state);
    $form['menu']['menu'] = $this->exoMenuSettings->buildForm($form['menu']['menu'], $subform_state);
    $form['menu']['menu']['plugin_settings']['#title'] = $this->t('Menu Settings');
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    parent::blockValidate($form, $form_state);
    $subform_state = SubformState::createForSubform($form['menu']['menu'], $form, $form_state);
    $this->exoMenuSettings->validateForm($form['menu']['menu'], $subform_state);
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    // There is a bug in BlockForm that passes the whole form vs just the
    // settings subform like it does in validate.
    $subform_state = SubformState::createForSubform($form['settings']['menu']['menu'], $form['settings'], $form_state);
    $this->exoMenuSettings->submitForm($form['settings']['menu']['menu'], $subform_state);
    $this->configuration['menus'] = $form_state->getValue(['menu', 'menus']);
    $this->configuration['menu'] = $form_state->getValue(['menu', 'menu']);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $this->configuration['modal']['modal']['onOpening'] = 'Drupal.ExoMenu.refresh';
    return parent::build();
  }

  /**
   * {@inheritdoc}
   */
  public function buildModal() {
    $this->configuration['modal']['modal']['onOpening'] = 'Drupal.ExoMenu.refresh';
    return parent::buildModal();
  }

  /**
   * {@inheritdoc}
   */
  protected function buildModalContent() {
    return $this->exoMenuGenerator->generate(
      'exo_modal_block_' . $this->configuration['block_id'],
      $this->configuration['menu']['plugin'],
      $this->configuration['menus'],
      $this->configuration['menu']['plugin_settings']
    )->toRenderable();
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
