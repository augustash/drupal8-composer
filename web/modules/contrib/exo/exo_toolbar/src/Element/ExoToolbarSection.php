<?php

namespace Drupal\exo_toolbar\Element;

use Drupal\Core\Render\Element\RenderElement;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\exo_toolbar\Entity\ExoToolbarItem;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\exo_toolbar\Entity\ExoToolbarItemInterface;
use Drupal\Core\Render\Element;

/**
 * Provides a render element for a Drupal toolbar section.
 *
 * @RenderElement("exo_toolbar_section")
 */
class ExoToolbarSection extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#theme' => 'exo_toolbar_section',
      '#exo_toolbar' => NULL,
      '#exo_toolbar_region_id' => NULL,
      '#exo_toolbar_section_id' => NULL,
      '#pre_render' => [
        [$class, 'preRenderSection'],
      ],
      'items' => NULL,
    ];
  }

  /**
   * Builds the Toolbar section as a structured array ready for drupal_render().
   *
   * @param array $element
   *   A renderable array.
   *
   * @return array
   *   A renderable array.
   */
  public static function preRenderSection(array $element) {
    /* @var \Drupal\exo_toolbar\Entity\ExoToolbarInterface $exo_toolbar */
    $exo_toolbar = $element['#exo_toolbar'];
    /* @var \Drupal\exo_toolbar\Entity\ExoToolbarItemInterface[] $exo_toolbar_items */
    if ($element['#exo_toolbar_region_id'] == 'item:region') {
      $exo_toolbar_items = $exo_toolbar->getVisibleItems($element['#exo_toolbar_region_id'], $element['#exo_toolbar_section_id']);
    }
    $exo_toolbar_items = $exo_toolbar->getVisibleItems($element['#exo_toolbar_region_id'], $element['#exo_toolbar_section_id']);
    /* @var \Drupal\exo_toolbar\ExoToolbarSectionInterface $exo_toolbar_section */
    $exo_toolbar_section = $exo_toolbar->getRegion($element['#exo_toolbar_region_id'])->getSection($element['#exo_toolbar_section_id']);
    $is_admin_mode = $exo_toolbar->isAdminMode();

    $cacheable_metadata = new CacheableMetadata();
    foreach ($exo_toolbar_items as $entity_id => $entity) {
      $plugin = $entity->getPlugin();

      $cache_tags = Cache::mergeTags($entity->getCacheTags(), $plugin->getCacheTags());

      // Create the render array for the item as a whole.
      // @see template_preprocess_item().
      $element['items'][$entity_id] = [
        '#cache' => [
          'keys' => ['exo_toolbar', 'exo_toolbar_item', $entity->id()],
          'contexts' => Cache::mergeContexts(
            $entity->getCacheContexts(),
            $plugin->getCacheContexts()
          ),
          'tags' => $cache_tags,
          'max-age' => $plugin->getCacheMaxAge(),
        ],
        '#weight' => $entity->getWeight(),
      ];

      $cacheable_metadata->merge(CacheableMetadata::createFromRenderArray($element['items'][$entity_id]));

      // Assign a #lazy_builder callback, which will generate a #pre_render-
      // able item lazily (when necessary).
      $element['items'][$entity_id] += [
        '#lazy_builder' => [static::class . '::lazyBuilder', [$entity_id]],
      ];
    }

    if ($is_admin_mode) {
      // Dynamically add create items to a section when in edit mode.
      $plugin_id = 'create';
      $properties = [
        'toolbar' => $exo_toolbar->id(),
        'region' => $element['#exo_toolbar_region_id'],
        'section' => $element['#exo_toolbar_section_id'],
      ];
      $exo_toolbar_item = ExoToolbarItem::create([
        'id' => 'create_' . implode($properties, '_'),
        'plugin' => $plugin_id,
        'weight' => $exo_toolbar_section->getSort() == 'asc' ? 1000 : -1000,
        'settings' => $properties,
      ] + $properties);
      $element['items']['create'] = self::createRenderArray($exo_toolbar_item);

      // This attribute is currently only needed when in admin mode.
      $element['#attributes']['data-exo-section-id'] = $element['#exo_toolbar_region_id'] . ':' . $element['#exo_toolbar_section_id'];
      $element['#attributes']['data-exo-section-sort'] = $exo_toolbar_section->getSort();
    }

    // Let each item, regardless of the region it exists in, alter the
    // region render array.
    $context = [
      'items' => $exo_toolbar_items,
      'section' => $exo_toolbar_section,
      'toolbar' => $exo_toolbar,
    ];
    foreach ($exo_toolbar_items as $exo_toolbar_item) {
      $exo_toolbar_item->alterSectionElement($element, $context);
    }

    $cacheable_metadata->applyTo($element);
    return $element;
  }

  /**
   * Builds a #pre_render-able item render array.
   *
   * @param \Drupal\exo_toolbar\Entity\ExoToolbarItemInterface $entity
   *   A item config entity.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   *
   * @return array
   *   A render array with a #pre_render callback to render the item.
   */
  protected static function buildPreRenderableItem(ExoToolbarItemInterface $entity, ModuleHandlerInterface $module_handler) {
    $plugin = $entity->getPlugin();
    $base_id = $plugin->getBaseId();

    // Inject runtime contexts.
    if ($plugin instanceof ContextAwarePluginInterface) {
      $contexts = \Drupal::service('context.repository')->getRuntimeContexts($plugin->getContextMapping());
      \Drupal::service('context.handler')->applyContextMapping($plugin, $contexts);
    }

    // Create the render array for the item as a whole.
    // @see template_preprocess_exo_toolbar_item().
    $build = static::createRenderArray($entity);

    // If an alter hook wants to modify the item contents, it can append
    // another #pre_render hook.
    $module_handler->alter(['exo_toolbar_item_view', "exo_toolbar_item_view_$base_id"], $build, $plugin);

    return $build;
  }

  /**
   * Create the render array for an item as a whole.
   *
   * @param \Drupal\exo_toolbar\Entity\ExoToolbarItemInterface $entity
   *   A item config entity.
   *
   * @return mixed
   *   The render array.
   */
  protected static function createRenderArray(ExoToolbarItemInterface $entity) {
    return [
      '#theme' => 'exo_toolbar_item',
      '#attributes' => [],
      '#weight' => $entity->getWeight(),
      '#id' => $entity->id(),
      '#exo_toolbar_item' => $entity,
      '#pre_render' => [
        static::class . '::preRenderItem',
      ],
    ];
  }

  /**
   * Lazy builder callback; builds a #pre_render-able item.
   *
   * @param string $entity_id
   *   A item config entity ID.
   *
   * @return array
   *   A render array with a #pre_render callback to render the item.
   */
  public static function lazyBuilder($entity_id) {
    return static::buildPreRenderableItem(ExoToolbarItem::load($entity_id), \Drupal::service('module_handler'));
  }

  /**
   * The #pre_render callback for building an item.
   *
   * Renders the content using the provided item plugin, and then:
   * - if there is no content, aborts rendering, and makes sure the item won't
   *   be rendered.
   */
  public static function preRenderItem($build) {
    $entity = $build['#exo_toolbar_item'];
    $content = $entity->build();
    unset($build['#exo_toolbar_item']);

    if ($content !== NULL && !Element::isEmpty($content)) {
      foreach (['#attributes', '#contextual_links', 'aside'] as $property) {
        if (isset($content[$property])) {
          if (isset($build[$property])) {
            $build[$property] += $content[$property];
          }
          else {
            $build[$property] = $content[$property];
          }
          unset($content[$property]);
        }
      }
      $build['item'] = $content;
    }
    else {
      $build = [
        '#markup' => '',
        '#cache' => $build['#cache'],
      ];
      if (!empty($content)) {
        CacheableMetadata::createFromRenderArray($build)
          ->merge(CacheableMetadata::createFromRenderArray($content))
          ->applyTo($build);
      }
    }
    return $build;
  }

}
