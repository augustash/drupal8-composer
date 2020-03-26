<?php

namespace Drupal\exo_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ExoFormDemoForm.
 *
 * @package Drupal\exo_form\Form
 */
class ExoFormDemoMixForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'exo_form_example_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Fieldset'),
    ];
    $form['fieldset']['input'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Textfield'),
    ];

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
      $form['table_' . $theme] = [
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

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Display result.
    foreach ($form_state->getValues() as $key => $value) {
      if (is_array($value)) {
        foreach ($value as $i => $v) {
          \Drupal::messenger()->addMessage($key . ':' . $i . ': ' . $v);
        }
      }
      else {
        \Drupal::messenger()->addMessage($key . ': ' . $value);
      }
    }

  }

}
