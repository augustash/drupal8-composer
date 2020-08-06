<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentEnhancement;

use Drupal\Component\Utility\Html;
use Drupal\exo_alchemist\ExoComponentAttribute;
use Drupal\exo_alchemist\Plugin\ExoComponentEnhancementBase;

/**
 * A 'tabs' enhancer for exo components.
 *
 * @ExoComponentEnhancement(
 *   id = "tabs",
 *   label = @Translation("Tabs"),
 * )
 */
class Tabs extends ExoComponentEnhancementBase {

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    return [
      'wrapper' => $this->t('Attributes that should be added to a wrapper element that contains elements that will be tabs.'),
      'trigger' => $this->t('Attributes that should be added to each element that should act as a trigger for the toggle. Use [data-ee--tab-id="ID"] to associate with content of same id.'),
      'content' => $this->t('Attributes that should be added to each element that should expand/contract. Use [data-ee--tab-id="ID"] to associate with trigger of same id.'),
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
        'library' => ['exo_alchemist/enhancement.tabs'],
      ],
      'wrapper' => new ExoComponentAttribute([
        'class' => ['ee--tabs-wrapper'],
        'data-ee--tabs-id' => $id,
      ], $is_layout_builder),
      'trigger' => new ExoComponentAttribute([
        'class' => ['ee--tabs-trigger'],
        'data-ee--tabs-id' => $id,
      ], $is_layout_builder),
      'content' => new ExoComponentAttribute([
        'class' => ['ee--tabs-content'],
        'data-ee--tabs-id' => $id,
      ], $is_layout_builder),
    ];
    if ($is_layout_builder) {
      $view['trigger']->events(TRUE);
    }
    return $view;
  }

}
