<?php

namespace Drupal\exo_aos\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\exo_aos\ExoAosGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ExoAosDemoController.
 */
class ExoAosDemoController extends ControllerBase {

  /**
   * The eXo modal generator service.
   *
   * @var \Drupal\exo_modal\ExoModalGeneratorInterface
   */
  protected $exoAosGenerator;

  /**
   * Constructs a new ExoModalDemoController object.
   */
  public function __construct(ExoAosGeneratorInterface $exo_aos_generator) {
    $this->exoAosGenerator = $exo_aos_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('exo_aos.generator')
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
    $build['#attached']['library'][] = 'exo_aos/demo';

    // The exo_aos element can be used to build AOS.
    $build['element'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('As Element'),
    ];
    for ($i = 1; $i < 11; $i++) {
      $build['element'][$i] = [
        '#type' => 'exo_aos',
        '#exo_aos_settings' => [
          'animation' => 'zoom-in-left',
        ],
        '#markup' => '<div class="exo-aos-demo-element">' . $i . '</div>',
      ];
    }
    // The exo_aos wrapper can be used to build AOS.
    $build['wrapper'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('As Theme Wrapper'),
    ];
    for ($i = 1; $i < 11; $i++) {
      $animation = $i % 2 == 0 ? 'fade-left' : 'fade-right';
      $build['wrapper'][$i] = [
        '#type' => 'markup',
        '#markup' => '<div class="exo-aos-demo-element">' . $i . '</div>',
        '#theme_wrappers' => ['exo_aos'],
        '#exo_aos_settings' => [
          'animation' => $animation,
          'mirror' => TRUE,
        ],
      ];
    }

    // The exo_aos generator can be used to build AOS.
    $build['generator'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Using Generator'),
      '#attributes' => ['class' => ['exo-aos-demo-wrapper', 'cell-2']],
    ];
    for ($i = 1; $i < 11; $i++) {
      $animation = $i % 2 == 0 ? 'slide-left' : 'slide-right';
      $build['generator'][$i] = [
        '#type' => 'container',
      ];
      $aos = $this->exoAosGenerator->generate();
      $aos->setAnimation($animation);
      $aos->applyTo($build['generator'][$i]);

      $animation = $i % 2 == 0 ? 'zoom-in-left' : 'zoom-in-right';
      $build['generator'][$i]['inner'] = [
        '#type' => 'container',
        '#markup' => '<div class="exo-aos-demo-element">' . $i . '</div>',
        '#prefix' => '<div class="exo-aos-demo-element">',
        '#suffix' => '</div>',
      ];
      $aos = $this->exoAosGenerator->generate();
      $aos->setAnimation($animation);
      $aos->setOffset(300);
      $aos->setMirror();
      $aos->applyTo($build['generator'][$i]['inner']);
    }

    // Add some spacing.
    $build['top'] = [
      '#markup' => '<div>Scroll Down</div>',
      '#weight' => -1000,
    ];
    $build['bottom']['#markup'] = '';
    for ($i = 0; $i < 80; $i++) {
      $build['top']['#markup'] .= '<br>';
      $build['bottom']['#markup'] .= '<br>';
    }
    $build['bottom']['#markup'] .= '<div>Scroll Up</div>';

    return $build;
  }

}
