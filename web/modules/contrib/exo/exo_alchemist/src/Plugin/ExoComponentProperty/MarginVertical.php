<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentProperty;

/**
 * A 'margin' adapter for exo components.
 *
 * @ExoComponentProperty(
 *   id = "margin_vertical",
 *   label = @Translation("Margin: Vertical"),
 * )
 */
class MarginVertical extends ClassAttribute {

  /**
   * {@inheritdoc}
   */
  protected $type = 'exo_radios_slider';

  /**
   * {@inheritdoc}
   */
  protected $options = [
    '0' => '0',
    '30' => '30',
    '60' => '60',
    '90' => '90',
    '120' => '120',
  ];

}
