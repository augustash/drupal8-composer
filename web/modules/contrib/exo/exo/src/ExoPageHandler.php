<?php

namespace Drupal\exo;

use Drupal\Core\Security\TrustedCallbackInterface;

/**
 * Class ExoFormElementHandler.
 */
class ExoPageHandler implements TrustedCallbackInterface {

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return ['preRender'];
  }

  /**
   * Pre-render an element.
   *
   * @param array $element
   *   The element being pre-rendered.
   */
  public static function preRender(array $element) {
    unset($element['#theme_wrappers']['off_canvas_page_wrapper']);
    if (exo_is_admin()) {
      $element['#attributes']['class'][] = 'is-admin';
    }
    return $element;
  }

}
