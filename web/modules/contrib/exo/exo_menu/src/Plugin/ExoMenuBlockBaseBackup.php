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
abstract class ExoMenuBlockBaseBackup extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The eXo Menu options service.
   *
   * @var \Drupal\exo\ExoSettingsPluginInterface
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
    $this->exoSettings = $exo_settings;
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
      'menu_style' => '',
      'menu' => [
        'exo_default' => 1,
      ],
      'menu_menus' => [],
    ];
  }

  /**
   * Get style type instance.
   */
  protected function getExoSettingsInstance(array $form, FormStateInterface $form_state) {
    $style = $form_state->getCompleteFormState()->getValue(['settings', 'menu_style'], $this->configuration['menu_style']);
    if (!$style) {
      return NULL;
    }
    $style_settings = $form_state->getCompleteFormState()->getValue(['settings', 'menu'], $this->configuration['menu']);
    return $this->exoSettings->createPluginInstance($style, $style_settings);
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $element_id = 'exo-menu-block-settings';
    $style_options = [];
    foreach (\Drupal::service('plugin.manager.exo_menu')->getDefinitions() as $plugin_id => $definition) {
      $style_options[$plugin_id] = $definition['label'];
    }

    $form['menu_menus'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Menu'),
        $this->t('Weight'),
      ],
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'menu-weight',
        ],
      ],
    ];
    $count = 0;
    foreach ($this->getMenuOptions() as $id => $label) {
      $form['menu_menus'][$id]['#attributes']['class'][] = 'draggable';
      $form['menu_menus'][$id]['#weight'] = $count;
      $form['menu_menus'][$id]['status'] = [
        '#type' => 'checkbox',
        '#title' => $label,
        '#default_value' => in_array($id, $this->configuration['menu_menus']),
      ];
      $form['menu_menus'][$id]['weight'] = [
        '#type' => 'weight',
        '#title' => t('Weight for @title', ['@title' => $label]),
        '#title_display' => 'invisible',
        '#default_value' => $count,
        '#attributes' => ['class' => ['menu-weight']],
      ];
      $count++;
    }

    $form['menu_style'] = [
      '#type' => 'select',
      '#title' => $this->t('Menu Style'),
      '#options' => $style_options,
      '#default_value' => $this->configuration['menu_style'],
      '#empty_option' => $this->t('- Select -'),
      '#required' => TRUE,
      '#limit_validation_errors' => [['settings', 'menu_style']],
      '#ajax' => [
        'callback' => [get_class($this), 'ajaxMenuStyle'],
        'event' => 'change',
        'wrapper' => $element_id,
        'progress' => [
          'type' => 'throbber',
          'message' => t('Getting menu style settings'),
        ],
      ],
    ];

    $exo_settings_instance = $this->getExoSettingsInstance($form, $form_state);
    $form['menu'] = [];
    if ($exo_settings_instance) {
      $form['menu'] = $exo_settings_instance->buildForm($form['menu'], $form_state);
    }
    if (!empty($form['menu'])) {
      $form['menu'] = [
        '#type' => 'fieldset',
        '#id' => $element_id,
        '#title' => $this->t('Menu Style'),
      ] + $form['menu'];
    }
    else {
      $form['menu'] = [
        '#type' => 'container',
        '#id' => $element_id,
      ];
    }

    return $form;
  }

  /**
   * AJAX function to get display IDs for a particular View.
   */
  public static function ajaxMenuStyle(array &$form, FormStateInterface $form_state) {
    return $form['settings']['menu'];
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
   * Gets a list of menu names for use as options.
   *
   * @param array $menu_names
   *   (optional) Array of menu names to limit the options, or NULL to load all.
   *
   * @return array
   *   Keys are menu names (ids) values are the menu labels.
   */
  protected function getMenuOptions(array $menu_names = NULL) {
    $menus = $this->entityTypeManager->getStorage('menu')->loadMultiple($menu_names);
    $options = array_flip($this->configuration['menu_menus']);
    /** @var \Drupal\system\MenuInterface[] $menus */
    foreach ($menus as $menu) {
      $options[$menu->id()] = $menu->label();
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    $subform_state = SubformState::createForSubform($form['menu'], $form, $form_state);
    $this->getExoSettingsInstance($form, $form_state)->validateForm($form['menu'], $subform_state);

    // Clean menus.
    $values = $form_state->getValues();
    $menus = array_filter($values['menu_menus'], function ($menu) {
      return $menu['status'] == 1;
    });
    uasort($menus, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);
    $values['menu_menus'] = array_keys($menus);
    $form_state->setValues($values);

  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    // There is a bug in BlockForm that passes the whole form vs just the
    // settings subform like it does in validate.
    $subform_state = SubformState::createForSubform($form['settings']['menu'], $form['settings'], $form_state);
    $this->getExoSettingsInstance($form, $form_state)->submitForm($form['settings']['menu'], $subform_state);

    $this->configuration['menu_style'] = $form_state->getValue('menu_style');
    $this->configuration['menu'] = $form_state->getValue('menu');
    $this->configuration['menu_menus'] = $form_state->getValue('menu_menus');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['menu'] = $this->exoMenuGenerator->generate(
      \Drupal::service('uuid')->generate(),
      $this->configuration['menu_style'],
      $this->configuration['menu_menus'],
      $this->configuration['menu']
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
    foreach ($this->configuration['menu_menus'] as $menu) {
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
    foreach ($this->configuration['menu_menus'] as $menu) {
      $cache_contexts[] = 'route.menu_active_trails:' . $menu;
    }
    return Cache::mergeContexts(parent::getCacheContexts(), $cache_contexts);
  }

}
