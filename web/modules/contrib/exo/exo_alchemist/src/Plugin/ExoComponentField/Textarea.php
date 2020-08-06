<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\exo_alchemist\ExoComponentValue;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldDisplayFormTrait;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldFieldableBase;

/**
 * A 'textarea' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "textarea",
 *   label = @Translation("Text"),
 *   properties = {
 *     "value" = @Translation("The raw value."),
 *     "formatted" = @Translation("The formatted value."),
 *   },
 *   storage = {
 *     "type" = "text_long",
 *   },
 *   widget = {
 *     "type" = "text_textarea",
 *   }
 * )
 */
class Textarea extends ExoComponentFieldFieldableBase {
  use ExoComponentFieldDisplayFormTrait;

  /**
   * {@inheritdoc}
   */
  public function processDefinition() {
    parent::processDefinition();
    $field = $this->getFieldDefinition();
    if (!$field->hasAdditionalValue('textarea_format')) {
      $field->setAdditionalValue('textarea_format', 'exo_component_html');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateValue(ExoComponentValue $value) {
    $value->setIfUnset('format', $value->getDefinition()->getAdditionalValue('textarea_format'));
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultValue($delta = 0) {
    $field = $this->getFieldDefinition();
    return [
      'value' => $this->t('Placeholder for <strong>@label</strong>', [
        '@label' => strtolower($field->getLabel()),
      ]),
      'format' => $field->getAdditionalValue('textarea_format'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function viewValue(FieldItemInterface $item, $delta, array $contexts) {
    $render = [
      '#type' => 'processed_text',
      '#text' => $item->value,
      '#format' => $item->format,
      '#langcode' => $item->getLangcode(),
    ];
    if ($this->isLayoutBuilder($contexts)) {
      $render = $this->getFormAsPlaceholder($render);
    }
    return [
      'value' => $item->value,
      'render' => $render,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function formAlter(array &$form, FormStateInterface $form_state) {
    foreach (Element::children($form['widget']) as $delta) {
      $form['widget'][$delta]['#allowed_formats'] = [
        $this->getFieldDefinition()->getAdditionalValue('textarea_format'),
      ];
      // Support allowed_formats module.
      if (function_exists('_allowed_formats_remove_textarea_help')) {
        $form['widget'][$delta]['#allowed_format_hide_settings']['hide_help'] = TRUE;
        $form['widget'][$delta]['#allowed_format_hide_settings']['hide_guidelines'] = TRUE;
        $form['widget'][$delta]['#after_build'][] = '_allowed_formats_remove_textarea_help';
      }
    }
  }

}
