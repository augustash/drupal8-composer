<?php

namespace Drupal\exo_menu\Plugin\ExoMenu;

use Drupal\exo_menu\Plugin\ExoMenuBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_icon\ExoIconTranslationTrait;

/**
 * Plugin implementation of the 'slide_vertical' eXo menu.
 *
 * @ExoMenu(
 *   id = "slide_vertical",
 *   label = @Translation("Slide Vertical"),
 *   as_levels = true,
 * )
 */
class SlideVertical extends ExoMenuBase {
  use ExoIconTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'backNav' => 0,
      'backText' => 'Back',
      'backIcon' => '',
      'breadcrumbNav' => 1,
      'breadcrumbText' => 'All',
      'breadcrumbIcon' => '',
      'breadcrumbSeparatorIcon' => '',
      'itemIcon' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['backNav'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use back button'),
      '#description' => $this->t('The back button provides a way to navigate backwards one level.'),
      '#default_value' => $this->configuration['backNav'],
    ];
    $form['backText'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Back nav text'),
      '#description' => $this->t('The text shown that represents the home location.'),
      '#default_value' => $this->configuration['backText'],
    ];
    $form['backIcon'] = [
      '#type' => 'exo_icon',
      '#title' => $this->t('Back button icon'),
      '#default_value' => $this->configuration['backIcon'],
    ];
    $form['breadcrumbNav'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use breadcrumb'),
      '#description' => $this->t('The breadcrumb provides a navigation trail to return to previous levels.'),
      '#default_value' => $this->configuration['breadcrumbNav'],
    ];
    $form['breadcrumbText'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Initial breadcrumb text'),
      '#description' => $this->t('The text shown that represents the home location.'),
      '#default_value' => $this->configuration['breadcrumbText'],
    ];
    $form['breadcrumbIcon'] = [
      '#type' => 'exo_icon',
      '#title' => $this->t('Breadcrumb icon'),
      '#description' => $this->t('Icon used to represent the home location.'),
      '#default_value' => $this->configuration['breadcrumbIcon'],
    ];
    $form['breadcrumbSeparatorIcon'] = [
      '#type' => 'exo_icon',
      '#title' => $this->t('Breadcrumb separator icon'),
      '#description' => $this->t('Icon used to separate breadcrumb items.'),
      '#default_value' => $this->configuration['breadcrumbSeparatorIcon'],
    ];
    $form['itemIcon'] = [
      '#type' => 'exo_icon',
      '#title' => $this->t('Item icon'),
      '#description' => $this->t('Icon used to signify menu items that will open a submenu.'),
      '#default_value' => $this->configuration['itemIcon'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareSettings(array $settings, $type) {
    $icons = [
      'backIcon',
      'breadcrumbIcon',
      'breadcrumbSeparatorIcon',
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
    $build['#attributes']['class'][] = 'exo-menu-slide';
    $build['#attributes']['class'][] = 'exo-menu-slide-vertical';
    $build['#attached']['library'][] = 'exo_menu/slide.vertical';
    return $build;
  }

}
