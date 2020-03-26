<?php

namespace Drupal\exo_form\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\profile\Plugin\Field\FieldWidget\ProfileFormWidget;

/**
 * Plugin implementation of the 'profile_form' widget.
 *
 * @FieldWidget(
 *   id = "exo_profile_form",
 *   label = @Translation("eXo Profile form"),
 *   field_types = {
 *     "entity_reference"
 *   },
 *   provider = "profile",
 * )
 */
class ExoProfileFormWidget extends ProfileFormWidget {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'wrapper' => 'fieldset',
      'optional' => FALSE,
      'optional_title' => 'Enable',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);
    $field_name = $this->fieldDefinition->getName();

    $element['wrapper'] = [
      '#type' => 'select',
      '#title' => $this->t('Wrapper'),
      '#default_value' => $this->getSetting('wrapper'),
      '#options' => $this->getWrapperOptions(),
      '#required' => TRUE,
    ];

    $element['optional'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Optional'),
      '#default_value' => $this->getSetting('optional'),
    ];

    $element['optional_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Optional Title'),
      '#default_value' => $this->getSetting('optional_title'),
      '#states' => [
        'visible' => [
          ':input[name="fields[' . $field_name . '][settings_edit_form][settings][optional]"]' => ['checked' => TRUE],
        ],
      ],
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
    if ($this->getSetting('optional')) {
      /** @var \Drupal\user\UserInterface $account */
      $account = $items->getEntity();
      /** @var \Drupal\profile\ProfileStorageInterface $profile_storage */
      $profile_storage = $this->entityTypeManager->getStorage('profile');
      $profile_type_storage = $this->entityTypeManager->getStorage('profile_type');
      /** @var \Drupal\profile\Entity\ProfileTypeInterface $profile_type */
      $profile_type = $profile_type_storage->load($this->getFieldSetting('profile_type'));
      $field_name = $this->fieldDefinition->getName();
      $wrapper_id = Html::getClass('exo-profile-form-' . $field_name);

      if ($form_state->getValue([
        $field_name,
        $delta,
        'status',
      ])) {
        $element = parent::formElement($items, $delta, $element, $form, $form_state);
      }
      else {
        $element = [
          '#type' => 'details',
          '#description' => '',
          '#open' => TRUE,
          // Remove the "required" clue, it's display-only and confusing.
          '#required' => FALSE,
          '#field_title' => $profile_type->getDisplayLabel() ?: $profile_type->label(),
          '#after_build' => [
            [get_class($this), 'removeTranslatabilityClue'],
          ],
        ] + $element;
      }
      $element['#type'] = $this->getSetting('wrapper');
      $element['#id'] = $wrapper_id;

      $element['status'] = [
        '#type' => 'checkbox',
        '#title' => $this->getSetting('optional_title'),
        '#weight' => -1000,
        '#ajax' => [
          'callback' => [get_class($this), 'toggleStatus'],
          'wrapper' => $wrapper_id,
        ],
      ];
    }
    else {
      $element = parent::formElement($items, $delta, $element, $form, $form_state);
      $element['#type'] = $this->getSetting('wrapper');
    }
    return $element;
  }

  /**
   * Ajax callback for the handler settings form.
   *
   * @see static::fieldSettingsForm()
   */
  public static function toggleStatus($form, FormStateInterface $form_state) {
    $element = $form_state->getTriggeringElement();
    // Go one level up in the form, to the widgets container.
    return NestedArray::getValue($form, array_slice($element['#array_parents'], 0, -1));
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
