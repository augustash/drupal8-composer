<?php

namespace Drupal\exo_oembed\Plugin\Field\FieldFormatter;

use Drupal\exo_modal\Plugin\ExoModalFieldFormatterBase;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\media\Plugin\media\Source\OEmbedInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\exo\ExoSettingsInterface;
use Drupal\exo_modal\ExoModalGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\media\OEmbed\UrlResolverInterface;
use Drupal\media\OEmbed\ResourceFetcherInterface;
use Drupal\media\IFrameUrlHelper;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\token\Token;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\image\ImageStyleStorageInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Html;
use Drupal\media\MediaInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\exo_modal\ExoModalInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\media\OEmbed\ResourceException;
use Drupal\media\OEmbed\Resource;
use Drupal\Core\Url;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\exo_icon\ExoIconTranslationTrait;

/**
 * Plugin implementation of the thumbnail field formatter.
 *
 * @FieldFormatter(
 *   id = "exo_oembed_modal",
 *   label = @Translation("eXo Modal"),
 *   provider = "exo_modal",
 *   field_types = {
 *     "link",
 *     "string",
 *     "string_long",
 *   }
 * )
 */
class ExoOEmbedModal extends ExoModalFieldFormatterBase {
  use ExoIconTranslationTrait;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The oEmbed resource fetcher.
   *
   * @var \Drupal\media\OEmbed\ResourceFetcherInterface
   */
  protected $resourceFetcher;

  /**
   * The oEmbed URL resolver service.
   *
   * @var \Drupal\media\OEmbed\UrlResolverInterface
   */
  protected $urlResolver;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $mediaLogger;

  /**
   * The media settings config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * The iFrame URL helper service.
   *
   * @var \Drupal\media\IFrameUrlHelper
   */
  protected $iFrameUrlHelper;

  /**
   * The image style entity storage.
   *
   * @var \Drupal\image\ImageStyleStorageInterface
   */
  protected $imageStyleStorage;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * An array of thumbnail render arrays.
   *
   * @var array
   */
  protected $thumbnails;

  /**
   * Constructs a new instance of the plugin.
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
   *   Third party settings.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   The entity display repository.
   * @param \Drupal\exo\ExoSettingsInterface $exo_modal_settings
   *   The eXo options service.
   * @param \Drupal\exo_modal\ExoModalGeneratorInterface $exo_modal_generator
   *   The eXo modal generator.
   * @param \Drupal\Core\Utility\Token $token
   *   The token service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\media\OEmbed\ResourceFetcherInterface $resource_fetcher
   *   The oEmbed resource fetcher service.
   * @param \Drupal\media\OEmbed\UrlResolverInterface $url_resolver
   *   The oEmbed URL resolver service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\media\IFrameUrlHelper $iframe_url_helper
   *   The iFrame URL helper service.
   * @param \Drupal\image\ImageStyleStorageInterface $image_style_storage
   *   The image style entity storage handler.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, LoggerChannelFactoryInterface $logger_factory, EntityTypeManagerInterface $entity_type_manager, EntityDisplayRepositoryInterface $entity_display_repository, ExoSettingsInterface $exo_modal_settings, ExoModalGeneratorInterface $exo_modal_generator, Token $token, MessengerInterface $messenger, ResourceFetcherInterface $resource_fetcher, UrlResolverInterface $url_resolver, ConfigFactoryInterface $config_factory, IFrameUrlHelper $iframe_url_helper, ImageStyleStorageInterface $image_style_storage, RendererInterface $renderer) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings, $logger_factory, $entity_type_manager, $entity_display_repository, $exo_modal_settings, $exo_modal_generator);
    $this->token = $token;
    $this->messenger = $messenger;
    $this->resourceFetcher = $resource_fetcher;
    $this->urlResolver = $url_resolver;
    $this->mediaLogger = $logger_factory->get('media');
    $this->config = $config_factory->get('media.settings');
    $this->iFrameUrlHelper = $iframe_url_helper;
    $this->imageStyleStorage = $image_style_storage;
    $this->renderer = $renderer;
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
      $container->get('logger.factory'),
      $container->get('entity_type.manager'),
      $container->get('entity_display.repository'),
      $container->get('exo_modal.settings'),
      $container->get('exo_modal.generator'),
      $container->get('token'),
      $container->get('messenger'),
      $container->get('media.oembed.resource_fetcher'),
      $container->get('media.oembed.url_resolver'),
      $container->get('config.factory'),
      $container->get('media.oembed.iframe_url_helper'),
      $container->get('entity_type.manager')->getStorage('image_style'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'max_width' => 0,
      'max_height' => 0,
      'resolution' => '16:9',
      'type' => 'image',
      'text' => 'View Video',
      'icon' => 'regular-play-circle',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    $field_name = $this->fieldDefinition->getName();

    $type_id = Html::getUniqueId('exo-oembed-modal-type');
    $form['modal']['settings']['trigger']['#access'] = FALSE;

    $form['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Trigger Type'),
      '#id' => $type_id,
      '#options' => [
        'image' => $this->t('Image'),
        'text' => $this->t('Text and Icon'),
      ],
      '#default_value' => $this->getSetting('type'),
      '#required' => TRUE,
    ];
    $image_visibility = [
      'visible' => [
        '#' . $type_id => ['value' => 'image'],
      ],
    ];

    $image_styles = image_style_options(FALSE);
    $form['image_style'] = [
      '#title' => t('Image style'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('image_style'),
      '#empty_option' => t('None (original image)'),
      '#options' => $image_styles,
      '#states' => $image_visibility,
    ];

    $form['text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Text'),
      '#default_value' => $this->getSetting('text'),
      '#description' => $this->t('Supports token replacement.'),
    ];

    $form['icon'] = [
      '#type' => 'exo_icon',
      '#title' => $this->t('Icon'),
      '#default_value' => $this->getSetting('icon'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function generateModal(FieldItemInterface $item, $delta, $settings = []) {
    $modal = parent::generateModal($item, $delta, $settings);

    $title = $this->getSetting('text');
    if ($title) {
      $entity = $item->getEntity();
      $entity_type = $entity->getEntityTypeId();
      $title = $this->token->replace($title, [$entity_type => $entity]);
    }

    // Build trigger contents.
    if ($this->getSetting('type') == 'image' && isset($this->thumbnails[$delta])) {
      $title = [
        'text' => [
          '#markup' => $this->icon($title)->setIcon($this->getSetting('icon')),
          '#weight' => 10,
          '#prefix' => '<div class="exo-oembed-text"><div class="exo-oembed-text-inner">',
          '#suffix' => '</div></div>',
        ],
      ];
      $title['image'] = $this->thumbnails[$delta];
      $modal->setTrigger($title);
    }
    else {
      $modal->setTrigger($title, $this->getSetting('icon'));
    }

    return $modal;
  }

  /**
   * {@inheritdoc}
   */
  public function generateOembed(FieldItemInterface $item, $delta) {
    $element = [];
    $max_width = $this->getSetting('max_width');
    $max_height = $this->getSetting('max_height');

    $main_property = $item->getFieldDefinition()->getFieldStorageDefinition()->getMainPropertyName();
    $value = $item->{$main_property};

    if (empty($value)) {
      return;
    }

    try {
      $resource_url = $this->urlResolver->getResourceUrl($value, $max_width, $max_height);
      $resource = $this->resourceFetcher->fetchResource($resource_url);
    }
    catch (ResourceException $exception) {
      $this->logger->error("Could not retrieve the remote URL (@url).", ['@url' => $value]);
      return;
    }

    if ($resource->getType() === Resource::TYPE_LINK) {
      $element[$delta] = [
        '#title' => $resource->getTitle(),
        '#type' => 'link',
        '#url' => Url::fromUri($value),
      ];
    }
    elseif ($resource->getType() === Resource::TYPE_PHOTO) {
      $element[$delta] = [
        '#theme' => 'image',
        '#uri' => $resource->getUrl()->toString(),
        '#width' => $max_width ?: $resource->getWidth(),
        '#height' => $max_height ?: $resource->getHeight(),
      ];
    }
    else {
      $url = Url::fromRoute('media.oembed_iframe', [], [
        'query' => [
          'url' => $value,
          'max_width' => $max_width,
          'max_height' => $max_height,
          'hash' => $this->iFrameUrlHelper->getHash($value, $max_width, $max_height),
        ],
      ]);

      $domain = $this->config->get('iframe_domain');
      if ($domain) {
        $url->setOption('base_url', $domain);
      }

      // Render videos and rich content in an iframe for security reasons.
      // @see: https://oembed.com/#section3
      $element[$delta] = [
        '#type' => 'html_tag',
        '#tag' => 'iframe',
        '#attributes' => [
          'src' => $url->toString(),
          'frameborder' => 0,
          'scrolling' => FALSE,
          'allowtransparency' => TRUE,
          'width' => $max_width ?: $resource->getWidth(),
          'height' => $max_height ?: $resource->getHeight(),
          'class' => ['media-oembed-content'],
        ],
        '#attached' => [
          'library' => [
            'media/oembed.formatter',
          ],
        ],
      ];

      CacheableMetadata::createFromObject($resource)
        ->addCacheTags($this->config->getCacheTags())
        ->applyTo($element[$delta]);
    }
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  protected function viewModalElement(ExoModalInterface $modal, FieldItemInterface $item, $delta, $langcode) {
    $element = [];
    $element += $this->generateOembed($item, $delta);
    $modal->setSetting(['modal', 'closeButton'], FALSE);
    $modal->setSetting(['modal', 'closeInBody'], TRUE);

    // Use iframe functionality if possible.
    if (isset($element[0]['#attributes']['src'])) {
      $resolution = explode(':', $this->getSetting('resolution'));
      $ratio = $resolution[0] / $resolution[1];
      $modal->setSetting(['modal', 'iframe'], TRUE);
      $modal->setSetting(['modal', 'iframeURL'], $element[0]['#attributes']['src']);
      $modal->setSetting(['modal', 'width'], '96%');
      $modal->setSetting(['modal', 'iframeWidth'], '800px');
      $modal->setSetting(['modal', 'iframeHeight'], 800 * $ratio . 'px');
      $modal->setSetting(['modal', 'iframeResponsive'], TRUE);
      $modal->setSetting(['modal', 'closeInBody'], TRUE);
      $element = [];
    }
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $media = $item->getEntity();
      $this->thumbnails[$delta] = [
        '#theme' => 'image_formatter',
        '#item' => $media->get('thumbnail')->first(),
        '#item_attributes' => [],
        '#image_style' => $this->getSetting('image_style'),
        '#url' => $this->getMediaThumbnailUrl($media, $items->getEntity()),
      ];

      // Add cacheability of each item in the field.
      $this->renderer->addCacheableDependency($this->thumbnails[$delta], $media);
    }
    return parent::viewElements($items, $langcode);
  }

  /**
   * Get the URL for the media thumbnail.
   *
   * @param \Drupal\media\MediaInterface $media
   *   The media item.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity that the field belongs to.
   *
   * @return \Drupal\Core\Url|null
   *   The URL object for the media item or null if we don't want to add
   *   a link.
   */
  protected function getMediaThumbnailUrl(MediaInterface $media, EntityInterface $entity) {
    $url = NULL;
    $image_link_setting = $this->getSetting('image_link');
    // Check if the formatter involves a link.
    if ($image_link_setting == 'content') {
      if (!$entity->isNew()) {
        $url = $entity->toUrl();
      }
    }
    elseif ($image_link_setting === 'media') {
      $url = $media->toUrl();
    }
    return $url;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    if ($field_definition->getTargetEntityTypeId() !== 'media') {
      return FALSE;
    }

    if (parent::isApplicable($field_definition)) {
      $media_type = $field_definition->getTargetBundle();

      if ($media_type) {
        $media_type = \Drupal::entityTypeManager()->getStorage('media_type')->load($media_type);
        return $media_type && $media_type->getSource() instanceof OEmbedInterface;
      }
    }
    return FALSE;
  }

}
