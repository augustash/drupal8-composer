<?php

namespace Drupal\exo_menu\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\exo_menu\ExoMenuGeneratorInterface;

/**
 * Class ExoMenuDemoController.
 */
class ExoMenuDemoController extends ControllerBase {

  /**
   * The eXo menu generator service.
   *
   * @var \Drupal\exo_menu\ExoMenuGeneratorInterface
   */
  protected $exoMenuGenerator;

  /**
   * Constructs a new ExoMenuDemoController object.
   */
  public function __construct(ExoMenuGeneratorInterface $exo_menu_generator) {
    $this->exoMenuGenerator = $exo_menu_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('exo_menu.generator')
    );
  }

  /**
   * Demo.
   *
   * @return string
   *   Return Hello string.
   */
  public function demo() {
    $build = [];

    $build['simple'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Simple'),
    ] + $this->demoSimple();

    $build['slide_vertical'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Slide: Vertical'),
    ] + $this->demoStyleThemes('slide_vertical');

    $build['dropdown_horizontal'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Dropdown: Horizontal'),
    ] + $this->demoStyleThemes('dropdown_horizontal', ['level' => 2, 'expandable' => TRUE]);

    $build['dropdown_vertical'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Dropdown: Vertical'),
    ] + $this->demoStyleThemes('dropdown_vertical', ['level' => 2]);

    $build['mega_vertical'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Mega: Vertical'),
    ] + $this->demoStyleThemes('mega_vertical', ['level' => 2]);

    return $build;
  }

  /**
   * Demo menus.
   */
  protected function demoSimple() {
    $build = [];
    $build[] = $this->exoMenuGenerator->generate('simple', 'tree', [
      'admin',
    ], ['depth' => 2])->toRenderable();
    return $build;
  }

  /**
   * Demo menus.
   */
  protected function demoStyleThemes($style, $settings = [], $menus = ['admin']) {
    $build = [];

    $themes = ['' => 'None'] + exo_theme_options();
    foreach ($themes as $id => $label) {
      $build[$id] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Theme: @theme', ['@theme' => $label]),
      ];
      $build[$id][] = $this->exoMenuGenerator->generate($style . '_' . $id, $style, $menus, $settings + ['exo_preset' => 'regular_icons', 'theme' => $id])->toRenderable();
    }

    return $build;
  }

}
