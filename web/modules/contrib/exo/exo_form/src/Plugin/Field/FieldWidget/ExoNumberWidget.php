<?php

namespace Drupal\exo_form\Plugin\Field\FieldWidget;

use Drupal\Core\Field\Plugin\Field\FieldWidget\NumberWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'number' widget.
 *
 * @FieldWidget(
 *   id = "exo_number",
 *   label = @Translation("Number +/- field"),
 *   field_types = {
 *     "integer",
 *     "decimal",
 *     "float"
 *   }
 * )
 */
class ExoNumberWidget extends NumberWidget {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'step' => '1',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $step = $this->getSetting('step');
    $element['#element_validate'][] = [get_class($this), 'validateSettingsForm'];
    $element['allow_decimal'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow decimal values'),
      '#default_value' => $step != '1',
    ];
    $element['step'] = [
      '#type' => 'select',
      '#title' => $this->t('Step'),
      '#description' => $this->t('Only values that are multiples of the selected step will be allowed.'),
      '#default_value' => $step != '1' ? $step : '0.1',
      '#options' => [
        '0.1' => '0.1',
        '0.01' => '0.01',
        '0.25' => '0.25',
        '0.5' => '0.5',
        '0.05' => '0.05',
      ],
      '#states' => [
        'visible' => [
          ':input[name="fields[quantity][settings_edit_form][settings][allow_decimal]"]' => ['checked' => TRUE],
        ],
      ],
      '#required' => TRUE,
    ];

    return $element;
  }

  /**
   * Validates the settings form.
   *
   * @param array $element
   *   The settings form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public static function validateSettingsForm(array $element, FormStateInterface $form_state) {
    $value = $form_state->getValue($element['#parents']);
    if (empty($value['allow_decimal'])) {
      $value['step'] = '1';
    }
    unset($value['allow_decimal']);
    $form_state->setValue($element['#parents'], $value);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    if ($this->getSetting('step') == 1) {
      $summary[] = $this->t('Decimal values not allowed');
    }
    else {
      $summary[] = $this->t('Decimal values allowed');
      $summary[] = $this->t('Step: @step', ['@step' => $this->getSetting('step')]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $element['value']['#type'] = 'exo_number';
    $element['value']['#step'] = $this->getSetting('step');
    $element['value']['#min'] = $this->getSetting('step');

    return $element;
  }

}
