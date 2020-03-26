<?php

namespace Drupal\exo_form\Plugin\Field\FieldWidget;

use Drupal\address\Plugin\Field\FieldWidget\AddressDefaultWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'address_exo' widget.
 *
 * @FieldWidget(
 *   id = "address_exo",
 *   label = @Translation("eXo Address"),
 *   field_types = {
 *     "address"
 *   },
 *   provider = "address",
 * )
 */
class ExoAddressWidget extends AddressDefaultWidget {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'wrapper' => 'fieldset',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $element['wrapper'] = [
      '#type' => 'select',
      '#title' => $this->t('Wrapper'),
      '#default_value' => $this->getSetting('wrapper'),
      '#options' => $this->getWrapperOptions(),
      '#required' => TRUE,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $summary[] = $this->t('Wrapper type: @value', ['@value' => $this->getWrapperOptions()[$this->getSetting('wrapper')]]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $element['#type'] = $this->getSetting('wrapper');
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  protected function getWrapperOptions() {
    return [
      'fieldset' => $this->t('Fieldset'),
      'details' => $this->t('Details'),
      'container' => $this->t('Container'),
    ];
  }

}
