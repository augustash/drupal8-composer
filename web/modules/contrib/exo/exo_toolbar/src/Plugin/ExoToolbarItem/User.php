<?php

namespace Drupal\exo_toolbar\Plugin\ExoToolbarItem;

use Drupal\exo_toolbar\Plugin\ExoToolbarItemDialogBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_toolbar\Plugin\ExoToolbarDialogTypeManagerInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Cache\Cache;
use Drupal\exo_toolbar\Entity\ExoToolbarItemInterface;
use Drupal\exo_toolbar\Plugin\ExoToolbarBadgeTypeManagerInterface;

/**
 * Plugin implementation of the 'user' eXo toolbar item.
 *
 * @ExoToolbarItem(
 *   id = "user",
 *   admin_label = @Translation("User"),
 *   category = @Translation("Common"),
 * )
 */
class User extends ExoToolbarItemDialogBase implements ContainerFactoryPluginInterface {

  /**
   * The menu used for the links.
   *
   * @var string
   */
  protected $menuName = 'account';

  /**
   * Drupal\Core\Session\AccountProxy definition.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Drupal\user\UserInterface definition.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $currentAccount;

  /**
   * The menu link tree service.
   *
   * @var \Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected $menuTree;

  /**
   * The menu storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $menuStorage;

  /**
   * Creates a User instance.
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
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user service.
   * @param \Drupal\Core\Menu\MenuLinkTreeInterface $menu_tree
   *   The menu link tree service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ExoToolbarBadgeTypeManagerInterface $exo_toolbar_badge_type_manager, ExoToolbarDialogTypeManagerInterface $exo_toolbar_dialog_type_manager, ModuleHandlerInterface $module_handler, AccountProxyInterface $current_user, MenuLinkTreeInterface $menu_tree) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $exo_toolbar_badge_type_manager, $exo_toolbar_dialog_type_manager);
    $this->moduleHandler = $module_handler;
    $this->currentUser = $current_user;
    $this->currentAccount = $this->entityTypeManager()->getStorage('user')->load($this->currentUser->id());
    $this->menuTree = $menu_tree;
    $this->menuStorage = $this->entityTypeManager()->getStorage('menu');
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
      $container->get('plugin.manager.exo_toolbar_dialog_type'),
      $container->get('module_handler'),
      $container->get('current_user'),
      $container->get('menu.link_tree')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function itemForm(array $form, FormStateInterface $form_state) {
    $form = parent::itemForm($form, $form_state);
    $form['icon']['#access'] = FALSE;
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function elementBuild() {
    $element = parent::elementBuild();

    $image = $this->getGravatar($this->currentAccount->getEmail());
    $this->moduleHandler->alter('exo_toolbar_user_image', $image, $this->currentAccount);
    $element
      ->setTitle($this->currentAccount->label())
      ->setIcon('regular-chevron-down')
      ->setIconPosition('after')
      ->setIconSize('small')
      ->setImage($image);

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function dialogBuild(ExoToolbarItemInterface $exo_toolbar_item, $arg = NULL) {
    return $this->buildMenuTree($this->menuName, 1, 1);
  }

  /**
   * Render a menu for display within eXo.
   *
   * @param string $menu_name
   *   The menu name to render.
   * @param string $level
   *   The menu level to start rendering from.
   * @param string $depth
   *   The menu deptch to render.
   */
  protected function buildMenuTree($menu_name, $level = 1, $depth = 1) {
    $parameters = new MenuTreeParameters();

    // Adjust the menu tree parameters based on the block's configuration.
    $level = $level;
    $depth = $depth;
    $parameters->setMinDepth($level);
    // When the depth is configured to zero, there is no depth limit. When depth
    // is non-zero, it indicates the number of levels that must be displayed.
    // Hence this is a relative depth that we must convert to an actual
    // (absolute) depth, that may never exceed the maximum depth.
    if ($depth > 0) {
      $parameters->setMaxDepth(min($level + $depth - 1, $this->menuTree->maxDepth()));
    }

    $tree = $this->menuTree->load($menu_name, $parameters);
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $this->menuTree->transform($tree, $manipulators);
    return $this->menuTree->build($tree);
  }

  /**
   * Get a Gravatar URL for a specified email address.
   *
   * @param string $email
   *   The email address.
   * @param string $s
   *   Size in pixels, defaults to 80px [ 1 - 2048 ].
   * @param string $d
   *   Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ].
   * @param string $r
   *   Maximum rating (inclusive) [ g | pg | r | x ].
   *
   * @return string
   *   String containing either just a URL or a complete image tag
   */
  protected function getGravatar($email, $s = 50, $d = 'mm', $r = 'g') {
    $url = 'https://www.gravatar.com/avatar/';
    $url .= md5(strtolower(trim($email)));
    $url .= "?s=$s&d=$d&r=$r";
    return $url;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $cache_tags = parent::getCacheTags();
    $cache_tags[] = 'config:system.menu.' . $this->menuName;
    return Cache::mergeTags($cache_tags, $this->currentAccount->getCacheTags());
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    // ::build() uses MenuLinkTreeInterface::getCurrentRouteMenuTreeParameters()
    // to generate menu tree parameters, and those take the active menu trail
    // into account. Therefore, we must vary the rendered menu by the active
    // trail of the rendered menu.
    // Additional cache contexts, e.g. those that determine link text or
    // accessibility of a menu, will be bubbled automatically.
    $menu_name = $this->menuName;
    return Cache::mergeContexts(parent::getCacheContexts(), ['user', 'route.menu_active_trails:' . $menu_name]);
  }

}
