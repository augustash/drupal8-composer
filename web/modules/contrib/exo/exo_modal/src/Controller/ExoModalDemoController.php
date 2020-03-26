<?php

namespace Drupal\exo_modal\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\exo_modal\ExoModalGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ExoModalDemoController.
 */
class ExoModalDemoController extends ControllerBase {

  /**
   * The eXo modal generator service.
   *
   * @var \Drupal\exo_modal\ExoModalGeneratorInterface
   */
  protected $exoModalGenerator;

  /**
   * Constructs a new ExoModalDemoController object.
   */
  public function __construct(ExoModalGeneratorInterface $exo_modal_generator) {
    $this->exoModalGenerator = $exo_modal_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('exo_modal.generator')
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

    $build[] = $this->exoModalGenerator->generate('exo_modal_demo_1', [
      'trigger' => [
        'text' => $this->t('Default Inline Modal'),
        'icon' => 'thin-expand',
      ],
      'modal' => [
        'padding' => '10',
        'icon' => 'thin-user',
      ],
    ], [
      '#markup' => 'Default Inline Modal Content',
    ])->addTriggerClass('button')->toRenderable();

    $build[] = $this->exoModalGenerator->generate('exo_modal_demo_preset', [
      'exo_preset' => 'aside_top',
      'trigger' => [
        'text' => $this->t('Preset'),
        'icon' => 'thin-expand',
      ],
      'modal' => [
        'title' => 'Preset',
        'padding' => '10',
        'icon' => 'thin-user',
      ],
    ], [
      '#markup' => 'Default Preset Content',
    ])->addTriggerClass('button')->toRenderable();

    $build[] = $this->exoModalGenerator->generate('exo_modal_demo_2', [
      'trigger' => [
        'text' => 'Full Screen Modal',
        'icon' => 'thin-expand',
      ],
      'modal' => [
        'title' => $this->t('Full Screen'),
        'subtitle' => $this->t('This is a subtitle'),
        'icon' => 'thin-user',
        'openFullscreen' => TRUE,
        'padding' => '30px',
      ],
    ], [
      '#markup' => 'Superman!',
    ])->addTriggerClass('button')->toRenderable();

    $nested = $this->exoModalGenerator->generate('exo_modal_demo_nested', [
      'trigger' => [
        'text' => $this->t('A Nested Modal'),
        'icon' => 'thin-expand',
      ],
      'modal' => [
        'padding' => '10',
      ],
    ], [
      '#markup' => 'Default Inline Modal Content',
    ])->addTriggerClass('button');

    $build[] = $this->exoModalGenerator->generate('exo_modal_demo_3', [
      'trigger' => [
        'text' => $this->t('Nested'),
        'icon' => 'thin-expand',
      ],
      'modal' => [
        'padding' => '10',
      ],
    ], $nested->toRenderable())->addTriggerClass('button')->toRenderable();

    $build[] = $this->exoModalGenerator->generate('exo_modal_demo_4', [
      'exo_preset' => 'aside_right',
      'theme' => 'primary',
      'theme_content' => 'white',
      'trigger' => [
        'text' => $this->t('Auto Open'),
        'icon' => 'thin-expand',
      ],
      'modal' => [
        'title' => $this->t('Automatically Open'),
        'subtitle' => $this->t('Automatically Open'),
        'icon' => 'thin-expand',
        'autoOpen' => TRUE,
        'padding' => 30,
        'toolbarRegion' => 'default_top',
        'timeout' => '5000',
        'timeoutProgressbar' => TRUE,
      ],
    ], [
      '#markup' => 'Auto Open',
    ])->addTriggerClass('button')->toRenderable();

    $build[] = $this->exoModalGenerator->generate('exo_modal_demo_5', [
      'trigger' => [
        'text' => $this->t('Chained'),
        'icon' => 'thin-expand',
      ],
      'modal' => [
        'group' => 'chained',
        'loop' => TRUE,
      ],
    ], [
      '#markup' => 'Chained 1',
    ])->addTriggerClass('button')->toRenderable();

    $build[] = $this->exoModalGenerator->generate('exo_modal_demo_6', [
      'exo_preset' => 'aside_right',
      'modal' => [
        'group' => 'chained',
        'weight' => 2,
        'loop' => TRUE,
      ],
    ], ['#markup' => 'Chained 2'])->addTriggerClass('button')->toRenderableModal();

    $build[] = $this->exoModalGenerator->generate('exo_modal_demo_7', [
      'exo_preset' => 'aside_left',
      'modal' => [
        'group' => 'chained',
        'weight' => 3,
        'width' => 300,
        'loop' => TRUE,
      ],
    ], ['#markup' => 'Chained 3'])->addTriggerClass('button')->toRenderableModal();

    return $build;
  }

}
