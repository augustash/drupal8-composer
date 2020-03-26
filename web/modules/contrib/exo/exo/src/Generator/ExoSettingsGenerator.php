<?php

namespace Drupal\exo\Generator;

use Drupal\Console\Extension\Manager;
use Drupal\Console\Core\Utils\TranslatorManagerInterface;
use Drupal\Console\Core\Utils\StringConverter;

/**
 * Class ExoSettingsGenerator.
 *
 * @package Drupal\Console\Generator
 */
class ExoSettingsGenerator extends ExoGeneratorBase {

  /**
   * Drupal\Console\Extension\Manager definition.
   *
   * @var \Drupal\Console\Extension\Manager
   */
  protected $extensionManager;

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
   * @param Drupal\Console\Core\Utils\TranslatorManagerInterface $translator
   *   The translator manager.
   * @param Drupal\Console\Core\Utils\StringConverter $stringConverter
   *   The string converter.
   */
  public function __construct(
    Manager $extensionManager,
    TranslatorManagerInterface $translator,
    StringConverter $stringConverter
  ) {
    $this->extensionManager = $extensionManager;
    $this->translator = $translator;
    $this->stringConverter = $stringConverter;
  }

  /**
   * {@inheritdoc}
   */
  public function generate(array $parameters) {
    $module = $parameters['module'];
    $label = $parameters['label'];
    $id = $parameters['id'];
    $class = $parameters['class'];
    $module_path = $this->extensionManager->getModule($module)->getPath();
    $exo_path = substr($this->extensionManager->getModule('exo')->getPath(), 0, -4);

    $module_services_yml = $this->extensionManager->getModule($module)->getPath() . '/' . $module . '.services.yml';
    $parameters['class_path'] = sprintf('Drupal\%s\%s', $module, $class);
    $parameters['services_file_exists'] = file_exists($module_services_yml);

    // Initial files are stored in generator module.
    $this->addSkeletonDir(__DIR__ . '/../../templates/');

    // Generate service.
    $this->renderFile(
      'module/exo-settings.services.yml.twig',
      $module_path . '/' . $module . '.services.yml',
      $parameters,
      FILE_APPEND
    );

    // Generate config install.
    $this->renderFile(
      'module/src/ExoSettings.php.twig',
      $module_path . '/src/' . $class . '.php',
      $parameters
    );

    // Generate config install.
    $this->renderFile(
      'module/src/Form/ExoSettingsForm.php.twig',
      $module_path . '/src/Form/' . $class . 'Form.php',
      $parameters
    );

    // Generate config install.
    $this->renderFile(
      'module/config/install/exo-settings.settings.yml.twig',
      $module_path . '/config/install/' . $id . '.yml',
      $parameters
    );

    // Generate path.
    $this->renderFile(
      'module/exo_settings.links.menu.yml.twig',
      $module_path . '/' . $module . '.links.menu.yml',
      $parameters,
      FILE_APPEND
    );
    $this->renderFile(
      'module/exo_settings.links.task.yml.twig',
      $module_path . '/' . $module . '.links.task.yml',
      $parameters,
      FILE_APPEND
    );
    $this->renderFile(
      'module/exo_settings.routing.yml.twig',
      $module_path . '/' . $module . '.routing.yml',
      $parameters,
      FILE_APPEND
    );

    // Permissions.
    $this->renderFile(
      'module/exo_settings.permissions.yml.twig',
      $module_path . '/' . $module . '.permissions.yml',
      $parameters,
      FILE_APPEND
    );
  }

}
