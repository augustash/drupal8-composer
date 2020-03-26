<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentProperty;

/**
 * A 'border_radius' adapter for exo components.
 *
 * @ExoComponentProperty(
 *   id = "border_radius",
 *   label = @Translation("Border Radius"),
 * )
 */
class BorderRadius extends ClassAttribute {

  /**
   * {@inheritdoc}
   */
  protected $type = 'exo_radios_slider';

  /**
   * {@inheritdoc}
   */
  protected $options = [
    '0' => '0px',
    '5' => '5px',
    '10' => '10px',
    '30' => '30px',
    '50' => '50px',
    '50p' => '50%',
  ];

}
