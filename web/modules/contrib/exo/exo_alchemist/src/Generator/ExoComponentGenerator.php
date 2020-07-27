<?php

namespace Drupal\exo_alchemist\Generator;

use Drupal\Console\Core\Generator\Generator;
use Drupal\Console\Core\Generator\GeneratorInterface;
use Drupal\Console\Extension\Manager;

/**
 * Class ExoComponentGenerator.
 *
 * @package Drupal\Console\Generator
 */
class ExoComponentGenerator extends Generator implements GeneratorInterface {

  /**
   * Extension Manager.
   *
   * @var \Drupal\Console\Extension\Manager
   */
  protected $extensionManager;

  /**
   * ExoComponentGenerator constructor.
   *
   * @param \Drupal\Console\Extension\Manager $extensionManager
   *   Extension manager.
   */
  public function __construct(Manager $extensionManager) {
    $this->extensionManager = $extensionManager;
  }

  /**
   * {@inheritdoc}
   */
  public function generate(array $parameters) {
    $theme = $parameters['theme'];
    $theme_path = $this->extensionManager->getTheme($theme)->getPath();
    $id = $parameters['id'];

    $parameters['id_dashed'] = str_replace('_', '-', $id);

    $this->renderer->addSkeletonDir(__DIR__ . '/../../console/templates');

    $this->renderFile(
      'components/component.yml.twig',
      $theme_path . '/components/' . $id . '/' . $id . '.yml',
      $parameters
    );

    $this->renderFile(
      'components/component.html.twig.twig',
      $theme_path . '/components/' . $id . '/' . $id . '.html.twig',
      $parameters
    );

    $this->renderFile(
      'components/component.css.twig',
      $theme_path . '/components/' . $id . '/' . $id . '.css',
      $parameters
    );

    $this->renderFile(
      'components/src/styles/component.scss.twig',
      $theme_path . '/components/' . $id . '/src/styles/' . $id . '.scss',
      $parameters
    );
  }

}
