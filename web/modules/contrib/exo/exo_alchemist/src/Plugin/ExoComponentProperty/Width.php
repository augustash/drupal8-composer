<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentProperty;

/**
 * A 'width' adapter for exo components.
 *
 * @ExoComponentProperty(
 *   id = "width",
 *   label = @Translation("Width"),
 * )
 */
class Width extends ClassAttribute {

  /**
   * {@inheritdoc}
   */
  protected $type = 'exo_radios_slider';

  /**
   * {@inheritdoc}
   */
  protected $options = [
    '_none' => 'Auto',
    '30' => '30%',
    '40' => '40%',
    '50' => '50%',
    '60' => '60%',
    '70' => '70%',
    '80' => '80%',
    '90' => '90%',
    '100' => '100%',
  ];

}
