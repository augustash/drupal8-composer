<?php

namespace Drupal\exo\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Throbber item annotation object.
 *
 * @Annotation
 */
class ExoThrobber extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

}
