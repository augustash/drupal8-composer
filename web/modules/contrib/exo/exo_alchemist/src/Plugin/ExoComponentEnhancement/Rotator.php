<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentEnhancement;

use Drupal\exo_alchemist\ExoComponentAttribute;
use Drupal\exo_alchemist\Plugin\ExoComponentEnhancementBase;

/**
 * A 'rotator' enhancer for exo components.
 *
 * @ExoComponentEnhancement(
 *   id = "rotator",
 *   label = @Translation("Rotator"),
 * )
 */
class Rotator extends ExoComponentEnhancementBase {

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    return [
      'wrapper' => $this->t('Attributes that should be added to a wrapper element that contains elements that will be rotated.'),
      'item' => $this->t('Attributes that should be added to each element that should be rotated.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function view(array $contexts) {
    $is_layout_builder = $this->isLayoutBuilder($contexts);
    return [
      '#attached' => [
        'library' => ['exo_alchemist/enhancement.rotator'],
      ],
      'wrapper' => new ExoComponentAttribute([
        'class' => ['exo-enhancement--rotator-wrapper'],
        'data-rotator-speed' => $this->getEnhancementDefinition()->getAdditionalValue('speed'),
      ], $is_layout_builder),
      'item' => new ExoComponentAttribute(['class' => ['exo-enhancement--rotator-item']], $is_layout_builder),
    ];
  }

}
