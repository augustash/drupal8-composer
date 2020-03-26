<?php

namespace Drupal\exo_alchemist\Form;

use Drupal\Component\Utility\SortArray;
use Drupal\Core\Ajax\AjaxFormHelperTrait;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RedirectDestinationTrait;
use Drupal\Core\Url;
use Drupal\exo_alchemist\Ajax\ExoComponentModifierAttributesCommand;
use Drupal\exo_alchemist\Definition\ExoComponentDefinition;
use Drupal\exo_alchemist\ExoComponentManager;
use Drupal\exo_alchemist\ExoComponentPropertyManager;
use Drupal\exo_alchemist\Plugin\ExoComponentPropertyOptionsInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form form removing a component.
 *
 * @internal
 */
class ExoComponentPreviewForm extends FormBase {
  use AjaxFormHelperTrait;
  use RedirectDestinationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The eXo component plugin manager.
   *
   * @var \Drupal\exo_alchemist\ExoComponentManager
   */
  protected $exoComponentManager;

  /**
   * The eXo component plugin manager.
   *
   * @var \Drupal\exo_alchemist\ExoComponentPropertyManager
   */
  protected $exoComponentPropertyManager;

  /**
   * Constructs a new ExoComponentAppearanceForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\exo_alchemist\ExoComponentManager $exo_component_manager
   *   The eXo component manager.
   * @param \Drupal\exo_alchemist\ExoComponentPropertyManager $exo_component_property_manager
   *   The eXo component property manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ExoComponentManager $exo_component_manager, ExoComponentPropertyManager $exo_component_property_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->exoComponentManager = $exo_component_manager;
    $this->exoComponentPropertyManager = $exo_component_property_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.exo_component'),
      $container->get('plugin.manager.exo_component_property')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'exo_component_preview_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ExoComponentDefinition $definition = NULL) {
    $entity = $form_state->get('entity');
    if (empty($entity)) {
      $entity = $this->exoComponentManager->loadEntity($definition);
      $form_state->set('entity', $entity);
    }

    if ($entity) {
      $build = [
        '#type' => 'container',
        '#attributes' => [
          'id' => 'exo-component-preview',
          'class' => ['exo-component-preview'],
        ],
      ];
      $entity->exoAlchemistPreview = TRUE;
      $build['entity'] = $this->entityTypeManager->getViewBuilder($entity->getEntityTypeId())->view($entity);
      $form['#prefix'] = \Drupal::service('renderer')->render($build);

      if ($this->exoComponentManager->accessDefinition($definition, 'update')->isAllowed()) {
        \Drupal::messenger()->addWarning(t('The definition of this component has been changed. <a href="@url">Update this component</a>.', [
          '@url' => Url::fromRoute('exo_alchemist.component.update', [
            'definition' => $definition->id(),
          ], [
            'query' => $this->getDestinationArray(),
          ])->toString(),
        ]));
      }

      $form['#id'] = 'exo-alchemist-appearance-form';
      $form['#attributes']['data-exo-alchemist-refresh'] = '';
      $form['#attached']['library'][] = 'exo_alchemist/admin.preview';
      $form['#attributes']['class'][] = 'exo-form-wrap';
      $form['#exo_theme'] = 'black';
      $form['modifiers'] = [
        '#type' => 'exo_modal',
        '#title' => $this->t('Modify Appearance'),
        '#trigger_icon' => 'regular-pencil-paintbrush',
        '#trigger_attributes' => [
          'class' => ['button', 'button--primary'],
        ],
        '#use_close' => FALSE,
        '#modal_attributes' => [
          'class' => ['exo-form-theme-black'],
        ],
        '#modal_settings' => [
          'exo_preset' => 'aside_right',
          'modal' => [
            'title' => $this->t('Appearance'),
            'subtitle' => $this->t('Change component preview appearance.'),
            'theme' => 'black',
            'theme_content' => 'black',
            'icon' => 'regular-pencil-paintbrush',
            'width' => 400,
            'overlayColor' => 'transparent',
            // 'class' => 'exo-form exo-form-theme-black',
          ],
        ],
        '#tree' => TRUE,
      ];

      $this->exoComponentPropertyManager->buildForm($form, $form_state, $definition, $entity);
      $form['modifiers']['#attributes']['data-exo-alchemist-refresh'] = '';

      $form['refresh'] = [
        '#type' => 'submit',
        '#value' => $this->t('Refresh'),
        '#id' => 'exo-alchemist-appearance-refresh',
        '#button_type' => 'primary',
        '#attributes' => [
          'class' => ['js-hide'],
        ],
        '#ajax' => [
          'callback' => '::ajaxSubmit',
        ],
      ];
    }

    $info = $this->exoComponentManager->getPropertyInfo($definition);
    if (!empty($info)) {
      $form['properties'] = $this->buildInfo($info, $this->t('Twig Properties'), '{{ ', ' }}');
      $form['properties']['#open'] = TRUE;
    }

    $info = $this->exoComponentManager->getExoComponentPropertyManager()->getAttributeInfo($definition);
    if (!empty($info)) {
      $form['attributes'] = $this->buildInfo($info, $this->t('Modifier: Attributes'), '.', '');
    }

    $info = $this->exoComponentManager->getExoComponentAnimationManager()->getAttributeInfo($definition);
    if (!empty($info)) {
      $form['animations'] = $this->buildInfo($info, $this->t('Animation: Options'), '', '');
    }

    $info = [];
    foreach (ExoComponentDefinition::getGlobalModifiers() as $key => $value) {
      $data = [
        'ID: ' . $key,
        'Type: ' . $value['type'],
      ];
      $info[$key] = [
        'label' => $value['label'],
        'properties' => [
          '<small>' . implode('<br>', $data) . '</small>' => !empty($value['status']) ? $this->t('Enabled') : '-',
        ],
      ];
    }
    if (!empty($info)) {
      uasort($info, [get_class($this), 'infoSortByLabel']);
      $form['globals'] = $this->buildInfo($info, $this->t('Modifier: Globals'), '', '', [
        $this->t('Property'),
        $this->t('Options'),
        $this->t('Status'),
      ]);
    }

    $info = [];
    foreach ($this->exoComponentManager->getExoComponentPropertyManager()->getDefinitions() as $plugin_id => $property) {
      $instance = $this->exoComponentManager->getExoComponentPropertyManager()->createInstance($plugin_id);
      $data = [];
      if ($instance instanceof ExoComponentPropertyOptionsInterface) {
        $options = $instance->getOptions();
        unset($options['_none']);
        $data[] = 'Options: ' . implode(', ', array_keys($options));
      }
      $info[$plugin_id] = [
        'label' => $property['label'],
        'properties' => [
          $plugin_id => implode('<br>', $data),
        ],
      ];
    }
    if (!empty($info)) {
      uasort($info, [get_class($this), 'infoSortByLabel']);
      $form['property_types'] = $this->buildInfo($info, $this->t('Modifier: Property Types'));
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $entity = $form_state->get('entity');
    $entity->get(ExoComponentPropertyManager::MODIFIERS_FIELD_NAME)->setValue(['value' => $form_state->getValue('modifiers')]);
    $form_state->set('entity', $entity);
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  protected function successfulAjaxSubmit(array $form, FormStateInterface $form_state) {
    $entity = $form_state->get('entity');
    $definition = $this->exoComponentManager->getEntityBundleComponentDefinition($entity->type->entity);
    $attributes = $this->exoComponentPropertyManager->getModifierAttributes($definition, $entity, TRUE);
    $response = new AjaxResponse();
    $response->addCommand(new ExoComponentModifierAttributesCommand($attributes));
    return $response;
  }

  /**
   * Given info, build display.
   */
  protected function buildInfo(array $info, $label, $prefix = '', $suffix = '', $header = []) {
    $build = [
      '#type' => 'details',
      '#title' => $label,
      '#open' => FALSE,
    ];
    $build['table'] = [
      '#theme' => 'table',
      '#header' => $header + [
        0 => $this->t('Element'),
        1 => $this->t('Property'),
        2 => $this->t('Description'),
      ],
    ];
    $rows = [];
    foreach ($info as $key => $data) {
      $count = 0;
      foreach ($data['properties'] as $property => $label) {
        $row = [];
        if ($count == 0) {
          $row[] = ['data' => ['#markup' => '<small><strong>' . $data['label'] . '</strong></small>']];
        }
        else {
          $row[] = '';
        }
        $row[] = ['data' => ['#markup' => '<small>' . $prefix . $property . $suffix . '</small>']];
        $row[] = ['data' => ['#markup' => '<small>' . $label . '</small>']];
        $rows[] = $row;
        $count++;
      }
    }
    $build['table']['#rows'] = $rows;
    return $build;
  }

  /**
   * Sorts a structured array by either a set 'label' property.
   *
   * @param array $a
   *   First item for comparison.
   * @param array $b
   *   Second item for comparison.
   *
   * @return int
   *   The comparison result for uasort().
   */
  public static function infoSortByLabel(array $a, array $b) {
    return SortArray::sortByKeyString($a, $b, 'label');
  }

}
