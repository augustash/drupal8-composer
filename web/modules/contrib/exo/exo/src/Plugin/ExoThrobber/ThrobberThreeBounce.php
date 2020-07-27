<?php

namespace Drupal\exo\Plugin\ExoThrobber;

use Drupal\exo\Plugin\ExoThrobberPluginBase;

/**
 * Class ThrobberThreeBounce.
 *
 * @ExoThrobber(
 *   id = "throbber_three_bounce",
 *   label = @Translation("Three bounce")
 * )
 */
class ThrobberThreeBounce extends ExoThrobberPluginBase {

  /**
   * Function to set markup.
   *
   * @inheritdoc
   */
  protected function setMarkup() {
    return '<div class="ajax-throbber sk-three-bounce">
              <div class="sk-child sk-bounce1"></div>
              <div class="sk-child sk-bounce2"></div>
              <div class="sk-child sk-bounce3"></div>
            </div>';
  }

  /**
   * Function to set css file.
   *
   * @inheritdoc
   */
  protected function setCssFile() {
    return $this->path . '/css/throbber/three-bounce.css';
  }

}
