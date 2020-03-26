<?php

namespace Drupal\exo_menu\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an eXo menu annotation object.
 *
 * Plugin namespace: Plugin\ExoMenu.
 *
 * @see plugin_api
 *
 * @Annotation
 */
class ExoMenu extends Plugin {

  /**
   * The dialog type ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the eXo toolbar dialog type.
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * Break out each menu level into its own menu.
   *
   * @var bool
   */
  public $as_levels;

}
