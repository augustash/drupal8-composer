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

  /**
   * The category in the admin UI where the block will be listed.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $category = '';

  /**
   * An array of context definitions describing the context used by the plugin.
   *
   * The array is keyed by context names.
   *
   * @var \Drupal\Core\Annotation\ContextDefinition[]
   */
  public $context_definitions = [];

}
