<?php

namespace Drupal\exo_video\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * Plugin implementation of the thumbnail field formatter.
 *
 * @FieldFormatter(
 *   id = "exo_video_media_modal",
 *   label = @Translation("eXo Video Modal"),
 *   provider = "exo_modal",
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class ExoVideoMediaModal extends ExoVideoModal {

  /**
   * {@inheritdoc}
   */
  public function view(FieldItemListInterface $items, $langcode = NULL) {
    $entities = $items->getEntity()->{$this->fieldDefinition->getName()}->referencedEntities();
    $entity = reset($entities);
    if (!empty($entity)) {
      $items = $entity->field_media_video_embed_field;
    }
    return parent::view($items, $langcode);
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    if (\Drupal::service('module_handler')->moduleExists('video_embed_field')) {
      return FALSE;
    }
    return parent::isApplicable($field_definition);
  }

}
