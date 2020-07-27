<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentProperty;

/**
 * A 'column' adapter for exo components.
 *
 * @ExoComponentProperty(
 *   id = "column",
 *   label = @Translation("Column"),
 * )
 */
class Column extends ClassAttribute {

  /**
   * {@inheritdoc}
   */
  protected $options = [
    '12' => '1',
    '6' => '2',
    '4' => '3',
    '3' => '4',
    '5' => '5',
  ];

  /**
   * {@inheritdoc}
   */
  public function asAttributeArray() {
    $attributes = parent::asAttributeArray();
    $attributes['class'][] = 'exo-modifier--column';
    return $attributes;
  }

}
