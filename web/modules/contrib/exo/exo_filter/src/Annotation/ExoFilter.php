<?php

namespace Drupal\exo_filter\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a eXo Filter item annotation object.
 *
 * @see \Drupal\exo_filter\Plugin\ExoFilterManager
 * @see plugin_api
 *
 * @Annotation
 */
class ExoFilter extends Plugin {


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
