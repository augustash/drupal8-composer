<?php

namespace Drupal\exo\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\Select;

/**
 * Provides a slider form element.
 *
 * {@inheritdoc}
 *
 * @FormElement("exo_radios_slider")
 */
class ExoRadiosSlider extends Select {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#process' => [
        [$class, 'processSelect'],
        [$class, 'processAjaxForm'],
        [$class, 'processExoRadiosSlider'],
      ],
      '#tooltips' => TRUE,
      '#tooltips_on_slide' => TRUE,
      '#pips' => FALSE,
    ] + parent::getInfo();
  }

  // /**
  //  * {@inheritdoc}
  //  */
  // public static function preRenderRange($element) {
  //   $element = parent::preRenderRange($element);
  //   return $element;
  // }

  /**
   * Processes a rangeslider form element.
   */
  public static function processExoRadiosSlider(&$element, FormStateInterface $form_state, &$complete_form) {
    if (count($element['#options']) > 0) {
      $settings = [
        'start' => 0,
        'options' => [],
      ];
      $count = 0;
      foreach ($element['#options'] as $key => $value) {
        if (isset($element['#default_value']) && (string) $element['#default_value'] == (string) $key) {
          $settings['start'] = $count;
        }
        $settings['options'][] = [
          'key' => $key,
          'value' => $value,
        ];
        $count++;
      }
      $element['#wrapper_attributes']['id'] = 'exo-radios-slider-' . $element['#id'];
      $element['#field_suffix'] = '<div class="exo-radios-slider-slide"><div id="exo-radios-slider-slide-' . $element['#id'] . '" class="slider"></div></div>';

      $settings['tooltips'] = !empty($element['#tooltips']);
      $settings['pips'] = !empty($element['#pips']);
      $element['#attached']['drupalSettings']['exo']['exoRadiosSlider'][$element['#id']] = $settings;

      if (!empty($element['#pips'])) {
        $element['#wrapper_attributes']['class'][] = 'exo-radios-slider-pips';
      }

      if (!empty($element['#tooltips_on_slide'])) {
        $element['#wrapper_attributes']['class'][] = 'exo-radios-slider-tooltips-on-slide';
      }
    }
    $element['#attached']['library'][] = 'exo/radios.slider';
    return $element;
  }

}
