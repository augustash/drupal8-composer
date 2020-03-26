<?php

namespace Drupal\exo_form\Plugin\ExoForm;

use Drupal\exo_form\Plugin\ExoFormBase;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "exo_modal",
 *   label = @Translation("ExoModal"),
 *   element_types = {
 *     "exo_modal",
 *   }
 * )
 */
class ExoModal extends ExoFormBase {

  /**
   * {@inheritdoc}
   */
  protected $intersectSupported = TRUE;

}
