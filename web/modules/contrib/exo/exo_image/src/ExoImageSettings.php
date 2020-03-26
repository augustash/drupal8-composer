<?php

namespace Drupal\exo_image;

use Drupal\exo\ExoSettingsBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Html;

/**
 * Class ExoImageSettings.
 */
class ExoImageSettings extends ExoSettingsBase {

  /**
   * {@inheritdoc}
   */
  public function getModuleId() {
    return 'exo_image';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $id = Html::getUniqueId('exo-image-handler');
    $form['handler'] = [
      '#type' => 'radios',
      '#id' => $id,
      '#title' => $this->t('Image handling'),
      '#default_value' => $this->getSetting('handler'),
      '#options' => $this->imageHandlingOptions(),
      'scale' => [
        '#description' => $this->t('The image will be scaled in width untill it fits. This maintains the original aspect ratio of the image.'),
      ],
      'ratio' => [
        '#description' => $this->t('The image will be scaled and cropped to an exact aspect ratio you define.'),
      ],
    ];

    $form['ratio'] = [
      '#title' => $this->t('Aspect ratio'),
      '#type' => 'fieldset',
      '#states' => [
        'visible' => [
          '#' . $id . ' :input' => [
            'value' => 'ratio',
          ],
        ],
      ],
      'width' => [
        '#type' => 'number',
        '#title' => $this->t('Width'),
        '#default_value' => $this->getSetting(['ratio', 'width']),
        '#min' => 1,
        '#max' => 100,
        '#step' => 1,
      ],
      'height' => [
        '#type' => 'number',
        '#title' => $this->t('Height'),
        '#default_value' => $this->getSetting(['ratio', 'height']),
        '#min' => 1,
        '#max' => 100,
        '#step' => 1,
      ],
    ];

    $form['bg'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('As background image'),
      '#description' => $this->t('Assign as CSS background on img parent.'),
      '#default_value' => $this->getSetting(['bg']),
    ];

    $form['animate'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Animate Reveal'),
      '#default_value' => $this->getSetting('animate'),
      '#description' => $this->t('Animate image in when fully loaded.'),
    ];

    $form['blur'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Blur'),
      '#default_value' => $this->getSetting('blur'),
      '#description' => $this->t('Show blurred image before full image has loaded.'),
    ];

    $form['visible'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Load when visible'),
      '#description' => $this->t('Full image will only be loaded when viewable within viewport.'),
      '#default_value' => $this->getSetting(['visible']),
    ];

    return $form;
  }

  /**
   * Returns the handling options.
   *
   * @return array
   *   The image handling options key|label.
   */
  public function imageHandlingOptions() {
    return [
      'scale' => $this->t('Scale'),
      'ratio' => $this->t('Fixed aspect ratio crop'),
    ];
  }

}
