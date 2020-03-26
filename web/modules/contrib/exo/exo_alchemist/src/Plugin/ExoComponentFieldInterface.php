<?php

namespace Drupal\exo_alchemist\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_alchemist\Definition\ExoComponentDefinitionField;

/**
 * Defines an interface for Component Field plugins.
 */
interface ExoComponentFieldInterface extends PluginInspectionInterface {

  /**
   * Process a component field definition.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField $field
   *   The eXo component field.
   */
  public function componentProcessDefinition(ExoComponentDefinitionField $field);

  /**
   * Runs before install of the config entity used as the entity type.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField $field
   *   The eXo component field.
   * @param \Drupal\Core\Config\Entity\ConfigEntityInterface $entity
   *   The config entity used as the entity type.
   */
  public function componentInstallEntityType(ExoComponentDefinitionField $field, ConfigEntityInterface $entity);

  /**
   * Runs before update of the config entity used as the entity type.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField $field
   *   The eXo component field.
   * @param \Drupal\Core\Config\Entity\ConfigEntityInterface $entity
   *   The config entity used as the entity type.
   */
  public function componentUpdateEntityType(ExoComponentDefinitionField $field, ConfigEntityInterface $entity);

  /**
   * Runs before delete of the config entity used as the entity type.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField $field
   *   The eXo component field.
   * @param \Drupal\Core\Config\Entity\ConfigEntityInterface $entity
   *   The config entity used as the entity type.
   */
  public function componentUninstallEntityType(ExoComponentDefinitionField $field, ConfigEntityInterface $entity);

  /**
   * Return component property info.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField $field
   *   The eXo component field.
   *
   * @return array
   *   An array of property_id => description.
   */
  public function componentPropertyInfo(ExoComponentDefinitionField $field);

  /**
   * Method called when displaying a form widget for a field.
   *
   * @param array $form
   *   The form to alter.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state interface.
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField $field
   *   The eXo component field.
   */
  public function componentFormAlter(array &$form, FormStateInterface $form_state, ExoComponentDefinitionField $field);

  /**
   * Build component parents.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionField $field
   *   The eXo component field.
   * @param string $delta
   *   The delta of the item being viewed.
   *
   * @return array
   *   The array of parents.
   */
  public function componentParents(ExoComponentDefinitionField $field, $delta);

}
