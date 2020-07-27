<?php

namespace Drupal\exo\Plugin\Field\FieldFormatter;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a trait for selecting which entities to view.
 */
trait ExoEntityReferenceLinkTrait {

  /**
   * {@inheritdoc}
   */
  public function linkSettingsForm(array &$form, FormStateInterface $form_state) {
    $elements = [];

    if (isset($form['image_link'])) {
      if ($options = $this->getLinkFieldOptions()) {
        $form['image_link']['#options'] += $options;
      }
    }

    return $elements;
  }

  /**
   * Get link field options.
   */
  public function getLinkFieldOptions() {
    $options = [];
    $fields = \Drupal::service('entity_field.manager')->getFieldDefinitions($this->fieldDefinition->getTargetEntityTypeId(), $this->fieldDefinition->getTargetBundle());
    foreach ($fields as $field_name => $field) {
      if ($field->getType() == 'link') {
        $options[$field->getName()] = $this->t('Field @label', ['@label' => $field->getLabel()]);
      }
    }
    return $options;
  }

}
