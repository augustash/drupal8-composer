<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

/**
 * A layout builder 'section' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "section_column_two_medium_medium",
 *   label = @Translation("Section: Two Columns (Medium|Medium)"),
 * )
 */
class SectionColumnTwoMediumMedium extends SectionBase {

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
      'first' => 'medium',
      'second' => 'medium',
    ];
  }

}
