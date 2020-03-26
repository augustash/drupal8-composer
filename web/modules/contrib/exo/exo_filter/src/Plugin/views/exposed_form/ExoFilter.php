<?php

namespace Drupal\exo_filter\Plugin\views\exposed_form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\exo_filter\Plugin\ExoFilterManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Component\Utility\Html;
use Drupal\exo_modal\ExoModalGeneratorInterface;
use Drupal\exo\ExoSettingsInterface;
use Drupal\views\ViewExecutable;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\Core\Form\SubformState;
use Drupal\views\Plugin\views\exposed_form\InputRequired;

/**
 * Exposed form plugin that provides a basic exposed form.
 *
 * @ingroup views_exposed_form_plugins
 *
 * @ViewsExposedForm(
 *   id = "exo_filter",
 *   title = @Translation("eXo | Filters"),
 *   help = @Translation("Provides additional options for exposed form elements.")
 * )
 */
class ExoFilter extends InputRequired {

  /**
   * The eXo filter plugin manager.
   *
   * @var \Drupal\exo_filter\Plugin\ExoFilterManager
   */
  protected $exoFilterManager;

  /**
   * The eXo Modal options service.
   *
   * @var \Drupal\exo\ExoSettingsPluginInstanceInterface
   */
  protected $exoModalSettings;

  /**
   * The eXo modal generator.
   *
   * @var \Drupal\exo_modal\ExoModalGeneratorInterface
   */
  protected $exoModalGenerator;

  /**
   * Constructs a PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param Drupal\exo_filter\Plugin\ExoFilterManager $exo_filter_manager
   *   The eXo filter manager.
   * @param \Drupal\exo\ExoSettingsInterface $exo_modal_settings
   *   The eXo options service.
   * @param \Drupal\exo_modal\ExoModalGeneratorInterface $exo_modal_generator
   *   The eXo modal generator.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ExoFilterManager $exo_filter_manager, ExoSettingsInterface $exo_modal_settings, ExoModalGeneratorInterface $exo_modal_generator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->definition = $plugin_definition + $configuration;
    $this->exoFilterManager = $exo_filter_manager;
    $this->exoModalSettings = $exo_modal_settings;
    $this->exoModalGenerator = $exo_modal_generator;
  }

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    $this->exoModalSettings = $this->exoModalSettings->createInstance($this->options['modal']);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.exo_filter'),
      $container->get('exo_modal.settings'),
      $container->get('exo_modal.generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['input_required'] = ['default' => FALSE];
    $options['general'] = [
      'default' => [
        'actions_first' => FALSE,
        'autosubmit' => FALSE,
        'autosubmit_hide' => FALSE,
        'allow_secondary' => FALSE,
        'secondary_label' => $this->t('Advanced options'),
        'use_modal' => TRUE,
        'modal_clone' => FALSE,
      ],
    ];
    $options['modal'] = [
      'default' => [
        'exo_default' => TRUE,
      ],
    ];
    foreach ($this->view->display_handler->getHandlers('filter') as $id => $filter) {
      $options[$id] = [
        'default' => [
          'format' => '',
          'more' => [
            'is_secondary' => 0,
          ],
        ],
      ];
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['text_input_required']['#weight'] = 2;
    $form['text_input_required']['#states'] = [
      'visible' => [
        ':input[name="exposed_form_options[input_required]"]' => ['checked' => TRUE],
      ],
    ];
    $form['input_required'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Require Input'),
      '#description' => $this->t('Do not show any results until a filter has been applied.'),
      '#default_value' => $this->options['input_required'],
      '#weight' => 1,
    ];

    $form['general']['#weight'] = 10;
    $form['general']['actions_first'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Actions First'),
      '#description' => $this->t('Will show all form actions before the form filters.'),
      '#default_value' => $this->options['general']['actions_first'],
    ];

    $form['general']['autosubmit'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Autosubmit'),
      '#description' => $this->t('Automatically submit the form once an element is changed.'),
      '#default_value' => $this->options['general']['autosubmit'],
    ];

    $form['general']['autosubmit_hide'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hide submit button'),
      '#description' => $this->t('Hide submit button if javascript is enabled.'),
      '#default_value' => $this->options['general']['autosubmit_hide'],
      '#states' => [
        'visible' => [
          ':input[name="exposed_form_options[general][autosubmit]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['general']['allow_secondary'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable secondary exposed form options'),
      '#default_value' => $this->options['general']['allow_secondary'],
      '#description' => $this->t('Allows you to specify some exposed form elements as being secondary options and places those elements in a collapsible "details" element. Use this option to place some exposed filters in an "Advanced Search" area of the form, for example.'),
    ];
    $form['general']['secondary_label'] = [
      '#type' => 'textfield',
      '#default_value' => $this->options['general']['secondary_label'],
      '#title' => $this->t('Secondary options label'),
      '#description' => $this->t(
        'The name of the details element to hold secondary options. This cannot be left blank or there will be no way to show/hide these options.'
      ),
      '#states' => [
        'required' => [
          ':input[name="exposed_form_options[general][allow_secondary]"]' => ['checked' => TRUE],
        ],
        'visible' => [
          ':input[name="exposed_form_options[general][allow_secondary]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $states = [
      'visible' => [
        ':input[name="exposed_form_options[general][use_modal]"]' => ['checked' => TRUE],
      ],
    ];
    $form['general']['use_modal'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use Modal'),
      '#default_value' => $this->options['general']['use_modal'],
      '#description' => $this->t('Allows you to specify some exposed form elements as being secondary options and places those elements in a collapsible "details" element. Use this option to place some exposed filters in an "Advanced Search" area of the form, for example.'),
    ];

    $form['general']['modal_clone'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Clone Modal'),
      '#default_value' => $this->options['general']['modal_clone'],
      '#description' => $this->t('Allows the filter to be shown outside of a modal as well as within a modal. This is useful when you want a filter to be displayed normally for desktop but wrapped in a modal for mobile.'),
      '#states' => $states,
    ];

    $form['modal'] = [];
    $form['modal'] = $this->exoModalSettings->buildForm($form['modal'], $form_state) + [
      '#type' => 'details',
      '#title' => $this->t('Modal'),
    ] + [
      '#states' => $states,
      '#weight' => 15,
    ];

    // Go through each filter and add eXo options.
    foreach ($this->view->display_handler->getHandlers('filter') as $id => $filter) {
      if (!$filter->options['exposed']) {
        continue;
      }
      $type = $filter->getPluginId();
      $title = $filter->options['expose']['identifier'];
      $identifier = '"' . $title . '"';

      $form[$id] = [
        '#type' => 'fieldset',
        '#title' => $title,
        '#weight' => 20,
      ];

      $options = ['' => '- Default -'] + $this->exoFilterManager->getOptions($type);
      $form[$id]['format'] = [
        '#type' => 'select',
        '#title' => $this->t('Display @identifier exposed filter as', ['@identifier' => $identifier]),
        '#default_value' => $this->options[$id]['format'],
        '#options' => $options,
      ];
      // Details element to keep the UI from getting out of hand.
      $form[$id]['more'] = [
        '#type' => 'details',
        '#title' => $this->t('More options for @identifier', ['@identifier' => $identifier]),
      ];

      // Allow any filter to be moved into the secondary options element.
      $form[$id]['more']['is_secondary'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('This is a secondary option'),
        '#default_value' => $this->options[$id]['more']['is_secondary'],
        '#states' => [
          'visible' => [
            ':input[name="exposed_form_options[general][allow_secondary]"]' => ['checked' => TRUE],
          ],
        ],
        '#description' => $this->t('Places this element in the secondary options portion of the exposed form.'),
      ];
    }

  }

  /**
   * {@inheritdoc}
   */
  public function validateOptionsForm(&$form, FormStateInterface $form_state) {
    $subform_state = SubformState::createForSubform($form['modal'], $form_state->getCompleteForm(), $form_state);
    $this->exoModalSettings->validateForm($form['modal'], $subform_state);
    parent::validateOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitOptionsForm(&$form, FormStateInterface $form_state) {
    $subform_state = SubformState::createForSubform($form['modal'], $form_state->getCompleteForm(), $form_state);
    $this->exoModalSettings->submitForm($form['modal'], $subform_state);
    parent::submitOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function exposedFilterApplied() {
    if ($this->options['input_required']) {
      return parent::exposedFilterApplied();
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function exposedFormAlter(&$form, FormStateInterface $form_state) {
    parent::exposedFormAlter($form, $form_state);
    $settings = $this->options;
    $allow_secondary = $settings['general']['allow_secondary'];

    if ($this->view->ajaxEnabled()) {
      // Disable the cache for ajax requests.
      $form['#cache']['max-age'] = 0;
    }

    // Some elements may be placed in a secondary details element (eg: "Advanced
    // search options"). Place this after the exposed filters and before the
    // rest of the items in the exposed form.
    if ($allow_secondary) {
      $secondary = [
        '#type' => 'details',
        '#title' => $this->options['general']['secondary_label'],
        '#weight' => 1000,
      ];
      $form['actions']['#weight'] = 1001;
    }

    // Apply autosubmit values.
    if (!empty($settings['general']['autosubmit'])) {
      $form['#attributes']['data-exo-auto-submit-full-form'] = '';
      $form['actions']['submit']['#attributes']['data-exo-auto-submit-click'] = '';
      $form['#attached']['library'][] = 'exo/auto_submit';

      if (!empty($settings['general']['autosubmit_hide'])) {
        $form['actions']['submit']['#attributes']['class'][] = 'js-hide';
      }
    }

    if (!empty($this->options['general']['actions_first'])) {
      $form['actions']['#weight'] = -1000;
    }

    // Go through each filter and alter if necessary.
    foreach ($this->view->display_handler->getHandlers('filter') as $id => $filter) {
      if (!isset($form['#info']["filter-$id"]['value'])) {
        continue;
      }
      $identifier = $form['#info']["filter-$id"]['value'];
      $format = $this->options[$id]['format'];
      if ($format) {
        $plugin = $this->exoFilterManager->createInstance($format);
        $context = [
          'id' => $identifier,
          'plugin' => $this,
        ];
        $plugin->exposedElementAlter($form[$identifier], $form_state, $context);
      }

      if ($allow_secondary && $this->options[$id]['more']['is_secondary']) {
        if (!empty($form[$identifier])) {
          // Move exposed operators with exposed filters.
          if (!empty($this->display->display_options['filters'][$identifier]['expose']['use_operator'])) {
            $op_id = $this->display->display_options['filters'][$identifier]['expose']['operator_id'];
            $secondary[$op_id] = $form[$op_id];
            unset($form[$op_id]);
          }
          $secondary[$identifier] = $form[$identifier];
          unset($form[$identifier]);
          $secondary[$identifier]['#title'] = $form['#info']["filter-$id"]['label'];
          unset($form['#info']["filter-$id"]);
        }
      }
    }

    // Check for secondary elements.
    if ($allow_secondary && !empty($secondary)) {
      // Add secondary elements after regular exposed filter elements.
      $remaining = array_splice($form, count($form['#info']) + 1);
      $form['secondary'] = $secondary;
      $form = array_merge($form, $remaining);
      $form['#info']['filter-secondary']['value'] = 'secondary';
    }

    if (empty($this->view->live_preview)) {
      $form['#after_build'][] = [$this, 'afterBuild'];
    }
  }

  /**
   * Act on form after build.
   */
  public function afterBuild(array $form) {
    $use_modal = $this->options['general']['use_modal'];
    if ($use_modal) {
      $modal_clone = $this->options['general']['modal_clone'];
      $modal_content_id = Html::getId('exo-filter-filters-' . $this->view->id() . '-' . $this->view->current_display);
      $modal_content = [
        '#type' => 'container',
        '#id' => $modal_content_id,
        '#attributes' => [
          'id' => $modal_content_id,
          'class' => ['exo-filter-filters'],
        ],
        '#weight' => 0,
      ];
      // Move all children into the modal container.
      foreach (Element::children($form) as $id) {
        $modal_content[$id] = $form[$id];
        unset($form[$id]);
      }
      // Allow each submit button to close the modal.
      foreach (Element::children($modal_content['actions']) as $id) {
        $element = &$modal_content['actions'][$id];
        if (isset($element['#type']) && $element['#type'] == 'submit' && $id != 'reset') {
          $element['#attributes']['data-exo-modal-close'] = '';
          $element['#attributes']['data-exo-modal-action-delay'] = 'closed';
        }
      }
      $modal_content['actions']['#attributes']['class'][] = 'exo-modal-actions';
      // Go through each filter and adjust as needed.
      foreach ($this->view->display_handler->getHandlers('filter') as $id => $filter) {
        if (!isset($form['#info']["filter-$id"]['value'])) {
          continue;
        }
        $identifier = $form['#info']["filter-$id"]['value'];
        $modal_content[$identifier]['#title'] = $form['#info']["filter-$id"]['label'];
      }

      $modal_options = $this->options['modal'] + ['modal' => []];
      $modal_options['modal'] += [
        'appendTo' => 'form',
        'appendToOverlay' => 'form',
        'appendToNavigate' => 'form',
        'appendToClosest' => TRUE,
        'class' => $this->view->ajaxEnabled() ? 'exo-auto-submit-disable' : '',
      ];
      $modal = \Drupal::service('exo_modal.generator')->generate($modal_content_id . '-modal', $modal_options)->addTriggerClass('button');
      if ($modal_clone) {
        $modal->setSetting(['modal', 'contentSelector'], '#' . $modal_content_id);
        $form['modal_content'] = $modal_content;
      }
      else {
        $modal->setContent($modal_content);
      }
      $form['modal'] = $modal->toRenderable();
    }
    return $form;
  }

}
