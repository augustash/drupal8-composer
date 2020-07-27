<?php

namespace Drupal\exo\Plugin\ExoThrobber;

use Drupal\exo\Plugin\ExoThrobberPluginBase;

/**
 * Class ThrobberPulse.
 *
 * @ExoThrobber(
 *   id = "throbber_pulse",
 *   label = @Translation("Pulse")
 * )
 */
class ThrobberPulse extends ExoThrobberPluginBase {

  /**
   * Function to set markup.
   *
   * @inheritdoc
   */
  protected function setMarkup() {
    return '<div class="ajax-throbber sk-spinner sk-spinner-pulse"></div>';
  }

  /**
   * Function to set css file.
   *
   * @inheritdoc
   */
  protected function setCssFile() {
    return $this->path . '/css/throbber/pulse.css';
  }

}
