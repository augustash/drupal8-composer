<?php

namespace Drupal\exo_alchemist\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Component Property item annotation object.
 *
 * @see \Drupal\exo_alchemist\Plugin\ExoComponentPropertyManager
 * @see plugin_api
 *
 * @Annotation
 */
class ExoComponentProperty extends Plugin {


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
