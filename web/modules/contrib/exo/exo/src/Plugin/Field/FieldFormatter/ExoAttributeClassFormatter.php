<?php

namespace Drupal\exo\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'exo_attribute_class' formatter.
 *
 * @FieldFormatter(
 *   id = "exo_attribute_class",
 *   label = @Translation("eXo Attribute Class"),
 *   field_types = {
 *     "exo_attribute",
 *     "list_integer",
 *     "list_float",
 *     "list_string",
 *     "boolean",
 *   }
 * )
 */
class ExoAttributeClassFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'as_class' => TRUE,
      'as_data' => FALSE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['as_class'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Add as a CSS class'),
      '#default_value' => $this->getSetting('as_class'),
    ];

    $form['as_data'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Add as a data-* attribute'),
      '#default_value' => $this->getSetting('as_data'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('As class: %value', [
      '%value' => $this->getSetting('as_class') ? $this->t('Yes') : $this->t('No'),
    ]);
    $summary[] = $this->t('As data: %value', [
      '%value' => $this->getSetting('as_data') ? $this->t('Yes') : $this->t('No'),
    ]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    // The magic happens in exo_entity_view_alter().
    return [];
  }

}
