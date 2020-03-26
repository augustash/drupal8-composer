<?php

namespace Drupal\exo\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceLabelFormatter;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'entity reference comma' formatter.
 *
 * @FieldFormatter(
 *   id = "entity_reference_comma",
 *   label = @Translation("Label with Comma"),
 *   description = @Translation("Display the label of the referenced entities seperated with a comma."),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class ExoEntityReferenceCommaFormatter extends EntityReferenceLabelFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    foreach ($elements as $delta => &$element) {
      if ($delta != count($elements) - 1) {
        $element['#suffix'] = ', ';
      }
    }
    return $elements;
  }

}
