<?php

namespace Drupal\exo_alchemist\Plugin;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\exo_alchemist\Definition\ExoComponentDefinitionField;

/**
 * Defines an interface for Component Field plugins.
 */
interface ExoComponentFieldFieldableInterface extends ExoComponentFieldInterface {

  /**
   * Extending classes must use to return the storage values for the field.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField $field
   *   The eXo component field.
   *
   * @return array
   *   An array of values to set to the FieldStorageConfig.
   */
  public function componentStorage(ExoComponentDefinitionField $field);

  /**
   * Extending classes must use to return the values for the field.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField $field
   *   The eXo component field.
   *
   * @return array
   *   An array of values to set to the FieldConfig.
   */
  public function componentField(ExoComponentDefinitionField $field);

  /**
   * Extending classes should use to define the field widget config.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField $field
   *   The eXo component field.
   *
   * @return array
   *   A field widget definition ['type' => string, 'settings' => []].
   */
  public function componentWidget(ExoComponentDefinitionField $field);

  /**
   * Extending classes should use to define the field formatter config.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField $field
   *   The eXo component field.
   *
   * @return array
   *   A field widget definition ['type' => string, 'settings' => []].
   */
  public function componentFormatter(ExoComponentDefinitionField $field);

  /**
   * Operations that can be run before update during layout building.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField $field
   *   The eXo component field.
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   The field items.
   */
  public function componentPreUpdate(ExoComponentDefinitionField $field, FieldItemListInterface $items);

  /**
   * Return the default value of a field.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField $field
   *   The eXo component field.
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   The field items.
   *
   * @return array
   *   A value that will be set to the Drupal default entity field.
   */
  public function componentValues(ExoComponentDefinitionField $field, FieldItemListInterface $items);

  /**
   * Acts on field before it is saved.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField $field
   *   The eXo component field.
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   The field items.
   */
  public function componentUpdate(ExoComponentDefinitionField $field, FieldItemListInterface $items);

  /**
   * Acts on field before it is deleted.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField $field
   *   The eXo component field.
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   The field items.
   */
  public function componentDelete(ExoComponentDefinitionField $field, FieldItemListInterface $items);

  /**
   * Acts on field before it is uninstalled.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField $field
   *   The eXo component field.
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   The field items.
   */
  public function componentUninstall(ExoComponentDefinitionField $field, FieldItemListInterface $items);

  /**
   * Acts on field before it is cloned.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField $field
   *   The eXo component field.
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   The field items.
   */
  public function componentClone(ExoComponentDefinitionField $field, FieldItemListInterface $items);

  /**
   * Acts on empty field to restore its values.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField $field
   *   The eXo component field.
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   The field items.
   */
  public function componentRestore(ExoComponentDefinitionField $field, FieldItemListInterface $items);

  /**
   * Return the default value of a field.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField $field
   *   The eXo component field.
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   The field items.
   * @param string $is_layout_builder
   *   TRUE if we are in layout builder mode.
   *
   * @return array
   *   A value that will be sent to twig.
   */
  public function componentView(ExoComponentDefinitionField $field, FieldItemListInterface $items, $is_layout_builder);

  /**
   * Return the default value of an item.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField $field
   *   The eXo component field.
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   The field item.
   * @param string $delta
   *   The field item delta.
   * @param string $is_layout_builder
   *   TRUE if we are in layout builder mode.
   *
   * @return array
   *   A value that will be sent to twig.
   */
  public function componentViewValue(ExoComponentDefinitionField $field, FieldItemInterface $item, $delta, $is_layout_builder);

  /**
   * Extending classes can use this method to return values for an empty item.
   *
   * Should reflect properties reflected in componentPropertyInfo().
   *
   * @return array
   *   An array of key => value that will be passed as Twig variables.
   */
  public function componentViewEmptyValue(ExoComponentDefinitionField $field, $is_layout_builder);

}
