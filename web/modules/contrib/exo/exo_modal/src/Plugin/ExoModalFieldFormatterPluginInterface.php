<?php

namespace Drupal\exo_modal\Plugin;

use Drupal\Core\Field\FormatterInterface;
use Drupal\Core\Field\FieldItemInterface;

/**
 * Defines the required interface for all eXo modal block plugins.
 */
interface ExoModalFieldFormatterPluginInterface extends FormatterInterface {

  /**
   * Builds and returns the renderable modal array for this field plugin.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   The field values to be rendered.
   * @param int $delta
   *   The delta of the item as it related to the field.
   * @param string $langcode
   *   The language that should be used to render the field.
   *
   * @return array
   *   A renderable array for $items, as an array of child elements keyed by
   *   consecutive numeric indexes starting from 0.
   */
  public function buildModal(FieldItemInterface $item, $delta, $langcode);

}
