<?php

namespace Drupal\exo_alchemist\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Component Field item annotation object.
 *
 * @see \Drupal\exo_alchemist\Plugin\ExoComponentFieldManager
 * @see plugin_api
 *
 * @Annotation
 */
class ExoComponentField extends Plugin {

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

  // /**
  //  * The storage configuration.
  //  *
  //  * @var array
  //  */
  // public $storage;

  // /**
  //  * The field configuration.
  //  *
  //  * @var array
  //  */
  // public $field;

  // /**
  //  * The widget configuration.
  //  *
  //  * @var array
  //  */
  // public $widget;

  // /**
  //  * The formatter configuration.
  //  *
  //  * @var array
  //  */
  // public $formatter;

}
