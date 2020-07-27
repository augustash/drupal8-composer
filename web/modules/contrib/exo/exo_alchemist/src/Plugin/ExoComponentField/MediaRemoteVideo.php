<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\exo_alchemist\ExoComponentValue;

/**
 * A 'media' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "media_remote_video",
 *   label = @Translation("Media: Remote Video"),
 *   properties = {
 *     "url" = @Translation("The absolute url of the video."),
 *     "embed" = @Translation("The embed code."),
 *     "thumbnailUrl" = @Translation("The thumbnail URL"),
 *     "thumbnailHeight" = @Translation("The thumbnail height"),
 *     "thumbnailWidth" = @Translation("The thumbnail width"),
 *     "title" = @Translation("The title of the video."),
 *   },
 *   provider = "media",
 * )
 */
class MediaRemoteVideo extends MediaBase {

  /**
   * Get the entity type.
   */
  protected function getEntityTypeBundles() {
    return ['remote_video' => 'remote_video'];
  }

  /**
   * {@inheritdoc}
   */
  public function validateValue(ExoComponentValue $value) {
    parent::validateValue($value);
    if ($value->get('value')) {
      $value->set('path', $value->get('value'));
      $value->unset('value');
    }
    if (!$value->has('path')) {
      throw new PluginException(sprintf('eXo Component Field plugin (%s) requires [default.path] be set.', $value->getDefinition()->getType()));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    $properties = parent::propertyInfo();
    $properties['url'] = $this->t('The video url.');
    $properties['embed'] = $this->t('The video embed code.');
    $properties['thumbnailUrl'] = $this->t('The video thumbnail URL.');
    $properties['thumbnailHeight'] = $this->t('The video thumbnail height.');
    $properties['thumbnailWidth'] = $this->t('The video thumbnail width.');
    $properties['title'] = $this->t('The video title.');
    if ($this->moduleHandler()->moduleExists('exo_video')) {
      $properties['background'] = $this->t('The video as a background.');
    }
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function viewValue(FieldItemInterface $item, $delta, array $contexts) {
    $media = $item->entity;
    if ($media) {
      $field = $this->getFieldDefinition();
      $source_field_definition = $media->getSource()->getSourceFieldDefinition($media->bundle->entity);
      $url = $media->{$source_field_definition->getName()}->value;
      /** @var \Drupal\media\OEmbed\UrlResolverInterface $url_resolver */
      $url_resolver = \Drupal::service('media.oembed.url_resolver');
      $resource_url = $url_resolver->getResourceUrl($url);
      $provider = $url_resolver->getProviderByUrl($url);
      /** @var \Drupal\media\OEmbed\Resource $resource */
      $resource = \Drupal::service('media.oembed.resource_fetcher')->fetchResource($resource_url);
      if ($resource) {
        $value = [
          'url' => $url,
          'embed' => $resource->getHtml(),
          'thumbnailUrl' => $resource->getThumbnailUrl(),
          'thumbnailHeight' => $resource->getThumbnailHeight(),
          'thumbnailWidth' => $resource->getThumbnailWidth(),
          'title' => $resource->getTitle(),
        ];
        if ($this->moduleHandler()->moduleExists('exo_video')) {
          $settings = $field->getAdditionalValue('video_bg_settings') ?: [];
          $value['background'] = [
            '#type' => 'exo_video_bg',
            '#video_provider' => $provider->getName(),
            '#video_url' => $url,
          ];
          if ($thumbnail = $resource->getThumbnailUrl()) {
            $value['background']['#video_image'] = $thumbnail->toString();
          }
          foreach ($settings as $key => $val) {
            $value['background']['#' . $key] = $val;
          }
          if ($this->isLayoutBuilder($contexts)) {
            $value['background']['#attributes']['class'][] = 'component-passthrough';
          }
        }
      }
      return $value;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function setMediaValue(ExoComponentValue $value, FieldItemInterface $item = NULL) {
    return [
      'value' => $value->get('path'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultValue($delta = 0) {
    return [
      'name' => 'Example Video',
      'path' => 'https://vimeo.com/171918951',
    ];
  }

}
