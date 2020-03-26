<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentProperty;

/**
 * A 'padding' adapter for exo components.
 *
 * @ExoComponentProperty(
 *   id = "padding",
 *   label = @Translation("Padding"),
 * )
 */
class Padding extends ClassAttribute {

  /**
   * {@inheritdoc}
   */
  protected $type = 'exo_radios_slider';

  /**
   * {@inheritdoc}
   */
  protected $options = [
    '0' => '0',
    '10' => '10',
    '20' => '20',
    '30' => '30',
    '60' => '60',
    '90' => '90',
    '120' => '120',
  ];

}
