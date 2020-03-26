<?php

namespace Drupal\exo_toolbar\Routing;

/**
 * Provides an interface for URL path matchers.
 */
interface ExoToolbarPathMatcherInterface {

  /**
   * Checks if the current page is an eXo Toolbar admin page.
   *
   * @return bool
   *   TRUE if the current page is an eXo Toolbar admin page.
   */
  public function isAdmin();

}
