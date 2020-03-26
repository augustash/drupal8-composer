<?php

namespace Drupal\exo_oembed\Plugin\Field\FieldFormatter;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\image\ImageStyleStorageInterface;
use Drupal\media\Entity\MediaType;
use Drupal\media\IFrameUrlHelper;
use Drupal\media\OEmbed\ResourceException;
use Drupal\media\OEmbed\ResourceFetcherInterface;
use Drupal\media\OEmbed\UrlResolverInterface;
use Drupal\media\Plugin\media\Source\OEmbedInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Utility\Html;
use Drupal\exo_icon\ExoIconTranslationTrait;
use Drupal\media\Plugin\Field\FieldFormatter\OEmbedFormatter;
use Drupal\Core\Utility\Token;

/**
 * Plugin implementation of the 'exo_oembed_click' formatter.
 *
 * @internal
 *   This is an internal part of the oEmbed system and should only be used by
 *   oEmbed-related code in Drupal core.
 *
 * @FieldFormatter(
 *   id = "exo_oembed_click",
 *   label = @Translation("eXo Click to Content"),
 *   field_types = {
 *     "link",
 *     "string",
 *     "string_long",
 *   },
 * )
 */
class ExoOEmbedClick extends OEmbedFormatter {
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
  protected $logger;

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
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

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
   * Constructs an OEmbedFormatter instance.
   *
   * @param string $plugin_id
   *   The plugin ID for the formatter.
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
   *   Any third party settings.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\media\OEmbed\ResourceFetcherInterface $resource_fetcher
   *   The oEmbed resource fetcher service.
   * @param \Drupal\media\OEmbed\UrlResolverInterface $url_resolver
   *   The oEmbed URL resolver service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\media\IFrameUrlHelper $iframe_url_helper
   *   The iFrame URL helper service.
   * @param \Drupal\Core\Utility\Token $token
   *   The token service.
   * @param \Drupal\image\ImageStyleStorageInterface $image_style_storage
   *   The image style entity storage handler.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, MessengerInterface $messenger, ResourceFetcherInterface $resource_fetcher, UrlResolverInterface $url_resolver, LoggerChannelFactoryInterface $logger_factory, ConfigFactoryInterface $config_factory, IFrameUrlHelper $iframe_url_helper, Token $token, ImageStyleStorageInterface $image_style_storage, RendererInterface $renderer) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings, $messenger, $resource_fetcher, $url_resolver, $logger_factory, $config_factory, $iframe_url_helper);
    $this->token = $token;
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
      $container->get('messenger'),
      $container->get('media.oembed.resource_fetcher'),
      $container->get('media.oembed.url_resolver'),
      $container->get('logger.factory'),
      $container->get('config.factory'),
      $container->get('media.oembed.iframe_url_helper'),
      $container->get('token'),
      $container->get('entity_type.manager')->getStorage('image_style'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return parent::defaultSettings() + [
      'resolution' => '16:9',
      'type' => 'text',
      'text' => 'View Video',
      'icon' => 'regular-play-circle',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  protected function getTypes() {
    return [
      'text' => $this->t('Text and Icon'),
      'image' => $this->t('Image'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $type_id = Html::getUniqueId('exo-oembed-click-type');

    $form['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Trigger Type'),
      '#id' => $type_id,
      '#options' => $this->getTypes(),
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
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $type = $this->getSetting('type');
    $summary[] = $this->t('Type: %value', ['%value' => $this->getTypes()[$type]]);
    if ($type == 'image') {
      if ($image_style = $this->getSetting('image_style')) {
        $summary[] = $this->t('Image Style: %value', ['%value' => image_style_options(FALSE)[$image_style]]);
      }
    }
    if ($text = $this->getSetting('text')) {
      $summary[] = $this->t('Type: %value', ['%value' => $text]);
    }
    if ($icon = $this->getSetting('icon')) {
      $summary[] = $this->icon('Icon as')->setIcon($icon)->setIconAfter();
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    $max_width = $this->getSetting('max_width');
    $max_height = $this->getSetting('max_height');
    $entity = $items->getEntity();
    $entity_type = $entity->getEntityTypeId();

    foreach ($items as $delta => $item) {
      $main_property = $item->getFieldDefinition()->getFieldStorageDefinition()->getMainPropertyName();
      $value = $item->{$main_property};

      if (empty($value)) {
        continue;
      }

      try {
        $resource_url = $this->urlResolver->getResourceUrl($value, $max_width, $max_height);
        $resource = $this->resourceFetcher->fetchResource($resource_url);
      }
      catch (ResourceException $exception) {
        $this->logger->error("Could not retrieve the remote URL (@url).", ['@url' => $value]);
        continue;
      }
      $url = Url::fromRoute('exo.oembed_iframe', [], [
        'query' => [
          'url' => $value,
          'max_width' => $max_width,
          'max_height' => $max_height,
          'hash' => $this->iFrameUrlHelper->getHash($value, $max_width, $max_height),
        ],
      ]);

      $title = $this->getSetting('text');
      $this->token->replace($title, [$entity_type => $entity]);
      if ($icon = $this->getSetting('icon')) {
        $title = $this->icon($title)->setIcon($icon);
      }
      switch ($this->getSetting('type')) {
        case 'text':

          break;

        case 'image':
          $title = [
            '#theme' => 'image_formatter',
            '#item' => $entity->get('thumbnail')->first(),
            '#item_attributes' => [],
            '#image_style' => $this->getSetting('image_style'),
          ];

          // Add cacheability of each item in the field.
          $this->renderer->addCacheableDependency($title, $entity);
          break;
      }

      $domain = $this->config->get('iframe_domain');
      if ($domain) {
        $url->setOption('base_url', $domain);
      }

      $element[$delta] = [
        '#title' => $title,
        '#type' => 'link',
        '#url' => $url,
        '#attributes' => ['class' => ['exo-oembed-content', 'exo-oembed-click']],
        '#attached' => ['library' => ['exo_oembed/click']],
      ];
    }
    return $element;
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
        $media_type = MediaType::load($media_type);
        return $media_type && $media_type->getSource() instanceof OEmbedInterface && $media_type->getSource()->getPluginDefinition()['id'] == 'video';
      }
    }
    return FALSE;
  }

}
