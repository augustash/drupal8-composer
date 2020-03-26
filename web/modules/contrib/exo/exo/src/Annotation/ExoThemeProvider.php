<?php

namespace Drupal\exo\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an eXo theme annotation object.
 *
 * Plugin namespace: Plugin\ExoThemeProvider.
 *
 * @see plugin_api
 *
 * @Annotation
 */
class ExoThemeProvider extends Plugin {

  /**
   * The theme provider ID.
   *
   * @var string
   */
  public $id;

  /**
   * The module library the theme will be attached to.
   *
   * Defaults to the module name.
   *
   * @var string
   */
  public $library;

  /**
   * The relative path to the compiled CSS file.
   *
   * Defaults to ./css.
   *
   * @var string
   */
  public $path;

  /**
   * The filename of the compiled CSS file.
   *
   * Defaults to MODULENAME.theme.css.
   *
   * @var string
   */
  public $filename;

  /**
   * The relative path to the twig generator template for this theme.
   *
   * This template should exist within the modules /ExoThemeProvider folder.
   * Defaults to ExoTheme.scss.twig.
   *
   * @var string
   */
  public $template;

}
