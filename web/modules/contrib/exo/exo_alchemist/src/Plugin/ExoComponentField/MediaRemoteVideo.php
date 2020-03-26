<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\exo_alchemist\Definition\ExoComponentDefinitionField;
use Drupal\exo_alchemist\Definition\ExoComponentDefinitionFieldPreview;

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
  protected function getEntityTypeBundles(ExoComponentDefinitionField $field) {
    return ['remote_video' => 'remote_video'];
  }

  /**
   * {@inheritdoc}
   */
  public function componentProcessDefinition(ExoComponentDefinitionField $field) {
    parent::componentProcessDefinition($field);
    if (!$field->hasPreviewPropertyOnAll('path')) {
      throw new PluginException(sprintf('eXo Component Field plugin (%s) requires [preview.path] be set.', $field->getType()));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function componentPropertyInfo(ExoComponentDefinitionField $field) {
    $properties = parent::componentPropertyInfo($field);
    if ($this->moduleHandler()->moduleExists('exo_video')) {
      $properties['background'] = 'The video as a background.';
    }
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function componentViewValue(ExoComponentDefinitionField $field, FieldItemInterface $item, $delta, $is_layout_builder) {
    $media = $item->entity;
    if ($media) {
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
          if ($is_layout_builder) {
            $value['background']['#attributes']['class'][] = 'component-passthrough';
          }
        }
      }
      return $value;
    }
  }

  /**
   * Extending classes can use this method to set individual values.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionFieldPreview $preview
   *   The field preview.
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   The current item.
   *
   * @return mixed
   *   A value suitable for setting to \Drupal\Core\Field\FieldItemInterface.
   */
  protected function componentMediaValue(ExoComponentDefinitionFieldPreview $preview, FieldItemInterface $item = NULL) {
    return [
      'value' => $preview->getValue('path'),
    ];
  }

}
