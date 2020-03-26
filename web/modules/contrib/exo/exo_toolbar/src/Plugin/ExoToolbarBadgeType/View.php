<?php

namespace Drupal\exo_toolbar\Plugin\ExoToolbarBadgeType;

use Drupal\exo_toolbar\Plugin\ExoToolbarBadgeTypeBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_toolbar\ExoToolbarElementInterface;
use Drupal\exo_toolbar\Plugin\ExoToolbarItemPluginInterface;
use Drupal\views\Views;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'view' eXo toolbar badge type.
 *
 * @ExoToolbarBadgeType(
 *   id = "view",
 *   label = @Translation("View"),
 * )
 */
class View extends ExoToolbarBadgeTypeBase implements ContainerFactoryPluginInterface {

  /**
   * The entity storage class.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $viewStorage;

  /**
   * Adds a LocalTasksEscort instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\EntityStorageInterface $view_storage
   *   The entity storage class.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityStorageInterface $view_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->viewStorage = $view_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')->getStorage('view')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'view_id' => '',
      'view_display_id' => '',
      'view_argument' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function badgeTypeForm(array $form, FormStateInterface $form_state) {
    $form = parent::badgeTypeForm($form, $form_state);
    $element_id = 'exoToolbarBadge-view-display-select';
    $view = $form_state->getCompleteFormState()->getValue([
      'settings',
      'badge_settings',
      'view_id',
    ], $this->configuration['view_id']);

    $form['view_id'] = [
      '#type' => 'select',
      '#title' => $this->t('View'),
      '#options' => $this->getViews(),
      '#default_value' => $view,
      '#required' => TRUE,
      '#empty_option' => $this->t('- Select -'),
      '#multiple' => FALSE,
      '#ajax' => [
        'callback' => [get_class($this), 'exoToolbarBadgeViewFormAjax'],
        'event' => 'change',
        'wrapper' => $element_id,
        'progress' => [
          'type' => 'throbber',
          'message' => t('Getting display Ids...'),
        ],
      ],
    ];

    $form['view_display_id'] = [
      '#type' => 'select',
      '#title' => $this->t('View Display'),
      '#options' => [],
      '#default_value' => $this->configuration['view_display_id'],
      '#empty_option' => $this->t('- Select -'),
      '#multiple' => FALSE,
      '#wrapper_attributes' => [
        'id' => $element_id,
      ],
      '#states' => [
        'visible' => [
          ':input[name="settings[view][view_id]"]' => ['filled' => TRUE],
        ],
      ],
    ];

    if (!empty($view)) {
      $form['view_display_id'] = [
        '#options' => $this->getViewDisplayIds($view),
        '#required' => TRUE,
      ] + $form['view_display_id'];
    }

    $form['view_argument'] = [
      '#title' => 'Argument',
      '#type' => 'textfield',
      '#default_value' => $this->configuration['view_argument'],
      '#states' => [
        'visible' => [
          ':input[name="settings[view][view_id]"]' => ['filled' => TRUE],
        ],
      ],
    ];

    return $form;
  }

  /**
   * AJAX function to get display IDs for a particular View.
   */
  public static function exoToolbarBadgeViewFormAjax(array &$form, FormStateInterface $form_state) {
    return $form['settings']['badge_settings']['view_display_id'];
  }

  /**
   * Helper function to get all display ids.
   */
  protected function getView() {
    if (!isset($this->view)) {
      $argument = $this->configuration['view_argument'];
      $arguments = [$argument];
      if (preg_match('/\//', $argument)) {
        $arguments = explode('/', $argument);
      }
      $this->view = Views::getView($this->configuration['view_id']);
      $this->view->setDisplay($this->configuration['view_display_id']);
      $this->view->setArguments($arguments);
    }
    return $this->view;
  }

  /**
   * Helper function to get all display ids.
   */
  protected function getViews() {
    $views = Views::getEnabledViews();
    $options = [];
    foreach ($views as $view) {
      if ($view->status()) {
        $options[$view->get('id')] = $view->get('label');
      }
    }
    return $options;
  }

  /**
   * Helper to get display ids for a particular View.
   */
  protected function getViewDisplayIds($entity_id) {
    $views = Views::getEnabledViews();
    $options = [];
    foreach ($views as $view) {
      if ($view->get('id') == $entity_id) {
        foreach ($view->get('display') as $display) {
          $options[$display['id']] = $display['display_title'];
        }
      }
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function elementPrepare(ExoToolbarElementInterface $element, $delta, ExoToolbarItemPluginInterface $item) {
    $view = $this->getView();
    $view->build();
    // Get the total number of results from the view.
    $badge = $view->getQuery()->query()->countQuery()->execute()->fetchField();
    parent::elementPrepare($element, $delta, $item);
    $element->setBadge($badge);
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $dependencies = parent::calculateDependencies();
    if ($view = $this->viewStorage->load($this->configuration['view_id'])) {
      $dependencies['config'][] = $view->getConfigDependencyName();
    }
    return $dependencies;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return $this->getView()->getCacheTags();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return $this->getView()->display_handler->getCacheMetadata()->getCacheContexts();
  }

}
