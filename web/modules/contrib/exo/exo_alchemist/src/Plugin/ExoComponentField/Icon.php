<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Core\Field\FieldItemInterface;
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
  public function getStorageConfig() {
    return [
      'type' => 'icon',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getWidgetConfig() {
    return [
      'type' => 'icon',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    return [
      'value' => $this->t('The raw icon value.'),
      'render' => $this->t('The formatted icon.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultValue($delta = 0) {
    return [
      'value' => 'regular-smile',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function viewValue(FieldItemInterface $item, $delta, array $contexts) {
    return [
      'value' => $item->value,
      'render' => $this->icon()->setIcon($item->value)->toRenderable(),
    ];
  }

}
