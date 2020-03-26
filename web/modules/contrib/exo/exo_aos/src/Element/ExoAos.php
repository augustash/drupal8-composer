<?php

namespace Drupal\exo_aos\Element;

use Drupal\Core\Render\Element\Container;

/**
 * Provides a render element that wraps child elements in a animated container.
 *
 * @RenderElement("exo_aos")
 */
class ExoAos extends Container {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#optional' => FALSE,
      '#exo_aos_settings' => [],
      '#process' => [
        [$class, 'processGroup'],
        [$class, 'processContainer'],
      ],
      '#pre_render' => [
        [$class, 'preRenderGroup'],
        [$class, 'preRenderContainer'],
      ],
      '#theme_wrappers' => ['exo_aos'],
    ];
  }

}
