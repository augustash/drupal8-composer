<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentProperty;

/**
 * A 'box_shadow' adapter for exo components.
 *
 * @ExoComponentProperty(
 *   id = "box_shadow",
 *   label = @Translation("Box Shadow"),
 * )
 */
class BoxShadow extends ClassAttribute {

  /**
   * {@inheritdoc}
   */
  protected $type = 'exo_radios_slider';

  /**
   * {@inheritdoc}
   */
  protected $options = [
    '0' => '0',
    '5' => '',
    '10' => '',
    '20' => '',
    '30' => 'Max',
  ];

}
