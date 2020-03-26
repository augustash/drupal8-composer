<?php

namespace Drupal\exo\Generator;

use Drupal\Console\Extension\Manager;
use Drupal\Console\Core\Utils\StringConverter;

/**
 * Class ExoThemeProviderGenerator.
 *
 * @package Drupal\Console\Generator
 */
class ExoThemeProviderGenerator extends ExoGeneratorBase {

  /**
   * Drupal\Console\Extension\Manager definition.
   *
   * @var \Drupal\Console\Extension\Manager
   */
  protected $extensionManager;

  /**
   * Drupal\Console\Core\Utils\StringConverter definition.
   *
   * @var \Drupal\Console\Core\Utils\StringConverter
   */
  protected $stringConverter;

  /**
   * PluginFieldWidgetGenerator constructor.
   *
   * @param Drupal\Console\Extension\Manager $extensionManager
   *   The extention manager.
   * @param Drupal\Console\Core\Utils\StringConverter $stringConverter
   *   The string converter.
   */
  public function __construct(
    Manager $extensionManager,
    StringConverter $stringConverter
  ) {
    $this->extensionManager = $extensionManager;
    $this->stringConverter = $stringConverter;
  }

  /**
   * {@inheritdoc}
   */
  public function generate(array $parameters) {
    $module = $parameters['module'];
    $dashed_module = str_replace('_', '-', $module);
    $class_name = $parameters['class_name'];
    $plugin_id = $parameters['plugin_id'];
    $plugin_id_camel = $this->stringConverter->underscoreToCamelCase($plugin_id);
    $plugin_id_camel_upper = $this->stringConverter->anyCaseToUcFirst($plugin_id_camel);
    $dashed_plugin_id = str_replace('_', '-', $plugin_id);

    $exo_path = substr($this->extensionManager->getModule('exo')->getPath(), 0, -4);
    $module_path = $this->extensionManager->getModule($module)->getPath();
    $plugin_path = $module_path . '/src/ExoThemeProvider/' . $plugin_id_camel_upper;
    $plugin_asset_path = $plugin_path . '/scss';

    // Additional parameters.
    $parameters['module_relative_path'] = $this->getRelativePath($module_path, $exo_path);
    $parameters['scss_relative_path'] = $this->getRelativePath($plugin_asset_path, $exo_path);
    $parameters['plugin_id_camel'] = $plugin_id_camel_upper;
    // This parameter is used by ThemeGenerator.
    $parameters['theme_relative_path_twig_placeholder'] = '{{ theme_relative_path }}';
    $parameters['includes_twig_placeholder'] = '{{ includes }}';

    // Initial files are stored in generator module.
    $this->addSkeletonDir(__DIR__ . '/../../templates/');

    // Generate plugin.
    $this->renderFile(
      'module/src/ExoThemeProvider/exo-theme-provider.php.twig',
      $plugin_path . '/' . $class_name . '.php',
      $parameters
    );

    // Generate twig template for plugin.
    $this->renderFile(
      'module/src/ExoThemeProvider/exo-theme-provider.scss.twig.twig',
      $plugin_path . '/ExoTheme.scss.twig',
      $parameters
    );

    // Generate library.
    $this->renderFile(
      'module/exo-theme-provider.libraries.yml.twig',
      $module_path . '/' . $module . '.libraries.yml',
      $parameters,
      FILE_APPEND
    );

    // Generate SCSS.
    $this->renderFile(
      'module/src/ExoThemeProvider/scss/exo-theme-provider.scss.twig',
      $plugin_asset_path . '/' . $dashed_module . '.scss',
      $parameters
    );
    $this->renderFile(
      'module/src/ExoThemeProvider/scss/base/_exo-theme-provider.variables.scss.twig',
      $plugin_path . '/scss/base/_variables.scss',
      $parameters
    );
    $this->renderFile(
      'module/src/ExoThemeProvider/scss/base/_exo-theme-provider.global.scss.twig',
      $plugin_path . '/scss/base/_global.scss',
      $parameters
    );
    $this->renderFile(
      'module/src/ExoThemeProvider/scss/exo-theme-provider.theme.scss.twig',
      $plugin_asset_path . '/' . $dashed_module . '.theme.scss',
      $parameters
    );
    $this->renderFile(
      'module/src/ExoThemeProvider/scss/theme/_exo-theme-provider.variables.scss.twig',
      $plugin_path . '/scss/theme/_variables.scss',
      $parameters
    );
    $this->renderFile(
      'module/src/ExoThemeProvider/scss/theme/_exo-theme-provider.base.scss.twig',
      $plugin_path . '/scss/theme/_base.scss',
      $parameters
    );

    // Generate gulp and package.
    if ($parameters['gulp'] == 'yes') {
      $this->generateGulp($parameters);
    }
  }

}
