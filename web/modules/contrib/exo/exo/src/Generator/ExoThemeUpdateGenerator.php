<?php

namespace Drupal\exo\Generator;

use Drupal\Console\Extension\Manager;
use Drupal\Console\Core\Utils\TranslatorManagerInterface;
use Drupal\Console\Core\Utils\StringConverter;
use Drupal\exo\ExoThemeProviderPluginManagerInterface;
use Drupal\exo\ExoThemePluginManagerInterface;
use Drupal\Console\Core\Utils\TwigRenderer;

/**
 * Class ExoThemeUpdateGenerator.
 *
 * @package Drupal\Console\Generator
 */
class ExoThemeUpdateGenerator extends ExoGeneratorThemeBase {

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
   * Drupal\exo\ExoThemePluginManagerInterface definition.
   *
   * @var \Drupal\exo\ExoThemePluginManagerInterface
   */
  protected $exoThemeManager;

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
   * @param Drupal\exo\ExoThemePluginManagerInterface $exoThemeManager
   *   The eXo theme provider manager.
   * @param Drupal\Console\Core\Utils\TranslatorManagerInterface $translator
   *   The translator manager.
   * @param Drupal\Console\Core\Utils\StringConverter $stringConverter
   *   The string converter.
   */
  public function __construct(
    Manager $extensionManager,
    ExoThemeProviderPluginManagerInterface $exoThemeProviderManager,
    ExoThemePluginManagerInterface $exoThemeManager,
    TranslatorManagerInterface $translator,
    StringConverter $stringConverter
  ) {
    $this->extensionManager = $extensionManager;
    $this->exoThemeProviderManager = $exoThemeProviderManager;
    $this->exoThemeManager = $exoThemeManager;
    $this->translator = $translator;
    $this->stringConverter = $stringConverter;
  }

  /**
   * {@inheritdoc}
   */
  public function generate(array $parameters) {
    $theme = $parameters['theme'];
    $theme_definitions = $this->exoThemeManager->getDefinitions();
    $theme_definition = isset($theme_definitions[$theme]) ? $theme_definitions[$theme] : NULL;
    if (!$theme_definition) {
      throw new \Exception(
        sprintf(
          $this->translator->trans(
            'commands.exo.common.messages.no-theme'
          ),
          $theme
        )
      );
    }
    $plugin_id = $theme_definition['id'];
    $plugin_id_camel = $this->stringConverter->underscoreToCamelCase($plugin_id);
    $plugin_id_camel_upper = $this->stringConverter->anyCaseToUcFirst($plugin_id_camel);

    $plugin_path = $theme_definition['providerPath'] . '/src/ExoTheme/' . $plugin_id_camel_upper;
    $plugin_asset_path = $plugin_path . '/scss';

    // Prepare includes.
    $parameters['includes'] = $this->getThemeIncludes();

    $updated = 0;
    foreach ($this->exoThemeProviderManager->getAllDefinitions() as $provider_plugin_id => $provider_definition) {
      $parameters['theme_relative_path'] = $this->getRelativePath($plugin_asset_path, $provider_definition['providerPath']);
      $filepath = $plugin_asset_path . '/' . str_replace('_', '-', $provider_definition['id']) . '.scss';
      if (!file_exists($this->drupalFinder->getDrupalRoot() . '/' . $filepath)) {
        // Generate a new renderer as we need to crawl a different directory.
        $this->renderer = new TwigRenderer($this->translator, $this->stringConverter);
        $this->addSkeletonDir(DRUPAL_ROOT . '/' . $provider_definition['providerPath'] . '/src/ExoThemeProvider/');

        $this->renderFile(
          $provider_definition['template'],
          $filepath,
          $parameters
        );
        $updated++;
      }
    }

    return $updated;
  }

}
