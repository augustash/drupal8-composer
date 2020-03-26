<?php

/**
 * @file
 * Contains exo_site_settings.page.inc.
 *
 * Page callback for config pages.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for config page templates.
 *
 * Default template: exo_site_settings.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_exo_site_settings(array &$variables) {
  // Fetch SiteSettings Entity Object.
  $exo_site_settings = $variables['elements']['#exo_site_settings'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
