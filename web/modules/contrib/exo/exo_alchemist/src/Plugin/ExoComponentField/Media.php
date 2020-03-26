<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\exo_alchemist\Definition\ExoComponentDefinitionField;
use Drupal\exo_alchemist\Definition\ExoComponentDefinitionFieldPreview;

/**
 * A 'media' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "media",
 *   label = @Translation("Media"),
 *   provider = "media",
 * )
 */
class Media extends MediaBase {

  /**
   * {@inheritdoc}
   */
  public function componentProcessDefinition(ExoComponentDefinitionField $field) {
    parent::componentProcessDefinition($field);
    $exo_field_manager = \Drupal::service('plugin.manager.exo_component_field');
    $previews = [];
    foreach ($field->getPreviews() as $delta => $preview) {
      $component_field_id = 'media_' . $preview->getValue('bundle');
      if ($component_field = $exo_field_manager->loadInstance($component_field_id)) {
        $temp_field = clone $field;
        $temp_field->setPreviews([$preview->toArray()]);
        $component_field->componentProcessDefinition($temp_field);
        $previews[] = $temp_field->getPreviews()[0]->toArray();
      }
    }
    $field->setPreviews($previews);
  }

  /**
   * {@inheritdoc}
   */
  public function componentViewValue(ExoComponentDefinitionField $field, FieldItemInterface $item, $delta, $is_layout_builder) {
    $output = [];
    if ($item->entity) {
      $component_field_id = 'media_' . $item->entity->bundle();
      if ($component_field = \Drupal::service('plugin.manager.exo_component_field')->loadInstance($component_field_id)) {
        $media = $item->entity;
        $output = $component_field->componentViewValue($field, $item, 0, $is_layout_builder) + ['bundle' => $media->bundle()];
      }
    }
    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function componentPropertyInfo(ExoComponentDefinitionField $field) {
    $properties = [
      'bundle' => $this->t('The bundle type.'),
    ];
    $component_field_manager = \Drupal::service('plugin.manager.exo_component_field');
    foreach (\Drupal::entityTypeManager()->getStorage('media_type')->loadMultiple($this->getEntityTypeBundles($field)) as $bundle => $media_type) {
      $component_field_id = 'media_' . $bundle;
      if ($component_field = $component_field_manager->loadInstance($component_field_id, FALSE)) {
        $properties += array_map(function ($description) use ($media_type) {
          return $media_type->label() . ': ' . $description;
        }, $component_field->componentPropertyInfo($field));
      }
    }
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  protected function componentEntity(ExoComponentDefinitionFieldPreview $preview, FieldItemInterface $item = NULL) {
    $entity = NULL;
    $component_field_id = 'media_' . $preview->getValue('bundle');
    if ($component_field = \Drupal::service('plugin.manager.exo_component_field')->loadInstance($component_field_id)) {
      $entity = $component_field->componentEntity($preview, $item);
    }
    return $entity;
  }

}
