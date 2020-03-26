<?php

namespace Drupal\exo\Generator;

/**
 * Class ThemeGenerator.
 *
 * @package Drupal\Console\Generator
 */
abstract class ExoGeneratorThemeBase extends ExoGeneratorBase {

  /**
   * Get theme includes for use in theme SCSS file.
   *
   * @return string
   *   A string of theme includes.
   */
  protected function getThemeIncludes() {
    // Prepare includes.
    $includes = ['exo-theme'];
    array_walk($includes, function (&$include) {
      $include = "@import '$include';";
    });
    return implode("\n", $includes);
  }

}
