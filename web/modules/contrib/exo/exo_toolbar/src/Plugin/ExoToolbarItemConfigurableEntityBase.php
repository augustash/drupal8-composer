<?php

namespace Drupal\exo_toolbar\Plugin;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\exo_toolbar\Entity\ExoToolbarItemInterface;

/**
 * Base class for eXo Toolbar Item configured entity plugins.
 */
abstract class ExoToolbarItemConfigurableEntityBase extends ExoToolbarItemDialogBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type.
   *
   * @var string
   */
  protected $entityType = '';

  /**
   * The entity type bundle.
   *
   * @var string
   */
  protected $entityTypeBundle = '';

  /**
   * The entity create route name.
   *
   * @var string
   */
  protected $entityCreateRoute = '';

  /**
   * The admin permission to check for access.
   *
   * @var string
   */
  protected $adminPermission = '';

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Adds a LocalTasksEscort instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param Drupal\exo_toolbar\Plugin\ExoToolbarBadgeTypeManagerInterface $exo_toolbar_badge_type_manager
   *   The eXo toolbar dialog type manager.
   * @param Drupal\exo_toolbar\Plugin\ExoToolbarDialogTypeManagerInterface $exo_toolbar_dialog_type_manager
   *   The eXo toolbar dialog type manager.
   * @param Drupal\Core\Render\RendererInterface $renderer
   *   The render service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ExoToolbarBadgeTypeManagerInterface $exo_toolbar_badge_type_manager, ExoToolbarDialogTypeManagerInterface $exo_toolbar_dialog_type_manager, RendererInterface $renderer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $exo_toolbar_badge_type_manager, $exo_toolbar_dialog_type_manager);
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.exo_toolbar_badge_type'),
      $container->get('plugin.manager.exo_toolbar_dialog_type'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'icon' => 'regular-plus-circle',
      'bundles' => [],
      'type' => 'include',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function itemForm(array $form, FormStateInterface $form_state) {
    $form = parent::itemForm($form, $form_state);
    $options = [];
    foreach ($this->entityTypeManager()->getStorage($this->entityTypeBundle)->loadMultiple() as $entity) {
      $options[$entity->id()] = $this->icon($entity->label())->setIcon(exo_icon_entity_icon($entity));
    }
    $form['bundles'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Bundles'),
      '#options' => $options,
      '#default_value' => $this->configuration['bundles'],
    ];
    $form['type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Type'),
      '#options' => ['include' => $this->t('Include'), 'exclude' => $this->t('Exclude')],
      '#default_value' => $this->configuration['type'],
      '#required' => TRUE,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function itemSubmit(array $form, FormStateInterface $form_state) {
    parent::itemSubmit($form, $form_state);
    $this->configuration['bundles'] = array_filter($form_state->getValue('bundles'));
    $this->configuration['type'] = $form_state->getValue('type');
  }

  /**
   * {@inheritdoc}
   */
  public function dialogBuild(ExoToolbarItemInterface $exo_toolbar_item, $arg = NULL) {
    $build = [
      '#theme' => 'links',
      '#links' => [],
      '#attributes' => ['class' => ['exo-toolbar-element-grid']],
      '#cache' => [
        'tags' => $this->entityTypeManager()->getDefinition($this->entityTypeBundle)->getListCacheTags(),
      ],
    ];

    $entities = $this->entityTypeManager()->getStorage($this->entityTypeBundle)->loadMultiple();
    if ($bundles = $this->configuration['bundles']) {
      switch ($this->configuration['type']) {
        case 'include':
          $entities = array_intersect_key($entities, $bundles);
          break;

        case 'exclude':
          $entities = array_diff_key($entities, $bundles);
          break;
      }
    }

    foreach ($entities as $entity) {
      $access = $this->entityTypeManager()->getAccessControlHandler($this->entityType)->createAccess($entity->id(), NULL, [], TRUE);
      if ($access->isAllowed()) {
        $title = $entity->label();
        $title = $this->icon($title)->setIcon(exo_icon_entity_icon($entity));
        $build['#links'][$entity->id()] = [
          'title' => $title,
          'url' => new Url($this->entityCreateRoute, [$this->entityTypeBundle => $entity->id()]),
        ];
      }
      $this->renderer->addCacheableDependency($build, $access);
    }
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function itemAccess(AccountInterface $account) {
    $access_control_handler = $this->entityTypeManager()->getAccessControlHandler($this->entityType);
    $entity_types = $this->entityTypeManager()->getStorage($this->entityTypeBundle)->loadMultiple();

    // No entity types currently exist.
    if (empty($entity_types)) {
      return AccessResult::neutral();
    }

    // If checking whether a entity of a particular type may be created.
    if ($this->adminPermission && $account->hasPermission($this->adminPermission)) {
      return AccessResult::allowed()->cachePerPermissions();
    }
    // If checking whether a entity of any type may be created.
    foreach ($entity_types as $entity_type) {
      if (($access = $access_control_handler->createAccess($entity_type->id(), $account, [], TRUE)) && $access->isAllowed()) {
        return $access;
      }
    }

    // No opinion.
    return AccessResult::neutral();
  }

}
