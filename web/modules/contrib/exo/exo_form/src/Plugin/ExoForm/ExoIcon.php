<?php

namespace Drupal\exo_form\Plugin\ExoForm;

use Drupal\exo_form\Plugin\ExoFormBase;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "exo_icon",
 *   label = @Translation("eXo Icon"),
 *   element_types = {
 *     "exo_icon",
 *   }
 * )
 */
class ExoIcon extends ExoFormBase {

  /**
   * {@inheritdoc}
   */
  protected $intersectSupported = TRUE;

}
