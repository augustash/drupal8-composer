<?php

namespace Drupal\exo_form\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a eXo Form item annotation object.
 *
 * @see \Drupal\exo_form\Plugin\ExoFormManager
 * @see plugin_api
 *
 * @Annotation
 */
class ExoForm extends Plugin {

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
   * An array of element types the plugin supports.
   *
   * @var array
   */
  public $element_types = [];

  /**
   * An integer to determine the weight of this widget.
   *
   * The weight is relative to other widgets when processing a given element.
   *
   * @var int
   */
  public $weight = NULL;

}
