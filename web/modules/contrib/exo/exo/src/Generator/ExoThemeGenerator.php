<?php

namespace Drupal\exo\Generator;

use Drupal\Console\Extension\Manager;
use Drupal\exo\ExoThemeProviderPluginManagerInterface;
use Drupal\Console\Core\Utils\TranslatorManagerInterface;
use Drupal\Console\Core\Utils\StringConverter;
use Drupal\Console\Core\Utils\TwigRenderer;

/**
 * Class ExoThemeGenerator.
 *
 * @package Drupal\Console\Generator
 */
class ExoThemeGenerator extends ExoGeneratorThemeBase {

  /**
   * Drupal\Console\Extension\Manager definition.
   *
   * @var \Drupal\Console\Extension\Manager
   */
  protected $extensionManager;

  /**
   * Drupal\exo\ExoThemeProviderPluginManagerInterface definition.
   *
   * @var \Drupal\exo\ExoThemeProviderPluginManagerInterface
   */
  protected $exoThemeProviderManager;

  /**
   * Drupal\Console\Core\Utils\TranslatorManagerInterface definition.
   *
   * @var \Drupal\Console\Core\Utils\TranslatorManagerInterface
   */
  protected $translator;

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
   * @param Drupal\exo\ExoThemeProviderPluginManagerInterface $exoThemeProviderManager
   *   The eXo theme provider manager.
   * @param Drupal\Console\Core\Utils\TranslatorManagerInterface $translator
   *   The translator manager.
   * @param Drupal\Console\Core\Utils\StringConverter $stringConverter
   *   The string converter.
   */
  public function __construct(
    Manager $extensionManager,
    ExoThemeProviderPluginManagerInterface $exoThemeProviderManager,
    TranslatorManagerInterface $translator,
    StringConverter $stringConverter
  ) {
    $this->extensionManager = $extensionManager;
    $this->exoThemeProviderManager = $exoThemeProviderManager;
    $this->translator = $translator;
    $this->stringConverter = $stringConverter;
  }

  /**
   * {@inheritdoc}
   */
  public function generate(array $parameters) {
    $module = $parameters['module'];
    $class_name = $parameters['class_name'];
    $plugin_id = $parameters['plugin_id'];
    $plugin_id_camel = $this->stringConverter->underscoreToCamelCase($plugin_id);
    $plugin_id_camel_upper = $this->stringConverter->anyCaseToUcFirst($plugin_id_camel);

    $exo_path = substr($this->extensionManager->getModule('exo')->getPath(), 0, -4);
    $module_path = $this->extensionManager->getModule($module)->getPath();
    $plugin_path = $module_path . '/src/ExoTheme/' . $plugin_id_camel_upper;
    $plugin_asset_path = $plugin_path . '/scss';

    // Additional parameters.
    $parameters['module_relative_path'] = $this->getRelativePath($module_path, $exo_path);
    $parameters['plugin_id_camel'] = $plugin_id_camel_upper;
    $parameters['colors'] = [];
    foreach (['base', 'offset', 'primary', 'secondary'] as $key) {
      $parameters['colors'][$key] = $parameters[$key . '_color'];
    }

    // Prepare includes.
    $parameters['includes'] = $this->getThemeIncludes();

    // Initial files are stored in generator module.
    $this->addSkeletonDir(__DIR__ . '/../../templates/');

    // Generate plugin.
    $this->renderFile(
      'module/src/ExoTheme/exo-theme.php.twig',
      $plugin_path . '/' . $class_name . '.php',
      $parameters
    );

    // Generate SCSS variables file.
    $this->renderFile(
      'module/src/ExoTheme/_exo-theme.scss.twig',
      $plugin_asset_path . '/_exo-theme.scss',
      $parameters
    );

    // Generate gulp and package.
    if ($parameters['gulp'] == 'yes') {
      $this->generateGulp($parameters);
    }

    foreach ($this->exoThemeProviderManager->getAllDefinitions() as $provider_plugin_id => $definition) {
      // Generate a new renderer as we need to crawl a different directory.
      $this->renderer = new TwigRenderer($this->translator, $this->stringConverter);
      $this->addSkeletonDir($this->drupalFinder->getDrupalRoot() . '/' . $definition['providerPath'] . '/src/ExoThemeProvider/');
      $parameters['theme_relative_path'] = $this->getRelativePath($plugin_asset_path, $definition['providerPath']);

      $this->renderFile(
        $definition['template'],
        $plugin_asset_path . '/' . str_replace('_', '-', $definition['id']) . '.scss',
        $parameters
      );
    }
  }

}
