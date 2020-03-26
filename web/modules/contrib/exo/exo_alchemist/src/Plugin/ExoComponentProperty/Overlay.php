<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentProperty;

/**
 * A 'overlay' adapter for exo components.
 *
 * @ExoComponentProperty(
 *   id = "overlay",
 *   label = @Translation("Overlay"),
 * )
 */
class Overlay extends ClassAttribute {

  /**
   * {@inheritdoc}
   */
  protected $type = 'exo_radios_slider';

  /**
   * {@inheritdoc}
   */
  protected $options = [
    '_none' => 'Off',
    '10' => '',
    '20' => '',
    '30' => '',
    '40' => '',
    '50' => '',
    '60' => '',
    '70' => '',
    '80' => '',
    '90' => 'Full',
  ];

  /**
   * {@inheritdoc}
   */
  public function asAttributeArray() {
    $attributes = parent::asAttributeArray();
    $attributes['class'][] = 'exo-modifier--overlay';
    return $attributes;
  }

}
