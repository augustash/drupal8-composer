<?php

namespace Drupal\exo_icon_local_task\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ExoIconLocalTaskSettingsForm.
 */
class ExoIconLocalTaskSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'exo_icon_local_task.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'exo_icon_local_task_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('exo_icon_local_task.settings');
    $form['icon_only'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Icon Only'),
      '#description' => $this->t('When an local task has an icon, only show the icon and hide the text.'),
      '#default_value' => $config->get('icon_only'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('exo_icon_local_task.settings')
      ->set('icon_only', $form_state->getValue('icon_only'))
      ->save();
  }

}
