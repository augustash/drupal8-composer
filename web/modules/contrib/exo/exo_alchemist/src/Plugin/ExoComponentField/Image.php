<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\exo_alchemist\Definition\ExoComponentDefinitionField;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldFieldableBase;

/**
 * A 'image' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "image",
 *   label = @Translation("Image"),
 *   storage = {
 *     "type" = "image",
 *   },
 *   widget = {
 *     "type" = "image_image",
 *   },
 * )
 */
class Image extends ExoComponentFieldFieldableBase {

  /**
   * {@inheritdoc}
   */
  public function componentField(ExoComponentDefinitionField $field) {
    return [
      'settings' => [
        'file_directory' => $field->getType() . '/' . $field->getName(),
        'file_extensions' => 'png gif jpg jpeg',
      ],
    ];
  }

}
