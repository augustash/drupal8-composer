<?php

namespace Drupal\exo_image\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\exo\ExoSettingsFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ExoImageSettings.
 */
class ExoImageSettingsForm extends ExoSettingsFormBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('exo_image.settings')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $form['global'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Initialization settings'),
      '#weight' => -10,
      '#tree' => TRUE,
    ];

    $form['global']['ratio_distortion'] = [
      '#type' => 'number',
      '#title' => $this->t('Maximum allowed ratio distortion'),
      '#default_value' => $this->exoSettings->getSetting('ratio_distortion'),
      '#description' => $this->t('How much ratio distortion is allowed when trying to reuse image styles that crop images. The aspect ratio of the generated images will be distorted by the browser to keep the exact aspect ratio your CSS rules require. A minimum of 30 minutes is required to allow for small rounding errors.'),
      '#min' => 1,
      '#max' => 3600,
      '#step' => 1,
      '#field_suffix' => ' ' . $this->t("minutes. (1° = 60')"),
    ];

    $form['global']['downscale'] = [
      '#type' => 'number',
      '#title' => $this->t('Maximum image style width'),
      '#default_value' => $this->exoSettings->getSetting('downscale'),
      '#description' => $this->t("The maximum width for the biggest image style. Anything bigger will be scaled down to this size unless aspect ratio's and other min/max settings force it otherwise."),
      '#min' => 1,
      '#max' => 10000,
      '#step' => 1,
      '#field_suffix' => ' ' . $this->t('pixels'),
    ];

    $form['global']['upscale'] = [
      '#type' => 'number',
      '#title' => $this->t('Minimum image style width'),
      '#default_value' => $this->exoSettings->getSetting('upscale'),
      '#description' => $this->t("The minimal width for the smallest image style. Anything smaller will be scaled up to this size unless aspect ratio's and other min/max settings force it otherwise."),
      '#min' => 1,
      '#max' => 500,
      '#step' => 1,
      '#field_suffix' => ' ' . $this->t('pixels'),
    ];

    $form['global']['multiplier'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable device pixel ratio detection'),
      '#default_value' => $this->exoSettings->getSetting('multiplier'),
      '#description' => $this->t('Will produce higher quality images on screens that have more physical pixels then logical pixels.'),
    ];

    $form['global']['webp'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable WebP Support'),
      '#default_value' => $this->exoSettings->getSetting('webp'),
      '#description' => $this->t('Automatically convert images to webp on supported browsers.'),
    ];

    $form['global']['webp_quality'] = [
      '#type' => 'number',
      '#title' => $this->t('WebP Quality'),
      '#default_value' => $this->exoSettings->getSetting('webp_quality'),
      '#description' => $this->t('Images will be encoded into WebP format if possible. This is the quality that will be used.'),
      '#min' => 1,
      '#max' => 100,
      '#step' => 1,
      '#states' => [
        'visible' => [
          ':input[name="global[webp]"]' => [
            'checked' => TRUE,
          ],
        ],
      ],
    ];

    $form['actions']['delete'] = [
      '#type' => 'submit',
      '#value' => $this->t('Purge generated image styles'),
      '#submit' => [[get_class($this), 'submitPurge']],
    ];

    $form['actions']['migrate'] = [
      '#type' => 'submit',
      '#value' => $this->t('Migrate from Drimage to eXo Image'),
      '#submit' => [[get_class($this), 'submitMigrate']],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    // Move instance settings into the global setting scope so that they get
    // saved.
    foreach ($form_state->getValue('global') as $setting => $value) {
      $form_state->setValue(['settings', $setting], $value);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function submitPurge(array &$form, FormStateInterface $form_state) {
    \Drupal::service('exo_image.style.manager')->deleteImageStyles();
  }

  /**
   * {@inheritdoc}
   */
  public static function submitMigrate(array &$form, FormStateInterface $form_state) {
    if ($view_displays = \Drupal::entityTypeManager()->getStorage('entity_view_display')->loadMultiple()) {
      $breakpoints = \Drupal::service('exo_image.style.manager')->getBreakpoints();
      $default_breakpoint_key = key($breakpoints);
      foreach ($view_displays as $view_display_id => $view_display) {
        foreach ($view_display->getComponents() as $field_name => $component) {
          if (!isset($component['type'])) {
            continue;
          }
          switch ($component['type']) {
            case 'drimage':
              self::toExoImage($component['type'], $component['settings'], $view_display_id, $field_name, $default_breakpoint_key);
              $view_display->setComponent($field_name, $component)->save();
              break;

            case 'exo_asset':
              switch ($component['settings']['image']['formatter']) {
                case 'drimage':
                  self::toExoImage($component['settings']['image']['formatter'], $component['settings']['image']['settings'], $view_display_id, $field_name, $default_breakpoint_key);
                  $view_display->setComponent($field_name, $component)->save();
                  break;
              }
              break;
          }
        }
      }
    }
  }

  /**
   * Convert drimage to exo image.
   */
  protected static function toExoImage(&$type, &$settings, $view_display_id, $field_name, $breakpoint) {
    switch ($type) {
      case 'drimage':
        switch ($settings['image_handling']) {
          case 'scale':
            $type = 'exo_image';
            $settings = [];
            break;

          case 'background':
            $type = 'exo_image';
            $settings = [];
            \Drupal::messenger()->addMessage(t('The %field_name on %view_display_id has been changed from background to scale and will need to be reviewed.', [
              '%view_display_id' => $view_display_id,
              '%field_name' => $field_name,
            ]), 'warning');
            break;

          case 'aspect_ratio':
            $type = 'exo_image';
            $settings = [
              'breakpoints' => [
                $breakpoint => [
                  'handler' => 'ratio',
                  'ratio' => $settings['aspect_ratio'],
                ],
              ],
            ];
            break;
        }
        break;
    }
  }

}
