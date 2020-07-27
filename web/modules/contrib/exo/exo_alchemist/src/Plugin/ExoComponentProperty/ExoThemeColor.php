<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentProperty;

use Drupal\Core\Form\FormStateInterface;

/**
 * A 'exo_theme_color' adapter for exo components.
 *
 * @ExoComponentProperty(
 *   id = "exo_theme_color",
 *   label = @Translation("Theme Color"),
 * )
 */
class ExoThemeColor extends ClassAttribute {

  /**
   * {@inheritdoc}
   */
  public function getOptions() {
    $options = [];
    foreach ([
      '_none' => [
        'label' => t('None'),
        'hex' => 'transparent',
      ],
      'white' => [
        'label' => t('White'),
        'hex' => '#fff',
      ],
      'black' => [
        'label' => t('Black'),
        'hex' => '#000',
      ],
    ] + exo_theme_colors() + [
      'success' => [
        'label' => t('Success'),
        'hex' => '#86c13d',
      ],
      'warning' => [
        'label' => t('Warning'),
        'hex' => '#f1ba2e',
      ],
      'alert' => [
        'label' => t('Alert'),
        'hex' => '#e54040',
      ],
    ] as $key => $color) {
      $options[$key] = '<div class="exo-icon exo-swatch no-pad large exo-swatch-' . str_replace('#', '', $color['hex']) . '" style="background-color:' . $color['hex'] . '"></div>';
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  protected function getHtmlClassName($name, $value) {
    return 'exo-modifier--' . $name . '-theme-' . $value;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['#exo_style'] = 'grid-compact';
    return $form;
  }

}
