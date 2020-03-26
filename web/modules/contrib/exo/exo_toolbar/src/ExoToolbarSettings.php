<?php

namespace Drupal\exo_toolbar;

use Drupal\exo\ExoSettingsBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Component\Utility\NestedArray;

/**
 * Class UxMenuOptions.
 *
 * @package Drupal\exo_toolbar
 */
class ExoToolbarSettings extends ExoSettingsBase {

  /**
   * The eXo toolbar repository.
   *
   * @var \Drupal\exo_toolbar\ExoToolbarRepositoryInterface
   */
  protected $exoToolbarRepository;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $options_factory, ExoToolbarRepositoryInterface $exo_toolbar_repository) {
    parent::__construct($options_factory);
    $this->exoToolbarRepository = $exo_toolbar_repository;
  }

  /**
   * {@inheritdoc}
   */
  public function getModuleId() {
    return 'exo_toolbar';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $enabled = $form_state->getValue(['enabled']) ? $form_state->getValue(['enabled']) : $this->getSetting('enabled');
    $enabled = array_filter($enabled);
    $region_wrapper_id = 'exo-toolbar-settings-regions';

    $region_settings = $this->getSetting('regions');
    foreach (array_filter($enabled) as $region_id) {
      if (!isset($region_settings[$region_id])) {
        $region_settings[$region_id] = ['id' => $region_id];
      }
    }

    $form['debug'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable debug mode'),
      '#default_value' => $this->getSetting('debug'),
    ];

    $form['enabled'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Regions'),
      '#options' => $this->exoToolbarRepository->getRegionLabels(),
      '#default_value' => $enabled,
      '#ajax' => [
        'callback' => [get_class($this), 'ajaxEnable'],
        'wrapper' => $region_wrapper_id,
      ],
    ];

    $form['regions'] = [
      '#type' => 'container',
      '#id' => $region_wrapper_id,
    ];

    $form['regions']['region_tabs'] = [
      '#type' => 'vertical_tabs',
      '#title' => $this->t('Region Settings'),
      '#parents' => ['region_tabs'],
    ];

    $region_collection = $this->exoToolbarRepository->getRegionCollection($region_settings)->sort();
    foreach ($region_collection as $region) {
      /** @var \Drupal\exo_header\Plugin\ExoToolbarRegionPluginInterface $region */
      if (in_array($region->getPluginId(), $enabled)) {
        $element = [];
        $subform_state = SubformState::createForSubform($element, $form, $form_state);
        $element = $region->buildConfigurationForm($element, $subform_state);
        if (!empty($element)) {
          $form['regions'][$region->getPluginId()] = [
            '#type' => 'details',
            '#title' => $region->label(),
            '#open' => TRUE,
            '#tree' => TRUE,
            '#group' => 'region_tabs',
          ] + $element;
        }
      }
    }

    return $form;
  }

  /**
   * Ajax callback triggered when enabling a region.
   */
  public static function ajaxEnable(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $element = NestedArray::getValue($form, array_slice($trigger['#array_parents'], 0, -2));
    return $element['regions'];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array $form, FormStateInterface $form_state) {
    $enabled = array_filter($form_state->getValue(['enabled']));
    $form_state->setValue('enabled', $enabled);
    foreach ($this->exoToolbarRepository->getRegionCollection() as $region) {
      /** @var \Drupal\exo_header\Plugin\ExoToolbarRegionPluginInterface $region */
      $region_id = $region->getPluginId();
      if (!isset($enabled[$region_id])) {
        $form_state->unsetValue(['regions', $region_id]);
      }
      elseif (isset($form['regions'][$region_id])) {
        $element = $form['regions'][$region_id];
        $subform_state = SubformState::createForSubform($element, $form, $form_state);
        $region->validateConfigurationForm($element, $subform_state);
      }
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array $form, FormStateInterface $form_state) {
    $enabled = array_filter($form_state->getValue(['enabled']));
    foreach ($this->exoToolbarRepository->getRegionCollection() as $region) {
      /** @var \Drupal\exo_header\Plugin\ExoToolbarRegionPluginInterface $region */
      if (!empty($form['regions'][$region->getPluginId()])) {
        $element = $form['regions'][$region->getPluginId()];
        $subform_state = SubformState::createForSubform($element, $form, $form_state);
        $region->submitConfigurationForm($element, $subform_state);
      }
    }
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function saveSettings(array $settings) {
    parent::saveSettings($settings);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(FormStateInterface $form_state) {
    $values = parent::massageFormValues($form_state);
    if (isset($values['enabled'])) {
      $values['enabled'] = array_values(array_filter($values['enabled']));
    }
    if (isset($values['regions'])) {
      $values['regions'] = array_intersect_key($values['regions'], array_flip($values['enabled']));
    }
    return $values;
  }

}
