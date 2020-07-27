<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

/**
 * A layout builder 'section' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "section_column_two_small_large",
 *   label = @Translation("Section: Two Columns (Small|Large)"),
 * )
 */
class SectionColumnTwoSmallLarge extends SectionBase {

  /**
   * The layout id.
   *
   * @var string
   */
  protected $layoutId = 'layout_twocol_section';

  /**
   * {@inheritdoc}
   */
  protected function getRegionSizes() {
    return [
      'first' => 'small',
      'second' => 'large',
    ];
  }

}
