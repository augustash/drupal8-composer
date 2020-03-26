<?php

namespace Drupal\exo_image\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Modify drimage routes to support redirects.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('drimage.image')) {
      $route->setDefault('_disable_route_normalizer', TRUE);
    }
    if ($route = $collection->get('exo_image.image')) {
      $route->setDefault('_disable_route_normalizer', TRUE);
    }
  }

}
