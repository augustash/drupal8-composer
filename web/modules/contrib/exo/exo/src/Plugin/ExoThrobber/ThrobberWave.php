<?php

namespace Drupal\exo\Plugin\ExoThrobber;

use Drupal\exo\Plugin\ExoThrobberPluginBase;

/**
 * Class ThrobberWave.
 *
 * @ExoThrobber(
 *   id = "throbber_wave",
 *   label = @Translation("Wave")
 * )
 */
class ThrobberWave extends ExoThrobberPluginBase {

  /**
   * Function to set markup.
   *
   * @inheritdoc
   */
  protected function setMarkup() {
    return '<div class="ajax-throbber sk-wave">
              <div class="sk-rect sk-rect1"></div>
              <div class="sk-rect sk-rect2"></div>
              <div class="sk-rect sk-rect3"></div>
              <div class="sk-rect sk-rect4"></div>
              <div class="sk-rect sk-rect5"></div>
            </div>';
  }

  /**
   * Function to set css file.
   *
   * @inheritdoc
   */
  protected function setCssFile() {
    return $this->path . '/css/throbber/wave.css';
  }

}
