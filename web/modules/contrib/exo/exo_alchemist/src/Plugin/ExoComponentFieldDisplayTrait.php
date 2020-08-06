<?php

namespace Drupal\exo_alchemist\Plugin;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides methods for attaching entity displays to components.
 */
trait ExoComponentFieldDisplayTrait {

  /**
   * Component definition.
   *
   * @var \Drupal\exo_alchemist\Definition\ExoComponentDefinition
   *   The component definition.
   */
  protected $componentDisplayDefinition;

  /**
   * {@inheritdoc}
   */
  public function useDisplay() {
    return !empty($this->getDisplayedEntityTypeId()) && !empty($this->getDisplayedBundle());
  }

  /**
   * {@inheritdoc}
   */
  public function onFieldInstallFieldDisplay() {
    $this->getEntityViewMode()->save();
    $this->getEntityViewDisplay()->set('status', TRUE)->save();
  }

  /**
   * {@inheritdoc}
   */
  public function onFieldUpdateFieldDisplay() {
    $this->getEntityViewMode()->save();
    $this->getEntityViewDisplay()->set('status', TRUE)->save();
  }

  /**
   * {@inheritdoc}
   */
  public function onFieldUninstallFieldDisplay() {
    $this->getEntityViewDisplay()->delete();
    $this->getEntityViewMode()->delete();
  }

  /**
   * {@inheritdoc}
   */
  public function propertyInfoFieldDisplay() {
    $properties = [];
    $definition = $this->getComponentDisplayDefinition();
    $info = $this->exoComponentManager()->getPropertyInfo($definition);
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
  public function viewValueFieldDisplay(ContentEntityInterface $entity, array $contexts) {
    $definition = $this->getComponentDisplayDefinition();
    return $this->exoComponentManager()->viewEntityValues($definition, $entity, $contexts);
  }

  /**
   * {@inheritdoc}
   *
   * Pass alter to children.
   */
  public function formAlterFieldDisplay(array &$form, FormStateInterface $form_state) {
    $field_name = $form_state->get('exo_component_key');
    $definition = $this->getComponentDefinition();
    if ($field = $definition->getField($field_name)) {
      $component_field = $this->exoComponentManager()->getExoComponentFieldManager()->createFieldInstance($field);
      if ($component_field instanceof ExoComponentFieldFormInterface) {
        $component_field->formAlter($form, $form_state);
      }
    }
  }

  /**
   * {@inheritdoc}
   *
   * Pass validate to children.
   */
  public function formValidateFieldDisplay(array $form, FormStateInterface $form_state) {
    $field_name = $form_state->get('exo_component_key');
    $definition = $this->getComponentDefinition();
    if ($field = $definition->getField($field_name)) {
      $component_field = $this->exoComponentManager()->getExoComponentFieldManager()->createFieldInstance($field);
      if ($component_field instanceof ExoComponentFieldFormInterface) {
        $component_field->formValidate($form, $form_state);
      }
    }
  }

  /**
   * {@inheritdoc}
   *
   * Pass submit to children.
   */
  public function formSubmitFieldDisplay(array $form, FormStateInterface $form_state) {
    $field_name = $form_state->get('exo_component_key');
    $definition = $this->getComponentDefinition();
    if ($field = $definition->getField($field_name)) {
      $component_field = $this->exoComponentManager()->getExoComponentFieldManager()->createFieldInstance($field);
      if ($component_field instanceof ExoComponentFieldFormInterface) {
        $component_field->formSubmit($form, $form_state);
      }
    }
  }

  /**
   * Get the entity view display.
   *
   * @return \Drupal\Core\Entity\Display\EntityViewDisplayInterface
   *   The entity view display.
   */
  protected function getEntityViewDisplay() {
    $entity_type_id = $this->getDisplayedEntityTypeId();
    $bundle = $this->getDisplayedBundle();
    $view_mode = $this->getViewMode();
    $id = $entity_type_id . '.' . $bundle . '.' . $view_mode;
    $storage = $this->entityTypeManager->getStorage('entity_view_display');
    $display = $storage->load($id);
    if (!$display) {
      $display = $storage->create([
        'id' => $id,
        'targetEntityType' => $entity_type_id,
        'bundle' => $bundle,
        'mode' => $view_mode,
      ]);
    }
    return $display;
  }

  /**
   * Get the entity view mode.
   *
   * @return \Drupal\Core\Entity\Entity\EntityViewModeInterface
   *   The entity view mode.
   */
  protected function getEntityViewMode() {
    $storage = $this->entityTypeManager->getStorage('entity_view_mode');
    $entity_type_id = $this->getDisplayedEntityTypeId();
    $view_mode = $this->getViewMode();
    $id = $entity_type_id . '.' . $view_mode;
    $display = $storage->load($id);
    if (!$display) {
      $display = $storage->create([
        'id' => $id,
        'label' => $this->getFieldDefinition()->getComponent()->getLabel() . ': ' . $this->getFieldDefinition()->getLabel(),
        'targetEntityType' => $entity_type_id,
      ]);
    }
    return $display;
  }

  /**
   * Get the component view mode.
   */
  protected function getViewMode() {
    return $this->getFieldDefinition()->safeId();
  }

  /**
   * Get entity type id.
   *
   * @return string
   *   The entity type id.
   */
  public function getDisplayedEntityTypeId() {
    return NULL;
  }

  /**
   * Get bundle id.
   *
   * @return string
   *   The bundle id.
   */
  public function getDisplayedBundle() {
    return NULL;
  }

  /**
   * Get the component definition.
   *
   * @return \Drupal\exo_alchemist\Definition\ExoComponentDefinition
   *   The component definition.
   */
  protected function getComponentDisplayDefinition() {
    if (!isset($this->componentDisplayDefinition)) {
      $entity_type_id = $this->getDisplayedEntityTypeId();
      $bundle = $this->getDisplayedBundle();
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
          'type' => 'display_component:' . $entity_type_id . ':' . $bundle,
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
      $this->componentDisplayDefinition = $definition;
    }
    return $this->componentDisplayDefinition;
  }

  /**
   * Get the eXo component manager.
   *
   * @return \Drupal\exo_alchemist\ExoComponentManager
   *   The eXo component manager.
   */
  public function exoComponentManager() {
    if (!isset($this->exoComponentManager)) {
      $this->exoComponentManager = \Drupal::service('plugin.manager.exo_component');
    }
    return $this->exoComponentManager;
  }

}
