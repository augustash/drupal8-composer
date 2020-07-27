<?php

namespace Drupal\exo_alchemist\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a eXo Component Enhancement item annotation object.
 *
 * @see \Drupal\exo_alchemist\Plugin\ExoComponentEnhancementManager
 * @see plugin_api
 *
 * @Annotation
 */
class ExoComponentEnhancement extends Plugin {

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

  /**
   * The library of the plugin.
   *
   * @var string
   */
  public $library;

}
