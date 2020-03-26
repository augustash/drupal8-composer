<?php

namespace Drupal\exo\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an eXo theme annotation object.
 *
 * Plugin namespace: Plugin\ExoTheme.
 *
 * @see plugin_api
 *
 * @Annotation
 */
class ExoTheme extends Plugin {

  /**
   * The theme ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the eXo theme.
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * The relative path to the compiled CSS files.
   *
   * Defaults to ./css.
   *
   * @var string
   */
  public $path;

  /**
   * The colors the theme provides.
   *
   * @var array
   */
  public $colors;

}
