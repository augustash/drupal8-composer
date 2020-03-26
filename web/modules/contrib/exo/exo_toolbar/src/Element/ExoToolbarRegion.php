<?php

namespace Drupal\exo_toolbar\Element;

use Drupal\Core\Render\Element\RenderElement;
use Drupal\Component\Utility\Html;

/**
 * Provides a render element for a Drupal toolbar region.
 *
 * @RenderElement("exo_toolbar_region")
 */
class ExoToolbarRegion extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#theme' => 'exo_toolbar_region',
      '#exo_toolbar' => NULL,
      '#exo_toolbar_region_id' => NULL,
      '#pre_render' => [
        [$class, 'preRenderRegion'],
      ],
      '#attributes' => [],
      'sections' => NULL,
    ];
  }

  /**
   * Builds the Toolbar region as a structured array ready for drupal_render().
   *
   * @param array $element
   *   A renderable array.
   *
   * @return array
   *   A renderable array.
   */
  public static function preRenderRegion(array $element) {
    /* @var \Drupal\exo_toolbar\Entity\ExoToolbarInterface $exo_toolbar */
    $exo_toolbar = $element['#exo_toolbar'];
    /* @var \Drupal\exo_toolbar\Plugin\ExoToolbarRegionPluginInterface $exo_region */
    $exo_region_id = $element['#exo_toolbar_region_id'];
    $exo_region = $exo_toolbar->getRegion($exo_region_id);
    /* @var \Drupal\exo_toolbar\Entity\ExoToolbarItemInterface[] $exo_toolbar_items */
    $exo_toolbar_items = $exo_toolbar->getVisibleItems();
    $element['#exo_toolbar_region'] = $exo_region;

    $element['#attributes']['id'] = Html::getId(implode('-', [
      'exo-toolbar-region-',
      $exo_toolbar->id(),
      $exo_region_id,
    ]));

    // JS settings need to be set within the region itself.
    $js_settings = [
      'id' => $exo_region_id,
      'toolbar' => $exo_toolbar->id(),
      'edge' => $exo_region->getEdge(),
      'alignment' => $exo_region->getAlignment(),
      'weight' => $exo_region->getWeight(),
      'hidden' => $exo_region->isHidden(),
      'toggleable' => $exo_region->isToggleable(),
    ];
    if ($exo_region->isExpandable()) {
      $js_settings['expanded'] = $exo_region->isExpanded();
    }

    foreach ($exo_region->getSections() as $section) {
      /** @var \Drupal\exo_toolbar\ExoToolbarSectionInterface $section */
      $exo_section_id = $section->id();
      if ($exo_toolbar->isAdminMode() || !$exo_toolbar->sectionIsEmpty($exo_region_id, $exo_section_id)) {
        $element['sections'][$exo_section_id] = [
          '#type' => 'exo_toolbar_section',
          '#exo_toolbar' => $exo_toolbar,
          '#exo_toolbar_region_id' => $exo_region_id,
          '#exo_toolbar_section_id' => $exo_section_id,
          '#cache' => [
            'keys' => [
              'exo_toolbar',
              $exo_toolbar->id(),
              $exo_region_id,
              $exo_section_id,
            ],
            'contexts' => $exo_toolbar->getItemCacheContexts($exo_region_id, $exo_section_id),
            'tags' => $exo_toolbar->getItemCacheTags($exo_region_id, $exo_section_id),
            'max-age' => $exo_toolbar->getItemCacheMaxAge($exo_region_id, $exo_section_id),
          ],
        ];
        $element['sections'][$exo_section_id]['#attributes']['id'] = Html::getId(implode('-', [
          'exo-toolbar-section-',
          $exo_toolbar->id(),
          $exo_region_id,
          $exo_section_id,
        ]));
        $element['sections'][$exo_section_id]['#attached']['drupalSettings']['exoToolbar']['toolbars'][$exo_toolbar->id()]['sections'][$exo_region_id . ':' . $exo_section_id] = [
          'id' => $exo_section_id,
          'toolbar' => $exo_toolbar->id(),
          'region' => $exo_region_id,
          'sort' => $section->getSort(),
        ];
      }
    }

    // Let each item, regardless of the region it exists in, alter these
    // settings.
    foreach ($exo_toolbar_items as $exo_toolbar_item) {
      $exo_toolbar_item->alterRegionJsSettings($js_settings, $exo_region);
    }
    $element['#attached']['drupalSettings']['exoToolbar']['toolbars'][$exo_toolbar->id()]['regions'][$exo_region_id] = $js_settings;

    // Let each item, regardless of the region it exists in, alter the
    // region render array.
    foreach ($exo_toolbar_items as $exo_toolbar_item) {
      $exo_toolbar_item->alterRegionElement($element, $exo_region);
    }

    return $element;
  }

}
