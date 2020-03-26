<?php

namespace Drupal\exo_alchemist\Plugin;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Render\Element;
use Drupal\exo_alchemist\Definition\ExoComponentDefinitionField;
use Drupal\exo_alchemist\Definition\ExoComponentDefinitionFieldPreview;

/**
 * Base class for Component Field plugins.
 */
abstract class ExoComponentFieldFieldableBase extends ExoComponentFieldBase implements ExoComponentFieldFieldableInterface {

  /**
   * {@inheritdoc}
   */
  public function componentStorage(ExoComponentDefinitionField $field) {
    return $this->pluginDefinition['storage'];
  }

  /**
   * {@inheritdoc}
   */
  public function componentField(ExoComponentDefinitionField $field) {
    return $this->pluginDefinition['field'];
  }

  /**
   * {@inheritdoc}
   */
  public function componentWidget(ExoComponentDefinitionField $field) {
    return $this->pluginDefinition['widget'];
  }

  /**
   * {@inheritdoc}
   */
  public function componentFormatter(ExoComponentDefinitionField $field) {
    return $this->pluginDefinition['formatter'];
  }

  /**
   * {@inheritdoc}
   */
  public function componentPreUpdate(ExoComponentDefinitionField $field, FieldItemListInterface $items) {
  }

  /**
   * {@inheritdoc}
   */
  public function componentValues(ExoComponentDefinitionField $field, FieldItemListInterface $items) {
    $values = [];
    $previews = $field->getPreviews();
    foreach ($items as $delta => $item) {
      // If we do not have a incoming preview for an item, we want to clean it
      // as if we are uninstalling it.
      $update = isset($previews[$delta]);
      $this->componentValueClean($field, $item, $update);
    }
    foreach ($previews as $delta => $preview) {
      $item = $items->offsetExists($delta) ? $items->get($delta) : NULL;
      $values[$delta] = $this->componentValue($preview, $item);
    }
    return $values;
  }

  /**
   * Extending classes can use this method to set individual values.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionFieldPreview $preview
   *   The field preview.
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   The current item.
   *
   * @return mixed
   *   A value suitable for setting to \Drupal\Core\Field\FieldItemInterface.
   */
  protected function componentValue(ExoComponentDefinitionFieldPreview $preview, FieldItemInterface $item = NULL) {
    return $preview->toArray();
  }

  /**
   * Extending classes can use this method to clean existing values.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField $field
   *   The eXo component field.
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   The field item.
   * @param bool $update
   *   TRUE if called when updating.
   */
  protected function componentValueClean(ExoComponentDefinitionField $field, FieldItemInterface $item, $update = TRUE) {
  }

  /**
   * {@inheritdoc}
   */
  public function componentClone(ExoComponentDefinitionField $field, FieldItemListInterface $items) {
    foreach ($items as $item) {
      $item->setValue($this->componentCloneValue($field, $item));
    }
  }

  /**
   * Extending classes can use this method to clone existing values.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField $field
   *   The eXo component field.
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   The field item.
   *
   * @return mixed
   *   A value suitable for setting to \Drupal\Core\Field\FieldItemInterface.
   */
  protected function componentCloneValue(ExoComponentDefinitionField $field, FieldItemInterface $item) {
    return $item->getValue();
  }

  /**
   * {@inheritdoc}
   */
  public function componentRestore(ExoComponentDefinitionField $field, FieldItemListInterface $items) {
    $values = [];
    if ($items->isEmpty()) {
      $values = $this->componentValues($field, $items);
    }
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function componentView(ExoComponentDefinitionField $field, FieldItemListInterface $items, $is_layout_builder) {
    $output = [];
    if ($items->count()) {
      foreach ($items as $delta => $item) {
        $output[$delta] = $this->componentViewValue($field, $item, $delta, $is_layout_builder);
      }
    }
    elseif ($value = $this->componentViewEmptyValue($field, $is_layout_builder)) {
      $output[0] = $value;
    }
    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function componentViewValue(ExoComponentDefinitionField $field, FieldItemInterface $item, $delta, $is_layout_builder) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function componentViewEmptyValue(ExoComponentDefinitionField $field, $is_layout_builder) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function componentUpdate(ExoComponentDefinitionField $field, FieldItemListInterface $items) {}

  /**
   * {@inheritdoc}
   */
  public function componentDelete(ExoComponentDefinitionField $field, FieldItemListInterface $items) {
  }

  /**
   * {@inheritdoc}
   */
  public function componentUninstall(ExoComponentDefinitionField $field, FieldItemListInterface $items) {
    foreach ($items as $item) {
      $this->componentValueClean($field, $item, FALSE);
    }
  }

}
