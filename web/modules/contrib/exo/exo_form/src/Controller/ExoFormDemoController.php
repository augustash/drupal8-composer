<?php

namespace Drupal\exo_form\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Render\Element;

/**
 * Class ExoFormDemoController.
 */
class ExoFormDemoController extends ControllerBase {

  /**
   * Democrossover.
   *
   * @return string
   *   Return Hello string.
   */
  public function demoMix() {
    $build = [];

    $form_state = new FormState();
    $form = $this->formBuilder()->buildForm('Drupal\exo_form\Form\ExoFormDemoMixForm', $form_state);
    $build['form'] = $form;

    $build['content'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Non-Form'),
    ];
    foreach (Element::children($form) as $key) {
      if (!empty($form[$key]['#type']) && !in_array($form[$key]['#type'], [
        'table',
        'hidden',
        'token',
      ])) {
        $build['content'][$key] = $form[$key];
      }
    }

    foreach ([
      'default',
      'inverse',
      'primary',
      'secondary',
      'white',
      'black',
      'success',
      'warning',
      'alert',
    ] as $theme) {
      $build['content']['table_' . $theme] = [
        '#type' => 'table',
        '#header' => ['Table (#exo_theme = ' . $theme . ')', 'Header'],
        '#rows' => [
          ['row', 'result'],
          ['row', 'result'],
          ['row', 'result'],
        ],
        '#exo_theme' => $theme,
      ];
    }

    return $build;
  }

}
