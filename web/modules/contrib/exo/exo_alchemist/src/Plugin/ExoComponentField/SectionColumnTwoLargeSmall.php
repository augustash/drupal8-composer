<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

/**
 * A layout builder 'section' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "section_column_two_large_small",
 *   label = @Translation("Section: Two Columns (Large|Small)"),
 * )
 */
class SectionColumnTwoLargeSmall extends SectionBase {

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
      'first' => 'large',
      'second' => 'small',
    ];
  }

}
