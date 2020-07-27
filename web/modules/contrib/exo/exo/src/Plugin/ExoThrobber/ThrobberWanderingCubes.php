<?php

namespace Drupal\exo\Plugin\ExoThrobber;

use Drupal\exo\Plugin\ExoThrobberPluginBase;

/**
 * Class ThrobberWanderingCubes.
 *
 * @ExoThrobber(
 *   id = "throbber_wandering_cubes",
 *   label = @Translation("Wandering cubes")
 * )
 */
class ThrobberWanderingCubes extends ExoThrobberPluginBase {

  /**
   * Function to set markup.
   *
   * @inheritdoc
   */
  protected function setMarkup() {
    return '<div class="ajax-throbber sk-wandering-cubes">
              <div class="sk-cube sk-cube1"></div>
              <div class="sk-cube sk-cube2"></div>
            </div>';
  }

  /**
   * Function to set css file.
   *
   * @inheritdoc
   */
  protected function setCssFile() {
    return $this->path . '/css/throbber/wandering-cubes.css';
  }

}
