<?php

namespace Drupal\exo\Plugin\Field\FieldWidget;

/**
 * Provides an interface for attribute widgets.
 */
interface ExoAttributeWidgetInterface {

  /**
   * Get the default options.
   *
   * @return array
   *   An array of options.
   */
  public function getDefaultOptions();

}
