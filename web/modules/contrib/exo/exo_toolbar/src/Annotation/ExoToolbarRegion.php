<?php

namespace Drupal\exo_toolbar\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an eXo toolbar region annotation object.
 *
 * Plugin namespace: Plugin\ExoToolbarRegion.
 *
 * @see plugin_api
 *
 * @Annotation
 */
class ExoToolbarRegion extends Plugin {

  /**
   * The region ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the eXo toolbar region.
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * The position.
   *
   * Can be 'top', 'left', 'bottom' or 'right'.
   *
   * @var string
   */
  public $position;

  /**
   * The edge the region is positioned on.
   *
   * @var string
   */
  public $edge;

  /**
   * The weight of the plugin in it's group.
   *
   * @var int
   */
  public $weight;

  /**
   * A boolean stating that items of this type cannot be created through the UI.
   *
   * @var bool
   */
  public $no_ui = FALSE;

}
