<?php

namespace Drupal\exo_icon\TwigExtension;

/**
 * A class providing ExoIcon Twig extensions.
 *
 * This provides a Twig extension that registers the {{ icon() }} extension
 * to Twig.
 */
class ExoIcon extends \Twig_Extension {

  /**
   * Gets a unique identifier for this Twig extension.
   *
   * @return string
   *   A unique identifier for this Twig extension.
   */
  public function getName() {
    return 'twig.exo_icon';
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new \Twig_SimpleFunction('icon', [$this, 'renderIcon']),
    ];
  }

  /**
   * Render the icon.
   *
   * @param string $icon
   *   The icon_id of the icon to render.
   *
   * @return mixed[]
   *   A render array.
   */
  public static function renderIcon($icon) {
    $build = [
      '#theme' => 'exo_icon',
      '#icon' => $icon,
    ];
    return $build;
  }

}
