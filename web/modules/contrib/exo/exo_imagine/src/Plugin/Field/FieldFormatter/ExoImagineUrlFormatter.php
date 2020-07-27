<?php

namespace Drupal\exo_imagine\Plugin\Field\FieldFormatter;

use Drupal\image\Plugin\Field\FieldFormatter\ImageUrlFormatter;
use Drupal\exo_media\Plugin\Field\FieldFormatter\ExoMediaFormatterTrait;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;

/**
 * Plugin implementation of the 'image_url' formatter.
 *
 * @FieldFormatter(
 *   id = "exo_imagine_url",
 *   label = @Translation("URL to image"),
 *   provider = "exo_media",
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class ExoImagineUrlFormatter extends ImageUrlFormatter {
  use ExoMediaFormatterTrait;

  /**
   * {@inheritdoc}
   */
  protected function needsEntityLoad(EntityReferenceItem $item) {
    return $this->mediaNeedsEntityLoad($item);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntitiesToView(EntityReferenceFieldItemListInterface $items, $langcode) {
    return $this->mediaGetEntitiesToView($items, $langcode);
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    if (!self::mediaIsApplicable($field_definition)) {
      return FALSE;
    }

    $storage = \Drupal::service('entity_type.manager')->getStorage('media_type');
    $settings = $field_definition->getSetting('handler_settings');
    if (isset($settings['target_bundles'])) {
      foreach ($settings['target_bundles'] as $bundle) {
        if ($storage->load($bundle)->getSource()->getPluginId() !== 'image') {
          return FALSE;
        }
      }
    }
    return parent::isApplicable($field_definition);
  }

}
