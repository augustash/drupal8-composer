<?php

namespace Drupal\exo_alchemist\Element;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\SortArray;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Render\Element;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\exo_alchemist\Definition\ExoComponentDefinitionField;
use Drupal\exo_alchemist\ExoComponentFieldManager;
use Drupal\exo_alchemist\ExoComponentSectionNestedStorageInterface;
use Drupal\exo_icon\ExoIconTranslatableMarkup;
use Drupal\exo_icon\ExoIconTranslationTrait;
use Drupal\layout_builder\DefaultsSectionStorageInterface;
use Drupal\layout_builder\Element\LayoutBuilder;
use Drupal\layout_builder\LayoutEntityHelperTrait;
use Drupal\layout_builder\LayoutTempstoreRepositoryInterface;
use Drupal\layout_builder\Section;
use Drupal\layout_builder\SectionStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\layout_builder\SectionStorage\SectionStorageManagerInterface;

/**
 * Defines a render element for building the Layout Builder UI.
 *
 * @internal
 *   Plugin classes are internal.
 */
class ExoLayoutBuilder extends LayoutBuilder {
  use ExoIconTranslationTrait;
  use LayoutEntityHelperTrait;

  /**
   * The section delta being process.
   *
   * @var int
   */
  protected $delta;

  /**
   * The section being process.
   *
   * @var \Drupal\layout_builder\Section
   */
  protected $section;

  /**
   * The parent section being process.
   *
   * Used for nested sections.
   *
   * @var \Drupal\layout_builder\Section
   */
  protected $parentSection;

  /**
   * An array of contexts.
   *
   * @var array
   */
  protected $contexts;

  /**
   * The section storage manager.
   *
   * @var \Drupal\layout_builder\SectionStorage\SectionStorageManagerInterface
   */
  protected $sectionStorageManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a new LayoutBuilder.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\layout_builder\LayoutTempstoreRepositoryInterface $layout_tempstore_repository
   *   The layout tempstore repository.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\layout_builder\SectionStorage\SectionStorageManagerInterface $section_storage_manager
   *   The section storage manager.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LayoutTempstoreRepositoryInterface $layout_tempstore_repository, MessengerInterface $messenger, SectionStorageManagerInterface $section_storage_manager, AccountInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $layout_tempstore_repository, $messenger);
    $this->sectionStorageManager = $section_storage_manager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('layout_builder.tempstore_repository'),
      $container->get('messenger'),
      $container->get('plugin.manager.layout_builder.section_storage'),
      $container->get('current_user')
    );
  }

  /**
   * The exo icon prefixes to use for icon lookup.
   *
   * @var array
   */
  protected $prefixes = [
    'exo_alchemist',
    'local_task',
    'admin',
  ];

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $info = parent::getInfo();
    $info['#process'][] = [$this, 'process'];
    return $info;
  }

  /**
   * Process element.
   */
  public function process($element, FormStateInterface $form_state) {
    \Drupal::service('exo_alchemist.generator')->handleLayoutBuilderProcess($element, $form_state);
    return $element;
  }

  /**
   * Renders the Layout UI.
   *
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The section storage.
   *
   * @return array
   *   A render array.
   */
  protected function layout(SectionStorageInterface $section_storage) {
    if ($section_storage instanceof ExoComponentSectionNestedStorageInterface) {
      // We always defer back to parent layout storage.
      $section_storage = $section_storage->getParentEntityStorage();
      if ($this->layoutTempstoreRepository->has($section_storage)) {
        $section_storage = $this->layoutTempstoreRepository->get($section_storage);
      }
    }
    $build = parent::layout($section_storage);
    $build['#attached']['library'][] = 'exo_alchemist/admin';
    $build['#attached']['drupalSettings']['exoAlchemist']['icons']['close'] = $this->icon('Close')->setIcon('regular-times')->toString();

    if ($this->isDefaultStorage($section_storage)) {
      $build[] = $this->buildFooterAddSectionLink($section_storage, count($section_storage));
    }
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function buildAddSectionLink(SectionStorageInterface $section_storage, $delta) {
    // We don't want ANY of these.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  protected function buildFooterAddSectionLink(SectionStorageInterface $section_storage, $delta) {
    $link = parent::buildAddSectionLink($section_storage, $delta);
    $link['link']['#attributes']['class'][] = 'exo-font';
    return $link;
  }

  /**
   * {@inheritdoc}
   */
  protected function buildAdministrativeSection(SectionStorageInterface $section_storage, $delta) {
    $this->delta = $delta;
    $this->section = $section_storage->getSection($delta);
    $is_locked = $this->isSectionLocked($this->section);
    $permission = $this->getSectionPermission($this->section);
    $is_default_storage = $this->isDefaultStorage($section_storage);
    $is_nested_storage = $this->isNestedStorage($section_storage);
    $build = parent::buildAdministrativeSection($section_storage, $delta);
    $build['#attributes']['class'][] = 'exo-section';
    if ($this->isSectionEditable($section_storage)) {
      $build['#attributes']['class'][] = 'exo-section-edit';
    }
    $build['remove']['#title'] = ExoIconTranslatableMarkup::fromString('Remove')->match($this->prefixes);
    $build['remove']['#attributes']['class'] = array_merge($build['remove']['#attributes']['class'], [
      'exo-reset',
      'exo-font',
    ]);
    $build['configure']['#title'] = ExoIconTranslatableMarkup::fromString('Settings')->match($this->prefixes);
    $build['configure']['#attributes']['class'] = array_merge($build['configure']['#attributes']['class'], [
      'exo-reset',
      'exo-font',
    ]);
    if ($is_default_storage) {
      if (!empty($this->section->getLayoutSettings()['label'])) {
        $build['title'] = [
          '#type' => 'html_tag',
          '#tag' => 'div',
          '#value' => $this->section->getLayoutSettings()['label'],
          '#weight' => -2,
          '#attributes' => [
            'class' => [
              'layout-builder__info',
              'layout-builder__info--title',
              'exo-reset',
              'exo-font',
            ],
          ],
        ];
      }
      if ($is_locked) {
        $build['lock'] = [
          '#type' => 'html_tag',
          '#tag' => 'div',
          '#value' => ExoIconTranslatableMarkup::fromString('Locked')->match($this->prefixes),
          '#weight' => -2,
          '#attributes' => [
            'class' => [
              'layout-builder__info',
              'layout-builder__info--alert',
              'exo-reset',
              'exo-font',
            ],
          ],
        ];
      }
      if (!empty($permission)) {
        $build['permission'] = [
          '#type' => 'html_tag',
          '#tag' => 'div',
          '#value' => ExoIconTranslatableMarkup::fromString('Permission')->match($this->prefixes),
          '#weight' => -2,
          '#attributes' => [
            'class' => [
              'layout-builder__info',
              'layout-builder__info--warning',
              'exo-reset',
              'exo-font',
            ],
          ],
        ];
      }
    }
    $build['move'] = [
      '#type' => 'link',
      '#title' => ExoIconTranslatableMarkup::fromString('Move')->match($this->prefixes),
      '#url' => Url::fromRoute('layout_builder.section.move', [
        'section_storage_type' => $section_storage->getStorageType(),
        'section_storage' => $section_storage->getStorageId(),
      ]),
      '#attributes' => [
        'class' => [
          'use-ajax',
          'layout-builder__link',
          'layout-builder__link--move',
          'exo-reset',
          'exo-font',
        ],
        'data-dialog-type' => 'dialog',
        'data-dialog-renderer' => 'off_canvas',
      ],
      '#weight' => -1,
    ];
    if (!$is_default_storage || $is_nested_storage) {
      $build['remove']['#access'] = FALSE;
      $build['configure']['#access'] = FALSE;
      $build['move']['#access'] = FALSE;
    }
    foreach (Element::children($build['layout-builder__section']) as $region_id) {
      $build['layout-builder__section'][$region_id] = $this->buildAdministrativeRegion($section_storage, $build['layout-builder__section'][$region_id], $delta, $region_id);
    }
    return $build;
  }

  /**
   * Builds the render array for the layout region while editing.
   *
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The section storage.
   * @param array $build
   *   The render array.
   * @param int $delta
   *   The delta of the section.
   * @param string $region_id
   *   The region id.
   *
   * @return array
   *   The render array for a given section.
   */
  protected function buildAdministrativeRegion(SectionStorageInterface $section_storage, array $build, $delta, $region_id) {
    uasort($build, [SortArray::class, 'sortByWeightProperty']);
    $is_editable = $this->isSectionEditable($section_storage);
    if (isset($build['layout_builder_add_block'])) {
      $url = $build['layout_builder_add_block']['link']['#url'];
      $url_options = $url->getOptions();
      $url_options['attributes']['data-dialog-options'] = Json::encode([
        'exo_modal' => [
          'title' => t('Component'),
          'subtitle' => t('Select a component to add it to the layout.'),
          'icon' => 'regular-layer-plus',
          'width' => 600,
        ],
      ]);
      $settings = $this->section->getLayoutSettings();
      $label = $this->t('Add Component');
      if (!empty($settings['label'])) {
        $label = $this->t('Add Component into %label', [
          '%label' => $settings['label'],
        ]);
      }
      $build['layout_builder_add_block']['link']['#title'] = $label;
      $build['layout_builder_add_block']['link']['#attributes']['class'][] = 'exo-font';
      $build['layout_builder_add_block']['link']['#url'] = Url::fromRoute('layout_builder.component.choose', $url->getRouteParameters(), $url_options);
      $build['layout_builder_add_block']['#access'] = $is_editable;
    }
    if (!$is_editable) {
      // Disable dragging.
      foreach ($build['#attributes']['class'] as $key => $value) {
        if ($value == 'js-layout-builder-region') {
          unset($build['#attributes']['class'][$key]);
        }
      }
    }
    $build['region_label']['#access'] = FALSE;
    $components = array_filter($build, function ($element) {
      return !empty($element['#exo_component']);
    });
    $position = 0;
    foreach ($components as $component_id => $component) {
      $build[$component_id] = $this->buildAdministrativeComponent($section_storage, $build[$component_id], $delta, $region_id, $component_id, $position, array_keys($components));
      $position++;
    }
    return $build;
  }

  /**
   * Builds the render array for the layout component while editing.
   *
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The section storage.
   * @param array $build
   *   The render array.
   * @param int $delta
   *   The delta of the section.
   * @param string $region_id
   *   The region id.
   * @param string $component_id
   *   The component id.
   * @param int $position
   *   The component position within the section.
   * @param array $component_ids
   *   An ordered array of all component ids within this section.
   *
   * @return array
   *   The render array for a given component.
   */
  protected function buildAdministrativeComponent(SectionStorageInterface $section_storage, array $build, $delta, $region_id, $component_id, $position, array $component_ids) {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $build['#block_content'];
    /** @var \Drupal\exo_alchemist\Definition\ExoComponentDefinition $exo_component */
    $exo_component = $build['#exo_component'];
    $is_locked = $this->isSectionLocked();
    $is_nested_storage = $this->isNestedStorage($section_storage);
    $is_default_storage = $this->isDefaultStorage($section_storage);
    if ($is_nested_storage) {
      $is_locked = $this->isSectionLocked($this->parentSection);
    }
    // Contextual data.
    $data = [
      'section_storage_type' => $section_storage->getStorageType(),
      'section_storage' => $section_storage->getStorageId(),
      'delta' => $delta,
      'region' => $region_id,
      'uuid' => $component_id,
      'label' => $exo_component->getLabel(),
    ];
    if ($count = count(ExoComponentFieldManager::getHiddenFieldNames($entity))) {
      $data['elements_badge'] = $this->icon((string) $count)->setIcon('regular-low-vision');
    }
    // Set next key.
    $location = array_search($component_id, $component_ids);
    if (isset($component_ids[$location - 1])) {
      $data['prev_uuid'] = $component_ids[$location - 1];
    }
    if (isset($component_ids[$location + 1])) {
      $data['next_uuid'] = $component_ids[$location + 1];
    }

    // Ops.
    // @TODO Allow altering.
    if ($is_locked && !$is_default_storage) {
      // No changes can be made within locked sections.
      $data['ops'] = [];
    }
    else {
      $ops_allow = array_flip((array) $build['#exo_component_ops']);
      if ($position === 0) {
        unset($ops_allow['up']);
      }
      if ($position + 1 === count($component_ids)) {
        unset($ops_allow['down']);
      }
      $data['ops'] = array_keys($ops_allow);
    }
    $build['#wrapper_attributes']['id'] = Html::getId('exo-component-' . $component_id);
    $build['#wrapper_attributes']['class'][] = 'exo-component-edit';
    $build['#wrapper_attributes']['class'][] = 'js-layout-builder-block';
    if ($is_locked && !$is_default_storage) {
      $build['#wrapper_attributes']['class'][] = 'exo-component-locked';
      $data['description'] = $this->icon('Only minimal changes allowed.')->setIcon('regular-lock');
    }
    $build['#wrapper_attributes']['data-exo-component'] = Json::encode($data);
    $build['#attributes']['class'] = array_filter((array) $build['#attributes']['class'], function ($class_name) {
      return !in_array($class_name, ['layout-builder-block', 'js-layout-builder-block']);
    });
    unset($build['#contextual_links']);

    foreach ($exo_component->getFields() as $field) {
      if (substr($field->getType(), 0, 15) === 'section_column_') {
        $build['#' . $field->getName()]['render'] = $this->buildAdministrativeComponentSection($section_storage, $entity, $field, $delta);
      }
    }
    return $build;
  }

  /**
   * Build nested section.
   *
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The section storage.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The render array.
   * @param Drupal\exo_alchemist\Definition\ExoComponentDefinitionField $field
   *   The field definition.
   * @param int $delta
   *   The delta of the section.
   *
   * @return array
   *   The render array for a given component.
   */
  protected function buildAdministrativeComponentSection(SectionStorageInterface $section_storage, ContentEntityInterface $entity, ExoComponentDefinitionField $field, $delta) {
    $component_field = \Drupal::service('plugin.manager.exo_component_field')->createFieldInstance($field);
    /** @var \Drupal\exo_alchemist\Plugin\SectionStorage\ExoOverridesSectionStorage $section_storage */
    /** @var \Drupal\exo_alchemist\Plugin\SectionStorage\ExoOverridesSectionStorage $component_section_storage */
    $component_section_storage = $component_field->getTemporarySectionStorage($entity, $section_storage->getEntity());
    $this->parentSection = $this->section;
    return $this->buildAdministrativeSection($component_section_storage, 0);
  }

  /**
   * Alter the complete form.
   */
  public static function alterCompleteForm(array &$form, FormStateInterface $form_state) {
    $form['actions']['preview_toggle']['toggle_content_preview']['#title'] = t('Preview');
    /** @var \Drupal\layout_builder\SectionStorageInterface $section_storage */
    $section_storage = $form['layout_builder__layout']['widget']['#section_storage'];
    foreach ($section_storage->getSections() as $section) {
      $settings = $section->getLayoutSettings();
      if (!empty($settings['exo_section_permission'])) {
        // If there is a section that the user does not have the ability to
        // edit they should not be able to revert.
        if (!\Drupal::currentUser()->hasPermission($settings['exo_section_permission'])) {
          $form['actions']['revert']['#access'] = FALSE;
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getAvailableContexts(SectionStorageInterface $section_storage) {
    $key = $section_storage->getStorageId();
    if (isset($this->delta)) {
      $key .= '.' . $this->delta;
    }
    if (!isset($this->contexts[$key])) {
      $this->contexts[$key] = parent::getAvailableContexts($section_storage);
      if ($this->section) {
        $is_nested_storage = $this->isNestedStorage($section_storage);
        $this->contexts[$key]['default_storage'] = new Context(new ContextDefinition('boolean'), $this->isDefaultStorage($section_storage));
        $this->contexts[$key]['nested_storage'] = new Context(new ContextDefinition('boolean'), $is_nested_storage);
        // Nested storage inherity parent's locked status.
        $is_locked = $is_nested_storage ? $this->isSectionLocked($this->parentSection) : $this->isSectionLocked();
        $this->contexts[$key]['exo_section_lock'] = new Context(new ContextDefinition('boolean'), $is_locked);
      }
    }
    return $this->contexts[$key];
  }

  /**
   * Check if section is editable.
   *
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The storage.
   * @param \Drupal\layout_builder\Section $section
   *   The section.
   *
   * @return bool
   *   Returns TRUE if the section is default.
   */
  protected function isSectionEditable(SectionStorageInterface $section_storage, Section $section = NULL) {
    $section = $section ?: $this->section;
    $is_default_storage = $this->isDefaultStorage($section_storage);
    $is_nested_storage = $this->isNestedStorage($section_storage);
    $is_locked = $this->isSectionLocked($section);
    $permission = $this->getSectionPermission($section);

    if (!$is_default_storage && $is_nested_storage) {
      /** @var \Drupal\exo_alchemist\ExoComponentSectionNestedStorageInterface $section_storage */
      $is_locked = $this->isSectionLocked($this->parentSection);
      $permission = $this->getSectionPermission($this->parentSection);
    }

    // Always allow edit on default storage.
    if (!$is_default_storage) {
      // Do now allow edit on locked.
      if ($is_locked) {
        return FALSE;
      }
      // Do not allow if permission is set and user does not have access.
      elseif ($permission && !$this->currentUser->hasPermission($permission)) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Check if section is locked.
   *
   * @param \Drupal\layout_builder\Section $section
   *   The section.
   *
   * @return bool
   *   Returns TRUE if section is locked.
   */
  protected function isSectionLocked(Section $section = NULL) {
    $section = $section ?: $this->section;
    $locked = !empty($section->getLayoutSettings()['exo_section_lock']);
    if (!$locked && !empty($section->getLayoutSettings()['exo_section_permission'])) {
      $locked = !$this->currentUser->hasPermission($section->getLayoutSettings()['exo_section_permission']);
    }
    return $locked;
  }

  /**
   * Get section permission.
   *
   * @param \Drupal\layout_builder\Section $section
   *   The section.
   *
   * @return string
   *   The permission.
   */
  protected function getSectionPermission(Section $section = NULL) {
    $section = $section ?: $this->section;
    return !empty($section->getLayoutSettings()['exo_section_permission']) ? $section->getLayoutSettings()['exo_section_permission'] : NULL;
  }

  /**
   * Check if storage is default the default storage.
   *
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The storage.
   *
   * @return bool
   *   Returns TRUE if the section is default.
   */
  protected function isDefaultStorage(SectionStorageInterface $section_storage) {
    return $section_storage instanceof DefaultsSectionStorageInterface;
  }

  /**
   * Check if storage is a nested storage.
   *
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The storage.
   *
   * @return bool
   *   Returns TRUE if the section is default.
   */
  protected function isNestedStorage(SectionStorageInterface $section_storage) {
    return $section_storage instanceof ExoComponentSectionNestedStorageInterface;
  }

}
