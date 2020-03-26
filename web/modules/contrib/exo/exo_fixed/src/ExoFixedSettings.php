<?php

namespace Drupal\exo_fixed;

use Drupal\exo\ExoSettingsBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ExoFixedSettings.
 *
 * @package Drupal\exo_fixed
 */
class ExoFixedSettings extends ExoSettingsBase {

  /**
   * {@inheritdoc}
   */
  public function getModuleId() {
    return 'exo_fixed';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $form['themes'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Fixed Regions'),
      '#tree' => TRUE,
      '#element_validate' => [[get_class($this), 'validateThemes']],
    ];

    foreach ($this->themeList() as $theme_id => $theme_label) {
      $form['themes'][$theme_id] = [
        '#type' => 'details',
        '#title' => $theme_label,
        '#element_validate' => [[get_class($this), 'validateThemeRegions']],
        '#open' => (bool) $this->getSetting([
          'themes',
          $theme_id,
        ]),
      ];
      foreach ($this->systemRegionList($theme_id, REGIONS_VISIBLE) as $region_id => $region_label) {
        $status = $this->getSetting([
          'themes',
          $theme_id,
          $region_id,
          'status',
        ]);
        $form['themes'][$theme_id][$region_id] = [
          '#type' => 'details',
          '#title' => $region_label,
          '#open' => (bool) $status,
        ];
        $form['themes'][$theme_id][$region_id]['status'] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Enabled'),
          '#default_value' => $status,
        ];
        $form['themes'][$theme_id][$region_id]['type'] = [
          '#type' => 'select',
          '#title' => $this->t('Type'),
          '#default_value' => $this->getSetting([
            'themes',
            $theme_id,
            $region_id,
            'type',
          ]),
          '#options' => [
            'always' => $this->t('Always show region'),
            'scroll' => $this->t('Hide on scroll down, show on scroll up'),
          ],
        ];
      }
    }

    return $form;
  }

  /**
   * Clean up empty values from theme region values.
   */
  public static function validateThemes(&$element, FormStateInterface $form_state) {
    $value = $form_state->getValue($element['#parents']);
    $form_state->setValueForElement($element, array_filter($value));
  }

  /**
   * Clean up empty values from theme region values.
   */
  public static function validateThemeRegions(&$element, FormStateInterface $form_state) {
    $value = $form_state->getValue($element['#parents']);
    $form_state->setValueForElement($element, array_filter($value, function ($item) {
      return !empty($item['status']);
    }));
  }

  /**
   * Gets the theme list..
   */
  protected function themeList() {
    $themes = \Drupal::service('theme_handler')->listInfo();
    $theme_options = [];
    foreach ($themes as &$theme) {
      if (!empty($theme->info['hidden'])) {
        continue;
      }
      if (!empty($theme->status)) {
        $theme_options[$theme->getName()] = $theme->info['name'];
      }
    }
    return $theme_options;
  }

  /**
   * Wraps system_region_list().
   */
  protected function systemRegionList($theme, $show = REGIONS_ALL) {
    return system_region_list($theme, $show);
  }

}
