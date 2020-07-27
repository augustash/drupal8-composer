<?php

namespace Drupal\exo\Plugin\ExoThrobber;

use Drupal\exo\Plugin\ExoThrobberPluginBase;

/**
 * Class ThrobberDoubleBounce.
 *
 * @ExoThrobber(
 *   id = "throbber_double_bounce",
 *   label = @Translation("Double bounce")
 * )
 */
class ThrobberDoubleBounce extends ExoThrobberPluginBase {

  /**
   * Function to set markup.
   *
   * @inheritdoc
   */
  protected function setMarkup() {
    return '<div class="ajax-throbber sk-double-bounce">
                <div class="sk-child sk-double-bounce1"></div>
                <div class="sk-child sk-double-bounce2"></div>
            </div>';
  }

  /**
   * Function to set css file.
   *
   * @inheritdoc
   */
  protected function setCssFile() {
    return $this->path . '/css/throbber/double-bounce.css';
  }

}
