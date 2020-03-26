<?php

namespace Drupal\exo_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\NestedArray;

/**
 * Class ExoFormDemoForm.
 *
 * @package Drupal\exo_form\Form
 */
class ExoFormDemoForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'exo_form_example_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['file'] = ['#open' => TRUE] + $this->buildFile($form_state);
    $form['mix'] = ['#open' => TRUE] + $this->buildMix($form_state);
    $form['textfield'] = ['#open' => TRUE] + $this->buildTextfield($form_state);
    $form['number'] = ['#open' => TRUE] + $this->buildNumber($form_state);
    $form['date'] = ['#open' => TRUE] + $this->buildDate($form_state);
    $form['select'] = ['#open' => TRUE] + $this->buildSelect($form_state);
    $form['checkbox'] = ['#open' => TRUE] + $this->buildCheckbox($form_state);
    $form['radio'] = ['#open' => TRUE] + $this->buildRadio($form_state);
    $form['other'] = ['#open' => TRUE] + $this->buildOther($form_state);
    $form['textarea'] = ['#open' => TRUE] + $this->buildTextarea($form_state);
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * Demo mix.
   */
  protected function buildMix(FormStateInterface $form_state) {
    $element = [
      '#type' => 'fieldset',
      '#title' => $this->t('Mix'),
    ];

    $element['container'] = [
      '#type' => 'fieldset',
    ];

    $element['container']['container'] = [
      '#type' => 'details',
      '#title' => $this->t('Details'),
    ];

    $element['container']['container']['textfield'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Textfield'),
    ];

    $element['container']['select'] = [
      '#type' => 'select',
      '#title' => $this->t('Select'),
      '#options' => [
        'One',
        'Two',
        'Three',
        'One',
        'Two',
        'Three',
        'One',
        'Two',
        'Three',
        'One',
        'Two',
        'Three',
        'One',
        'Two',
        'Three',
        'One',
        'Two',
        'Three',
        'One',
        'Two',
        'Three',
        'One',
        'Two',
        'Four',
        'One',
        'Two',
        'Three',
      ],
      '#empty_option' => '- None -',
    ];

    $element['select2'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Multiple with Defaults'),
      '#options' => [
        'One',
        'Two',
        'Three',
        'One',
        'Two',
        'Three',
        'One',
        'Two',
        'Three',
        'One',
        'Two',
        'Three',
        'One',
        'Two',
        'Three',
        'One',
        'Two',
        'Three',
        'One',
        'Two',
        'Three',
        'One',
        'Two',
        'Four',
        'One',
        'Two',
        'Three',
      ],
      '#multiple' => TRUE,
      '#default_value' => [0, 1],
    ];

    $element['textfield2'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Textfield'),
    ];

    $element['radios'] = [
      '#type' => 'radios',
      '#title' => $this->t('Radios'),
      '#options' => ['One', 'Two', 'Three'],
    ];

    $element['textfield3'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Textfield'),
    ];

    $element['checkboxes'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Checkboxes'),
      '#options' => ['One', 'Two', 'Three'],
    ];

    $element['textfield4'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Textfield'),
    ];

    $element['vertical_tabs'] = [
      '#type' => 'vertical_tabs',
      '#title' => t('Settings'),
    ];

    $element['details1'] = [
      '#type' => 'details',
      '#title' => $this->t('Details 1'),
      '#group' => 'vertical_tabs',
    ];

    $element['details1']['checkboxes'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Checkboxes'),
      '#options' => ['One', 'Two', 'Three'],
    ];

    $element['details1']['textfield4'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Textfield'),
    ];

    $element['details2'] = [
      '#type' => 'details',
      '#title' => $this->t('Details 2'),
      '#group' => 'vertical_tabs',
    ];

    $element['details2']['textfield4'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Textfield'),
    ];

    $element['details2']['checkboxes'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Checkboxes'),
      '#options' => ['One', 'Two', 'Three'],
    ];

    $element['details3'] = [
      '#type' => 'details',
      '#title' => $this->t('Details 3 with a really long title'),
      '#group' => 'vertical_tabs',
    ];

    $element['details3']['radios'] = [
      '#type' => 'radios',
      '#title' => $this->t('Radios'),
      '#options' => ['One', 'Two', 'Three'],
    ];

    return $element;
  }

  /**
   * Demo textfields.
   */
  protected function buildFile(FormStateInterface $form_state) {
    $element = [
      '#type' => 'fieldset',
      '#title' => $this->t('File'),
      '#description' => $this->t('File examples showing all file types.'),
    ];

    $element['file'] = [
      '#type' => 'file',
      '#title' => $this->t('File'),
      '#description' => $this->t('Example Description.'),
    ];

    $element['managed_file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Managed File'),
      '#description' => $this->t('Example description.'),
    ];

    $element['exo_config_file'] = [
      '#type' => 'exo_config_file',
      '#title' => $this->t('eXo Config File'),
      '#description' => $this->t('Example description.'),
    ];

    $element['exo_config_image'] = [
      '#type' => 'exo_config_image',
      '#title' => $this->t('eXo Config Image'),
      '#description' => $this->t('Example description.'),
    ];

    return $element;
  }

  /**
   * Demo textfields.
   */
  protected function buildTextfield(FormStateInterface $form_state) {
    $element = [
      '#type' => 'fieldset',
      '#title' => $this->t('Textfield'),
      '#description' => $this->t('Textfield examples showing all input types.'),
    ];

    $element['textfield'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Textfield'),
    ];

    $element['textfield2'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Textfield with Description'),
      '#description' => $this->t('Here is the description'),
    ];

    $element['textfield3a'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Textfield with Prefix & Suffix'),
      '#description' => $this->t('Here is the description'),
      '#field_prefix' => '$',
      '#field_suffix' => '.00',
    ];

    $element['textfield3b'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Textfield with Prefix'),
      '#description' => $this->t('Here is the description'),
      '#field_prefix' => '$',
    ];

    $element['textfield3c'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Textfield with Suffix'),
      '#description' => $this->t('Here is the description'),
      '#field_suffix' => '.00',
    ];

    $element['textfield4'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Textfield Required'),
      '#required' => TRUE,
    ];

    $element['textfield5'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Textfield Disabled'),
      '#disabled' => TRUE,
    ];

    $element['textfield6'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Textfield with Empty Placeholder'),
      '#attributes' => [
        'placeholder' => '',
      ],
    ];

    $element['textfield7'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Textfield with Placeholder'),
      '#attributes' => [
        'placeholder' => $this->t('Placeholder'),
      ],
    ];

    $element['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
    ];

    $element['number'] = [
      '#type' => 'number',
      '#title' => $this->t('Number'),
    ];

    $element['tel'] = [
      '#type' => 'tel',
      '#title' => $this->t('Telephone'),
    ];

    $element['url'] = [
      '#type' => 'url',
      '#title' => $this->t('URL'),
    ];

    $element['password'] = [
      '#type' => 'password',
      '#title' => $this->t('Password'),
    ];

    $element['fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Fieldset'),
      '#description' => $this->t('This is an example fieldset description.'),
    ];

    $element['fieldset']['textfield'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Textfield'),
    ];

    $element['fieldset']['textfield2'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Textfield with Prefix & Suffix'),
      '#description' => $this->t('Here is the description'),
      '#field_prefix' => '$',
      '#field_suffix' => '.00',
    ];

    $count = $form_state->get('count');
    if (empty($count)) {
      $count = 1;
      $form_state->set('count', $count);
    }

    $element['addmore'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Add More'),
      '#prefix' => '<div id="addmore-wrapper">',
      '#suffix' => '</div>',
      '#tree' => TRUE,
    ];
    for ($i = 0; $i < $count; $i++) {
      $element['addmore'][$i] = [
        '#title' => t('Textfield dynamic %delta', ['%delta' => $i + 1]),
        '#type' => 'textfield',
      ];
    }

    $element['addmore_add'] = [
      '#type' => 'submit',
      '#value' => t('Add Another'),
      '#submit' => [[get_class($this), 'addOneSubmit']],
      '#limit_validation_errors' => [],
      '#ajax' => [
        'callback' => [get_class($this), 'addOneAjax'],
        'wrapper' => 'addmore-wrapper',
        'effect' => 'fade',
      ],
    ];

    return $element;
  }

  /**
   * Demo textfields.
   */
  protected function buildNumber(FormStateInterface $form_state) {
    $element = [
      '#type' => 'fieldset',
      '#title' => $this->t('Numbers'),
      '#description' => $this->t('Number examples.'),
    ];

    $element['number'] = [
      '#type' => 'number',
      '#title' => $this->t('Number'),
    ];

    $element['exo_number'] = [
      '#type' => 'exo_number',
      '#title' => $this->t('eXo Number'),
    ];

    $element['exo_number10'] = [
      '#type' => 'exo_number',
      '#title' => $this->t('eXo Number: 10 Step'),
      '#step' => 10,
    ];

    return $element;
  }

  /**
   * Demo select.
   */
  protected function buildSelect(FormStateInterface $form_state) {
    $element = [
      '#type' => 'fieldset',
      '#title' => $this->t('Select'),
      '#open' => FALSE,
    ];

    $element['select'] = [
      '#type' => 'select',
      '#title' => $this->t('Select'),
      '#options' => ['One', 'Two', 'Three'],
      '#empty_option' => '- None -',
    ];

    $element['select5'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Disabled'),
      '#options' => ['One', 'Two', 'Three'],
      '#disabled' => TRUE,
    ];

    $element['select1'] = [
      '#type' => 'select',
      '#title' => $this->t('Select'),
      '#options' => ['One', 'Two', 'Three', 'Four'],
      '#empty_option' => '- None -',
    ];

    $element['select2'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Multiple'),
      '#options' => ['One', 'Two', 'Three'],
      '#multiple' => TRUE,
    ];

    $element['select3'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Multiple with Defaults'),
      '#options' => ['One', 'Two', 'Three'],
      '#multiple' => TRUE,
      '#default_value' => [0, 1],
    ];

    $element['select4'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Group'),
      '#multiple' => TRUE,
      '#options' => [
        'One Label' => [11 => 'One 1', 21 => 'Two 1', 31 => 'Three 1'],
        'Two Label' => [12 => 'One 2', 22 => 'Two 2', 32 => 'Three 2'],
        'Three Label' => [13 => 'One 3', 23 => 'Two 3', 33 => 'Three 3'],
      ],
    ];

    return $element;
  }

  /**
   * Demo checkbox.
   */
  protected function buildCheckbox(FormStateInterface $form_state) {
    $element = [
      '#type' => 'fieldset',
      '#title' => $this->t('Checkbox'),
      '#description' => $this->t('Example description.'),
      '#open' => FALSE,
    ];

    $element['checkboxes'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Checkboxes'),
      '#description' => $this->t('Example description.'),
      '#options' => ['One', 'Two', 'Three'],
    ];

    $element['checkboxes2'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Checkboxes Inline'),
      '#description' => $this->t('Example description.'),
      '#options' => ['One', 'Two', 'Three'],
      '#inline' => TRUE,
    ];

    $element['checkboxes3'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Checkboxes Disabled'),
      '#description' => $this->t('Example description.'),
      '#options' => ['One', 'Two', 'Three'],
      '#disabled' => TRUE,
      '#inline' => TRUE,
    ];

    $element['checkbox4'] = [
      '#type' => 'exo_checkboxes',
      '#title' => $this->t('eXo Checkboxes: Stacked'),
      '#description' => $this->t('Example description.'),
      '#description' => $this->t('Example description.'),
      '#options' => ['One', 'Two', 'Three'],
    ];

    $element['checkbox5'] = [
      '#type' => 'exo_checkboxes',
      '#title' => $this->t('eXo Checkboxes: Inline'),
      '#description' => $this->t('Example description.'),
      '#options' => [
        'One',
        'Two',
        'Three',
        'One',
        'Two',
        'Three',
        'One',
        'Two',
        'Three',
        'One',
        'Two',
        'Three',
        'One',
        'Two',
        'Three',
        'One',
        'Two',
        'Three',
        'One',
        'Two',
        'Three',
      ],
      '#default_value' => [1],
      '#exo_style' => 'inline',
    ];

    $element['checkbox6'] = [
      '#type' => 'exo_checkboxes',
      '#title' => $this->t('eXo Checkboxes: Grid'),
      '#description' => $this->t('Example description.'),
      '#options' => [
        'One',
        'Two',
        'Three',
        'One',
        'Two',
        'Three',
        'One',
        'Two',
        'Three',
        'One',
        'Two',
        'Three',
        'One',
        'Two',
        'Three',
        'One',
        'Two',
        'Three',
        'One',
        'Two',
        'Three',
      ],
      '#default_value' => [1],
      '#exo_style' => 'grid',
    ];

    $element['checkbox'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Checkbox'),
      '#required' => TRUE,
    ];

    $element['checkbox2'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Checkbox Checked'),
      '#default_value' => TRUE,
    ];

    return $element;
  }

  /**
   * Demo checkbox.
   */
  protected function buildRadio(FormStateInterface $form_state) {
    $element = [
      '#type' => 'fieldset',
      '#title' => $this->t('Radio'),
      '#open' => FALSE,
    ];

    $element['radios'] = [
      '#type' => 'radios',
      '#title' => $this->t('Radios'),
      '#description' => $this->t('Example description.'),
      '#options' => ['One', 'Two', 'Three'],
    ];

    $element['radios2'] = [
      '#type' => 'radios',
      '#title' => $this->t('Radios Inline'),
      '#description' => $this->t('Example description.'),
      '#options' => ['One', 'Two', 'Three'],
      '#default_value' => 1,
      '#inline' => TRUE,
    ];

    $element['radios3'] = [
      '#type' => 'radios',
      '#title' => $this->t('Radios Disabled'),
      '#description' => $this->t('Example description.'),
      '#options' => ['One', 'Two', 'Three'],
      '#disabled' => TRUE,
      '#inline' => TRUE,
    ];

    $element['radios4'] = [
      '#type' => 'radios',
      '#title' => $this->t('Radios with Default Value'),
      '#description' => $this->t('Example description.'),
      '#options' => ['One', 'Two', 'Three'],
      '#default_value' => 1,
    ];

    $element['radios5'] = [
      '#type' => 'exo_radios',
      '#title' => $this->t('eXo Radios: Stacked'),
      '#description' => $this->t('Example description.'),
      '#options' => ['One', 'Two', 'Three'],
      '#default_value' => 1,
    ];

    $element['radios6'] = [
      '#type' => 'exo_radios',
      '#title' => $this->t('eXo Radios: Inline'),
      '#description' => $this->t('Example description.'),
      '#options' => [
        'One',
        'Two',
        'Three',
        'One',
        'Two',
        'Three',
        'One',
        'Two',
        'Three',
        'One',
        'Two',
        'Three',
        'One',
        'Two',
        'Three',
        'One',
        'Two',
        'Three',
        'One',
        'Two',
        'Three',
      ],
      '#default_value' => 1,
      '#exo_style' => 'inline',
    ];

    $element['radios7'] = [
      '#type' => 'exo_radios',
      '#title' => $this->t('eXo Radios: Grid'),
      '#description' => $this->t('Example description.'),
      '#options' => [
        'One',
        'Two',
        'Three',
        'One',
        'Two',
        'Three',
        'One',
        'Two',
        'Three',
        'One',
        'Two',
        'Three',
        'One',
        'Two',
        'Three',
        'One',
        'Two',
        'Three',
        'One',
        'Two',
        'Three',
      ],
      '#default_value' => 1,
      '#exo_style' => 'grid',
    ];
    return $element;
  }

  /**
   * Demo checkbox.
   */
  protected function buildTextarea(FormStateInterface $form_state) {
    $element = [
      '#type' => 'fieldset',
      '#title' => $this->t('Textarea'),
      '#open' => FALSE,
    ];

    $element['textarea'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Textarea'),
    ];

    $element['textarea_autogrow'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Textarea with Autogrow'),
      '#autogrow' => TRUE,
      '#autogrow_max' => '400px',
    ];

    $element['text_format'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Text with Format'),
      '#format' => 'full_html',
    ];

    return $element;
  }

  /**
   * Demo date.
   */
  protected function buildDate(FormStateInterface $form_state) {
    $element = [
      '#type' => 'fieldset',
      '#title' => $this->t('Date'),
      '#open' => FALSE,
    ];

    $element['date'] = [
      '#type' => 'date',
      '#title' => $this->t('Date'),
    ];

    $element['time'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Time'),
      '#default_value' => NULL,
      '#date_date_element' => 'none',
      '#date_increment' => 1,
      '#date_timezone' => drupal_get_user_timezone(),
    ];

    $element['datetime'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Date & Time'),
      '#default_value' => NULL,
      '#date_increment' => 1,
      '#date_timezone' => drupal_get_user_timezone(),
    ];

    $element['exo_datetime'] = [
      '#type' => 'exo_datetime',
      '#title' => $this->t('UX Date & Time'),
      '#default_value' => NULL,
      '#date_increment' => 1,
      '#date_timezone' => drupal_get_user_timezone(),
    ];

    $element['exo_datetime2'] = [
      '#type' => 'exo_datetime',
      '#title' => $this->t('UX Date & Time'),
      '#default_value' => NULL,
      '#date_increment' => 1,
      '#date_timezone' => drupal_get_user_timezone(),
      '#exo_mode' => 'full',
    ];

    return $element;
  }

  /**
   * Demo other.
   */
  protected function buildOther(FormStateInterface $form_state) {
    $element = [
      '#type' => 'fieldset',
      '#title' => $this->t('Other'),
      '#open' => FALSE,
    ];

    $element['range'] = [
      '#type' => 'range',
      '#title' => $this->t('Range'),
    ];

    return $element;
  }

  /**
   * Callback for both ajax-enabled buttons.
   */
  public static function addOneAjax(array $form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();

    // Go one level up in the form, to the widgets container.
    $element = NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -1));
    return $element['addmore'];
  }

  /**
   * Submit handler for the "add-one-more" button.
   *
   * Increments the max counter and causes a rebuild.
   */
  public static function addOneSubmit(array $form, FormStateInterface $form_state) {
    $count = $form_state->get('count');
    $count = $count + 1;
    $form_state->set('count', $count);
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Display result.
    foreach ($form_state->getValues() as $key => $value) {
      if (is_array($value)) {
        foreach ($value as $i => $v) {
          \Drupal::messenger()->addMessage($key . ':' . $i . ': ' . $v);
        }
      }
      else {
        \Drupal::messenger()->addMessage($key . ': ' . $value);
      }
    }

  }

}
