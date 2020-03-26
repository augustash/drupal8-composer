<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\exo_alchemist\Definition\ExoComponentDefinitionField;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldComputedBase;
use Drupal\views\Views;

/**
 * A 'view' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "view",
 *   label = @Translation("View"),
 *   computed = TRUE
 * )
 */
class View extends ExoComponentFieldComputedBase {

  /**
   * {@inheritdoc}
   */
  public function componentProcessDefinition(ExoComponentDefinitionField $field) {
    parent::componentProcessDefinition($field);
    if (!$field->hasAdditionalValue('view_id')) {
      throw new PluginException(sprintf('eXo Component Field plugin (%s) requires [view_id] be set.', $field->getType()));
    }
    if (!$field->hasAdditionalValue('view_display')) {
      $field->setAdditionalValue('view_display', 'default');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function componentPropertyInfo(ExoComponentDefinitionField $field) {
    $properties = [
      'render' => $this->t('The view renderable.'),
    ];
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function componentViewValue(ExoComponentDefinitionField $field, $delta, $is_layout_builder) {
    $view = Views::getView($field->getAdditionalValue('view_id'));
    $view->setDisplay($field->getAdditionalValue('view_display'));
    if ($args = $field->getAdditionalValue('view_args')) {
      $view->setArguments($args);
    }
    return [
      'render' => $view->buildRenderable(),
    ];
  }

}
