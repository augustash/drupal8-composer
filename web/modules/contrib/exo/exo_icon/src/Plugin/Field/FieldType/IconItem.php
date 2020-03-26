<?php

namespace Drupal\exo_icon\Plugin\Field\FieldType;

use Drupal\Core\Field\Plugin\Field\FieldType\StringItem;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'icon' field type.
 *
 * @FieldType(
 *   id = "icon",
 *   label = @Translation("Icon"),
 *   description = @Translation("A field containing an icon."),
 *   default_widget = "icon",
 *   default_formatter = "icon"
 * )
 */
class IconItem extends StringItem {

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('value')->getValue();
    return $value === NULL || $value === '';
  }

}
