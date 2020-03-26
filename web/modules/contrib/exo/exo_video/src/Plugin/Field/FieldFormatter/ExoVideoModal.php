<?php

namespace Drupal\exo_video\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\video_embed_field\Plugin\Field\FieldFormatter\Video;
use Drupal\video_embed_field\Plugin\Field\FieldFormatter\Thumbnail;
use Drupal\exo\ExoSettingsInterface;
use Drupal\exo_modal\Plugin\ExoModalFieldFormatterBase;
use Drupal\exo_modal\ExoModalGeneratorInterface;
use Drupal\exo_icon\ExoIconTranslationTrait;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\video_embed_field\ProviderManagerInterface;
use Drupal\Component\Utility\Html;
use Drupal\exo_modal\ExoModalInterface;
use Drupal\token\Token;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;

/**
 * Plugin implementation of the thumbnail field formatter.
 *
 * @FieldFormatter(
 *   id = "exo_video_modal",
 *   label = @Translation("eXo Video Modal"),
 *   provider = "exo_modal",
 *   field_types = {
 *     "video_embed_field"
 *   }
 * )
 */
class ExoVideoModal extends ExoModalFieldFormatterBase {
  use ExoIconTranslationTrait;

  /**
   * The embed provider plugin manager.
   *
   * @var \Drupal\video_embed_field\ProviderManagerInterface
   */
  protected $providerManager;

  /**
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * The field formatter plugin instance for thumbnails.
   *
   * @var \Drupal\Core\Field\FormatterInterface
   */
  protected $thumbnailFormatter;

  /**
   * The field formatterp plguin instance for videos.
   *
   * @var \Drupal\Core\Field\FormatterInterface
   */
  protected $videoFormatter;

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
   * @param \Drupal\video_embed_field\ProviderManagerInterface $provider_manager
   *   The video embed provider manager.
   * @param \Drupal\Core\Utility\Token $token
   *   The token service.
   * @param \Drupal\Core\Field\FormatterInterface $thumbnail_formatter
   *   The field formatter for thumbnails.
   * @param \Drupal\Core\Field\FormatterInterface $video_formatter
   *   The field formatter for videos.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, LoggerChannelFactoryInterface $logger_factory, EntityTypeManagerInterface $entity_type_manager, EntityDisplayRepositoryInterface $entity_display_repository, ExoSettingsInterface $exo_modal_settings, ExoModalGeneratorInterface $exo_modal_generator, ProviderManagerInterface $provider_manager, Token $token, FormatterInterface $thumbnail_formatter, FormatterInterface $video_formatter) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings, $logger_factory, $entity_type_manager, $entity_display_repository, $exo_modal_settings, $exo_modal_generator);
    $this->providerManager = $provider_manager;
    $this->token = $token;
    $this->thumbnailFormatter = $thumbnail_formatter;
    $this->videoFormatter = $video_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $formatter_manager = $container->get('plugin.manager.field.formatter');
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
      $container->get('video_embed_field.provider_manager'),
      $container->get('token'),
      $formatter_manager->createInstance('video_embed_field_thumbnail', $configuration),
      $formatter_manager->createInstance('video_embed_field_video', $configuration)
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return parent::defaultSettings() + Thumbnail::defaultSettings() + Video::defaultSettings() + [
      'resolution' => '16:9',
      'type' => 'image',
      'text' => 'View Video',
      'icon' => 'regular-play-circle',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    $field_name = $this->fieldDefinition->getName();

    $form['modal']['settings']['trigger']['#access'] = FALSE;

    $form['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Trigger Type'),
      '#options' => [
        'image' => $this->t('Image'),
        'text' => $this->t('Text and Icon'),
      ],
      '#default_value' => $this->getSetting('type'),
      '#required' => TRUE,
    ];
    $image_visibility = [
      'visible' => [
        ':input[name="fields[' . $field_name . '][settings_edit_form][settings][type]"]' => ['value' => 'image'],
      ],
    ];
    $text_visibility = [
      'visible' => [
        ':input[name="fields[' . $field_name . '][settings_edit_form][settings][type]"]' => ['value' => 'text'],
      ],
    ];

    $form += $this->thumbnailFormatter->settingsForm([], $form_state);
    $form['image_style']['#states'] = $image_visibility;
    $form['link_image_to']['#access'] = FALSE;

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

    // We use resolution instead of the videoFormatter settings.
    // $form += $this->videoFormatter->settingsForm([], $form_state);.
    $form['resolution'] = [
      '#title' => $this->t('Resolution'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('resolution'),
      '#options' => [
        '16:9' => '16:9',
        '4:3' => '4:3',
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    switch ($this->getSetting('type')) {
      case 'image':
        $summary[] = $this->t('Image that launches an aside.');
        $summary[] = implode(',', $this->videoFormatter->settingsSummary());
        break;

      case 'text':
        $summary[] = $this->t('Text that launches an aside.');
        break;
    }
    if ($value = $this->getSetting('text')) {
      $summary[] = $this->t('Text: @value', ['@value' => $value]);
    }
    if (($value = $this->getSetting('icon'))) {
      $summary[] = $this->t('Icon: @value', ['@value' => $this->icon()->setIcon($value)]);
    }
    $summary[] = implode(',', $this->thumbnailFormatter->settingsSummary());
    $summary[] = $this->t('Resolution: @value', ['@value' => $this->getSetting('resolution')]);
    return $summary;
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
          '#prefix' => '<div class="exo-video-text"><div class="exo-video-text-inner">',
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
  public function viewElements(FieldItemListInterface $items, $langcode) {
    if ($this->getSetting('type') == 'image') {
      $this->thumbnails = $this->thumbnailFormatter->viewElements($items, $langcode);
    }
    return parent::viewElements($items, $langcode);
  }

  /**
   * {@inheritdoc}
   */
  protected function viewModalElement(ExoModalInterface $modal, FieldItemInterface $item, $delta, $langcode) {
    $element = [];
    $provider = $this->providerManager->loadProviderFromInput($item->value);
    if (!$provider) {
      $element = ['#theme' => 'video_embed_field_missing_provider'];
    }
    else {
      $autoplay = $this->getSetting('autoplay');
      $element = $provider->renderEmbedCode($this->getSetting('width'), $this->getSetting('height'), $autoplay);
      $element['#cache']['contexts'][] = 'user.permissions';

      $resolution = explode(':', $this->getSetting('resolution'));
      $ratio = $resolution[0] / $resolution[1];
      $modal_settings = $this->getSetting('modal');

      // Use iframe functionality if possible.
      if (isset($element['#url'])) {
        $modal->setSetting(['modal', 'iframe'], TRUE);
        $modal->setSetting(['modal', 'iframeURL'], $element['#url']);
        $modal->setSetting(['modal', 'iframeWidth'], 800);
        $modal->setSetting(['modal', 'iframeHeight'], 800 * $ratio . 'px');
        $modal->setSetting(['modal', 'iframeResponsive'], TRUE);
        $element = [];
      }
      else {
        $element = [
          '#type' => 'container',
          '#attributes' => ['class' => [Html::cleanCssIdentifier(sprintf('video-embed-field-provider-%s', $provider->getPluginId()))]],
          'children' => $element,
        ];

        // For responsive videos, wrap each field item in it's own container.
        if ($this->getSetting('responsive')) {
          $element['#attached']['library'][] = 'video_embed_field/responsive-video';
          $element['#attributes']['class'][] = 'video-embed-field-responsive-video';
        }
      }
    }
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return parent::calculateDependencies() + $this->videoFormatter->calculateDependencies();
  }

  /**
   * {@inheritdoc}
   */
  public function onDependencyRemoval(array $dependencies) {
    $parent = parent::onDependencyRemoval($dependencies);
    $video = $this->videoFormatter->onDependencyRemoval($dependencies);
    return $parent || $video;
  }

  /**
   * Return the video formatter.
   */
  public function getVideoFormatter() {
    return $this->videoFormatter;
  }

}
