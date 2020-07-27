<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\exo_icon\ExoIconTranslationTrait;
use Drupal\file\FileInterface;
use Drupal\media\MediaInterface;

/**
 * A 'media' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "media_document",
 *   label = @Translation("Media: Document"),
 *   properties = {
 *     "url" = @Translation("The absolute url of the document."),
 *     "title" = @Translation("The title of the document."),
 *   },
 *   provider = "media",
 * )
 */
class MediaDocument extends MediaFileBase {
  use ExoIconTranslationTrait;

  /**
   * Get the entity type.
   */
  protected function getEntityTypeBundles() {
    return ['document' => 'document'];
  }

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    $properties = parent::propertyInfo();
    $properties['icon'] = $this->t('The file type icon.');
    $properties['icon_render'] = $this->t('The rendered file type icon.');
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  protected function viewFileValue(MediaInterface $media, FileInterface $file) {
    $icon = \Drupal::service('exo_icon.mime_manager')->getMimeIcon($file->getMimeType());
    return [
      'icon' => $icon,
      'icon_render' => $this->icon()->setIcon($icon)->toRenderable(),
    ] + parent::viewFileValue($media, $file);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultValue($delta = 0) {
    return [
      'name' => 'Example File',
      'path' => drupal_get_path('module', 'exo_alchemist') . '/documents/default.pdf',
    ];
  }

}
