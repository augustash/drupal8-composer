<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentProperty;

/**
 * A 'padding_horizontal' adapter for exo components.
 *
 * @ExoComponentProperty(
 *   id = "padding_horizontal",
 *   label = @Translation("Padding: Horizontal"),
 * )
 */
class PaddingHorizontal extends ClassAttribute {

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
