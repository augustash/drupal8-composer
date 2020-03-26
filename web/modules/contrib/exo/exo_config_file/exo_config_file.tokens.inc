<?php

/**
 * @file
 * Contains exo_config_file.tokens.inc.
 */

use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_token_info().
 */
function exo_config_file_token_info() {
  $type = [
    'name' => t('Exo Config File'),
    'description' => t('Tokens for Exo Config File creation.'),
  ];

  $tokens['id'] = [
    'name' => t("Entity ID"),
    'description' => t('The entity ID of the entity used during Exo Config File creation.'),
  ];

  $tokens['type'] = [
    'name' => t("Entity Type"),
    'description' => t('The entity type of the entity used during Exo Config File creation.'),
  ];

  $tokens['field_name'] = [
    'name' => t("Field Name"),
    'description' => t('The field name used during Exo Config File creation.'),
  ];

  return [
    'types' => ['exo_config_file' => $type],
    'tokens' => ['exo_config_file' => $tokens],
  ];
}

/**
 * Implements hook_tokens().
 */
function exo_config_file_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];

  if ($type == 'exo_config_file' && !empty($data['exo_config_file'])) {
    foreach ($tokens as $name => $original) {
      $replacements[$original] = $data['exo_config_file'][$name];
    }
  }

  return $replacements;
}
