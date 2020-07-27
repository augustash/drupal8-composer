<?php

namespace Drupal\exo\Plugin\ExoThrobber;

use Drupal\exo\Plugin\ExoThrobberPluginBase;

/**
 * Class ThrobberRotatingPlane.
 *
 * @ExoThrobber(
 *   id = "throbber_rotating_plane",
 *   label = @Translation("Rotating plane")
 * )
 */
class ThrobberRotatingPlane extends ExoThrobberPluginBase {

  /**
   * Function to set markup.
   *
   * @inheritdoc
   */
  protected function setMarkup() {
    return '<div class="ajax-throbber sk-rotating-plane"></div>';
  }

  /**
   * Function to set css file.
   *
   * @inheritdoc
   */
  protected function setCssFile() {
    return $this->path . '/css/throbber/rotating-plane.css';
  }

}
