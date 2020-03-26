<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\exo_alchemist\Definition\ExoComponentDefinitionField;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldFieldableBase;

/**
 * A 'text' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "text",
 *   label = @Translation("Text")
 * )
 */
class Text extends ExoComponentFieldFieldableBase {

  /**
   * {@inheritdoc}
   */
  public function componentStorage(ExoComponentDefinitionField $field) {
    return [
      'type' => 'string',
      'settings' => [
        'max_length' => '255',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function componentWidget(ExoComponentDefinitionField $field) {
    return [
      'type' => 'string_textfield',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function componentPropertyInfo(ExoComponentDefinitionField $field) {
    return [
      'value' => $this->t('The string value.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function componentViewValue(ExoComponentDefinitionField $field, FieldItemInterface $item, $delta, $is_layout_builder) {
    return [
      'value' => $item->value,
    ];
  }

}
