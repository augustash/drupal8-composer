<?php

/**
 * @file
 * Contains Drush hooks.
 */

/**
 * Implements hook_drush_command().
 */
function exo_drush_command() {
  $items = [];

  $items['exo-theme'] = [
    'description' => 'Generate SCSS mixins and variables for currently enabled eXo icon sets.',
    'core' => ['8+'],
    'arguments' => [
      'path' => 'The destination where the _exo-theme.scss mixin file should be created. Do not include a trailing slash.',
    ],
    'options' => [],
    'examples' => [
      'drush exo-theme "themes/my_theme/src/scss/base"' => 'Creates the SCSS mixin file and places it within SITE_ROOT/themes/my_theme/src/scss/base',
    ],
  ];
  $items['exo-scss'] = [
    'description' => dt('Generate eXo SCSS utilities.'),
    'aliases' => ['exo-scss'],
  ];

  return $items;
}

/**
 * Implements hook_drush_help().
 */
function exo_drush_help($section) {
  switch ($section) {
    case 'drush:exo-theme':
      return dt('This command will generate SCSS mixins and variables for currently enabled eXo theme.');
  }
}

/**
 * Implements drush_hook_COMMAND().
 */
function drush_exo_theme($path = NULL) {

  // If no $name provided, abort.
  if (!$path) {
    drush_print(dt('Location path missing. See help using drush exo-theme --help.'));
    return;
  }

  $path = drush_get_context('DRUSH_DRUPAL_ROOT') . '/' . $path;
  if (!file_exists($path)) {
    drush_print(dt('Location directory not found. See help using drush exo-theme --help.'));
    return;
  }

  $fullpath = $path . '/_exo-theme.scss';
  $theme = \Drupal::service('plugin.manager.exo_theme')->getCurrentTheme();

  $content[] = '$exo-theme-colors: (';
  foreach ($theme->getColors() as $key => $color) {
    $content[] = '  ' . $key . ': ' . $color . ',';
  }
  $content[] = ');';

  $content[] = "\n";

  file_put_contents($fullpath, implode("\n", $content));

  // Notify user.
  $message = 'Successfully created the eXo theme SCSS file in: !path';

  $message = dt($message . '.', [
    '!path' => $path,
  ]);
  drush_print($message);
}

/**
 * A command callback. This is faster than 3 separate bootstraps.
 */
function drush_exo_scss() {
  $dirname = 'public://exo';
  \Drupal::service('file_system')->prepareDirectory($dirname, FILE_CREATE_DIRECTORY);

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

  drush_print(dt('eXo utilities generated at @destination', ['@destination' => $dirname]));
}
