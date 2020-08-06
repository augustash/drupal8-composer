<?php

namespace Drupal\exo_form\Plugin\ExoForm;

use Drupal\exo_form\Plugin\ExoFormBase;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "webform_permissions",
 *   label = @Translation("WebformPermissions"),
 *   element_types = {
 *     "webform_permissions",
 *   }
 * )
 */
class WebformPermissions extends ExoFormBase {

  /**
   * {@inheritdoc}
   */
  public function preRender($element) {
    $element = parent::preRender($element);
    // ksm($element);
    // $element['#select2'] = FALSE;
    return $element;
  }

}
