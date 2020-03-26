<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\exo_alchemist\Definition\ExoComponentDefinitionField;
use Drupal\exo_alchemist\Definition\ExoComponentDefinitionFieldPreview;
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

  /**
   * {@inheritdoc}
   */
  public function componentProcessDefinition(ExoComponentDefinitionField $field) {
    parent::componentProcessDefinition($field);
    if (!$field->hasAdditionalValue('textarea_format')) {
      $field->setAdditionalValue('textarea_format', 'exo_component_html');
    }
    $field->setPreviewValueOnAll('format', $field->getAdditionalValue('textarea_format'));
  }

  /**
   * {@inheritdoc}
   */
  public function componentViewValue(ExoComponentDefinitionField $field, FieldItemInterface $item, $delta, $is_layout_builder) {
    return [
      'value' => $item->value,
      'formatted' => [
        '#type' => 'processed_text',
        '#text' => $item->value,
        '#format' => $item->format,
        '#langcode' => $item->getLangcode(),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function componentFormAlter(array &$form, FormStateInterface $form_state, ExoComponentDefinitionField $field) {
    foreach (Element::children($form['widget']) as $delta) {
      $form['widget'][$delta]['#allowed_formats'] = [
        $form['widget'][$delta]['#format'],
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
