<?php

namespace Drupal\exo_icon\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\exo_icon\ExoIconTranslationTrait;

/**
 * Plugin implementation of the 'icon' formatter.
 *
 * @FieldFormatter(
 *   id = "icon",
 *   label = @Translation("eXo Icon"),
 *   field_types = {
 *     "icon"
 *   }
 * )
 */
class IconFormatter extends FormatterBase {
  use ExoIconTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta]['#markup'] = $this->icon()->setIcon($item->value);
    }

    return $elements;
  }

}
