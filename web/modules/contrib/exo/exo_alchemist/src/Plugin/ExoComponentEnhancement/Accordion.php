<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentEnhancement;

use Drupal\Component\Utility\Html;
use Drupal\exo_alchemist\ExoComponentAttribute;
use Drupal\exo_alchemist\Plugin\ExoComponentEnhancementBase;

/**
 * A 'accordion' enhancer for exo components.
 *
 * @ExoComponentEnhancement(
 *   id = "accordion",
 *   label = @Translation("Accordion"),
 * )
 */
class Accordion extends ExoComponentEnhancementBase {

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    return [
      'wrapper' => $this->t('Attributes that should be added to a wrapper element that contains elements that will be toggleable.'),
      'item' => $this->t('Attributes that should be added to each element that should be toggleable.'),
      'trigger' => $this->t('Attributes that should be added to each element that should act as a trigger for the toggle.'),
      'content' => $this->t('Attributes that should be added to each element that should expand/contract.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function view(array $contexts) {
    $id = Html::getId($this->getEnhancementDefinition()->id());
    $is_layout_builder = $this->isLayoutBuilder($contexts);
    $view = [
      '#attached' => [
        'library' => ['exo_alchemist/enhancement.accordion'],
      ],
      'wrapper' => new ExoComponentAttribute([
        'class' => ['ee--accordion-wrapper'],
        'data-ee--accordion-id' => $id,
      ], $is_layout_builder),
      'item' => new ExoComponentAttribute([
        'class' => ['ee--accordion-item'],
        'data-ee--accordion-id' => $id,
      ], $is_layout_builder),
      'trigger' => new ExoComponentAttribute([
        'class' => ['ee--accordion-trigger'],
        'data-ee--accordion-id' => $id,
      ], $is_layout_builder),
      'content' => new ExoComponentAttribute([
        'class' => ['ee--accordion-content'],
        'data-ee--accordion-id' => $id,
      ], $is_layout_builder),
    ];
    if ($is_layout_builder) {
      $view['trigger']->events(TRUE);
    }
    return $view;
  }

}
