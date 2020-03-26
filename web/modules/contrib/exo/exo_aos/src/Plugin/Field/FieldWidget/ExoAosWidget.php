<?php

namespace Drupal\exo_aos\Plugin\Field\FieldWidget;

use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\SubformState;
use Drupal\exo_aos\ExoAosSettings;
use Drupal\Component\Utility\Html;

/**
 * Plugin implementation of the 'exo_aos' widget.
 *
 * @FieldWidget(
 *   id = "exo_aos",
 *   label = @Translation("eXo Animate on Scroll"),
 *   field_types = {
 *     "exo_aos"
 *   }
 * )
 */
class ExoAosWidget extends WidgetBase {

  /**
   * The eXo Aos options service.
   *
   * @var \Drupal\exo\ExoSettingsPluginInstanceInterface
   */
  protected $exoAosSettings;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->exoAosSettings = \Drupal::service('exo_aos.settings')->createInstance($this->getSettings()['default']);
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'allow_override' => [
        'animation' => 'animation',
      ],
      'allow_animations' => [],
      'default' => [],
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $allow_override = $this->getSetting('allow_override');
    $allow_animations = $this->getSetting('allow_animations');
    $default = $this->getSetting('default');
    $summary[] = $this->t('Allow override: %values', [
      '%values' => empty($allow_override) ? $this->t('No') : implode(', ', array_intersect_key(ExoAosSettings::getElementPropertyLabels(), $allow_override)),
    ]);
    if (!empty($allow_override)) {
      $summary[] = $this->t('Allow animations: %values', [
        '%values' => empty($allow_animations) ? $this->t('All') : implode(', ', array_intersect_key(ExoAosSettings::getElementAnimations(), $allow_animations)),
      ]);
    }
    if (empty($default)) {
      $summary[] = $this->t('Default animations: %values', [
        '%values' => $this->t('Site Default'),
      ]);
    }
    else {
      foreach (ExoAosSettings::getElementPropertyLabels() as $key => $label) {
        $value = $this->exoAosSettings->getSetting($key);
        if (is_bool($value)) {
          $value = $value ? $this->t('Yes') : $this->t('No');
        }
        $summary[] = $this->t('@label: %values', [
          '@label' => $label,
          '%values' => (string) $value,
        ]);
      }
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $options = [];
    foreach (ExoAosSettings::getElementPropertyLabels() as $key => $label) {
      $options[$key] = $label;
    }

    $element['allow_override'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Allow override of'),
      '#description' => $this->t('If nothing is selected, the user will only be able to enable/disable the animation.'),
      '#options' => $options,
      '#default_value' => $this->getSetting('allow_override'),
    ];

    $options = [];
    foreach (ExoAosSettings::getElementAnimations() as $key => $label) {
      $options[$key] = $label;
    }

    $element['allow_animations'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Allowed animations'),
      '#description' => $this->t('If nothing is selected, all animations will be allowed.'),
      '#options' => $options,
      '#default_value' => $this->getSetting('allow_animations'),
      '#states' => [
        'visible' => [
          ':input[name="fields[' . $this->fieldDefinition->getName() . '][settings_edit_form][settings][allow_override][animation]"]' => [
            'checked' => TRUE,
          ],
        ],
      ],
    ];

    $element['default'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Default Animation'),
      '#description' => $this->t('These settings will be the defaults set when the entity has been saved.'),
    ];

    $element['default'] = $this->exoAosSettings->buildForm($element['default'], $form_state);
    $element['#element_validate'] = [[$this, 'settingsFormValidate']];

    return $element;
  }

  /**
   * Validate eXo modal.
   */
  public function settingsFormValidate($element, FormStateInterface $form_state) {
    $form_state->setValue($element['allow_override']['#parents'], array_filter($form_state->getValue($element['allow_override']['#parents'])));
    $form_state->setValue($element['allow_animations']['#parents'], array_filter($form_state->getValue($element['allow_animations']['#parents'])));
    $subform_state = SubformState::createForSubform($element['default'], $form_state->getCompleteForm(), $form_state);
    $this->exoAosSettings->validateForm($element['default'], $subform_state);
    $this->exoAosSettings->submitForm($element['default'], $subform_state);
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $value = $items[$delta]->value;
    $allow_override = $this->getSetting('allow_override');
    $allow_animations = $this->getSetting('allow_animations');
    if ($allow_override && !empty($value['aos'])) {
      $this->exoAosSettings->setSettings($value['aos']);
    }
    $element['#type'] = 'fieldset';
    $element['value'] = [];

    $key = implode('-', array_merge($element['#field_parents'], [
      $this->fieldDefinition->getName(),
      $delta,
    ]));
    $id = Html::getUniqueId('exo-aos-' . $key . '-enable');
    $element['value']['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Animation'),
      '#default_value' => !empty($value),
      '#id' => $id,
      '#weight' => -10,
    ];

    $element['value']['aos'] = [
      '#type' => 'container',
    ];
    $subform_state = SubformState::createForSubform($element['value']['aos'], $form, $form_state);
    $element['value']['aos'] = $this->exoAosSettings->buildForm($element['value']['aos'], $subform_state);
    $element['value']['aos']['exo_default']['#title'] = $this->t('Use Default Animation');
    $element['value']['aos']['#states']['visible']['#' . $id]['checked'] = TRUE;
    $element['value']['aos']['exo_default']['#states']['visible']['#' . $id]['checked'] = TRUE;

    if (empty($allow_override)) {
      $element['value']['aos']['settings']['#access'] = FALSE;
    }
    else {
      foreach (ExoAosSettings::getElementProperties() as $key => $data) {
        if (empty($allow_override[$key])) {
          $element['value']['aos']['settings'][$key]['#access'] = FALSE;
        }
      }
      if (!empty($allow_animations)) {
        foreach (ExoAosSettings::getElementAnimations() as $key => $label) {
          if (empty($allow_animations[$key])) {
            unset($element['value']['aos']['settings']['animation']['#options'][$key]);
          }
        }
      }
    }
    $element['#element_validate'] = [[$this, 'formElementValidate']];
    return $element;
  }

  /**
   * Validate eXo modal.
   */
  public function formElementValidate($element, FormStateInterface $form_state) {
    $value = $form_state->getValue($element['#parents']);
    if (empty($value['value']['status'])) {
      $value['value'] = NULL;
      $form_state->setValue($element['#parents'], $value);
    }
    else {
      $subform_state = SubformState::createForSubform($element['value']['aos'], $form_state->getCompleteForm(), $form_state);
      $this->exoAosSettings->validateForm($element['value']['aos'], $subform_state);
      $this->exoAosSettings->submitForm($element['value']['aos'], $subform_state);
    }
  }

}
