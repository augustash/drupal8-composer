<?php

namespace Drupal\exo\Plugin\ExoThrobber;

use Drupal\exo\Plugin\ExoThrobberPluginBase;

/**
 * Class ThrobberFoldingCube.
 *
 * @ExoThrobber(
 *   id = "throbber_folding_cube",
 *   label = @Translation("Folding cube")
 * )
 */
class ThrobberFoldingCube extends ExoThrobberPluginBase {

  /**
   * Function to set markup.
   *
   * @inheritdoc
   */
  protected function setMarkup() {
    return '<div class="ajax-throbber sk-folding-cube">
              <div class="sk-cube1 sk-cube"></div>
              <div class="sk-cube2 sk-cube"></div>
              <div class="sk-cube4 sk-cube"></div>
              <div class="sk-cube3 sk-cube"></div>
            </div>';
  }

  /**
   * Function to set css file.
   *
   * @inheritdoc
   */
  protected function setCssFile() {
    return $this->path . '/css/throbber/folding-cube.css';
  }

}
