<?php

namespace Drupal\exo_imagine\Plugin\Field\FieldFormatter;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Template\Attribute;
use Drupal\exo\ExoSettingsInterface;
use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatter;
use Drupal\exo\Plugin\Field\FieldFormatter\ExoEntityReferenceSelectionTrait;
use Drupal\exo\Plugin\Field\FieldFormatter\ExoEntityReferenceLinkTrait;
use Drupal\exo_imagine\ExoImagineManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;

/**
 * Plugin implementation of the 'eXo Image' formatter.
 *
 * @FieldFormatter(
 *   id = "exo_imagine",
 *   label = @Translation("eXo Image"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class ExoImagineFormatter extends ImageFormatter {
  use ExoEntityReferenceSelectionTrait;
  use ExoEntityReferenceLinkTrait;

  /**
   * The exo imagine manager.
   *
   * @var \Drupal\exo_imagine\ExoImagineManager
   */
  protected $exoImagineManager;

  /**
   * The exo imagine settings.
   *
   * @var \Drupal\exo\ExoSettingsInstanceInterface
   */
  protected $exoImagineSettings;

  /**
   * Constructs an ImageFormatter object.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings settings.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Entity\EntityStorageInterface $image_style_storage
   *   The image style storage.
   * @param \Drupal\exo\ExoSettingsInterface $exo_imagine_settings
   *   The exo image settings.
   * @param \Drupal\exo_imagine\ExoImagineManager $exo_imagine_manager
   *   The exo image stype manager.
   * @param \Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface $mime_type_guesser
   *   The MIME type guesser.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, AccountInterface $current_user, EntityStorageInterface $image_style_storage, ExoSettingsInterface $exo_imagine_settings, ExoImagineManager $exo_imagine_manager, MimeTypeGuesserInterface $mime_type_guesser, LoggerChannelFactoryInterface $logger_factory) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings, $current_user, $image_style_storage);
    $this->exoImagineSettings = $exo_imagine_settings->createInstance($this->getSetting('display'));
    $this->exoImagineManager = $exo_imagine_manager;
    $this->mimeTypeGuesser = $mime_type_guesser;
    $this->logger = $logger_factory->get('exo_imagine');
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
      $container->get('current_user'),
      $container->get('entity.manager')->getStorage('image_style'),
      $container->get('exo_imagine.settings'),
      $container->get('exo_imagine.manager'),
      $container->get('file.mime_type.guesser'),
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $breakpoints = [];
    foreach (\Drupal::service('exo_imagine.manager')->getBreakpoints() as $key => $breakpoint) {
      $width = '';
      switch ($key) {
        case 'large':
          $width = 1200;
          break;

        case 'medium':
          $width = 1024;
          break;

        case 'small':
          $width = 640;
          break;
      }
      $breakpoints[$key] = [
        'width' => $width,
        'height' => '',
        'unique' => '',
      ];
    }
    return [
      'breakpoints' => $breakpoints,
      'display' => [],
    ] + self::selectionDefaultSettings() + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    // Do not use an image style here. exo_image calculates one for us.
    unset($element['image_style']);

    $element['breakpoints'] = [];
    foreach ($this->getBreakpointSettings() as $key => $data) {
      $element['breakpoints'][$key] = [
        '#type' => 'details',
        '#title' => $data['label'],
        '#open' => !empty($data['width']) || !empty($data['height']),
      ];
      $element['breakpoints'][$key]['width'] = [
        '#type' => 'number',
        '#title' => $this->t('Width'),
        '#field_suffix' => ' ' . t('pixels'),
        '#default_value' => $data['width'],
        '#min' => 1,
        '#step' => 1,
      ];
      $element['breakpoints'][$key]['height'] = [
        '#type' => 'number',
        '#title' => $this->t('Height'),
        '#field_suffix' => ' ' . t('pixels'),
        '#default_value' => $data['height'],
        '#min' => 1,
        '#step' => 1,
      ];
    }

    $display = $this->getSetting('display');
    $element['display'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Display Overrides'),
    ];
    $element['display'] = $this->exoImagineSettings->buildForm([], $form_state);
    $element['display']['#element_validate'][] = [get_class($this), 'validateElementDisplay'];

    $element += $this->linkSettingsForm($element, $form_state);
    $element += $this->selectionSettingsForm($element, $form_state);
    return $element;
  }

  /**
   * Validate breakpoint settings.
   */
  public static function validateElementDisplay(array $element, FormStateInterface $form_state) {
    $exo_imagine_settings = \Drupal::service('exo_imagine.settings');
    $values = $form_state->getValue($element['#parents']);
    $subform_state = SubformState::createForSubform($element, $form_state->getCompleteForm(), $form_state);
    $instance = $exo_imagine_settings->createInstance($values);
    $instance->validateForm($element, $subform_state);
    $instance->submitForm($element, $subform_state);
  }

  /**
   * Get breakpoint settings.
   */
  public function getBreakpointSettings() {
    $settings = [];
    $breakpoints = $this->exoImagineManager->getBreakpoints();
    foreach ($breakpoints as $key => $breakpoint) {
      $breakpoint_settings = $this->getSetting('breakpoints')[$key];
      $settings[$key] = [
        'label' => $breakpoint->getLabel(),
        'media' => $breakpoint->getMediaQuery(),
        'width' => $breakpoint_settings['width'],
        'height' => $breakpoint_settings['height'],
        // @TODO Support unique id.
        'unique' => '',
      ];
    }
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);

    if ($items instanceof EntityReferenceFieldItemListInterface) {
      $site_settings = $this->exoImagineSettings->getSiteSettingsDiff();
      unset($site_settings['usage']);
      $settings = $this->exoImagineSettings->getLocalSettingsDiff();
      unset($settings['usage']);
      $files = $this->getEntitiesToView($items, $langcode);
      $breakpoint_settings = $this->getBreakpointSettings();
      $blur = $this->exoImagineSettings->getSetting('blur');
      // $settings = $this->exoImagineSettings->getLocalSettingsDiff();
      foreach ($elements as $delta => &$element) {
        /** @var \Drupal\file\FileInterface $file */
        $file = $files[$delta];
        $item = $element['#item'];
        $cache = $element['#cache'] + ['tags' => [], 'contexts' => []];
        $element = [
          '#theme' => 'exo_imagine',
          '#attributes' => [
            'class' => 'exo-imagine',
            'data-exo-imagine' => Json::encode($settings),
          ],
          '#image_picture_attributes' => new Attribute([
            'class' => ['exo-imagine-image-picture'],
          ]),
          '#preview_picture_attributes' => new Attribute([
            'class' => ['exo-imagine-preview-picture'],
          ]),
        ];
        if ($blur) {
          $element['#preview_picture_attributes']['class'][] = 'exo-imagine-blur';
        }

        // SVG Support.
        if ($file->getMimeType() === 'image/svg+xml') {
          // TODO.
        }
        else {
          $last = array_key_last($breakpoint_settings);
          foreach ($breakpoint_settings as $key => $data) {
            if (empty($data['width']) && empty($data['height'])) {
              continue;
            }
            $image_definition = $this->exoImagineManager->getImageDefinition($file, $data['width'], $data['height'], $data['unique']);
            $cache['tags'] = Cache::mergeTags($cache['tags'], $image_definition['cache_tags']);
            $preview_definition = $this->exoImagineManager->getImagePreviewDefinition($file, $data['width'], $data['height'], $data['unique'], $blur);
            $cache['tags'] = Cache::mergeTags($cache['tags'], $preview_definition['cache_tags']);

            if (isset($image_definition['webp'])) {
              $element['#image_sources'][$key . 'webp'] = new Attribute([
                'media' => $data['media'],
                'srcset' => $image_definition['webp'],
                'width' => $preview_definition['width'],
                'height' => $preview_definition['height'],
                'type' => 'image/webp',
              ]);
            }

            $element['#image_sources'][$key] = new Attribute([
              'media' => $data['media'],
              'data-srcset' => $image_definition['src'],
              'type' => $image_definition['mime'],
            ]);

            if (isset($preview_definition['webp'])) {
              $element['#preview_sources'][$key . 'webp'] = new Attribute([
                'media' => $data['media'],
                'srcset' => $preview_definition['webp'],
                'width' => $preview_definition['width'],
                'height' => $preview_definition['height'],
                'type' => 'image/webp',
              ]);
            }

            $element['#preview_sources'][$key] = new Attribute([
              'media' => $data['media'],
              'srcset' => $preview_definition['src'],
              'width' => $preview_definition['width'],
              'height' => $preview_definition['height'],
              'type' => $preview_definition['mime'],
            ]);

            //
            if ($key === $last) {
              $element['#image_attributes'] = new Attribute([
                'src' => 'about:blank',
                'class' => ['exo-imagine-image'],
                'alt' => $item->getValue()['alt'],
              ]);
              $element['#preview_attributes'] = new Attribute([
                'src' => $preview_definition['src'],
                'src' => 'about:blank',
                'class' => ['exo-imagine-preview'],
                'alt' => $item->getValue()['alt'],
              ]);
            }
          }
        }
        $element['#cache'] = $cache;
        $element['#attached']['drupalSettings']['exoImagine']['defaults'] = $site_settings;
      }
    }

    return $elements;
  }

}
