<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\exo_alchemist\Definition\ExoComponentDefinitionField;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldComputedBase;

/**
 * A 'webform' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "webform",
 *   label = @Translation("Webform"),
 *   computed = TRUE
 * )
 */
class Webform extends ExoComponentFieldComputedBase {

  /**
   * {@inheritdoc}
   */
  public function componentProcessDefinition(ExoComponentDefinitionField $field) {
    parent::componentProcessDefinition($field);
    if (!$field->hasAdditionalValue('webform_id')) {
      throw new PluginException(sprintf('eXo Component Field plugin (%s) requires [webform_id] be set.', $field->getType()));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function componentPropertyInfo(ExoComponentDefinitionField $field) {
    $properties = [
      'render' => $this->t('The webform renderable.'),
    ];
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function componentViewValue(ExoComponentDefinitionField $field, $delta, $is_layout_builder) {
    $output = $is_layout_builder ? $this->componentPlaceholder($this->t('Webform Placeholder')) : [
      '#type' => 'webform',
      '#webform' => $field->getAdditionalValue('webform_id'),
      '#default_data' => [],
    ];
    return [
      'render' => $output,
    ];
  }

}
