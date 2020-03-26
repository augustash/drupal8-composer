<?php

namespace Drupal\exo_icon\Element;

use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Component\Utility\Html;

/**
 * Provides a one-line text field form element.
 *
 * Usage example:
 * @code
 * $form['icon'] = array(
 *   '#type' => 'exo_icon',
 *   '#title' => $this->t('Icon'),
 *   '#default_value' => $icon_id,
 *   '#required' => TRUE,
 *   '#packages' => ['regular'],
 * );
 * @endcode
 *
 * @FormElement("exo_icon")
 */
class ExoIcon extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#process' => [
        [$class, 'processIcon'],
        [$class, 'processAjaxForm'],
      ],
      '#pre_render' => [
        [$class, 'preRenderIcon'],
      ],
      '#theme' => 'input__textfield',
      '#theme_wrappers' => ['form_element'],
      '#packages' => [],
    ];
  }

  /**
   * Processes an eXo icon form element.
   *
   * @param array $element
   *   The form element to process.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form structure.
   *
   * @return array
   *   The processed element.
   *
   * @see _form_validate()
   */
  public static function processIcon(array &$element, FormStateInterface $form_state, array &$complete_form) {
    $element['#type'] = 'textfield';
    $element['#exo_form_clean'] = TRUE;
    $id = Html::getUniqueId('exo-icon-' . implode('_', $element['#parents']) . '-modal');
    $url = 'api/exo/icon/browser/' . $id;
    $empty_icon = 'regular-ban';
    $element['#wrapper_attributes']['data-exo-icon'] = $id;
    $element['#wrapper_attributes']['class'][] = 'exo-icon-field';
    if (\Drupal::moduleHandler()->moduleExists('exo_form')) {
      $element['#theme_wrappers'][] = 'exo_form_element_container';
    }
    if (!empty($element['#packages'])) {
      $url .= '/' . implode('+', $element['#packages']);
    }
    if (empty($element['#default_value'])) {
      $element['#wrapper_attributes']['class'][] = 'empty';
    }
    $element['#field_suffix']['icon'] = [
      '#prefix' => '<span class="exo-icon-field-widget">',
      '#suffix' => '</span>',
    ];
    $element['#field_suffix']['icon']['preview'] = [
      '#theme' => 'exo_icon',
      '#icon' => !empty($element['#default_value']) ? $element['#default_value'] : $empty_icon,
      '#prefix' => '<span class="exo-icon-field-icon">',
      '#suffix' => '</span>',
    ];
    $element['#field_suffix']['icon']['modal'] = \Drupal::service('exo_modal.generator')->generate(
      $id,
      [
        'trigger' => [
          'text' => t('Browse Icons'),
          'icon' => 'regular-search',
          'icon_only' => TRUE,
        ],
        'modal' => [
          'ajax' => $url,
        ],
      ]
    )->toRenderableTrigger();
    $element['#attached']['drupalSettings']['exoIcon']['field'] = [
      'emptyIcon' => exo_icon()->setIcon($empty_icon)->toMarkup(),
    ];
    $element['#attached']['library'][] = 'exo_modal/ajax';
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    if ($input !== FALSE) {
      if (isset($element['#empty_value']) && $input === (string) $element['#empty_value']) {
        return $element['#empty_value'];
      }
      else {
        return $input;
      }
    }
  }

  /**
   * Prepares an eXo icon render element.
   */
  public static function preRenderIcon($element) {
    $element['#attributes']['type'] = 'text';
    Element::setAttributes($element, ['id', 'name', 'value', 'size']);
    static::setAttributes($element, ['form-exo-icon']);
    $element['#attached']['library'][] = 'exo_icon/field';
    return $element;
  }

}
