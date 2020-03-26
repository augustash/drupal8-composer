<?php

namespace Drupal\exo_toolbar\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an eXo toolbar dialog type annotation object.
 *
 * Plugin namespace: Plugin\ExoToolbarDialogType.
 *
 * @see plugin_api
 *
 * @Annotation
 */
class ExoToolbarDialogType extends Plugin {

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

}
