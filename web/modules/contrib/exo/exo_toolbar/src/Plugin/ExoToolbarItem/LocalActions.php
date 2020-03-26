<?php

namespace Drupal\exo_toolbar\Plugin\ExoToolbarItem;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\exo_toolbar\Plugin\ExoToolbarItemBase;
use Drupal\exo_toolbar\Plugin\ExoToolbarItemPluginLinkTrait;
use Drupal\Core\Menu\LocalActionManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\Element;
use Drupal\exo_toolbar\ExoToolbarElement;
use Drupal\exo_toolbar\Plugin\ExoToolbarBadgeTypeManagerInterface;

/**
 * Plugin implementation of the 'local_actions' eXo toolbar item.
 *
 * @ExoToolbarItem(
 *   id = "local_actions",
 *   admin_label = @Translation("Local Actions"),
 *   category = @Translation("Common"),
 * )
 */
class LocalActions extends ExoToolbarItemBase implements ContainerFactoryPluginInterface {

  use ExoToolbarItemPluginLinkTrait;

  /**
   * The local task manager.
   *
   * @var \Drupal\Core\Menu\LocalActionManagerInterface
   */
  protected $localActionManager;

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
   * Creates a LocalActions instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param Drupal\exo_toolbar\Plugin\ExoToolbarBadgeTypeManagerInterface $exo_toolbar_badge_type_manager
   *   The eXo toolbar badge type manager.
   * @param \Drupal\Core\Menu\LocalActionManagerInterface $local_action_manager
   *   The local task manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ExoToolbarBadgeTypeManagerInterface $exo_toolbar_badge_type_manager, LocalActionManagerInterface $local_action_manager, RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $exo_toolbar_badge_type_manager);
    $this->localActionManager = $local_action_manager;
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
      $container->get('plugin.manager.menu.local_action'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function elementBuildMultiple() {
    $elements = [];
    $route_name = $this->routeMatch->getRouteName();
    $local_actions = $this->localActionManager->getActionsForRoute($route_name);
    $cacheable_metadata = CacheableMetadata::createFromRenderArray($local_actions);
    foreach (Element::children($local_actions) as $key) {
      $action = $local_actions[$key];
      $url = $action['#link']['url'];
      if (!empty($action['#link']['localized_options']['query'])) {
        $url->setOption('query', $action['#link']['localized_options']['query']);
      }
      $elements[$key] = ExoToolbarElement::create([
        'title' => $action['#link']['title'],
        'url' => $url,
        'weight' => $action['#weight'],
        'access' => $action['#access'],
        'attributes' => !empty($action['#link']['localized_options']['attributes']) ? $action['#link']['localized_options']['attributes'] : [],
      ])->addClass('as-pill')->addClass('is-primary')->setAsLink();
      if (!empty($action['#link']['localized_options']['attributes']['data-icon'])) {
        $elements[$key]->setIcon($action['#link']['localized_options']['attributes']['data-icon']);
      }
    }
    $this->addCacheableDependency($cacheable_metadata);
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
