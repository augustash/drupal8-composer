<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_alchemist\ExoComponentSectionStorageInterface;
use Psr\Log\LoggerInterface;

/**
 * A 'display' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "reference_display",
 *   deriver = "\Drupal\exo_alchemist\Plugin\Derivative\ExoComponentReferenceDisplayEntityDeriver"
 * )
 */
class ReferenceEntityDisplay extends EntityDisplay {

  /**
   * The field name.
   *
   * @var string
   */
  protected $fieldName;

  /**
   * Constructs a new FieldBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, LoggerInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $logger);
    $this->parentEntityTypeId = $this->entityTypeId;
    $this->parentBundle = $this->bundle;
    $this->entityTypeId = $this->getPluginDefinition()['targetEntityTypeId'];
    $this->bundle = $this->getPluginDefinition()['targetBundle'];
    $this->fieldName = $this->getFieldName();
  }

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    $component = $this->getComponentDefinition();
    $info = $this->exoComponentManager()->getPropertyInfo($component);
    $properties = [
      'entity_id' => $this->t('The entity id.'),
      'entity_type_id' => $this->t('The entity type id.'),
    ];
    foreach ($info as $key => $data) {
      if ($key !== '_global') {
        $properties += $data['properties'];
      }
    }
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  protected function getReferencedEntity(array $contexts) {
    $parent_entity = parent::getReferencedEntity($contexts);
    $target_entity = NULL;
    if ($parent_entity->hasField($this->fieldName)) {
      $target_entity = $parent_entity->get($this->fieldName)->entity;
      // Use cached entity so that changes are preserved.
      if ($target_entity && isset($parent_entity->_exoComponentReferenceSave[$target_entity->uuid()])) {
        $target_entity = $parent_entity->_exoComponentReferenceSave[$target_entity->uuid()];
      }
    }
    return $target_entity;
  }

  /**
   * {@inheritdoc}
   *
   * Pass alter to children.
   */
  public function formAlter(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\exo_alchemist\Form\ExoFieldUpdateForm $form_object */
    $form_object = $form_state->getFormObject();
    $section_storage = $form_object->getSectionStorage();
    if ($section_storage instanceof ExoComponentSectionStorageInterface) {
      $entity = $section_storage->getParentEntity();
      if ($entity) {
        \Drupal::messenger()->addWarning($this->t('Be careful. The information you are changing may be used by other content.'));
        $form_state->set('display_entity', $entity->get($this->fieldName)->entity);
      }
      parent::formAlter($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   *
   * Pass submit to children.
   */
  public function formSubmit(array $form, FormStateInterface $form_state) {
    parent::formSubmit($form, $form_state);
    /** @var \Drupal\exo_alchemist\Form\ExoFieldUpdateForm $form_object */
    $form_object = $form_state->getFormObject();
    $section_storage = $form_object->getSectionStorage();
    if ($section_storage instanceof ExoComponentSectionStorageInterface) {
      /** @var \Drupal\Core\Entity\ContentEntityInterface $parent_entity */
      $parent_entity = $section_storage->getParentEntity();
      /** @var \Drupal\Core\Entity\ContentEntityInterface $referenced_entity */
      $referenced_entity = $form_state->get('display_entity');
      // Store changes on the parent entity. The parent entity is stored in
      // the temp storage and entities place here will be saved later.
      // @see \Drupal\exo_alchemist\Form\ExoOverridesEntityForm::save().
      $parent_entity->_exoComponentReferenceSave[$referenced_entity->uuid()] = $referenced_entity;
    }
  }

  /**
   * Get the component definition.
   *
   * @return \Drupal\exo_alchemist\Definition\ExoComponentDefinition
   *   The component definition.
   */
  protected function getComponentDefinition() {
    if (!isset($this->componentDefinition)) {
      $field = $this->getFieldDefinition();
      $view_mode = $this->getViewMode();
      $definition = [
        'id' => $field->id(),
        'label' => $field->getComponent()->getLabel() . ': ' . $field->getLabel(),
        'description' => $field->getComponent()->getDescription(),
        'fields' => [],
        'modifier_globals' => FALSE,
        'computed' => TRUE,
      ] + $field->toArray() + $field->getComponent()->toArray();
      /** @var \Drupal\exo_alchemist\Entity\ExoLayoutBuilderEntityViewDisplay $display */
      $display = $this->getEntityViewDisplay();
      foreach ($display->getComponents() as $id => $component) {
        $field_key = $id;
        $field_name = $id;
        // Add fieldupe module support.
        if (substr($id, 0, 9) === 'fieldupe_') {
          /** @var \Drupal\fieldupe\Entity\Fieldupe $dupe */
          $dupe = $this->entityTypeManager->getStorage('fieldupe')->load($id);
          if ($dupe) {
            $field_name = $dupe->getParentField();
            // Shorten the key a bit.
            $field_key = str_replace('fieldupe_' . $dupe->getParentEntityType() . '_' . $dupe->getParentBundle() . '_', 'dupe_', $id);
          }
        }
        $definition['fields'][$field_key] = [
          'type' => 'display_component:' . $this->entityTypeId . ':' . $this->bundle,
          'label' => $display->getComponentLabel($id),
          'component_name' => $id,
          'field_name' => $field_name,
          'view_mode' => $view_mode,
          'computed' => TRUE,
        ];
      }
      $this->exoComponentManager()->processDefinition($definition, $this->getPluginId());
      /** @var \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition */
      $definition->addParentField($field);
      $this->componentDefinition = $definition;
    }
    return $this->componentDefinition;
  }

  /**
   * Get entity type id.
   *
   * @return string
   *   The entity type id.
   */
  public function getEntityTypeId() {
    return $this->getPluginDefinition()['targetEntityTypeId'];
  }

  /**
   * Get bundle id.
   *
   * @return string
   *   The bundle id.
   */
  public function getBundle() {
    return $this->getPluginDefinition()['targetBundle'];
  }

  /**
   * Get field name.
   *
   * @return string
   *   The field name.
   */
  public function getFieldName() {
    return static::getFieldNameFromPluginId($this->getPluginId());
  }

  /**
   * Get field name from plugin id.
   *
   * @param string $plugin_id
   *   The plugin id.
   *
   * @return string
   *   The field name.
   */
  public static function getFieldNameFromPluginId($plugin_id) {
    $parts = explode(static::DERIVATIVE_SEPARATOR, $plugin_id, 4);
    return $parts[3];
  }

}
