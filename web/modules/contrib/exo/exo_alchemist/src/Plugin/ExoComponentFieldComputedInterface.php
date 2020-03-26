<?php

namespace Drupal\exo_alchemist\Plugin;

use Drupal\exo_alchemist\Definition\ExoComponentDefinitionField;

/**
 * Defines an interface for Component Field plugins.
 */
interface ExoComponentFieldComputedInterface extends ExoComponentFieldInterface {

  /**
   * Return the computed value of a field that is set as computed.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField $field
   *   The eXo component field.
   * @param string $is_layout_builder
   *   TRUE if we are in layout builder mode.
   *
   * @return array
   *   A value that will be sent to twig.
   */
  public function componentView(ExoComponentDefinitionField $field, $is_layout_builder);

}
