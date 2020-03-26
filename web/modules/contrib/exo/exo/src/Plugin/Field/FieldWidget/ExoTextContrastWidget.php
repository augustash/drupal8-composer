<?php

namespace Drupal\exo\Plugin\Field\FieldWidget;

/**
 * Plugin implementation of the 'exo_text_contrast' widget.
 *
 * @FieldWidget(
 *   id = "exo_text_contrast",
 *   label = @Translation("Text: Contrast"),
 *   field_types = {
 *     "exo_attribute"
 *   }
 * )
 */
class ExoTextContrastWidget extends ExoAttributeWidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultOptions() {
    return [
      'dark' => exo_icon('Dark')->setIcon('regular-adjust'),
      'light' => exo_icon('Light')->setIcon('regular-circle'),
    ];
  }

}
