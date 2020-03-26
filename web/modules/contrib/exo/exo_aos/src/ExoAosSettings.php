<?php

namespace Drupal\exo_aos;

use Drupal\exo\ExoSettingsBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ExoFormSettings.
 *
 * @package Drupal\exo_form
 */
class ExoAosSettings extends ExoSettingsBase {

  /**
   * {@inheritdoc}
   */
  public function getModuleId() {
    return 'exo_aos';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $form['animation'] = [
      '#type' => 'select',
      '#title' => $this->t('Animation'),
      '#description' => $this->t('The animation to perform.'),
      '#required' => TRUE,
      '#options' => self::getElementAnimations(),
      '#default_value' => $this->getSetting(['animation']),
    ];

    $form['offset'] = [
      '#type' => 'number',
      '#title' => $this->t('Offset'),
      '#field_suffix' => 'px',
      '#description' => $this->t('Offset (in px) from the original trigger point.'),
      '#required' => TRUE,
      '#default_value' => $this->getSetting(['offset']),
    ];

    $form['delay'] = [
      '#type' => 'number',
      '#title' => $this->t('Delay'),
      '#description' => $this->t('Values from 0 to 3000, with step 50ms.'),
      '#required' => TRUE,
      '#default_value' => $this->getSetting(['delay']),
    ];

    $form['duration'] = [
      '#type' => 'number',
      '#title' => $this->t('Duration'),
      '#description' => $this->t('Values from 0 to 3000, with step 50ms.'),
      '#required' => TRUE,
      '#default_value' => $this->getSetting(['duration']),
    ];

    $form['easing'] = [
      '#type' => 'select',
      '#title' => $this->t('Easing'),
      '#description' => $this->t('Easing for AOS animations.'),
      '#required' => TRUE,
      '#options' => self::getElementEasings(),
      '#default_value' => $this->getSetting(['easing']),
    ];

    $form['once'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Once'),
      '#description' => $this->t('Whether animation should happen only once - while scrolling down.'),
      '#default_value' => $this->getSetting(['once']),
    ];

    $form['mirror'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mirror'),
      '#description' => $this->t('Whether elements should animate out while scrolling past them.'),
      '#default_value' => $this->getSetting(['mirror']),
    ];

    $form['anchorPlacement'] = [
      '#type' => 'select',
      '#title' => $this->t('Anchor Placement'),
      '#description' => $this->t('Defines which position of the element regarding to window should trigger the animation.'),
      '#required' => TRUE,
      '#options' => self::getElementAnchorPlacements(),
      '#default_value' => $this->getSetting(['anchorPlacement']),
    ];

    return $form;
  }

  /**
   * A list of setting keys => html data keys.
   *
   * These are settings which can be set on a per-instance basis.
   */
  public static function getElementProperties() {
    return [
      'animation' => 'aos',
      'offset' => 'aos-offset',
      'delay' => 'aos-delay',
      'duration' => 'aos-duration',
      'easing' => 'aos-easing',
      'once' => 'aos-once',
      'mirror' => 'aos-mirror',
      'anchorPlacement' => 'aos-anchor-placement',
    ];
  }

  /**
   * A list of setting keys => labels.
   */
  public static function getElementPropertyLabels() {
    $labels = [];
    foreach (self::getElementProperties() as $key => $data) {
      $labels[$key] = ucwords(strtolower(preg_replace('/(?<!^)[A-Z]/', ' $0', $key)));
    }
    return $labels;
  }

  /**
   * The list of available animations.
   */
  public static function getElementAnimations() {
    return [
      'na' => 'Custom',
      'fade' => 'Fade',
      'fade-up' => 'Fade Up',
      'fade-down' => 'Fade Down',
      'fade-left' => 'Fade Left',
      'fade-right' => 'Fade Right',
      'fade-up-right' => 'Fade Up Right',
      'fade-up-left' => 'Fade Up Left',
      'fade-down-right' => 'Fade Down Right',
      'fade-down-left' => 'Fade Down Left',
      'flip-up' => 'Flip Up',
      'flip-down' => 'Flip Down',
      'flip-left' => 'Flip Left',
      'flip-right' => 'Flip Right',
      'slide-up' => 'Slide Up',
      'slide-down' => 'Slide Down',
      'slide-left' => 'Slide Left',
      'slide-right' => 'Slide Right',
      'zoom-in' => 'Zoom In',
      'zoom-in-up' => 'Zoom In Up',
      'zoom-in-down' => 'Zoom In Down',
      'zoom-in-left' => 'Zoom In Left',
      'zoom-in-right' => 'Zoom In Right',
      'zoom-out' => 'Zoom Out',
      'zoom-out-up' => 'Zoom Out Up',
      'zoom-out-down' => 'Zoom Out Down',
      'zoom-out-left' => 'Zoom Out Left',
      'zoom-out-right' => 'Zoom Out Right',
    ];
  }

  /**
   * The list of available anchor placements.
   */
  public static function getElementAnchorPlacements() {
    return [
      'top-bottom' => 'Top-Bottom',
      'center-bottom' => 'Center-Bottom',
      'bottom-bottom' => 'Bottom-Bottom',
      'top-center' => 'Top-Center',
      'center-center' => 'Center-Center',
      'bottom-center' => 'Bottom-Center',
      'top-top' => 'Top-Top',
      'bottom-top' => 'Bottom-Top',
      'center-top' => 'Center-Top',
    ];
  }

  /**
   * The list of available easings.
   */
  public static function getElementEasings() {
    return [
      'linear' => 'Linear',
      'ease' => 'Ease',
      'ease-in' => 'Ease-In',
      'ease-out' => 'Ease-Out',
      'ease-in-out' => 'Ease-In-Out',
      'ease-in-back' => 'Ease-In-Back',
      'ease-out-back' => 'Ease-Out-Back',
      'ease-in-out-back' => 'Ease-In-Out-Back',
      'ease-in-sine' => 'Ease-In-Sine',
      'ease-out-sine' => 'Ease-Out-Sine',
      'ease-in-out-sine' => 'Ease-In-Out-Sine',
      'ease-in-quad' => 'Ease-In-Quad',
      'ease-out-quad' => 'Ease-Out-Quad',
      'ease-in-out-quad' => 'Ease-In-Out-Quad',
      'ease-in-cubic' => 'Ease-In-Cubic',
      'ease-out-cubic' => 'Ease-Out-Cubic',
      'ease-in-out-cubic' => 'Ease-In-Out-Cubic',
      'ease-in-quart' => 'Ease-In-Quart',
      'ease-out-quart' => 'Ease-Out-Quart',
      'ease-in-out-quart' => 'Ease-In-Out-Quart',
    ];
  }

}
