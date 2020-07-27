<?php

namespace Drupal\exo_toolbar\Plugin\ExoToolbarItem;

use Drupal\exo_toolbar\Plugin\ExoToolbarItemBase;
use Drupal\exo_toolbar\Plugin\ExoToolbarItemPluginLinkTrait;
use Drupal\Core\Menu\LocalTaskManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\Element;
use Drupal\exo_toolbar\ExoToolbarElement;
use Drupal\exo_toolbar\Plugin\ExoToolbarBadgeTypeManagerInterface;

/**
 * Plugin implementation of the 'local_tasks' eXo toolbar item.
 *
 * @ExoToolbarItem(
 *   id = "local_tasks",
 *   admin_label = @Translation("Local Tasks"),
 *   category = @Translation("Common"),
 * )
 */
class LocalTasks extends ExoToolbarItemBase implements ContainerFactoryPluginInterface {

  use ExoToolbarItemPluginLinkTrait;

  /**
   * The local task manager.
   *
   * @var \Drupal\Core\Menu\LocalTaskManagerInterface
   */
  protected $localTaskManager;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Array of links.
   *
   * @var array
   */
  protected static $primary;

  /**
   * Creates a LocalTasks instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param Drupal\exo_toolbar\Plugin\ExoToolbarBadgeTypeManagerInterface $exo_toolbar_badge_type_manager
   *   The eXo toolbar badge type manager.
   * @param \Drupal\Core\Menu\LocalTaskManagerInterface $local_task_manager
   *   The local task manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ExoToolbarBadgeTypeManagerInterface $exo_toolbar_badge_type_manager, LocalTaskManagerInterface $local_task_manager, RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $exo_toolbar_badge_type_manager);
    $this->localTaskManager = $local_task_manager;
    $this->routeMatch = $route_match;
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
      $container->get('plugin.manager.menu.local_task'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function elementBuildMultiple() {
    $elements = [];
    $primary = $this->localTaskManager->getLocalTasks($this->routeMatch->getRouteName(), 0);
    $secondary = $this->localTaskManager->getLocalTasks($this->routeMatch->getRouteName(), 1);
    $this->addCacheableDependency($primary['cacheability']);
    $this->addCacheableDependency($secondary['cacheability']);
    if (count(Element::getVisibleChildren($primary['tabs'])) > 1) {
      foreach ($primary['tabs'] as $primary_tab_id => $primary_tab) {
        $elements[$primary_tab_id] = ExoToolbarElement::create([
          'title' => $primary_tab['#link']['title'],
          'url' => $primary_tab['#link']['url'],
          'weight' => $primary_tab['#weight'],
          'access' => $primary_tab['#access'],
          'attributes' => !empty($primary_tab['#link']['localized_options']['attributes']) ? $primary_tab['#link']['localized_options']['attributes'] : [],
        ])->addClass('as-pill')->setAsLink();
        if (is_a($elements[$primary_tab_id]->getTitle(), 'Drupal\exo_icon\ExoIconTranslatableMarkup')) {
          if ($icon = $elements[$primary_tab_id]->getTitle()->getIcon()) {
            $icon_only = $elements[$primary_tab_id]->getTitle()->isIconOnly() || !empty($primary_tab['#exo_icon_only']);
            $elements[$primary_tab_id]
              ->setIcon($icon->getId())
              ->setTitle($elements[$primary_tab_id]->getTitle()->getText());
            if ($icon_only) {
              $elements[$primary_tab_id]
                ->setHorizontalMarkOnly($icon_only)
                ->useTip();
            }
          }
        }
        if (!empty($primary_tab['#active'])) {
          $elements[$primary_tab_id]->addClass('is-active');
          if (count(Element::getVisibleChildren($secondary['tabs'])) > 1) {
            foreach ($secondary['tabs'] as $secondary_tab_id => $secondary_tab) {
              $title = $secondary_tab['#link']['title'];
              if (is_a($title, 'Drupal\exo_icon\ExoIconTranslatableMarkup')) {
                $title->setIconOnly(FALSE);
              }
              $subelement = $elements[$primary_tab_id]->addSubElement([
                'title' => $title,
                'url' => $secondary_tab['#link']['url'],
                'weight' => !empty($secondary_tab['#active']) ? -100 : 0,
                'access' => $secondary_tab['#access'],
              ])->setAsLink();
              if (!empty($secondary_tab['#active'])) {
                $subelement->addClass('as-pill');
                $elements[$primary_tab_id]->addSubElement([
                  'title' => $title,
                  'weight' => 0,
                  'access' => $secondary_tab['#access'],
                ])->addClass('is-active');
              }
            }
          }
        }
      }
    }
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  protected function elementPreviewBuild() {
    $elements = $this->elementBuildMultiple();
    if (empty($elements)) {
      $elements['preview'] = ExoToolbarElement::create([
        'title' => 'Local Tasks',
      ]);
    }
    return $elements;
  }

}
