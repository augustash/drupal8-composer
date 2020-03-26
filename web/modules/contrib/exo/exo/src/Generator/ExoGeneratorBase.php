<?php

namespace Drupal\exo\Generator;

use Drupal\Console\Core\Generator\Generator;
use Drupal\Console\Core\Generator\GeneratorInterface;

/**
 * Class ThemeGenerator.
 *
 * @package Drupal\Console\Generator
 */
abstract class ExoGeneratorBase extends Generator implements GeneratorInterface {

  /**
   * {@inheritdoc}
   */
  public function generate(array $parameters) {}

  /**
   * Find the relative file system path between two file system paths.
   *
   * @param string $from
   *   Path to start from.
   * @param string $to
   *   Path we want to end up in.
   * @param string $ps
   *   The directory separator.
   *
   * @return string
   *   Path leading from $from to $to
   */
  protected function getRelativePath($from, $to, $ps = DIRECTORY_SEPARATOR) {
    $arFrom = explode($ps, rtrim($from, $ps));
    $arTo = explode($ps, rtrim($to, $ps));
    while (count($arFrom) && count($arTo) && ($arFrom[0] == $arTo[0])) {
      array_shift($arFrom);
      array_shift($arTo);
    }
    $path = str_pad("", count($arFrom) * 3, '..' . $ps) . implode($ps, $arTo);
    if (substr($path, -1) !== '/') {
      $path .= '/';
    }
    return $path;
  }

  /**
   * Generate gulp tasks.
   *
   * @param array $parameters
   *   Generator parameters.
   */
  protected function generateGulp(array $parameters) {
    $module = $parameters['module'];
    $module_path = $this->extensionManager->getModule($module)->getPath();

    $this->renderFile(
      'module/exo-theme-provider.gulpfile.js.twig',
      $module_path . '/gulpfile.js',
      $parameters
    );
    $this->renderFile(
      'module/exo-theme-provider.package.json.twig',
      $module_path . '/package.json',
      $parameters
    );
    $this->renderFile(
      'module/exo-theme-provider.config.json.twig',
      $module_path . '/config.json',
      $parameters
    );
    $this->renderFile(
      'module/exo-theme-provider.config.README.md.twig',
      $module_path . '/README.md',
      $parameters
    );
    $this->renderFile(
      'module/exo-theme-provider.eslintrc.twig',
      $module_path . '/.eslintrc',
      $parameters
    );
    $this->renderFile(
      'module/exo-theme-provider.example.config.local.json.twig',
      $module_path . '/example.config.local.json',
      $parameters
    );
    $this->renderFile(
      'module/exo-theme-provider.sass-lint.yml.twig',
      $module_path . '/.sass-lint.yml',
      $parameters
    );
    $this->renderFile(
      'module/exo-theme-provider.tsconfig.json.twig',
      $module_path . '/tsconfig.json',
      $parameters
    );
  }

}
