<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentProperty;

/**
 * A 'containment' adapter for exo components.
 *
 * @ExoComponentProperty(
 *   id = "containment",
 *   label = @Translation("Containment"),
 * )
 */
class Containment extends ClassAttribute {

  /**
   * {@inheritdoc}
   */
  protected $type = 'exo_radios_slider';

  /**
   * {@inheritdoc}
   */
  protected $options = [
    'full' => 'Full',
    'wide' => 'Wide',
    'normal' => 'Normal',
    'narrow' => 'Narrow',
  ];

}
