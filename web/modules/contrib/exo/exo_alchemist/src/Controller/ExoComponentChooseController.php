<?php

namespace Drupal\exo_alchemist\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Html;
use Drupal\Core\Ajax\AjaxHelperTrait;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\exo_alchemist\ExoComponentManager;
use Drupal\image\Entity\ImageStyle;
use Drupal\layout_builder\LayoutBuilderHighlightTrait;
use Drupal\layout_builder\SectionStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a controller to choose a new section.
 *
 * @internal
 *   Controller classes are internal.
 */
class ExoComponentChooseController implements ContainerInjectionInterface {

  use AjaxHelperTrait;
  use LayoutBuilderHighlightTrait;
  use StringTranslationTrait;

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
   *
   * @return array
   *   The render array.
   */
  public function build(SectionStorageInterface $section_storage, $delta) {
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
    $definitions = $this->exoComponentManager->getInstalledDefinitions();
    foreach ($definitions as $plugin_id => $definition) {
      /** @var \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition */
      if ($definition->isHidden()) {
        continue;
      }
      $category = $definition->getCategory();
      $category_machine_name = Html::getClass($category);
      $categories[$category_machine_name] = $category;
      $image = $definition->getThumbnailSource();
      if ($image_style = ImageStyle::load('exo_alchemist_preview')) {
        /** @var \Drupal\Image\Entity\ImageStyle $image_style */
        $thumbnail = $definition->getThumbnailUri();
        if (!file_exists($thumbnail)) {
          $this->exoComponentManager->installThumbnail($definition);
        }
        $image = $image_style->buildUrl($thumbnail);
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
            '#children' => $definition->getLabel(),
          ],
        ],
        '#url' => Url::fromRoute(
          'layout_builder.add_component',
          [
            'section_storage_type' => $section_storage->getStorageType(),
            'section_storage' => $section_storage->getStorageId(),
            'delta' => $delta,
            'plugin_id' => $plugin_id,
          ]
        ),
      ];
      if ($this->isAjax()) {
        $item['#attributes']['class'][] = 'use-ajax';
        $item['#attributes']['data-dialog-type'][] = 'dialog';
        $item['#attributes']['data-dialog-renderer'][] = 'off_canvas';
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
