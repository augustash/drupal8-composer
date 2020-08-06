<?php

namespace Drupal\exo_alchemist\Plugin;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\exo_alchemist\ExoComponentValue;
use Drupal\exo_alchemist\ExoComponentValues;

/**
 * Base class for Component Field plugins.
 */
abstract class ExoComponentFieldFieldableBase extends ExoComponentFieldBase implements ExoComponentFieldFieldableInterface, ExoComponentFieldFormInterface {

  use ExoComponentFieldFormTrait;

  /**
   * {@inheritdoc}
   */
  public function getStorageConfig() {
    return $this->pluginDefinition['storage'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldConfig() {
    return $this->pluginDefinition['field'];
  }

  /**
   * {@inheritdoc}
   */
  public function getWidgetConfig() {
    return $this->pluginDefinition['widget'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormatterConfig() {
    return $this->pluginDefinition['formatter'];
  }

  /**
   * {@inheritdoc}
   */
  public function onFieldClean(FieldItemListInterface $items, $update = TRUE) {
    foreach ($items as $delta => $item) {
      $this->cleanValue($item, $delta, $update);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function populateValues(ExoComponentValues $values, FieldItemListInterface $items) {
    $field = $values->getDefinition();
    // When an item is empty, we populate the defaults.
    if (!$field->getDefaults()) {
      $count = $field->getCardinality() > 1 ? $field->getCardinality() : 1;
      for ($delta = 0; $delta < $count; $delta++) {
        if ($value = $this->getDefaultValue($delta)) {
          $values->set($delta, $value);
        }
      }
    }
    foreach ($items as $delta => $item) {
      // If we do have incoming values for an item, we want to clean it
      // as if we are uninstalling it.
      $this->cleanValue($item, $delta, $values->has($delta));
    }
    return $this->getValues($values, $items);
  }

  /**
   * Check if defaults exist for this field.
   *
   * @return bool
   *   TRUE if defaults exist.
   */
  protected function hasDefault() {
    return $this->getFieldDefinition()->hasDefault();
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultValue($delta = 0) {
    return [];
  }

  /**
   * Extending classes can use this method to clean existing values.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   The field item.
   * @param int $delta
   *   The field item delta.
   * @param bool $update
   *   TRUE if called when updating.
   */
  protected function cleanValue(FieldItemInterface $item, $delta, $update = TRUE) {}

  /**
   * {@inheritdoc}
   */
  public function formSubmit(array $form, FormStateInterface $form_state) {
    $field = $this->getFieldDefinition();
    /** @var \Drupal\Core\Field\FieldItemListInterface $items */
    $items = $form_state->get('component_entity')->get($field->safeId());
    if ($items->isEmpty()) {
      // When a field has been set to "empty", we place back in the defaults
      // and then hide the field so that it can later be restored.
      $values = ExoComponentValues::fromFieldDefaults($field);
      $items->setValue($this->populateValues($values, $items));

    }
  }

  /**
   * {@inheritdoc}
   */
  public function getValues(ExoComponentValues $values, FieldItemListInterface $items) {
    $field_values = [];
    foreach ($values as $delta => $value) {
      $this->validateValue($value);
      $item = $items->offsetExists($delta) ? $items->get($delta) : NULL;
      $field_values[$delta] = $this->getValue($value, $item);
    }
    return $field_values;
  }

  /**
   * {@inheritdoc}
   */
  public function validateValue(ExoComponentValue $value) {
  }

  /**
   * Extending classes can use this method to set individual values.
   *
   * @param \Drupal\exo_alchemist\ExoComponentValue $value
   *   The field value.
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   The current item.
   *
   * @return mixed
   *   A value suitable for setting to \Drupal\Core\Field\FieldItemInterface.
   */
  protected function getValue(ExoComponentValue $value, FieldItemInterface $item = NULL) {
    return $value->toArray();
  }

  /**
   * {@inheritdoc}
   */
  public function onFieldRestore(ExoComponentValues $values, FieldItemListInterface $items) {
    $field_values = [];
    if ($items->isEmpty()) {
      $field_values = $this->populateValues($values, $items);
    }
    return $field_values;
  }

  /**
   * {@inheritdoc}
   */
  public function onClone(FieldItemListInterface $items, $all = FALSE) {
    foreach ($items as $item) {
      $item->setValue($this->onCloneValue($item, $all));
    }
  }

  /**
   * Extending classes can use this method to clone existing values.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   The field item.
   * @param bool $all
   *   Flag that determines if this is a partial clone or full clone.
   *
   * @return mixed
   *   A value suitable for setting to \Drupal\Core\Field\FieldItemInterface.
   */
  protected function onCloneValue(FieldItemInterface $item, $all) {
    return $item->getValue();
  }

  /**
   * {@inheritdoc}
   */
  public function view(FieldItemListInterface $items, array $contexts) {
    $output = [];
    if ($items->count()) {
      foreach ($items as $delta => $item) {
        $output[$delta] = $this->viewValue($item, $delta, $contexts);
      }
    }
    elseif ($value = $this->viewEmptyValue($contexts)) {
      $output[0] = $value;
    }
    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function viewValue(FieldItemInterface $item, $delta, array $contexts) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function viewEmptyValue(array $contexts) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getRequiredPaths() {
    $paths = [];
    $field = $this->getFieldDefinition();
    $delta = 0;
    if ($field->isRequired() && $field->isEditable() && !$this->hasDefault() && !$this->getDefaultValue($delta)) {
      $paths[] = $this->getItemParentsAsPath($delta);
    }
    return $paths;
  }

  /**
   * {@inheritdoc}
   */
  public function onPreSaveLayoutBuilderEntity(FieldItemListInterface $items, EntityInterface $parent_entity) {}

  /**
   * {@inheritdoc}
   */
  public function onPostSaveLayoutBuilderEntity(FieldItemListInterface $items, EntityInterface $parent_entity) {}

  /**
   * {@inheritdoc}
   */
  public function onPostDeleteLayoutBuilderEntity(FieldItemListInterface $items, EntityInterface $parent_entity) {}

  /**
   * {@inheritdoc}
   */
  public function onDraftUpdateLayoutBuilderEntity(FieldItemListInterface $items) {}

  /**
   * {@inheritdoc}
   */
  public function access(FieldItemListInterface $items, array $contexts, AccountInterface $account = NULL, $return_as_object = FALSE) {
    $account = $account ?: \Drupal::currentUser();
    $access = $this->componentAccess($items, $contexts, $account);
    return $return_as_object ? $access : $access->isAllowed();
  }

  /**
   * Indicates whether the field should be shown.
   *
   * Fields with specific access checking should override this method rather
   * than access(), in order to avoid repeating the handling of the
   * $return_as_object argument.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   The field items.
   * @param \Drupal\Core\Plugin\Context\Context[] $contexts
   *   An array of current contexts.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user session for which to check access.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result.
   *
   * @see self::access()
   */
  protected function componentAccess(FieldItemListInterface $items, array $contexts, AccountInterface $account) {
    // By default, the field is visible.
    return AccessResult::allowed();
  }

}
