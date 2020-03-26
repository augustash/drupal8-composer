<?php

/**
 * @file
 * Builds placeholder replacement tokens for exo-media-related data.
 */

use Drupal\Core\Render\BubbleableMetadata;

/**
 * Impements hook_token_info_alter().
 */
function exo_media_token_info_alter(&$data) {
  $data['tokens']['node']['exo_media_smart_image'] = [
    'name' => t('eXo Media: Smart Image URL'),
    'description' => t('Will determine the best image to use for representing this entity.'),
    'type' => 'url',
  ];
}

/**
 * Implements hook_tokens().
 */
function exo_media_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $token_service = \Drupal::token();
  $replacements = [];

  if ($type == 'node' && !empty($data['node'])) {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $data['node'];
    if ($found_tokens = $token_service->findWithPrefix($tokens, 'exo_media_smart_image')) {
      $data = exo_media_smart_image($node);
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
