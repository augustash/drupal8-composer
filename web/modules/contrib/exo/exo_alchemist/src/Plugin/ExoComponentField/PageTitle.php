<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\exo_icon\ExoIconTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * A 'page_title' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "page_title",
 *   label = @Translation("Page Title"),
 *   computed = TRUE
 * )
 */
class PageTitle extends Text implements ContainerFactoryPluginInterface {
  use ExoIconTranslationTrait;

  /**
   * The current Request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The title resolver.
   *
   * @var \Drupal\Core\Controller\TitleResolverInterface
   */
  protected $titleResolver;

  /**
   * Creates a PageTitle instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\Core\Controller\TitleResolverInterface $title_resolver
   *   The title resolver service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Request $request, RouteMatchInterface $route_match, TitleResolverInterface $title_resolver) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->request = $request;
    $this->routeMatch = $route_match;
    $this->titleResolver = $title_resolver;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('current_route_match'),
      $container->get('title_resolver')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    $properties = [
      'value' => $this->t('The page title renderable.'),
    ];
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultValue($delta = 0) {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function isHideable(array $contexts) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function viewEmptyValue(array $contexts) {
    $title = $this->t('Dynamic Page Title');
    if ($this->isLayoutBuilder($contexts)) {
      $entity = $contexts['layout_builder.entity']->getContextValue();
      $title = $entity->isNew() ? $title : $contexts['layout_builder.entity']->getContextValue()->label();
      $title = [
        '#type' => 'inline_template',
        '#template' => '{{ title }} <span class="exo-alchemist-component-description">{{ description }}</span>',
        '#context' => [
          'title' => $title,
          'description' => $this->isEditable($contexts) ? $this->icon('This title will be automatically replaced with the actual page title. Edit to override.')->setIcon('regular-question-circle') : '',
        ],
      ];
    }
    elseif ($route_object = $this->routeMatch->getRouteObject()) {
      $title = $this->titleResolver->getTitle($this->request, $route_object);
    }
    return [
      'value' => $title,
    ];
  }

}
