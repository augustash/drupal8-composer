<?php

namespace Drupal\exo\Command;

use Drupal\Core\File\FileSystemInterface;
use Drupal\exo\ExoThemePluginManagerInterface;
use Drush\Commands\DrushCommands;

/**
 * Defines Drush commands for the Search API.
 */
class ExoDrushCommands extends DrushCommands {

  /**
   * The eXo theme manager.
   *
   * @var \Drupal\exo\ExoThemePluginManagerInterface
   */
  protected $exoThemeManager;

  /**
   * The file system manager.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Constructs a ExoDrushCommands object.
   *
   * @param \Drupal\exo\ExoThemePluginManagerInterface $exo_theme_manager
   *   The eXo theme manager.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system manager.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *   Thrown if the "search_api_index" or "search_api_server" entity types'
   *   storage handlers couldn't be loaded.
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *   Thrown if the "search_api_index" or "search_api_server" entity types are
   *   unknown.
   */
  public function __construct(ExoThemePluginManagerInterface $exo_theme_manager, FileSystemInterface $file_system) {
    $this->exoThemeManager = $exo_theme_manager;
    $this->fileSystem = $file_system;
  }

  /**
   * Generate global exo-theme.scss.
   *
   * @command exo:exo-scss
   *
   * @usage drush exo:scss
   *   Run the exo theme generator.
   *
   * @aliases exo-scss
   *
   * @throws \Exception
   *   If no index or no server were passed or passed values are invalid.
   */
  public function exoScss() {
    $dirname = 'public://exo';
    $this->fileSystem->prepareDirectory($dirname, FILE_CREATE_DIRECTORY);

    // Generate exo-common.scss.
    $destination = $dirname . '/exo-common.scss';
    $exo_path = DRUPAL_ROOT . '/' . drupal_get_path('module', 'exo');
    $data = file_get_contents($exo_path . '/src/scss/_common.scss');
    $data = str_replace("@import '", "@import '" . $exo_path . '/src/scss/', $data);
    file_save_data($data, $destination, FILE_EXISTS_REPLACE);

    // Generate exo-theme.scss.
    $destination = $dirname . '/exo-theme.scss';
    $theme = \Drupal::service('plugin.manager.exo_theme')->getCurrentTheme();
    $exo_path = DRUPAL_ROOT . '/' . $theme->getScssPath() . '/exo-theme';
    $data = "@import '$exo_path';";
    file_save_data($data, $destination, FILE_EXISTS_REPLACE);

    $this->logger()->info(dt('eXo utilities generated at @destination', ['@destination' => $dirname]));
  }

}
