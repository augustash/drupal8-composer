<?php

namespace Drupal\exo_form\Plugin\ExoForm;

use Drupal\exo_form\Plugin\ExoFormBase;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "generic",
 *   label = @Translation("Generic"),
 *   element_types = {
 *     "item",
 *     "exo_radios_slider",
 *     "jquery_colorpicker",
 *   }
 * )
 */
class Generic extends ExoFormBase {

}
