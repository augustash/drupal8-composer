<?php

namespace Drupal\exo_modal\Element;

use Drupal\exo\Element\ExoButton;

/**
 * Provides a render element that will close a modal when used within a modal.
 *
 * @RenderElement("exo_modal_close")
 */
class ExoModalClose extends ExoButton {

  /**
   * {@inheritdoc}
   */
  public static function preRenderButton($element) {
    $element['#wrapper_attributes']['data-exo-modal-close'] = '';
    $element['#wrapper_attributes']['class'][] = 'exo-modal-action';
    $element['#wrapper_attributes']['class'][] = 'exo-modal-close-button';
    $element['#as_button'] = TRUE;
    return parent::preRenderButton($element);
  }

}
