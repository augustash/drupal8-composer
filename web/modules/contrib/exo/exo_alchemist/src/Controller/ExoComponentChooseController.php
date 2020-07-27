<?php

namespace Drupal\exo_alchemist\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Html;
use Drupal\Core\Ajax\AjaxHelperTrait;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\exo_alchemist\ExoComponentManager;
use Drupal\exo_alchemist\ExoComponentSectionStorageInterface;
use Drupal\image\Entity\ImageStyle;
use Drupal\layout_builder\Context\LayoutBuilderContextTrait;
use Drupal\layout_builder\LayoutBuilderHighlightTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\exo_icon\ExoIconTranslationTrait;
use Drupal\layout_builder\SectionStorageInterface;

/**
 * Defines a controller to choose a new section.
 *
 * @internal
 *   Controller classes are internal.
 */
class ExoComponentChooseController implements ContainerInjectionInterface {

  use AjaxHelperTrait;
  use LayoutBuilderContextTrait;
  use LayoutBuilderHighlightTrait;
  use StringTranslationTrait;
  use ExoIconTranslationTrait;

  /**
   * The eXo component manager.
   *
   * @var \Drupal\exo_alchemist\ExoComponentManager
   */
  protected $exoComponentManager;

  /**
   * ChooseSectionController constructor.
   *
   * @param \Drupal\exo_alchemist\ExoComponentManager $exo_component_manager
   *   The layout manager.
   */
  public function __construct(ExoComponentManager $exo_component_manager) {
    $this->exoComponentManager = $exo_component_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.exo_component')
    );
  }

  /**
   * Choose a layout plugin to add as a section.
   *
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The section storage.
   * @param int $delta
   *   The delta of the section to splice.
   * @param string $region
   *   The region the block is going in.
   *
   * @return array
   *   The render array.
   */
  public function build(SectionStorageInterface $section_storage, $delta, $region) {

    $output = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['exo-component-choose'],
      ],
    ];
    $items = [];
    $categories = [
      'all' => $this->t('All'),
    ];

    $contexts = $this->getAvailableContexts($section_storage);
    if ($section_storage instanceof ExoComponentSectionStorageInterface) {
      $region_sizes = ['all'];
      $region_sizes[] = $section_storage->getRegionSize($delta, $region);
      $contexts['tags'] = new Context(new ContextDefinition('map'), $region_sizes);
    }
    $definitions = $this->exoComponentManager->getAlphabeticalDefinitions($this->exoComponentManager->getFilteredDefinitions('layout_builder', $contexts, [
      'section_storage' => $section_storage,
      'delta' => $delta,
      'region' => $region,
    ]));
    foreach ($definitions as $plugin_id => $definition) {
      /** @var \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition */
      if ($definition->isHidden() || $definition->isComputed()) {
        continue;
      }
      $category = $definition->getCategory();
      $category_machine_name = Html::getClass($category);
      $categories[$category_machine_name] = $category;
      $image = $definition->getThumbnailSource();
      $label = $definition->getLabel();
      if ($definition->getPermission()) {
        $label = $this->icon($label)->setIcon('regular-lock');
      }
      if ($image_style = ImageStyle::load('exo_alchemist_preview')) {
        /** @var \Drupal\Image\Entity\ImageStyle $image_style */
        $thumbnail = $definition->getThumbnailUri();
        if (!file_exists($thumbnail)) {
          $this->exoComponentManager->installThumbnail($definition);
        }
        $image = $image_style->buildUrl($thumbnail);
      }
      $required_field_paths = $this->exoComponentManager->getExoComponentFieldManager()->getRequiredPaths($definition);
      if (!empty($required_field_paths)) {
        $url = Url::fromRoute(
          'layout_builder.component.configure', [
            'section_storage_type' => $section_storage->getStorageType(),
            'section_storage' => $section_storage->getStorageId(),
            'delta' => $delta,
            'region' => $region,
            'plugin_id' => $plugin_id,
          ]
        );
      }
      else {
        $url = Url::fromRoute(
          'layout_builder.component.add', [
            'section_storage_type' => $section_storage->getStorageType(),
            'section_storage' => $section_storage->getStorageId(),
            'delta' => $delta,
            'region' => $region,
            'plugin_id' => $plugin_id,
          ]
        );
      }
      $item = [
        '#type' => 'link',
        '#wrapper_attributes' => [
          'class' => ['exo-component-select'],
          'data-groups' => Json::encode([
            $category_machine_name,
          ]),
        ],
        '#title' => [
          [
            '#type' => 'inline_template',
            '#template' => '<img src="{{ image }}" />',
            '#context' => [
              'image' => $image,
            ],
          ],
          [
            '#type' => 'container',
            '#attributes' => [
              'class' => ['exo-component-label'],
            ],
            '#children' => $label,
          ],
        ],
        '#url' => $url,
      ];
      if ($this->isAjax()) {
        $item['#attributes']['class'][] = 'use-ajax';
        $item['#attributes']['data-dialog-type'][] = 'dialog';
        $item['#attributes']['data-dialog-renderer'][] = 'off_canvas';
        if (!empty($required_field_paths)) {
          $item['#attributes']['data-dialog-options'] = Json::encode([
            'exo_modal' => [
              'title' => $this->t('Configure Component'),
              'subtitle' => $this->t('This component must be configured before the component can be added.'),
              'icon' => 'regular-cogs',
              'width' => 600,
            ],
          ]);
        }
      }
      $items[] = $item;
    }
    $output['category'] = [
      '#type' => 'inline_template',
      '#template' => '<a class="exo-component-filter">{{ label }}</a>',
      '#context' => [
        'label' => $this->t('Filter Components'),
      ],
    ];
    $category_items = [];
    foreach ($categories as $id => $label) {
      $category_items[$id] = [
        '#type' => 'inline_template',
        '#template' => '<a class="exo-component-category-button" data-group="{{ id }}">{{ label }}</a>',
        '#context' => [
          'label' => $label,
          'id' => $id,
        ],
      ];
    }
    $output['categories'] = [
      '#theme' => 'item_list',
      '#items' => $category_items,
      '#prefix' => '<div class="exo-component-categories">',
      '#suffix' => '</div>',
    ];
    $output['layouts'] = [
      '#theme' => 'item_list',
      '#items' => $items,
      '#attached' => [
        'library' => ['exo_alchemist/admin.choose'],
      ],
      '#attributes' => [
        'class' => [
          'exo-component-selection',
        ],
      ],
    ];

    return $output;
  }

}
