<?php

/**
 * @file
 * Builds placeholder replacement tokens for exo-media-related data.
 */

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Impements hook_token_info_alter().
 */
function exo_alchemist_token_info_alter(&$data) {
  $data['tokens']['node']['exo_alchemist_smart_image'] = [
    'name' => t('eXo Media: Smart Image URL'),
    'description' => t('Will determine the best image to use for representing this entity. Example: [node:exo_alchemist_smart_image:meta:url]'),
    'type' => 'url',
  ];
}

/**
 * Implements hook_tokens().
 */
function exo_alchemist_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $token_service = \Drupal::token();
  $replacements = [];

  if ($type == 'node' && !empty($data['node'])) {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $data['node'];
    if ($found_tokens = $token_service->findWithPrefix($tokens, 'exo_alchemist_smart_image')) {
      $data = exo_alchemist_smart_image($node);
      if ($data) {
        $entity_type = $data['entity']->getEntityTypeId();
        $smart_tokens = [];
        foreach ($found_tokens as $key => $value) {
          $smart_tokens[$data['fieldname'] . ':' . $key] = '[' . $entity_type . ':' . $data['fieldname'] . ':entity:' . $key . ']';
        }
        $smart_replacements = $token_service->generate($entity_type, $smart_tokens, [$entity_type => $data['entity']], $options, $bubbleable_metadata);
        $replacements += array_combine($found_tokens, $smart_replacements);
      }
      else {
        // Handle fallbacks.
        // @TODO Open this open to other modules.
        if (\Drupal::moduleHandler()->moduleExists('real_favicon')) {
          $favicon = real_favicon_load_by_theme();
          if ($favicon) {
            foreach ($found_tokens as $key => $value) {
              $replacements[$value] = file_create_url($favicon->getManifestLargeImage());
            }
          }
        }
      }
    }
  }
  return $replacements;
}

/**
 * Given an entity, find the best image file entity.
 *
 * @param \Drupal\Core\Entity\ContentEntityInterface $entity
 *   The entity.
 */
function exo_alchemist_smart_image(ContentEntityInterface $entity) {
  $data = [];
  if (function_exists('exo_media_smart_image')) {
    $data = exo_media_smart_image($entity);
  }
  if (empty($data)) {
    /** @var \Drupal\exo_alchemist\ExoComponentRepository $repository */
    $repository = \Drupal::service('exo_alchemist.repository');
    $items = $repository->getComponentItemsByFieldType($entity, 'media_image');
    if ($items) {
      $data = exo_alchemist_smart_image_find($items);
    }
  }
  return $data;
}

/**
 * Given a field, locate an image field.
 */
function exo_alchemist_smart_image_find(FieldItemListInterface $field, $path = []) {
  if (!$field->isEmpty()) {
    switch ($field->getFieldDefinition()->getType()) {
      case 'entity_reference_revisions':
      case 'entity_reference':
        $subpath = $path;
        $subpath[] = $field->getName() . ':0:entity';
        $entity = $field->first()->entity;
        if ($entity && $entity instanceof ContentEntityInterface) {
          $fields = $entity->getFields(FALSE);
          uasort($fields, function ($a, $b) {
            return substr($a->getName(), -3) === '_fg' ? -1 : 1;
          });
          foreach ($fields as $subfield) {
            $image = exo_media_smart_image_find($subfield, $subpath);
            if ($image) {
              return $image;
            }
          }
        }
        break;

      case 'image':
        return [
          'fieldname' => $field->getName(),
          'entity' => $field->getEntity(),
          'path' => $path,
        ];
    }
  }
  return NULL;
}
