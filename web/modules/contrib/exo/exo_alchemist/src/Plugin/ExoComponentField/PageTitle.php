<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\exo_alchemist\Definition\ExoComponentDefinitionField;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldComputedBase;
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
   * Creates a LocalTasksBlock instance.
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
  public function componentPropertyInfo(ExoComponentDefinitionField $field) {
    $properties = [
      'value' => $this->t('The page title renderable.'),
    ];
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function componentViewEmptyValue(ExoComponentDefinitionField $field, $is_layout_builder) {
    if ($is_layout_builder) {
      $title = [
        '#type' => 'inline_template',
        '#template' => '{{ title }} <span class="exo-alchemist-component-description">{{ description }}</span>',
        '#context' => [
          'title' => $this->t('Dynamic Page Title'),
          'description' => $this->icon('This title will be automatically replaced with the actual page title. Edit to override.')->setIcon('regular-question-circle'),
        ],
      ];
    }
    else {
      $title = $this->titleResolver->getTitle($this->request, $this->routeMatch->getRouteObject());
    }
    return [
      'value' => $title,
    ];
  }

}
