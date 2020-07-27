<?php

namespace Drupal\exo_imagine\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\exo_media\Plugin\Field\FieldFormatter\ExoMediaFormatterTrait;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;

/**
 * Plugin implementation of the 'exo image media' formatter.
 *
 * @FieldFormatter(
 *   id = "exo_imagine_media",
 *   label = @Translation("eXo Image"),
 *   provider = "exo_media",
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class ExoImagineMediaFormatter extends ExoImagineFormatter {
  use ExoMediaFormatterTrait;

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    foreach ($elements as $delta => $element) {
      $elements[$delta]['#item_attributes']['class'][] = 'exo-image';
    }
    return $elements;
  }

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
    $entities = $this->mediaGetEntitiesToView($items, $langcode);
    $entities = $this->filterSelectionEntities($entities);
    return $entities;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    if (!self::mediaIsApplicable($field_definition)) {
      return FALSE;
    }

    return parent::isApplicable($field_definition);
  }

}
