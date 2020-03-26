<?php

namespace Drupal\exo_form\Plugin\ExoForm;

use Drupal\exo_form\Plugin\ExoFormBase;
use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Render\Markup;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "radio",
 *   label = @Translation("Radio"),
 *   element_types = {
 *     "radio",
 *   }
 * )
 */
class Radio extends ExoFormBase {

  /**
   * {@inheritdoc}
   */
  public function preRender($element) {
    $this->disableWrapper();
    $element = parent::preRender($element);
    $element['#exo_form_element_attributes']['class'][] = 'exo-form-radio';
    $element['#exo_form_element_attributes']['class'][] = 'exo-form-radio-js';
    $element['#attached']['library'][] = 'exo_form/radio';
    if (empty($element['#title'])) {
      $element['#title'] = ' ';
    }
    if (isset($element['#title_display']) && $element['#title_display'] == 'invisible') {
      $element['#title_display'] = 'after';
      $element['#title'] = $this->t('<span class="visually-hidden">@title</span>', ['@title' => $element['#title']]);
    }
    // Drupal expects label to be at field.parent(). By default, we move the
    // label to this location to better support javascript.
    if (isset($element['#title_display']) && $element['#title_display'] == 'after') {
      $element['#title_display'] = 'input';
    }
    if (!empty($element['#title'])) {
      if ($element['#title'] instanceof MarkupInterface) {
        $element['#title'] = Markup::create('<div class="exo-ripple"></div><span class="exo-form-radio-label">' . $element['#title'] . '</span>');
      }
      else {
        $element['#title'] = '<div class="exo-ripple"></div><span class="exo-form-radio-label">' . $element['#title'] . '</span>';
      }
    }
    else {
      $element['#title'] = '<div class="exo-ripple"></div>';
    }
    return $element;
  }

}
