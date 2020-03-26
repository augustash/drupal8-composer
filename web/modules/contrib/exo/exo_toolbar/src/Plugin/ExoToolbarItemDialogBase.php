<?php

namespace Drupal\exo_toolbar\Plugin;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Plugin\DependentPluginInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Form\SubformStateInterface;
use Drupal\Core\Render\Element;
use Drupal\exo_toolbar\Entity\ExoToolbarItemInterface;

/**
 * Base class for eXo Toolbar item plugins that support dialogs.
 */
abstract class ExoToolbarItemDialogBase extends ExoToolbarItemBase implements ExoToolbarItemDialogPluginInterface, DependentPluginInterface, ContainerFactoryPluginInterface {

  /**
   * The eXo toolbar dialog type manager.
   *
   * @var \Drupal\exo_toolbar\Plugin\ExoToolbarDialogTypeManagerInterface
   */
  protected $exoToolbarDialogTypeManager;

  /**
   * The plugin collection that holds the item plugin for this entity.
   *
   * @var \Drupal\exo_toolbar\Plugin\ExoToolbarDialogTypeCollection
   */
  protected $dialogTypeCollection;

  /**
   * Creates a ExoToolbarItemDialogBase instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param Drupal\exo_toolbar\Plugin\ExoToolbarBadgeTypeManagerInterface $exo_toolbar_badge_type_manager
   *   The eXo toolbar badge type manager.
   * @param Drupal\exo_toolbar\Plugin\ExoToolbarDialogTypeManagerInterface $exo_toolbar_dialog_type_manager
   *   The eXo toolbar dialog type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ExoToolbarBadgeTypeManagerInterface $exo_toolbar_badge_type_manager, ExoToolbarDialogTypeManagerInterface $exo_toolbar_dialog_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $exo_toolbar_badge_type_manager);
    $this->exoToolbarDialogTypeManager = $exo_toolbar_dialog_type_manager;
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
      $container->get('plugin.manager.exo_toolbar_dialog_type')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function baseConfigurationDefaults() {
    return [
      'dialog_type' => 'tip',
      'dialog_settings' => [],
    ] + parent::baseConfigurationDefaults();
  }

  /**
   * {@inheritdoc}
   */
  public function getDialogType() {
    return $this->getDialogTypeCollection()->get($this->configuration['dialog_type']);
  }

  /**
   * Encapsulates the creation of the item's LazyPluginCollection.
   *
   * @return \Drupal\Component\Plugin\LazyPluginCollection
   *   The item's plugin collection.
   */
  protected function getDialogTypeCollection() {
    if (!$this->dialogTypeCollection) {
      $this->dialogTypeCollection = new ExoToolbarDialogTypeCollection($this->exoToolbarDialogTypeManager, $this->configuration['dialog_type'], $this->configuration['dialog_settings'], $this->getPluginId());
    }
    return $this->dialogTypeCollection;
  }

  /**
   * Get dialog type instance.
   */
  protected function getDialogTypeInstance(array $form, SubformStateInterface $form_state) {
    $dialog_type = $form_state->getCompleteFormState()->getValue(['settings', 'dialog_type'], $this->configuration['dialog_type']);
    $dialog_settings = $form_state->getCompleteFormState()->getValue(['settings', 'dialog_settings'], $this->configuration['dialog_settings']);
    return $this->exoToolbarDialogTypeManager->createInstance($dialog_type, $dialog_settings);
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $element_id = 'exo-toolbar-item-dialog-settings';
    $dialog_type_instance = $this->getDialogTypeInstance($form, $form_state);

    $form['dialog_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Dialog Type'),
      '#options' => $this->exoToolbarDialogTypeManager->getDialogTypeLabels(),
      '#default_value' => $this->configuration['dialog_type'],
      '#limit_validation_errors' => [['settings', 'dialog_type']],
      '#required' => TRUE,
      '#ajax' => [
        'callback' => [get_class($this), 'ajaxDialogType'],
        'event' => 'change',
        'wrapper' => $element_id,
        'progress' => [
          'type' => 'throbber',
          'message' => t('Getting dialog settings'),
        ],
      ],
    ];

    $form['dialog_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Dialog settings'),
      '#id' => $element_id,
    ];
    $subform_state = SubformState::createForSubform($form['dialog_settings'], $form, $form_state);
    $form['dialog_settings'] += $dialog_type_instance->buildConfigurationForm($form['dialog_settings'], $subform_state);
    if (empty(Element::getVisibleChildren($form['dialog_settings']))) {
      $form['dialog_settings']['#attributes']['style'] = 'display:none';
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * Most item plugins should not override this method. To add submission
   * handling for a specific item type, override BlockBase::itemSubmit().
   *
   * @see \Drupal\Core\Block\BlockBase::itemSubmit()
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::validateConfigurationForm($form, $form_state);

    $dialog_type_instance = $this->getDialogTypeInstance($form, $form_state);
    $subform_state = SubformState::createForSubform($form['dialog_settings'], $form, $form_state);
    $dialog_type_instance->validateConfigurationForm($form['dialog_settings'], $subform_state);
  }

  /**
   * {@inheritdoc}
   *
   * Most item plugins should not override this method. To add submission
   * handling for a specific item type, override BlockBase::itemSubmit().
   *
   * @see \Drupal\Core\Block\BlockBase::itemSubmit()
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    // Process the item's submission handling if no errors occurred only.
    if (!$form_state->getErrors()) {
      parent::submitConfigurationForm($form, $form_state);

      $dialog_type_instance = $this->getDialogTypeInstance($form, $form_state);
      $subform_state = SubformState::createForSubform($form['dialog_settings'], $form, $form_state);
      $dialog_type_instance->submitConfigurationForm($form['dialog_settings'], $subform_state);

      $this->configuration['dialog_type'] = $form_state->getValue('dialog_type');
      $this->configuration['dialog_settings'] = $form_state->getValue('dialog_settings');
    }
  }

  /**
   * AJAX function to get display IDs for a particular View.
   */
  public static function ajaxDialogType(array &$form, FormStateInterface $form_state) {
    return $form['settings']['dialog_settings'];
  }

  /**
   * {@inheritdoc}
   */
  protected function elementBuild() {
    $element = parent::elementBuild()
      ->addJsSetting('dialog_type', $this->configuration['dialog_type']);
    $this->getDialogType()->elementPrepare($element);
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function dialogBuild(ExoToolbarItemInterface $exo_toolbar_item, $arg = NULL) {
    return ['#markup' => ''];
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $this->calculatePluginDependencies($this->getDialogType());
    return $this->dependencies;
  }

}
