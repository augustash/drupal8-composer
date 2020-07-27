<?php

namespace Drupal\exo\Plugin\ExoThrobber;

use Drupal\exo\Plugin\ExoThrobberPluginBase;

/**
 * Class ThrobberCubeGrid.
 *
 * @ExoThrobber(
 *   id = "throbber_cube_grid",
 *   label = @Translation("Cube gird")
 * )
 */
class ThrobberCubeGrid extends ExoThrobberPluginBase {

  /**
   * Function to set markup.
   *
   * @inheritdoc
   */
  protected function setMarkup() {
    return '<div class="ajax-throbber sk-cube-grid">
              <div class="sk-cube sk-cube1"></div>
              <div class="sk-cube sk-cube2"></div>
              <div class="sk-cube sk-cube3"></div>
              <div class="sk-cube sk-cube4"></div>
              <div class="sk-cube sk-cube5"></div>
              <div class="sk-cube sk-cube6"></div>
              <div class="sk-cube sk-cube7"></div>
              <div class="sk-cube sk-cube8"></div>
              <div class="sk-cube sk-cube9"></div>
            </div>';
  }

  /**
   * Function to set css file.
   *
   * @inheritdoc
   */
  protected function setCssFile() {
    return $this->path . '/css/throbber/cube-grid.css';
  }

}
