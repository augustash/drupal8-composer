<?php

namespace Drupal\exo_alchemist\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    foreach ([
      // 'entity.block_content.field_ui_fields',
      'entity.entity_form_display.block_content.default',
      'entity.entity_form_display.block_content.form_mode',
      'entity.entity_view_display.block_content.default',
      'entity.entity_view_display.block_content.view_mode',
    ] as $route_id) {
      if ($route = $collection->get($route_id)) {
        $route->setRequirement('_entity_access', 'block_content_type.update');
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = parent::getSubscribedEvents();
    $events[RoutingEvents::ALTER] = ['onAlterRoutes', -200];
    return $events;
  }

}
