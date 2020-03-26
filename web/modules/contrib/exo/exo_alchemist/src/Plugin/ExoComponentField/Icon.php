<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\exo_alchemist\Definition\ExoComponentDefinitionField;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldFieldableBase;
use Drupal\exo_icon\ExoIconTranslationTrait;

/**
 * A 'icon' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "icon",
 *   label = @Translation("Icon")
 * )
 */
class Icon extends ExoComponentFieldFieldableBase {
  use ExoIconTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function componentStorage(ExoComponentDefinitionField $field) {
    return [
      'type' => 'icon',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function componentWidget(ExoComponentDefinitionField $field) {
    return [
      'type' => 'icon',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function componentPropertyInfo(ExoComponentDefinitionField $field) {
    return [
      'value' => $this->t('The raw icon value.'),
      'formatted' => $this->t('The formatted icon.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function componentViewValue(ExoComponentDefinitionField $field, FieldItemInterface $item, $delta, $is_layout_builder) {
    return [
      'value' => $item->value,
      'formatted' => $this->icon()->setIcon($item->value)->toRenderable(),
    ];
  }

}
