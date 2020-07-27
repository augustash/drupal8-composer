<?php

namespace Drupal\exo_form\Plugin\ExoForm;

use Drupal\exo_form\Plugin\ExoFormBase;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "Item",
 *   label = @Translation("Item"),
 *   element_types = {
 *     "exo_radios_slider",
 *   }
 * )
 */
class Item extends ExoFormBase {

  /**
   * {@inheritdoc}
   */
  protected $intersectSupported = TRUE;

}
