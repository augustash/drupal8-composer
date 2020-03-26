<?php

namespace Drupal\exo_menu;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\exo\ExoSettingsInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuActiveTrailInterface;
use Drupal\exo_menu\Plugin\ExoMenuPluginManagerInterface;

/**
 * Provides a class which generates an eXo menu.
 */
class ExoMenuGenerator implements ExoMenuGeneratorInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The eXo menu settings service.
   *
   * @var \Drupal\exo\ExoSettingsInterface
   */
  protected $exoSettings;

  /**
   * The menu link tree service.
   *
   * @var \Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected $menuTree;

  /**
   * The active menu trail service.
   *
   * @var \Drupal\Core\Menu\MenuActiveTrailInterface
   */
  protected $menuActiveTrail;

  /**
   * The eXo menu plugin manager.
   *
   * @var \Drupal\exo_menu\Plugin\ExoMenuPluginManagerInterface
   */
  protected $exoMenuManager;

  /**
   * Constructs a new ExoMenuGenerator object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\exo\ExoSettingsInterface $exo_settings
   *   The UX options service.
   * @param \Drupal\Core\Menu\MenuLinkTreeInterface $menu_tree
   *   The menu tree service.
   * @param \Drupal\Core\Menu\MenuActiveTrailInterface $menu_active_trail
   *   The active menu trail service.
   * @param \Drupal\exo_menu\Plugin\ExoMenuPluginManagerInterface $exo_menu_manager
   *   The eXo menu plugin manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ExoSettingsInterface $exo_settings, MenuLinkTreeInterface $menu_tree, MenuActiveTrailInterface $menu_active_trail, ExoMenuPluginManagerInterface $exo_menu_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->exoSettings = $exo_settings;
    $this->menuTree = $menu_tree;
    $this->menuActiveTrail = $menu_active_trail;
    $this->exoMenuManager = $exo_menu_manager;
  }

  /**
   * Generate an eXo menu.
   *
   * @return \Drupal\exo_menu\ExoMenuInterface
   *   An eXo menu.
   */
  public function generate($id, $style = 'tree', $menu_ids = [], $settings = []) {
    return new ExoMenu($id, $style, $this->entityTypeManager, $this->exoSettings->createPluginInstance($style, $settings), $this->menuTree, $this->menuActiveTrail, $this->exoMenuManager, $menu_ids);
  }

  /**
   * Returns the maximum depth of tree that is supported.
   *
   * @return int
   *   The maximum depth.
   */
  public function maxDepth() {
    return $this->menuTree->maxDepth();
  }

}
