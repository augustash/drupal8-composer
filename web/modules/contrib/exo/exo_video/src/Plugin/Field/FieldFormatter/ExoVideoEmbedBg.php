<?php

namespace Drupal\exo_video\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\video_embed_field\ProviderManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Utility\Html;
use Drupal\image\Entity\ImageStyle;
use Drupal\Core\Field\FieldItemInterface;

/**
 * Plugin implementation of the video field formatter.
 *
 * @FieldFormatter(
 *   id = "exo_video_embed_bg",
 *   label = @Translation("eXo Video Background"),
 *   field_types = {
 *     "video_embed_field"
 *   }
 * )
 */
class ExoVideoEmbedBg extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The embed provider plugin manager.
   *
   * @var \Drupal\video_embed_field\ProviderManagerInterface
   */
  protected $providerManager;

  /**
   * The logged in user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a new instance of the plugin.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param mixed $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param mixed $third_party_settings
   *   Third party settings.
   * @param \Drupal\video_embed_field\ProviderManagerInterface $provider_manager
   *   The video embed provider manager.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The logged in user.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, $settings, $label, $view_mode, $third_party_settings, ProviderManagerInterface $provider_manager, AccountInterface $current_user) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->providerManager = $provider_manager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('video_embed_field.provider_manager'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'autoplay' => TRUE,
      'loop' => TRUE,
      'mute' => TRUE,
      'when' => 'always',
      'resolution' => '16:9',
      'image_enable' => FALSE,
      'image_style' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getWhenOptions() {
    return [
      'always' => $this->t('always'),
      'hover' => $this->t('on hover'),
      'viewport' => $this->t('when in viewport'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();

    $form['autoplay'] = [
      '#title' => $this->t('Autoplay'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('autoplay'),
    ];
    $form['when'] = [
      '#title' => $this->t('When to Play'),
      '#type' => 'select',
      '#options' => $this->getWhenOptions(),
      '#default_value' => $this->getSetting('when'),
    ];
    $form['loop'] = [
      '#title' => $this->t('Loop'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('loop'),
    ];
    $form['mute'] = [
      '#title' => $this->t('Mute'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('mute'),
    ];
    $form['resolution'] = [
      '#title' => $this->t('Resolution'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('resolution'),
      '#options' => [
        '16:9' => '16:9',
        '4:3' => '4:3',
      ],
    ];
    $form['image_enable'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use Image'),
      '#description' => $this->t('An image will be displayed while the video is loading.'),
      '#default_value' => $this->getSetting('image_enable'),
      '#required' => FALSE,
      '#attributes' => [
        'class' => ['exo-video-image-enable-' . $field_name],
      ],
    ];
    $form['image_style'] = [
      '#title' => $this->t('Image Style'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('image_style'),
      '#required' => FALSE,
      '#options' => image_style_options(),
      '#states' => [
        'visible' => [
          '.exo-video-image-enable-' . $field_name => ['checked' => TRUE],
        ],
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary[] = $this->t('Video (@resolution@autoplay@loop@mute@when).', [
      '@resolution' => $this->getSetting('resolution'),
      '@autoplay' => $this->getSetting('autoplay') ? $this->t(', autoplaying') : '',
      '@loop' => $this->getSetting('loop') ? $this->t(', loop') : '',
      '@mute' => $this->getSetting('mute') ? $this->t(', mute') : '',
      '@when' => ', ' . $this->getWhenOptions()[$this->getSetting('when')],
    ]);
    if ($this->getSetting('image_enable')) {
      $summary[] = $this->t('Thumbnail (@style).', [
        '@style' => $this->getSetting('image_style') ? $this->getSetting('image_style') : $this->t('no image style'),
      ]);
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    $entity = $items->getEntity();
    $entity_type = $entity->getEntityTypeId();
    $field_name = $this->fieldDefinition->getName();
    foreach ($items as $delta => $item) {
      $keys = [
        $entity_type,
        $entity->bundle(),
        $entity->id(),
        $this->viewMode,
        $field_name,
        $delta,
      ];
      $provider = $this->getProvider($item);

      if (!$provider) {
        $element[$delta] = ['#theme' => 'video_embed_field_missing_provider'];
      }
      else {
        $image = FALSE;
        if ($this->getSetting('image_enable')) {
          $image = $provider->getLocalThumbnailUri();
          if ($image_style = $this->getSetting('image_style')) {
            $image = ImageStyle::load($image_style)->buildUrl($image);
          }
          else {
            $image = file_create_url($image);
          }
        }
        $provider->downloadThumbnail();

        $element[$delta] = [
          '#type' => 'exo_video_bg',
          '#id' => implode('-', $keys),
          '#video_provider' => $provider->getPluginId(),
          '#video_id' => $provider->getIdFromInput($this->getVideoUrl($item)),
          '#video_resolution' => $this->getSetting('resolution'),
          '#video_loop' => $this->getSetting('loop'),
          '#video_autoplay' => $this->getSetting('autoplay'),
          '#video_mute' => $this->getSetting('mute'),
          '#video_image' => $image,
          '#video_when' => $this->getSetting('when'),
        ];
      }
    }
    return $element;
  }

  /**
   * Return the Video URL.
   */
  protected function getVideoUrl(FieldItemInterface $item) {
    return $item->value;
  }

  /**
   * Returns the entity URI.
   */
  protected function getProvider(FieldItemInterface $item) {
    return $this->providerManager->loadProviderFromInput($item->value);
  }

}
