<?php

namespace Drupal\exo\Plugin\ExoThrobber;

use Drupal\exo\Plugin\ExoThrobberPluginBase;

/**
 * Class ThrobberChasingDots.
 *
 * @ExoThrobber(
 *   id = "throbber_chasing_dots",
 *   label = @Translation("Chasing dots")
 * )
 */
class ThrobberChasingDots extends ExoThrobberPluginBase {

  /**
   * Function to set markup.
   *
   * @inheritdoc
   */
  protected function setMarkup() {
    return '<div class="ajax-throbber sk-chasing-dots">
              <div class="sk-child sk-dot1"></div>
              <div class="sk-child sk-dot2"></div>
            </div>';
  }

  /**
   * Function to set css file.
   *
   * @inheritdoc
   */
  protected function setCssFile() {
    return $this->path . '/css/throbber/chasing-dots.css';
  }

}
