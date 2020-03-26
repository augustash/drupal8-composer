<?php

namespace Drupal\exo_menu\Plugin\ExoMenu;

use Drupal\exo_menu\Plugin\ExoMenuBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_icon\ExoIconTranslationTrait;

/**
 * Plugin implementation of the 'mega' eXo menu.
 *
 * @ExoMenu(
 *   id = "mega_vertical",
 *   label = @Translation("Mega Vertical"),
 * )
 */
class MegaVertical extends ExoMenuBase {
  use ExoIconTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'itemDelayInterval' => 60,
      'width' => '50%',
      'transitionIn' => 'fadeIn',
      'transitionOut' => 'fadeOut',
      'itemIcon' => '',
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
    $form['itemDelayInterval'] = [
      '#type' => 'number',
      '#title' => $this->t('Item Interval Delay'),
      '#description' => $this->t('The delay between items as they animate in.'),
      '#default_value' => $this->configuration['itemDelayInterval'],
    ];
    $form['width'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Width'),
      '#description' => $this->t('The width of each part of the menu. Should be in "px" or "%".'),
      '#default_value' => $this->configuration['width'],
    ];
    $form['transitionIn'] = [
      '#type' => 'select',
      '#title' => $this->t('Transition In'),
      '#options' => exo_animate_in_options(),
      '#default_value' => $this->configuration['transitionIn'],
    ];
    $form['transitionOut'] = [
      '#type' => 'select',
      '#title' => $this->t('Transition Out'),
      '#options' => exo_animate_out_options(),
      '#default_value' => $this->configuration['transitionOut'],
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
    $build['#attributes']['class'][] = 'exo-menu-mega';
    $build['#attributes']['class'][] = 'exo-menu-mega-vertical';
    $build['#attached']['library'][] = 'exo_menu/mega.vertical';
    return $build;
  }

}
