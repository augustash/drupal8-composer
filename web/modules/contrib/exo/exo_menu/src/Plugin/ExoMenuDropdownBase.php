<?php

namespace Drupal\exo_menu\Plugin;

use Drupal\exo_icon\ExoIconTranslationTrait;
use Drupal\Core\Form\FormStateInterface;

/**
 * Base class for eXo theme dropdown plugins.
 */
abstract class ExoMenuDropdownBase extends ExoMenuBase {
  use ExoIconTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'itemIcon' => '',
      'expandable' => TRUE,
      'unbindFirst' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['itemIcon'] = [
      '#type' => 'exo_icon',
      '#title' => $this->t('Item icon'),
      '#description' => $this->t('Icon used to signify menu items that will open a submenu.'),
      '#default_value' => $this->configuration['itemIcon'],
    ];
    $form['expandable'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Make sub-menus expandable'),
      '#default_value' => $this->configuration['expandable'],
    ];
    $form['unbindFirst'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Unbind events from first item when expanded'),
      '#default_value' => $this->configuration['unbindFirst'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareSettings(array $settings, $type) {
    $icons = [
      'itemIcon',
    ];
    foreach ($icons as $icon) {
      if (!empty($settings[$icon])) {
        $settings[$icon] = $this->icon()->setIcon($settings[$icon])->render();
      }
    }
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareBuild(array $build) {
    $build = parent::prepareBuild($build);
    $build['#attributes']['class'][] = 'exo-menu-dropdown';
    return $build;
  }

}
