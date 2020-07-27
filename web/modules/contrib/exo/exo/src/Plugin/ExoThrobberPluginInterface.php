<?php

namespace Drupal\exo\Plugin;

/**
 * Interface ThrobberInterface.
 */
interface ExoThrobberPluginInterface {

  /**
   * Returns markup for throbber.
   */
  public function getMarkup();

  /**
   * Returns path to css file.
   */
  public function getCssFile();

  /**
   * Returns human readable label.
   */
  public function getLabel();

}
