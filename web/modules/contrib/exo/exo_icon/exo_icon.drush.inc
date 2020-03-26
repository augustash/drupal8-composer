<?php

/**
 * @file
 * Contains Drush hooks.
 */

/**
 * Implements hook_drush_command().
 */
function exo_icon_drush_command() {
  $items = [];

  $items['exo-icon'] = [
    'description' => 'Generate SCSS mixins and variables for currently enabled eXo icon sets.',
    'arguments' => [
      'path' => 'The destination where the _exo_icon.scss mixin file should be created. Do not include a trailing slash.',
    ],
    'examples' => [
      'drush exo-icon "themes/my_theme/src/scss/base"' => 'Creates the SCSS mixin file and places it within SITE_ROOT/themes/my_theme/src/scss/base',
    ],
  ];

  return $items;
}

/**
 * Implements hook_drush_help().
 */
function exo_icon_drush_help($section) {
  switch ($section) {
    case 'drush:exo-icon':
      return dt('This command will generate SCSS mixins and variables for currently enabled eXo icon sets.');
  }
}

/**
 * Implements drush_hook_COMMAND().
 */
function drush_exo_icon($path = NULL) {

  // If no $name provided, abort.
  if (!$path) {
    drush_print(dt('Location path missing. See help using drush exo-icon --help.'));
    return;
  }

  $path = drush_get_context('DRUSH_DRUPAL_ROOT') . '/' . $path;
  if (!file_exists($path)) {
    drush_print(dt('Location directory not found. See help using drush exo-icon --help.'));
    return;
  }

  $fullpath = $path . '/_icons.scss';
  $exo_icon_repository = \Drupal::service('exo_icon.repository');

  $content = [];
  $content[] = '/**';
  $content[] = '* eXo icon mixins and variables.';
  $content[] = '*';
  $content[] = '* DO NOT MAKE MANUAL CHANGES TO THIS FILE';
  $content[] = '* Generate via `drush exo_icon ' . $path . '`.';
  $content[] = '*/' . "\n";
  $content[] = '@mixin icon($package: regular, $icon: rebel, $position: before) {';
  $content[] = '  $package: icon-#{$package};';
  $content[] = '  @if $position == both {';
  $content[] = '    $position: \'before, &:after\';';
  $content[] = '  }' . "\n";
  $content[] = '  &:#{$position} {';
  $content[] = '    font-family: \'#{$package}\' !important; /* stylelint-disable-line declaration-no-important */';
  $content[] = '    display: inline-block;';
  $content[] = '    speak: none;';
  $content[] = '    font-style: normal;';
  $content[] = '    font-weight: normal;';
  $content[] = '    font-variant: normal;';
  $content[] = '    text-transform: none;';
  $content[] = '    line-height: 1;';
  $content[] = '    -webkit-font-smoothing: antialiased; // sass-lint:disable-line no-vendor-prefixes';
  $content[] = '    -moz-osx-font-smoothing: grayscale; // sass-lint:disable-line no-vendor-prefixes';
  $content[] = '    content: "#{map-get($icons, #{$package}-#{$icon})}"; /* stylelint-disable-line string-quotes */';
  $content[] = '    @content;';
  $content[] = '  }';
  $content[] = '}' . "\n";

  $content[] = '$icons: (';
  foreach ($exo_icon_repository->getPackagesByStatus() as $package) {
    /* @var \Drupal\exo_icon\Entity\ExoIconPackage $package */
    foreach ($package->getInstances() as $icon) {
      $content[] = '  ' . $icon->getSelector() . ': \'' . $icon->getHex() . '\',';
    }
  }
  $content[] = '); /* stylelint-disable-line max-empty-lines */';

  $content[] = "\n";

  file_put_contents($fullpath, implode("\n", $content));

  // Notify user.
  $message = 'Successfully created the eXo SCSS file in: !path';

  $message = dt($message . '.', [
    '!path' => $path,
  ]);
  drush_print($message);
}
