<?php

namespace Drupal\exo_alchemist\Plugin;

use Drupal\exo_alchemist\Definition\ExoComponentDefinitionField;

/**
 * Base class for Component Field plugins.
 */
abstract class ExoComponentFieldComputedBase extends ExoComponentFieldBase implements ExoComponentFieldComputedInterface {

  /**
   * {@inheritdoc}
   */
  public function componentView(ExoComponentDefinitionField $field, $is_layout_builder) {
    return [
      $this->componentViewValue($field, 0, $is_layout_builder),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function componentViewValue(ExoComponentDefinitionField $field, $delta, $is_layout_builder) {
    return NULL;
  }

}
