<?php

namespace Drupal\exo_breadcrumbs\Breadcrumb;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatch;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\system\PathBasedBreadcrumbBuilder;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;

/**
 * Adds the current page title to the breadcrumb and applies the set
 * home/first link text from within the form.
 *
 * Extend PathBased Breadcrumbs to include the current page title.
 *
 * {@inheritdoc}
 */
class BreadcrumbFormatter extends PathBasedBreadcrumbBuilder {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $attributes) {
    // Get all parameters.
    $parameters = $attributes->getParameters()->all();

    // Determine if the current page is a node page 
    if (isset($parameters['node']) && !empty($parameters['node'])) {
      return TRUE;
    }

    return FALSE;
 }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $links = [];
    
    // Do not display a breadcrumb on the front page.
    if ($this->pathMatcher->isFrontPage()) {
      return $breadcrumb;
    }
    
    // General path-based breadcrumbs. Use the actual request path, prior to
    // resolving path aliases, so the breadcrumb can be defined by simply
    // creating a hierarchy of path aliases.
    $path = trim($this->context->getPathInfo(), '/');
    $path_elements = explode('/', $path);
    $exclude = [];
    // Don't show a link to the front-page path.
    $front = $this->config->get('page.front');
    $exclude[$front] = TRUE;

    // /user is just a redirect, so skip it.
    // @todo Find a better way to deal with /user.
    $exclude['/user'] = TRUE;
    
    // Adds current page title as non-clickable final breadcrumb.
    $request = \Drupal::request();
    $route = $request->attributes->get(RouteObjectInterface::ROUTE_OBJECT);
    $title = $this->titleResolver->getTitle($request, $route);
    
    if (!isset($title)) {
      // Fallback to using the raw path component as the title if the
      // route is missing a _title or _title_callback attribute.
      $title = str_replace(['-', '_'], ' ', Unicode::ucfirst(end($path_elements)));
    }
    
    $links[] = Link::createFromRoute($title, '<none>');
    
    // Adds additional paths.
    while (count($path_elements) > 1) {
      array_pop($path_elements);
      // Copy the path elements for up-casting.
      $route_request = $this->getRequestForPath('/' . implode('/', $path_elements), $exclude);
      if ($route_request) {
        $route_match = RouteMatch::createFromRequest($route_request);
        $access = $this->accessManager->check($route_match, $this->currentUser, NULL, TRUE);
        // The set of breadcrumb links depends on the access result, so merge
        // the access result's cacheability metadata.
        $breadcrumb = $breadcrumb->addCacheableDependency($access);
        if ($access->isAllowed()) {
          $title = $this->titleResolver->getTitle($route_request, $route_match->getRouteObject());
          if (!isset($title)) {
            // Fallback to using the raw path component as the title if the
            // route is missing a _title or _title_callback attribute.
            $title = str_replace(['-', '_'], ' ', Unicode::ucfirst(end($path_elements)));
          }
          $url = Url::fromRouteMatch($route_match);
          $links[] = new Link($title, $url);
        }
      }
    }

    // Add the Home link.
    $title = \Drupal::service('exo_breadcrumbs.settings')->getSetting('home_title');
    $links[] = Link::createFromRoute($title, '<front>');

    // Add the url.path.parent cache context. This code ignores the last path
    // part so the result only depends on the path parents.
    $breadcrumb->addCacheContexts(['url.path.parent', 'url.path.is_front', 'route']);
  
    return $breadcrumb->setLinks(array_reverse($links));
  }
}
