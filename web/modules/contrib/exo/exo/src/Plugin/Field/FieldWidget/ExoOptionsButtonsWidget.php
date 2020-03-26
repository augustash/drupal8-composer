<?php

namespace Drupal\exo\Plugin\Field\FieldWidget;

use Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsButtonsWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'options_buttons' widget.
 *
 * @FieldWidget(
 *   id = "exo_options_buttons",
 *   label = @Translation("eXo check boxes/radio buttons"),
 *   field_types = {
 *     "boolean",
 *     "entity_reference",
 *     "list_integer",
 *     "list_float",
 *     "list_string",
 *   },
 *   multiple_values = TRUE
 * )
 */
class ExoOptionsButtonsWidget extends OptionsButtonsWidget {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'style' => 'inline',
      'empty_label' => 'Auto',
      'reverse' => FALSE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    $form['style'] = [
      '#type' => 'select',
      '#title' => $this->t('Display Style'),
      '#options' => exo_element_style_types(),
      '#default_value' => $this->getSetting('style'),
    ];
    $form['empty_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Empty Label'),
      '#default_value' => $this->getSetting('empty_label'),
      '#required' => TRUE,
    ];
    $form['reverse'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Reverse Options'),
      '#description' => $this->t('Reverse the order of the options.'),
      '#default_value' => $this->getSetting('reverse'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $style_options = exo_element_style_types();
    $style = $this->getSetting('style');
    $summary = parent::settingsSummary();
    $summary[] = $this->t('Display style: %style', [
      '%style' => isset($style_options[$style]) ? $style_options[$style] : $style,
    ]);
    if ($this->getSetting('reverse')) {
      $summary[] = $this->t('Reverse option order');
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $element['#exo_style'] = $this->getSetting('style');
    $selected = $this->getSelectedOptions($items);

    if ($this->multiple) {
      $element['#type'] = 'exo_checkboxes';
    }
    else {
      $element['#type'] = 'exo_radios';
      $element['#default_value'] = $selected ? reset($selected) : '_none';
    }

    if ($this->getSetting('reverse')) {
      $element['#options'] = array_reverse($element['#options'], TRUE);
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEmptyLabel() {
    if (!$this->required && !$this->multiple) {
      return $this->t($this->getSetting('empty_label'));
    }
  }

}
