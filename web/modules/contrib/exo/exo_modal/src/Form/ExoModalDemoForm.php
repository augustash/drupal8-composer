<?php

namespace Drupal\exo_modal\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ExoModalDemoForm.
 */
class ExoModalDemoForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'exo_modal_demo_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['group1'] = [
      '#type' => 'exo_modal',
      '#title' => $this->t('eXo Modal'),
      '#trigger_as_button' => TRUE,
      '#tree' => TRUE,
      '#modal_settings' => [
        'group' => 'demo',
      ],
    ];

    $form['group1']['text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Text'),
    ];

    $form['group1']['icon'] = [
      '#type' => 'exo_icon',
      '#title' => $this->t('icon'),
    ];

    $form['fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Within fieldset'),
      '#tree' => TRUE,
    ];

    $form['fieldset']['group1'] = [
      '#type' => 'exo_modal',
      '#title' => $this->t('eXo Modal within Fieldset'),
      '#description' => $this->t('This is an eXo Modal within Fieldset'),
      '#trigger_as_button' => TRUE,
      '#modal_settings' => [
        'group' => 'demo',
      ],
    ];

    $form['fieldset']['group1']['text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Text'),
    ];

    $form['fieldset']['group1']['icon'] = [
      '#type' => 'exo_icon',
      '#title' => $this->t('icon'),
    ];

    $form['submit'] = [
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
      $this->outputValue($key, $value);
    }
  }

  /**
   * Output a value.
   */
  protected function outputValue($key, $value) {
    if (is_array($value)) {
      foreach ($value as $i => $val) {
        $this->outputValue($key . ':' . $i, $val);
      }
    }
    else {
      \Drupal::messenger()->addMessage($key . ': ' . $value);
    }
  }

}
