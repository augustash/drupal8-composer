<?php

namespace Drupal\exo_menu;

use Drupal\Core\Menu\MenuLinkTree;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Render\Markup;
use Drupal\exo_icon\ExoIconIconize;

/**
 * Implements the loading, transforming and rendering of menu link trees.
 */
class ExoMenuLinkTree extends MenuLinkTree {

  /**
   * {@inheritdoc}
   */
  public function build(array $tree) {
    $tree_access_cacheability = new CacheableMetadata();
    $tree_link_cacheability = new CacheableMetadata();
    $items = $this->buildItems($tree, $tree_access_cacheability, $tree_link_cacheability);
    $menu_names = [];
    foreach ($items as $item) {
      $link = $item['original_link'];
      $menu_name = $link->getMenuName();
      $menu_names[$menu_name] = $menu_name;
    }

    $build = [];

    // Apply the tree-wide gathered access cacheability metadata and link
    // cacheability metadata to the render array. This ensures that the
    // rendered menu is varied by the cache contexts that the access results
    // and (dynamic) links depended upon, and invalidated by the cache tags
    // that may change the values of the access results and links.
    $tree_cacheability = $tree_access_cacheability->merge($tree_link_cacheability);
    $tree_cacheability->applyTo($build);

    if ($items) {
      // Make sure drupal_render() does not re-order the links.
      $build['#sorted'] = TRUE;
      // Get the menu name from the last link.
      $item = end($items);
      $link = $item['original_link'];
      $menu_name = $link->getMenuName();
      // Add the theme wrapper for outer markup.
      // Allow menu-specific theme overrides.
      $build['#items'] = $items;
      $build['#menu_name'] = implode('_', $menu_names);
      $build['#theme'] = 'exo_menu__' . strtr($build['#menu_name'], '-', '_');
      foreach ($menu_names as $menu_name) {
        // Set cache tag.
        $build['#cache']['tags'][] = 'config:system.menu.' . $menu_name;
      }
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildAsLevels(array $tree) {
    $tree_access_cacheability = new CacheableMetadata();
    $tree_link_cacheability = new CacheableMetadata();
    $levels = $this->buildLevels($tree);
    foreach ($levels as $key => $level) {
      $levels[$key]['items'] = $this->buildItems($level['items'], $tree_access_cacheability, $tree_link_cacheability);
    }

    $build = [];

    // Apply the tree-wide gathered access cacheability metadata and link
    // cacheability metadata to the render array. This ensures that the
    // rendered menu is varied by the cache contexts that the access results
    // and (dynamic) links depended upon, and invalidated by the cache tags
    // that may change the values of the access results and links.
    $tree_cacheability = $tree_access_cacheability->merge($tree_link_cacheability);
    $tree_cacheability->applyTo($build);

    if ($levels) {
      // Make sure drupal_render() does not re-order the links.
      $build['#sorted'] = TRUE;
      $menu_names = [];
      foreach ($levels as $key => $level) {
        foreach ($level['items'] as $item) {
          $link = $item['original_link'];
          $menu_name = $link->getMenuName();
          $menu_names[$menu_name] = $menu_name;
        }
        $build['#levels'][$key]['attributes'] = new Attribute([
          'data-menu' => $key,
          'data-menu-parent' => $level['parent'],
        ]);
        $build['#levels'][$key]['items'] = $level['items'];
      }
      $build['#theme'] = 'exo_menu_levels__' . strtr(implode('_', $menu_names), '-', '_');
      $build['#menu_name'] = $menu_names;
      foreach ($menu_names as $menu_name) {
        // Set cache tag.
        $build['#cache']['tags'][] = 'config:system.menu.' . $menu_name;
      }
    }

    return $build;
  }

  /**
   * Nests the tree into subtrees.
   *
   * @param \Drupal\Core\Menu\MenuLinkTreeElement[] $tree
   *   A data structure representing the tree, as returned from
   *   MenuLinkTreeInterface::load().
   * @param string $parent
   *   The parent menu id.
   * @param int $section
   *   Storage for current section id.
   *
   * @return array
   *   And array of tree items.
   */
  protected function buildLevels(array $tree, $parent = 0, $section = 0) {
    $key = $parent . '-' . $section;
    // Reset section per level.
    $section = 0;
    foreach ($tree as $i => $data) {
      $link = $data->link;
      $id = $link->getPluginId();
      $subtree = $data->subtree;

      $data->level = $parent;
      $data->submenu = NULL;
      $data->subtree = [];
      $data->options = $link->getOptions();
      $data->isSubmenuParent = FALSE;
      $levels[$key]['parent'] = $parent;
      $levels[$key]['items'][$id] = $data;

      if ($subtree) {
        $section++;
        $data->submenu = $key . '-' . $section;

        // Add parent to submenu if it is a URL.
        if ($link->getUrlObject()->toString()) {
          $parent_data = clone $data;
          $parent_data->isSubmenuParent = TRUE;
          $parent_data->isSubmenuClone = TRUE;
          $subtree = [$i => $parent_data] + $subtree;

        }

        $levels += $this->buildLevels($subtree, $key, $section);
      }
    }
    ksort($levels);
    return $levels;

  }

  /**
   * Builds the #items property for a menu tree's renderable array.
   *
   * Helper function for ::build().
   *
   * @param \Drupal\Core\Menu\MenuLinkTreeElement[] $tree
   *   A data structure representing the tree, as returned from
   *   MenuLinkTreeInterface::load().
   * @param \Drupal\Core\Cache\CacheableMetadata &$tree_access_cacheability
   *   Internal use only. The aggregated cacheability metadata for the access
   *   results across the entire tree. Used when rendering the root level.
   * @param \Drupal\Core\Cache\CacheableMetadata &$tree_link_cacheability
   *   Internal use only. The aggregated cacheability metadata for the menu
   *   links across the entire tree. Used when rendering the root level.
   *
   * @return array
   *   The value to use for the #items property of a renderable menu.
   *
   * @throws \DomainException
   */
  protected function buildItems(array $tree, CacheableMetadata &$tree_access_cacheability, CacheableMetadata &$tree_link_cacheability) {
    $items = parent::buildItems($tree, $tree_access_cacheability, $tree_link_cacheability);
    foreach ($tree as $data) {
      /** @var \Drupal\Core\Menu\MenuLinkInterface $link */
      $link = $data->link;
      $id = $link->getPluginId();
      if (isset($items[$id])) {
        $element = &$items[$id];
        $options = $element['url']->getOptions();
        if (!empty($options['attributes']['class'])) {
          // Add any link classes to the parent.
          $element['attributes']->addClass($options['attributes']['class']);
          $options['attributes']['class'] = [];
        }
        if (!empty($options['attributes']['data-class'])) {
          $element['attributes']->addClass($options['attributes']['data-class'] . '-wrapper');
          $options['attributes']['class'] = [$options['attributes']['data-class']];
        }
        if (!empty($options['attributes']['data-target'])) {
          $options['attributes']['target'] = $options['attributes']['data-target'];
        }
        if (!empty($options['attributes']['data-icon'])) {
          $options['attributes']['target'] = $options['attributes']['data-icon'];
        }
        if (!empty($options['attributes']['data-icon']) && !empty($element['title'])) {
          $position = isset($options['attributes']['data-icon-position']) ? $options['attributes']['data-icon-position'] : 'before';
          $element['title'] = ExoIconIconize::iconize($element['title'])->setIcon($options['attributes']['data-icon'])->setIconPosition($position)->render();
        }
        unset($options['attributes']['data-icon']);
        unset($options['attributes']['data-icon-position']);
        unset($options['attributes']['data-target']);
        unset($options['attributes']['data-class']);
        if (!empty($options['spacer'])) {
          $element['title'] = '';
          $options['attributes']['class'][] = 'exo-menu-spacer';
        }
        else {
          $options['attributes']['class'][] = 'exo-menu-link';
        }
        if (isset($data->submenu)) {
          $options['attributes']['class'][] = 'has-submenu';
          $options['attributes']['data-submenu'] = $data->submenu;
        }
        if (isset($data->isSubmenuParent)) {
          $options['attributes']['class'][] = 'is-submenu-parent';
        }
        if (isset($data->isSubmenuClone)) {
          $options['attributes']['class'][] = 'is-submenu-clone';
        }
        $element['title'] = Markup::create('<span>' . $element['title'] . '</span>');
        $element['url']->setOptions($options);
        $element['link_attributes'] = new Attribute($options['attributes']);
      }
    }
    foreach ($items as &$item) {
      $item['below_prefix'] = NULL;
      $item['below_suffix'] = NULL;
    }

    return $items;
  }

}
