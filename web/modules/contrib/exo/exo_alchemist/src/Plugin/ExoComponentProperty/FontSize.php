<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentProperty;

/**
 * A 'font_size' adapter for exo components.
 *
 * @ExoComponentProperty(
 *   id = "font_size",
 *   label = @Translation("Font Size"),
 * )
 */
class FontSize extends ClassAttribute {

  /**
   * {@inheritdoc}
   */
  protected $type = 'exo_radios_slider';

  /**
   * {@inheritdoc}
   */
  protected $options = [
    '12' => '12',
    '14' => '14',
    '16' => '16',
    '18' => '18',
    '20' => '20',
    '24' => '24',
    '28' => '28',
    '32' => '32',
    '36' => '36',
    '40' => '40',
    '50' => '50',
    '60' => '60',
  ];

}
