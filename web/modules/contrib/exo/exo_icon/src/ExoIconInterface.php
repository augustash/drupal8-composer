<?php

namespace Drupal\exo_icon;

/**
 * Defines an object which can be rendered by the Render API.
 */
interface ExoIconInterface {

  /**
   * Get the icon id.
   *
   * @return string
   *   The icon id.
   */
  public function getId();

  /**
   * Get icon as render array.
   *
   * @return array
   *   A render array.
   */
  public function toRenderable();

}
