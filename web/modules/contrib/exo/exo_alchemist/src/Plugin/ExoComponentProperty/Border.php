<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentProperty;

/**
 * A 'border' adapter for exo components.
 *
 * @ExoComponentProperty(
 *   id = "border",
 *   label = @Translation("Border"),
 * )
 */
class Border extends ClassAttribute {

  /**
   * {@inheritdoc}
   */
  protected $type = 'exo_radios_slider';

  /**
   * {@inheritdoc}
   */
  protected $options = [
    '0' => '0px',
    '1' => '1px',
    '2' => '2px',
    '5' => '5px',
    '10' => '10px',
  ];

}
