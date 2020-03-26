<?php

namespace Drupal\exo_filter\Plugin\views\filter;

use Drupal\datetime\Plugin\views\filter\Date;
use Drupal\Core\Form\FormStateInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;

/**
 * Filter handler which allows to search on multiple fields.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("exo_filter_date")
 */
class ExoFilterDate extends Date {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['value']['contains']['date_only']['default'] = FALSE;
    return $options;
  }

  /**
   * Add a type selector to the value form.
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    if (!$form_state->get('exposed')) {
      $form['value']['date_only'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Force date selection only'),
        '#default_value' => !empty($this->value['date_only']) ? $this->value['date_only'] : FALSE,
      ];
    }
    parent::valueForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildExposedForm(&$form, FormStateInterface $form_state) {
    parent::buildExposedForm($form, $form_state);

    // Change the form element to a 'datetime' if the exposed field is
    // configured for 'date' input.
    if ($this->value['type'] === 'date') {
      $field_identifier = $this->options['expose']['identifier'];

      if ($this->operator === 'between') {
        $form[$field_identifier]['min']['#type'] = 'datetime';
        $form[$field_identifier]['max']['#type'] = 'datetime';

        // Check the element input matches the form structure.
        $input = $form_state->getUserInput();
        if (isset($input[$field_identifier], $input[$field_identifier]['min']) &&  !is_array($input[$field_identifier]['min']) && $value = $this->value['min']) {
          $date = new DrupalDateTime($value);
          $input[$field_identifier]['min'] = [
            'date' => $date->format('Y-m-d'),
            'time' => $date->format('H:i:s'),
          ];
        }
        if (isset($input[$field_identifier], $input[$field_identifier]['max']) &&  !is_array($input[$field_identifier]['max']) && $value = $this->value['max']) {
          $date = new DrupalDateTime($value);
          $input[$field_identifier]['max'] = [
            'date' => $date->format('Y-m-d'),
            'time' => $date->format('H:i:s'),
          ];
        }
        $form_state->setUserInput($input);
      }
      else {
        $form[$field_identifier]['#type'] = 'datetime';

        // Check the element input matches the form structure.
        $input = $form_state->getUserInput();
        if (isset($input[$field_identifier]) &&  !is_array($input[$field_identifier]) && $value = $this->value['value']) {
          $date = new DrupalDateTime($value);
          $input[$field_identifier] = [
            'date' => $date->format('Y-m-d'),
            'time' => $date->format('H:i:s'),
          ];
        }
        $form_state->setUserInput($input);
      }
    }

    // Hide the date_time_element for date only fields.
    if ($this->value['date_only'] || $this->fieldStorageDefinition->get('settings')['datetime_type'] === DateTimeItem::DATETIME_TYPE_DATE) {
      $field_identifier = $this->options['expose']['identifier'];

      if ($this->operator === 'between') {
        $form[$field_identifier]['min']['#date_time_element'] = 'none';
        $form[$field_identifier]['max']['#date_time_element'] = 'none';
      }
      else {
        $form[$field_identifier]['#date_time_element'] = 'none';
      }
    }
  }

}
