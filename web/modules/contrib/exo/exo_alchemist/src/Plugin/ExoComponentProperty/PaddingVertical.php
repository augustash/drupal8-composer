<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentProperty;

/**
 * A 'padding_vertical' adapter for exo components.
 *
 * @ExoComponentProperty(
 *   id = "padding_vertical",
 *   label = @Translation("Padding: Vertical"),
 * )
 */
class PaddingVertical extends ClassAttribute {

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
