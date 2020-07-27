<?php

namespace Drupal\exo_link_menu\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_icon\Entity\ExoIconPackage;

/**
 * Class ExoLinkMenuConfigForm.
 *
 * @package Drupal\exo_link_menu\Form
 */
class ExoLinkMenuConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'exo_link_menu.config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'exo_link_menu_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('exo_link_menu.config');
    $form['packages'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Icon Packages'),
      '#description' => $this->t('The icon packages that should be made available to menu items. If no packages are selected, all will be made available.'),
      '#options' => ExoIconPackage::getLabels(),
      '#default_value' => $config->get('packages'),
    ];

    $form['target'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow target selection'),
      '#description' => $this->t('If selected, an "open in new window" checkbox will be made available.'),
      '#default_value' => $config->get('target'),
    ];

    $form['class'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow adding custom CSS classes'),
      '#description' => $this->t('If selected, a textfield will be provided that will allow adding in custom CSS classes.'),
      '#default_value' => $config->get('class'),
    ];

    $form['spacers'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow spacer links'),
      '#description' => $this->t('If enabled, menu links can be created as spacers.'),
      '#default_value' => $config->get('spacers'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('exo_link_menu.config')
      ->set('packages', array_filter($form_state->getValue('packages')))
      ->set('target', !empty($form_state->getValue('target')))
      ->set('class', !empty($form_state->getValue('class')))
      ->set('spacers', !empty($form_state->getValue('spacers')))
      ->save();

    drupal_flush_all_caches();
  }

}
